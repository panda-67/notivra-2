<?php

namespace App\Domains\Admin\Controllers\Assignments;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentFileController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'assignment_id' => [
                'nullable',
                'integer',
                Rule::exists('assignments', 'id'),
            ],
            'duplicate_status' => [
                'nullable',
                'string',
                Rule::in([
                    'pending',
                    'clear',
                    'flagged',
                    'under_review',
                    'confirmed_duplicate',
                ]),
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $submissions = AssignmentSubmission::query()
            ->with([
                'assignment:id,title,slug',
                'group:id,assignment_id,group_name,reference_code,status',
                'group.members.student:id,name,student_number,program_study',
                'uploadedByMember.student:id,name,student_number',
            ])
            ->when(
                ! empty($filters['assignment_id']),
                fn($query) => $query->where(
                    'assignment_id',
                    $filters['assignment_id']
                )
            )
            ->when(
                ! empty($filters['duplicate_status']),
                fn($query) => $query->where(
                    'duplicate_status',
                    $filters['duplicate_status']
                )
            )
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);

                $query->where(function ($query) use ($search) {
                    $query->where(
                        'original_filename',
                        'like',
                        "%{$search}%"
                    )->orWhereHas('group', function ($groupQuery) use ($search) {
                        $groupQuery
                            ->where('group_name', 'like', "%{$search}%")
                            ->orWhere('reference_code', 'like', "%{$search}%");
                    })->orWhereHas('group.members.student', function ($studentQuery) use ($search) {
                        $studentQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('submitted_at')
            ->paginate(25)
            ->withQueryString()
            ->through(function (AssignmentSubmission $submission) {
                $group = $submission->group;

                return [
                    'id' => $submission->id,
                    'assignment' => [
                        'id' => $submission->assignment?->id,
                        'title' => $submission->assignment?->title,
                        'slug' => $submission->assignment?->slug,
                    ],
                    'group' => [
                        'id' => $group?->id,
                        'name' => $group?->group_name,
                        'reference_code' => $group?->reference_code,
                        'status' => $group?->status,
                        'members' => $group?->members->map(
                            fn($member) => [
                                'name' => $member->student?->name,
                                'student_number' =>
                                $member->student?->student_number,
                                'program_study' =>
                                $member->student?->program_study,
                            ]
                        )->values(),
                    ],
                    'filename' => $submission->original_filename,
                    'mime_type' => $submission->mime_type,
                    'file_size' => $submission->file_size,
                    'sha256' => $submission->sha256,
                    'content_fingerprint' => $submission->content_fingerprint,
                    'structure_fingerprint' => $submission->structure_fingerprint,
                    'metadata_fingerprint' => $submission->metadata_fingerprint,
                    'duplicate_status' => $submission->duplicate_status,
                    'metadata' => $submission->metadata,
                    'duplicate_findings' => $submission->duplicate_findings,
                    'submitted_at' => $submission->submitted_at?->toIso8601String(),
                    'download_url' => route(
                        'admin.assignments.files.download',
                        ['submission' => $submission->id]
                    ),
                ];
            });

        return Inertia::render(
            'Admin/Assignments/Files/Index',
            [
                'submissions' => $submissions,
                'assignments' => Assignment::query()
                    ->orderBy('title')
                    ->get(['id', 'title']),
                'filters' => [
                    'assignment_id' => $filters['assignment_id'] ?? '',
                    'duplicate_status' => $filters['duplicate_status'] ?? '',
                    'search' => $filters['search'] ?? '',
                ],
            ]
        );
    }

    public function download(
        AssignmentSubmission $submission
    ): StreamedResponse {
        $disk = Storage::disk('local');

        abort_unless(
            $submission->file_path !== ''
                && $disk->exists($submission->file_path),
            404,
            'Berkas tugas tidak ditemukan.'
        );

        $filename = basename(
            $submission->original_filename ?: 'submission.docx'
        );

        return $disk->download($submission->file_path, $filename);
    }
}
