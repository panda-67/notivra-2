<?php

namespace App\Domains\Assignments\Controllers;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Http\Controllers\Controller;
use App\Models\AssignmentQuizDevice;
use App\Models\AssignmentQuizQuestion;
use App\Models\AssignmentVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentQuizController extends Controller
{
    public function __construct(
        private readonly AssignmentVerificationService $verificationService,
    ) {}

    public function showQuiz(string $token): Response
    {
        return Inertia::render('Assignments/Quiz/Show', [
            'startToken' => $token,
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_token' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'],
            'device_token' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'],
        ]);

        $result = $this->verificationService->startWithToken(
            $validated['start_token'],
            $validated['device_token'],
        );

        return response()->json([
            'message' => 'Sesi kuis berhasil dimulai.',
            'resume_token' => $result['resume_token'],
            'session' => $this->sessionPayload(
                $result['verification']
            ),
        ], 201);
    }

    public function resume(Request $request): JsonResponse
    {
        $verification = $this->resolveVerification($request);

        return response()->json([
            'session' => $this->sessionPayload($verification),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $verification = $this->resolveVerification($request);

        return response()->json([
            'session' => $this->sessionPayload($verification),
        ]);
    }

    public function answer(Request $request, int $position,): JsonResponse
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'in:A,B,C,D,a,b,c,d'],
        ]);

        $verification = $this->resolveVerification($request);

        $verification = $this->verificationService->saveAnswer(
            $verification,
            $position,
            $validated['answer'],
        );

        return response()->json([
            'message' => 'Jawaban berhasil disimpan.',
            'session' => $this->sessionPayload($verification),
        ]);
    }

    public function recoverPage(int $verification): Response
    {
        $record = AssignmentVerification::query()
            ->findOrFail($verification);

        abort_unless(
            $record->status === 'in_progress',
            404
        );

        return Inertia::render('Assignments/Quiz/Show', [
            'startToken' => null,
            'recoverVerificationId' => $record->id,
        ]);
    }

    public function recover(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verification_id' => [
                'required',
                'integer',
            ],
            'device_token' => [
                'required',
                'string',
                'size:64',
                'regex:/^[a-f0-9]{64}$/',
            ],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $verification = AssignmentVerification::query()
                ->lockForUpdate()
                ->findOrFail($validated['verification_id']);

            if ($verification->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'verification' => 'Sesi kuis tidak sedang berlangsung.',
                ]);
            }

            if (! $verification->quiz_expires_at) {
                throw ValidationException::withMessages([
                    'quiz' => 'Sesi kuis tidak memiliki batas waktu yang valid.',
                ]);
            }

            if (now()->greaterThanOrEqualTo($verification->quiz_expires_at)) {
                throw ValidationException::withMessages([
                    'quiz' => 'Waktu kuis telah habis.',
                ]);
            }

            $deviceTokenHash = hash(
                'sha256',
                $validated['device_token']
            );

            $verification->loadMissing('member.group');

            $member = $verification->member;
            $group = $member?->group;

            if (! $member || ! $group) {
                throw ValidationException::withMessages([
                    'verification' => 'Data peserta kuis tidak valid.',
                ]);
            }

            $device = AssignmentQuizDevice::query()
                ->where('assignment_id', $group->assignment_id)
                ->where('device_token_hash', $deviceTokenHash)
                ->first();

            if (
                ! $device ||
                (int) $device->student_id !== (int) $member->student_id
            ) {
                throw ValidationException::withMessages([
                    'device_token' => 'Perangkat ini tidak terdaftar untuk sesi kuis tersebut.',
                ]);
            }

            $resumeToken = bin2hex(random_bytes(32));
            $now = now();

            $verification->forceFill([
                'resume_token_hash' => hash('sha256', $resumeToken),
                'token_expires_at' => $now->copy()->addHours(24),
                'token_revoked_at' => null,
                'last_seen_at' => $now,
            ])->save();

            $device->forceFill([
                'last_used_at' => $now,
            ])->save();

            return [
                'resume_token' => $resumeToken,
                'verification' => $verification->refresh(),
            ];
        });

        return response()->json([
            'message' => 'Sesi kuis berhasil dipulihkan.',
            'resume_token' => $result['resume_token'],
            'session' => $this->sessionPayload(
                $result['verification']
            ),
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $verification = $this->resolveVerification($request);

        $this->verificationService->complete($verification);

        $verification->refresh();

        return response()->json([
            'message' => 'Kuis berhasil diselesaikan.',
            'status' => $verification->status,
            'completed_at' => $verification->completed_at,
        ]);
    }

    private function resolveVerification(Request $request,): AssignmentVerification
    {
        $token = $request->bearerToken();

        if (! $token) {
            throw ValidationException::withMessages([
                'token' => 'Bearer token diperlukan.',
            ]);
        }

        return $this->verificationService->resume($token);
    }

    private function sessionPayload(AssignmentVerification $verification): array
    {
        $verification->loadMissing([
            'member.student',
            'member.group.assignment',
        ]);


        $assignment = $verification->member?->group?->assignment;

        if (! $assignment) {
            throw ValidationException::withMessages([
                'quiz' => 'Tugas untuk sesi kuis ini tidak ditemukan.',
            ]);
        }

        $questions = AssignmentQuizQuestion::query()
            ->where('assignment_id', $assignment->id)
            ->orderBy('position')
            ->get([
                'id',
                'position',
                'question',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
            ]);

        // Gunakan satu acuan waktu agar perhitungan konsisten.
        $serverTime = now();
        $expiresAt = $verification->quiz_expires_at;
        $student = $verification->member?->student;

        $remainingSeconds = $expiresAt === null
            ? null
            : max(
                0,
                (int) $serverTime->diffInSeconds($expiresAt, false)
            );

        return [
            'status' => $verification->status,
            'answers' => $verification->answers ?? [],
            'started_at' => $verification->started_at,
            'expires_at' => $expiresAt,
            'server_time' => $serverTime,
            'remaining_seconds' => $remainingSeconds,
            'completed_at' => $verification->completed_at,
            'questions' => $questions,
            'student' => [
                'name' => $student?->name,
                'npm' => $student?->student_number,
            ],
        ];
    }
}
