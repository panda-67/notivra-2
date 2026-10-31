<?php

namespace App\Domains\Admin\Controllers\Assignments;

use App\Domains\Admin\Exports\IndividualQuizParticipantsTemplateExport;
use App\Domains\Admin\Imports\IndividualQuizParticipantsImport;
use App\Domains\Assignments\Services\IndividualQuizParticipantImportService;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentParticipant;
use App\Models\AssignmentQuizAttempt;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                'attempt:id,assignment_participant_id,status,started_at,expires_at,completed_at,expired_at,device_token_hash,last_seen_at',
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
                        'device_bound' => ! empty($attempt->device_token_hash),
                        'last_seen_at' => $attempt->last_seen_at,
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Assignments/IndividualQuizParticipants/Index', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'duration_minutes' => $assignment->individualQuizSettings->duration_minutes,
                'opens_at' => $assignment->opens_at,
                'due_at' => $assignment->due_at,
            ],
            'participants' => $participants,
        ]);
    }

    public function updateSchedule(Request $request, string $slug): JsonResponse
    {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        $validated = $request->validate([
            'opens_at' => ['required', 'date'],
            'due_at' => [
                'required',
                'date',
                'after:opens_at',
            ],
        ], [
            'opens_at.required' => 'Waktu mulai wajib diisi.',
            'opens_at.date' => 'Format waktu mulai tidak valid.',
            'due_at.required' => 'Waktu berakhir wajib diisi.',
            'due_at.date' => 'Format waktu berakhir tidak valid.',
            'due_at.after' => 'Waktu berakhir harus setelah waktu mulai.',
        ]);

        $assignment->update([
            'opens_at' => $validated['opens_at'],
            'due_at' => $validated['due_at'],
        ]);

        return response()->json([
            'message' => 'Jadwal kuis berhasil diperbarui.',
            'schedule' => [
                'opens_at' => $assignment->fresh()->opens_at,
                'due_at' => $assignment->fresh()->due_at,
            ],
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

    public function resetSession(
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

        DB::transaction(function () use ($participant) {
            $lockedParticipant = AssignmentParticipant::query()
                ->whereKey($participant->id)
                ->lockForUpdate()
                ->firstOrFail();

            AssignmentQuizAttempt::query()
                ->where('assignment_participant_id', $lockedParticipant->id)
                ->lockForUpdate()
                ->delete();
        });

        return response()->json([
            'message' => 'Sesi kuis peserta berhasil direset. Peserta dapat memulai kuis dari awal.',
        ]);
    }

    public function removeDevice(
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

        $attempt = DB::transaction(function () use ($participant) {
            $lockedParticipant = AssignmentParticipant::query()
                ->whereKey($participant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $attempt = AssignmentQuizAttempt::query()
                ->where('assignment_participant_id', $lockedParticipant->id)
                ->lockForUpdate()
                ->first();

            if (! $attempt) {
                return null;
            }

            $attempt->forceFill([
                'device_token_hash' => null,
                'resume_token_hash' => null,
                'token_expires_at' => null,
                'token_revoked_at' => now(),
                'last_ip_address' => null,
                'last_user_agent' => null,
            ])->save();

            return $attempt;
        });

        if (! $attempt) {
            return response()->json([
                'message' => 'Peserta belum memiliki sesi kuis.',
            ], 404);
        }

        return response()->json([
            'message' => 'Ikatan perangkat berhasil dihapus. Peserta dapat menggunakan perangkat baru.',
        ]);
    }

    public function import(
        Request $request,
        string $slug,
        IndividualQuizParticipantImportService $service,
    ): JsonResponse {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ]);

        $import = new IndividualQuizParticipantsImport(
            assignmentId: $assignment->id,
            service: $service,
        );

        Excel::import($import, $validated['file']);

        $summary = $import->summary;

        return response()->json([
            'message' => 'Import peserta selesai diproses.',
            'summary' => $summary,
        ]);
    }

    public function downloadTemplate(string $slug): BinaryFileResponse
    {
        $assignment = Assignment::query()
            ->with('individualQuizSettings')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($assignment->individualQuizSettings, 404);

        return Excel::download(
            new IndividualQuizParticipantsTemplateExport(),
            'template-peserta-kuis-individu.xlsx',
        );
    }
}
