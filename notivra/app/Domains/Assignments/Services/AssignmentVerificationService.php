<?php

namespace App\Domains\Assignments\Services;

use App\Models\Assignment;
use App\Models\AssignmentQuizDevice;
use App\Models\AssignmentQuizQuestion;
use App\Models\AssignmentVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentVerificationService
{
    private const QUIZ_DURATION_MINUTES = 50;

    public function issueStartToken(AssignmentVerification $verification): string
    {
        if ($verification->status !== 'pending') {
            throw ValidationException::withMessages([
                'verification' => 'Start Quiz hanya tersedia untuk sesi yang belum dimulai.',
            ]);
        }

        $token = bin2hex(random_bytes(32));

        $verification->forceFill([
            'start_token_hash' => hash('sha256', $token),
            'start_token_encrypted' => $token,
            'start_token_used_at' => null,
        ])->save();

        return $token;
    }

    /**
     * Issue a resume token without starting the quiz timer.
     */
    public function issueResumeToken(AssignmentVerification $verification, int $ttlHours = 24,): string
    {
        if ($ttlHours < 1) {
            throw new \InvalidArgumentException(
                'Token lifetime must be at least one hour.'
            );
        }

        if (in_array(
            $verification->status,
            ['completed', 'invalid', 'expired'],
            true
        )) {
            throw ValidationException::withMessages([
                'verification' => 'Sesi verifikasi ini tidak dapat dilanjutkan.',
            ]);
        }

        $token = bin2hex(random_bytes(32));

        $verification->forceFill([
            'status' => 'in_progress',
            'resume_token_hash' => hash('sha256', $token),
            'token_expires_at' => now()->addHours($ttlHours),
            'token_revoked_at' => null,
            'last_seen_at' => now(),
        ])->save();

        return $token;
    }

    /**
     * Resolve a valid resume token.
     *
     * The quiz deadline is independent of the resume-token deadline.
     */
    public function resume(string $token): AssignmentVerification
    {
        if ($token === '') {
            throw $this->invalidTokenException();
        }

        $verification = AssignmentVerification::query()
            ->where('resume_token_hash', hash('sha256', $token))
            ->first();

        if (! $verification || $verification->token_revoked_at !== null) {
            throw $this->invalidTokenException();
        }

        if (
            $verification->token_expires_at === null ||
            $verification->token_expires_at->isPast()
        ) {
            if (! in_array(
                $verification->status,
                ['completed', 'invalid', 'expired'],
                true
            )) {
                $verification->forceFill([
                    'status' => 'expired',
                    'token_revoked_at' => now(),
                ])->save();
            }

            throw ValidationException::withMessages([
                'token' => 'Token untuk melanjutkan sesi telah kedaluwarsa.',
            ]);
        }

        $result = DB::transaction(function () use ($verification) {
            $locked = AssignmentVerification::query()
                ->with('member.group')
                ->lockForUpdate()
                ->findOrFail($verification->id);

            if ($locked->status !== 'in_progress') {
                return ['expired' => false, 'verification' => null];
            }

            if (
                $locked->quiz_expires_at !== null &&
                now()->greaterThanOrEqualTo($locked->quiz_expires_at)
            ) {
                $this->expireLocked($locked);

                return ['expired' => true, 'verification' => null];
            }

            $locked->forceFill([
                'last_seen_at' => now(),
            ])->save();

            return ['expired' => false, 'verification' => $locked];
        });

        logger()->debug('Quiz resume transaction result', [
            'verification_id' => $verification->id,
            'expired' => $result['expired'],
            'status_in_database' => AssignmentVerification::query()
                ->whereKey($verification->id)
                ->value('status'),
        ]);

        if ($result['expired']) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu kuis telah habis. Sesi kuis sudah diselesaikan.',
            ]);
        }

        if (! $result['verification']) {
            throw ValidationException::withMessages([
                'token' => 'Sesi verifikasi ini tidak dapat dilanjutkan.',
            ]);
        }

        return $result['verification'];
    }

    /**
     * Save an answer permanently.
     *
     * $position is the question position, not its database ID.
     * $answer must be A, B, C, or D.
     */
    public function saveAnswer(AssignmentVerification $verification, int $position, string $answer,): AssignmentVerification
    {
        $answer = strtoupper($answer);

        if ($position < 1 || $position > 10) {
            throw ValidationException::withMessages([
                'position' => 'Nomor soal tidak valid.',
            ]);
        }

        if (! in_array($answer, ['A', 'B', 'C', 'D'], true)) {
            throw ValidationException::withMessages([
                'answer' => 'Jawaban harus berupa A, B, C, atau D.',
            ]);
        }

        $result = DB::transaction(function () use (
            $verification,
            $position,
            $answer
        ) {
            $locked = AssignmentVerification::query()
                ->with('member.group.assignment')
                ->lockForUpdate()
                ->findOrFail($verification->id);

            if ($locked->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'quiz' => 'Sesi kuis tidak sedang berlangsung.',
                ]);
            }

            $group = $locked->member?->group;
            $assignment = $group?->assignment;

            if (! $assignment) {
                throw ValidationException::withMessages([
                    'quiz' => 'Tugas untuk sesi kuis ini tidak ditemukan.',
                ]);
            }

            $question = AssignmentQuizQuestion::query()
                ->where('assignment_id', $assignment->id)
                ->where('position', $position)
                ->first();

            if (! $question) {
                throw ValidationException::withMessages([
                    'position' => 'Soal tidak ditemukan untuk tugas ini.',
                ]);
            }

            // If the deadline has passed, finalize and commit first.
            if (
                $locked->quiz_expires_at !== null &&
                now()->greaterThanOrEqualTo($locked->quiz_expires_at)
            ) {
                $this->finalizeLocked($locked);

                return ['expired' => true, 'verification' => null];
            }

            $answers = $locked->answers ?? [];
            $key = (string) $position;

            if (array_key_exists($key, $answers)) {
                throw ValidationException::withMessages([
                    'answer' => 'Jawaban soal ini sudah tersimpan dan tidak dapat diubah.',
                ]);
            }

            $now = now();

            $answers[$key] = $answer;

            $updates = [
                'answers' => $answers,
                'last_seen_at' => $now,
            ];

            if ($locked->started_at === null) {
                $updates['started_at'] = $now;
                $updates['quiz_expires_at'] = $now
                    ->copy()
                    ->addMinutes(self::QUIZ_DURATION_MINUTES);
            }

            $locked->forceFill($updates)->save();

            return [
                'expired' => false,
                'verification' => $locked->refresh(),
            ];
        });

        if ($result['expired']) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu kuis telah habis. Jawaban tidak disimpan.',
            ]);
        }

        return $result['verification'];
    }

    /**
     * Complete a quiz manually.
     * Unanswered questions remain unanswered and count as incorrect.
     */
    public function complete(AssignmentVerification $verification): void
    {
        DB::transaction(function () use ($verification) {
            $locked = AssignmentVerification::query()
                ->with('member.group')
                ->lockForUpdate()
                ->findOrFail($verification->id);

            if ($locked->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'verification' => 'Sesi kuis tidak sedang berlangsung.',
                ]);
            }

            if (
                $locked->quiz_expires_at !== null &&
                now()->greaterThanOrEqualTo($locked->quiz_expires_at)
            ) {
                $this->expireLocked($locked);

                return;
            }

            $this->finalizeLocked($locked);
        });
    }

    public function startWithToken(string $token, string $deviceToken,): array
    {
        if ($token === '') {
            throw $this->invalidTokenException();
        }

        return DB::transaction(function () use ($token, $deviceToken) {
            $verification = AssignmentVerification::query()
                ->where('start_token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (
                ! $verification ||
                $verification->status !== 'pending' ||
                $verification->start_token_used_at !== null
            ) {
                throw ValidationException::withMessages([
                    'start_token' => 'Token mulai kuis tidak valid atau sudah digunakan.',
                ]);
            }

            $verification->loadMissing('member.group');

            $this->registerDevice($verification, $deviceToken);

            $resumeToken = bin2hex(random_bytes(32));
            $now = now();

            $verification->forceFill([
                'status' => 'in_progress',
                'start_token_used_at' => $now,
                'resume_token_hash' => hash('sha256', $resumeToken),
                'token_expires_at' => $now->copy()->addHours(24),
                'token_revoked_at' => null,
                'last_seen_at' => $now,
            ])->save();

            return [
                'verification' => $verification->refresh(),
                'resume_token' => $resumeToken,
            ];
        });
    }

    private function registerDevice(AssignmentVerification $verification, string $deviceToken,): void
    {
        $member = $verification->member;
        $group = $member?->group;

        if (! $member || ! $group) {
            throw ValidationException::withMessages([
                'device_token' => 'Data mahasiswa untuk sesi kuis tidak valid.',
            ]);
        }

        $assignmentId = (int) $group->assignment_id;
        $studentId = (int) $member->student_id;
        $deviceTokenHash = hash('sha256', $deviceToken);

        /*
     * Kunci baris assignment untuk mencegah dua transaksi
     * mendaftarkan hash yang sama kepada mahasiswa berbeda
     * secara bersamaan pada assignment tersebut.
     */
        Assignment::query()
            ->whereKey($assignmentId)
            ->lockForUpdate()
            ->firstOrFail();

        $registeredDevice = AssignmentQuizDevice::query()
            ->where('assignment_id', $assignmentId)
            ->where('device_token_hash', $deviceTokenHash)
            ->first();

        if ($registeredDevice && (int) $registeredDevice->student_id !== $studentId) {
            throw ValidationException::withMessages([
                'device_token' => 'Perangkat ini sudah pernah digunakan untuk menjawab soal atas nama mahasiswa lain.',
            ]);
        }

        if ($registeredDevice) {
            $registeredDevice->forceFill([
                'last_used_at' => now(),
            ])->save();

            return;
        }

        AssignmentQuizDevice::query()->create([
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'device_token_hash' => $deviceTokenHash,
            'first_used_at' => now(),
            'last_used_at' => now(),
        ]);
    }

    public function expireDueQuizzes(): int
    {
        $expiredCount = 0;

        AssignmentVerification::query()
            ->where('status', 'in_progress')
            ->whereNotNull('quiz_expires_at')
            ->where('quiz_expires_at', '<=', now())
            ->select('id')
            ->chunkById(200, function ($verifications) use (&$expiredCount) {
                foreach ($verifications as $verification) {
                    $expired = DB::transaction(function () use ($verification) {
                        $locked = AssignmentVerification::query()
                            ->with('member.group')
                            ->lockForUpdate()
                            ->find($verification->id);

                        if (! $locked || $locked->status !== 'in_progress') {
                            return false;
                        }

                        if (
                            $locked->quiz_expires_at === null ||
                            $locked->quiz_expires_at->isFuture()
                        ) {
                            return false;
                        }

                        $this->expireLocked($locked);

                        return true;
                    });

                    if ($expired) {
                        $expiredCount++;
                    }
                }
            });

        return $expiredCount;
    }

    public function resetSession(AssignmentVerification $verification): AssignmentVerification
    {
        return DB::transaction(function () use ($verification) {
            $locked = AssignmentVerification::query()
                ->lockForUpdate()
                ->findOrFail($verification->id);

            if (! in_array($locked->status, [
                'pending',
                'in_progress',
                'expired',
            ], true)) {
                throw ValidationException::withMessages([
                    'member' => 'Sesi dengan status ini tidak dapat di-reset.',
                ]);
            }

            $startToken = bin2hex(random_bytes(32));

            $locked->forceFill([
                'status' => 'pending',
                'answers' => [],
                'resume_token_hash' => null,
                'token_expires_at' => null,
                'token_revoked_at' => null,
                'start_token_hash' => hash('sha256', $startToken),
                'start_token_encrypted' => $startToken,
                'start_token_used_at' => null,
                'started_at' => null,
                'quiz_expires_at' => null,
                'last_seen_at' => null,
                'completed_at' => null,
            ])->save();

            return $locked->refresh();
        });
    }

    public function removeDevice(int $assignmentId, int $studentId): int
    {
        return DB::transaction(function () use ($assignmentId, $studentId) {
            return AssignmentQuizDevice::query()
                ->where('assignment_id', $assignmentId)
                ->where('student_id', $studentId)
                ->delete();
        });
    }

    /**
     * Finalize a session while its row lock is held.
     */
    private function finalizeLocked(AssignmentVerification $verification): void
    {
        if ($verification->status !== 'in_progress') {
            return;
        }

        $verification->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'token_revoked_at' => now(),
        ])->saveOrFail();

        $verification->refresh();

        $group = $verification->member?->group;

        if ($group) {
            app(AssignmentGroupService::class)
                ->refreshUploadReadiness($group);
        }
    }

    private function expireLocked(AssignmentVerification $verification,): void
    {
        if ($verification->status !== 'in_progress') {
            return;
        }

        $verification->forceFill([
            'status' => 'expired',
            'token_revoked_at' => now(),
        ])->saveOrFail();

        $group = $verification->member?->group;

        if ($group) {
            app(AssignmentGroupService::class)
                ->refreshUploadReadiness($group);
        }
    }

    public function revokeResumeToken(AssignmentVerification $verification,): void
    {
        $verification->forceFill([
            'token_revoked_at' => now(),
        ])->save();
    }

    private function invalidTokenException(): ValidationException
    {
        return ValidationException::withMessages([
            'token' => 'Token untuk melanjutkan sesi tidak valid atau sudah tidak tersedia.',
        ]);
    }
}
