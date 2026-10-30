<?php

namespace App\Domains\Admin\Controllers\Assignments;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentParticipant;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IndividualQuizParticipantController extends Controller
{

    public function index(string $slug): Response
    {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        $participants = AssignmentParticipant::query()
            ->with([
                'student:id,student_number,name,program_study',
                'attempt:id,assignment_participant_id,status,started_at,expires_at,completed_at,expired_at',
            ])
            ->where('assignment_id', $assignment->id)
            ->orderBy('id')
            ->get()
            ->map(function (AssignmentParticipant $participant) {
                $attempt = $participant->attempt;

                return [
                    'id' => $participant->id,
                    'status' => $participant->status,
                    'student' => [
                        'student_number' => $participant->student->student_number,
                        'name' => $participant->student->name,
                        'program_study' => $participant->student->program_study,
                    ],
                    'attempt' => $attempt ? [
                        'status' => $attempt->status,
                        'started_at' => $attempt->started_at,
                        'expires_at' => $attempt->expires_at,
                        'completed_at' => $attempt->completed_at,
                        'expired_at' => $attempt->expired_at,
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Assignments/IndividualQuizParticipants/Index', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'duration_minutes' => $assignment->individualQuizSettings->duration_minutes,
            ],
            'participants' => $participants,
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:100',
                Rule::exists('students', 'student_number'),
            ],
        ]);

        $student = Student::query()
            ->where('student_number', trim($validated['student_number']))
            ->firstOrFail();

        $participant = AssignmentParticipant::query()
            ->firstOrNew([
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ]);

        if ($participant->exists && $participant->attempt()->exists()) {
            return response()->json([
                'message' => 'Peserta sudah memiliki sesi kuis. Status pendaftaran tidak diubah.',
            ], 409);
        }

        $participant->status = 'registered';
        $participant->save();

        return response()->json([
            'message' => 'Peserta berhasil didaftarkan.',
            'participant' => [
                'id' => $participant->id,
                'student_number' => $student->student_number,
                'name' => $student->name,
                'status' => $participant->status,
            ],
        ], 201);
    }

    public function updateStatus(
        Request $request,
        string $slug,
        AssignmentParticipant $participant,
    ): JsonResponse {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);
        abort_unless(
            (int) $participant->assignment_id === (int) $assignment->id,
            404,
        );

        $validated = $request->validate([
            'status' => ['required', Rule::in(['registered', 'disabled'])],
        ]);

        if (
            $validated['status'] === 'disabled' &&
            $participant->attempt()->where('status', 'in_progress')->exists()
        ) {
            return response()->json([
                'message' => 'Selesaikan atau reset sesi yang sedang berlangsung sebelum menonaktifkan peserta.',
            ], 409);
        }

        $participant->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Status peserta berhasil diperbarui.',
            'status' => $participant->status,
        ]);
    }
}
