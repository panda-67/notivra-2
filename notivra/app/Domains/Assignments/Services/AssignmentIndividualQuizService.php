<?php

namespace App\Domains\Assignments\Services;

use App\Models\Assignment;
use App\Models\AssignmentParticipant;
use App\Models\AssignmentQuizAttempt;
use App\Models\AssignmentQuizQuestion;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentIndividualQuizService
{
    public function start(
        string $slug,
        string $studentNumber,
        string $deviceToken,
        Request $request,
    ): array {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        $this->assertAccessWindow($assignment);

        if (! $assignment->individualQuizSettings) {
            abort(404, 'Kuis individual belum dikonfigurasi.');
        }

        $student = Student::query()
            ->where('student_number', trim($studentNumber))
            ->first();

        if (! $student) {
            throw ValidationException::withMessages([
                'student_number' => 'NIM tidak ditemukan.',
            ]);
        }

        $participant = AssignmentParticipant::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->where('status', 'registered')
            ->first();

        if (! $participant) {
            throw ValidationException::withMessages([
                'student_number' => 'Peserta belum terdaftar atau telah dinonaktifkan.',
            ]);
        }

        $deviceHash = hash('sha256', $deviceToken);

        $result = DB::transaction(function () use (
            $assignment,
            $participant,
            $deviceHash,
            $request,
        ) {
            $participant = AssignmentParticipant::query()
                ->whereKey($participant->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($participant->status !== 'registered') {
                throw ValidationException::withMessages([
                    'participant' => 'Pendaftaran peserta tidak aktif.',
                ]);
            }

            $attempt = AssignmentQuizAttempt::query()
                ->where('assignment_participant_id', $participant->id)
                ->lockForUpdate()
                ->first();

            $now = now();

            if ($attempt && in_array(
                $attempt->status,
                ['completed', 'expired', 'invalid'],
                true,
            )) {
                throw ValidationException::withMessages([
                    'quiz' => 'Sesi kuis sudah berakhir dan tidak dapat dimulai ulang.',
                ]);
            }

            if ($attempt?->status === 'in_progress') {
                if (
                    ! $attempt->device_token_hash ||
                    ! hash_equals($attempt->device_token_hash, $deviceHash)
                ) {
                    throw ValidationException::withMessages([
                        'device_token' => 'Sesi kuis terikat pada perangkat lain.',
                    ]);
                }

                if (
                    $attempt->expires_at &&
                    $now->greaterThanOrEqualTo($attempt->expires_at)
                ) {
                    $attempt->forceFill([
                        'status' => 'expired',
                        'expired_at' => $now,
                    ])->save();

                    return ['expired' => true];
                }
            }

            $resumeToken = bin2hex(random_bytes(32));

            if (! $attempt) {
                $attempt = new AssignmentQuizAttempt([
                    'assignment_participant_id' => $participant->id,
                    'status' => 'pending',
                    'answers' => [],
                ]);
            }

            if ($attempt->status === 'pending') {
                if (empty($attempt->question_ids)) {
                    $attempt->question_ids = $this->selectQuestionIds($assignment);
                }

                $startedAt = $now;
                $durationExpiry = $startedAt->copy()->addMinutes(
                    (int) $assignment->individualQuizSettings->duration_minutes
                );

                $dueAt = $assignment->due_at;
                $expiresAt = $dueAt && $dueAt->lessThan($durationExpiry)
                    ? $dueAt
                    : $durationExpiry;

                $attempt->started_at = $startedAt;
                $attempt->expires_at = $expiresAt;
                $attempt->status = 'in_progress';
                $attempt->answers = [];
            }

            $attempt->forceFill([
                'resume_token_hash' => hash('sha256', $resumeToken),
                'token_expires_at' => $assignment->due_at &&
                    $assignment->due_at->lessThan($now->copy()->addHours(24))
                    ? $assignment->due_at
                    : $now->copy()->addHours(24),
                'token_revoked_at' => null,
                'device_token_hash' => $deviceHash,
                'last_ip_address' => $request->ip(),
                'last_user_agent' => substr(
                    (string) $request->userAgent(),
                    0,
                    4000,
                ),
                'last_seen_at' => $now,
            ])->save();

            return [
                'expired' => false,
                'resume_token' => $resumeToken,
                'attempt' => $attempt->refresh(),
            ];
        });

        if ($result['expired']) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu kuis telah habis.',
            ]);
        }

        return $result;
    }

    public function resolve(
        string $token,
        string $deviceToken,
    ): AssignmentQuizAttempt {
        if ($token === '') {
            throw $this->invalidToken();
        }

        $attempt = AssignmentQuizAttempt::query()
            ->where('resume_token_hash', hash('sha256', $token))
            ->with('participant.assignment.individualQuizSettings', 'participant.student')
            ->first();

        if (
            ! $attempt ||
            $attempt->token_revoked_at !== null ||
            ! $attempt->token_expires_at ||
            now()->greaterThanOrEqualTo($attempt->token_expires_at)
        ) {
            throw $this->invalidToken();
        }

        $deviceHash = hash('sha256', $deviceToken);

        if (
            ! $attempt->device_token_hash ||
            ! hash_equals($attempt->device_token_hash, $deviceHash)
        ) {
            throw ValidationException::withMessages([
                'device_token' => 'Perangkat tidak cocok dengan sesi kuis.',
            ]);
        }

        if (
            $attempt->status === 'in_progress' &&
            $attempt->expires_at &&
            now()->greaterThanOrEqualTo($attempt->expires_at)
        ) {
            $attempt->forceFill([
                'status' => 'expired',
                'expired_at' => now(),
            ])->save();

            $attempt->refresh();
        } elseif ($attempt->status === 'in_progress') {
            $attempt->forceFill([
                'last_seen_at' => now(),
            ])->save();
        }

        return $attempt->refresh();
    }

    public function saveAnswer(AssignmentQuizAttempt $attempt, int $position, string $answer,): AssignmentQuizAttempt
    {
        $answer = strtoupper($answer);

        if (! in_array($answer, ['A', 'B', 'C', 'D'], true)) {
            throw ValidationException::withMessages([
                'answer' => 'Jawaban harus A, B, C, atau D.',
            ]);
        }

        $result = DB::transaction(function () use (
            $attempt,
            $position,
            $answer,
        ) {
            $locked = AssignmentQuizAttempt::query()
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($locked->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'quiz' => 'Sesi kuis tidak sedang berlangsung.',
                ]);
            }

            $now = now();

            if (
                $locked->expires_at &&
                $now->greaterThanOrEqualTo($locked->expires_at)
            ) {
                $locked->forceFill([
                    'status' => 'expired',
                    'expired_at' => $now,
                ])->save();

                return [
                    'expired' => true,
                    'attempt' => $locked->refresh(),
                ];
            }

            $assignmentId = $locked->participant()
                ->value('assignment_id');

            $questionExists = AssignmentQuizQuestion::query()
                ->where('assignment_id', $assignmentId)
                ->whereIn('id', $locked->question_ids ?? [])
                ->where('position', $position)
                ->exists();

            if (! $questionExists) {
                throw ValidationException::withMessages([
                    'position' => 'Nomor soal tidak valid atau tidak termasuk dalam sesi kuis ini.',
                ]);
            }

            $answers = $locked->answers ?? [];

            // Peserta boleh mengganti jawaban selama waktu kuis aktif.
            $answers[(string) $position] = $answer;

            $locked->forceFill([
                'answers' => $answers,
                'last_seen_at' => $now,
            ])->save();

            return [
                'expired' => false,
                'attempt' => $locked->refresh(),
            ];
        });

        if ($result['expired']) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu kuis telah habis.',
            ]);
        }

        return $result['attempt'];
    }

    public function complete(
        AssignmentQuizAttempt $attempt,
    ): array {
        $locked = DB::transaction(function () use ($attempt) {
            $locked = AssignmentQuizAttempt::query()
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($locked->status === 'in_progress') {
                $now = now();

                if (
                    $locked->expires_at &&
                    $now->greaterThanOrEqualTo($locked->expires_at)
                ) {
                    $locked->forceFill([
                        'status' => 'expired',
                        'expired_at' => $now,
                    ])->save();
                } else {
                    $locked->forceFill([
                        'status' => 'completed',
                        'completed_at' => $now,
                    ])->save();
                }
            }

            return $locked->refresh();
        });

        return [
            'status' => $locked->status,
            'completed_at' => $locked->completed_at,
            'expired_at' => $locked->expired_at,
            ...$this->score($locked),
        ];
    }

    public function payload(
        AssignmentQuizAttempt $attempt,
    ): array {
        $attempt->loadMissing(
            'participant.assignment.individualQuizSettings',
            'participant.student',
        );

        $assignment = $attempt->participant->assignment;

        $questions = $this->selectedQuestions(
            $attempt,
            $assignment->id,
            [
                'id',
                'position',
                'question',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
            ],
        )->map(fn($question) => [
            'id' => $question->id,
            'position' => $question->position,
            'question' => $question->question,
            'option_a' => $question->option_a,
            'option_b' => $question->option_b,
            'option_c' => $question->option_c,
            'option_d' => $question->option_d,
        ])->values();

        $remaining = $attempt->expires_at
            ? max(0, (int) now()->diffInSeconds($attempt->expires_at, false))
            : 0;

        return [
            'assignment' => [
                'title' => $assignment->title,
                'due_at' => $assignment->due_at,
            ],
            'student' => [
                'student_number' => $attempt->participant->student->student_number,
                'name' => $attempt->participant->student->name,
            ],
            'status' => $attempt->status,
            'answers' => $attempt->answers ?? [],
            'started_at' => $attempt->started_at,
            'expires_at' => $attempt->expires_at,
            'server_time' => now(),
            'remaining_seconds' => $remaining,
            'completed_at' => $attempt->completed_at,
            'expired_at' => $attempt->expired_at,
            'questions' => $questions,
            'result' => in_array($attempt->status, ['completed', 'expired'], true)
                ? $this->score($attempt)
                : null,
        ];
    }

    private function selectQuestionIds(Assignment $assignment): array
    {
        $ranges = [
            [1, 12],
            [13, 24],
            [25, 36],
            [37, 48],
            [49, 60],
        ];

        $questionIds = collect();

        foreach ($ranges as [$start, $end]) {
            $ids = AssignmentQuizQuestion::query()
                ->where('assignment_id', $assignment->id)
                ->whereBetween('position', [$start, $end])
                ->inRandomOrder()
                ->limit(4)
                ->pluck('id');

            if ($ids->count() !== 4) {
                throw ValidationException::withMessages([
                    'quiz' => sprintf(
                        'Bank soal belum mencukupi. Kelompok posisi %d–%d harus memiliki minimal 4 soal.',
                        $start,
                        $end,
                    ),
                ]);
            }

            $questionIds = $questionIds->concat($ids);
        }

        return $questionIds
            ->shuffle()
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();
    }

    private function selectedQuestions(
        AssignmentQuizAttempt $attempt,
        int $assignmentId,
        array $columns,
    ) {
        $questionIds = array_map(
            'intval',
            $attempt->question_ids ?? [],
        );

        if (count($questionIds) !== 20 || count(array_unique($questionIds)) !== 20) {
            throw ValidationException::withMessages([
                'quiz' => 'Snapshot soal pada sesi kuis tidak valid.',
            ]);
        }

        $questions = AssignmentQuizQuestion::query()
            ->where('assignment_id', $assignmentId)
            ->whereIn('id', $questionIds)
            ->get($columns);

        if ($questions->count() !== 20) {
            throw ValidationException::withMessages([
                'quiz' => 'Sebagian soal pada sesi kuis tidak ditemukan.',
            ]);
        }

        // Pertahankan urutan acak yang tersimpan pada question_ids.
        $order = array_flip($questionIds);

        return $questions
            ->sortBy(fn($question) => $order[(int) $question->id])
            ->values();
    }

    private function score(AssignmentQuizAttempt $attempt): array
    {
        $attempt->loadMissing('participant');

        $questions = $this->selectedQuestions(
            $attempt,
            $attempt->participant->assignment_id,
            [
                'id',
                'position',
                'correct_option',
            ],
        );

        $answers = $attempt->answers ?? [];
        $correct = 0;

        foreach ($questions as $question) {
            if (
                strtoupper((string) ($answers[(string) $question->position] ?? ''))
                === strtoupper($question->correct_option)
            ) {
                $correct++;
            }
        }

        $total = $questions->count();

        return [
            'correct_answers' => $correct,
            'total_questions' => $total,
            'score_percentage' => $total > 0
                ? round(($correct / $total) * 100, 2)
                : 0,
        ];
    }

    private function assertAccessWindow(Assignment $assignment): void
    {
        $now = now();

        if ($assignment->status !== 'published') {
            throw ValidationException::withMessages([
                'assignment' => 'Tugas belum dipublikasikan atau sudah ditutup.',
            ]);
        }

        if ($assignment->opens_at && $now->lessThan($assignment->opens_at)) {
            throw ValidationException::withMessages([
                'assignment' => 'Kuis belum dibuka.',
            ]);
        }

        if ($assignment->due_at && $now->greaterThanOrEqualTo($assignment->due_at)) {
            throw ValidationException::withMessages([
                'assignment' => 'Batas waktu pengerjaan telah berakhir.',
            ]);
        }
    }

    private function assertAttemptActive(
        AssignmentQuizAttempt $attempt,
    ): void {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'quiz' => 'Sesi kuis tidak sedang berlangsung.',
            ]);
        }

        if (
            $attempt->expires_at &&
            now()->greaterThanOrEqualTo($attempt->expires_at)
        ) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu kuis telah habis.',
            ]);
        }
    }

    private function invalidToken(): ValidationException
    {
        return ValidationException::withMessages([
            'token' => 'Token sesi tidak valid atau sudah kedaluwarsa.',
        ]);
    }
}
