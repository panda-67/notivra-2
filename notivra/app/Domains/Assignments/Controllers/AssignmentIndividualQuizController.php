<?php

namespace App\Domains\Assignments\Controllers;

use App\Domains\Assignments\Services\AssignmentIndividualQuizService;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentParticipant;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentIndividualQuizController extends Controller
{
    public function __construct(
        private readonly AssignmentIndividualQuizService $quizService,
    ) {}

    public function show(string $slug): Response
    {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        return Inertia::render('Assignments/IndividualQuiz/Show', [
            'assignment' => [
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'opens_at' => $assignment->opens_at,
                'due_at' => $assignment->due_at,
            ],
        ]);
    }

    public function verifyStudent(
        Request $request,
        string $slug,
    ): JsonResponse {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:32'],
        ]);

        $student = Student::query()
            ->where('student_number', trim($validated['student_number']))
            ->first();

        if (! $student) {
            throw ValidationException::withMessages([
                'student_number' => 'NIM tidak ditemukan.',
            ]);
        }

        if ($student->status !== 'active') {
            throw ValidationException::withMessages([
                'student_number' => 'Mahasiswa dengan NIM tersebut tidak aktif.',
            ]);
        }

        $participant = AssignmentParticipant::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $participant) {
            throw ValidationException::withMessages([
                'student_number' => 'NIM tersebut belum terdaftar sebagai peserta kuis.',
            ]);
        }

        if ($participant->status !== 'registered') {
            throw ValidationException::withMessages([
                'student_number' => 'Peserta tersebut sedang dinonaktifkan.',
            ]);
        }

        return response()->json([
            'student' => [
                'student_number' => $student->student_number,
                'name' => $student->name,
                'program_study' => $student->program_study,
            ],
            'assignment' => [
                'title' => $assignment->title,
                'duration_minutes' =>
                $assignment->individualQuizSettings->duration_minutes,
            ],
        ]);
    }

    public function start(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:100'],
            'device_token' => [
                'required',
                'string',
                'size:64',
                'regex:/^[a-f0-9]{64}$/',
            ],
        ]);

        $result = $this->quizService->start(
            $slug,
            $validated['student_number'],
            $validated['device_token'],
            $request,
        );

        return response()->json([
            'message' => 'Sesi kuis berhasil dibuka.',
            'resume_token' => $result['resume_token'],
            'session' => $this->quizService->payload($result['attempt']),
        ], 201);
    }

    public function session(Request $request, string $slug): JsonResponse
    {
        $attempt = $this->resolveAttempt($request);

        return response()->json([
            'session' => $this->quizService->payload($attempt),
        ]);
    }

    public function answer(Request $request, string $slug, string $position,): JsonResponse
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'in:A,B,C,D,a,b,c,d'],
        ]);

        $attempt = $this->resolveAttempt($request);

        $attempt = $this->quizService->saveAnswer(
            $attempt,
            (int) $position,
            $validated['answer'],
        );

        return response()->json([
            'message' => 'Jawaban tersimpan.',
            'session' => $this->quizService->payload($attempt),
        ]);
    }

    public function complete(Request $request, string $slug): JsonResponse
    {
        $attempt = $this->resolveAttempt($request);
        $result = $this->quizService->complete($attempt);

        return response()->json([
            'message' => match ($result['status']) {
                'completed' => 'Kuis berhasil diselesaikan.',
                'expired' => 'Waktu kuis telah habis.',
                default => 'Sesi kuis tidak dapat diselesaikan.',
            },
            'result' => $result,
        ]);
    }

    private function resolveAttempt(Request $request)
    {
        $token = $request->bearerToken();
        $deviceToken = $request->header('X-Device-Token');

        if (! $token) {
            throw ValidationException::withMessages([
                'token' => 'Bearer token diperlukan.',
            ]);
        }

        if (
            ! is_string($deviceToken) ||
            ! preg_match('/^[a-f0-9]{64}$/', $deviceToken)
        ) {
            throw ValidationException::withMessages([
                'device_token' => 'Token perangkat tidak valid.',
            ]);
        }

        return $this->quizService->resolve($token, $deviceToken);
    }
}
