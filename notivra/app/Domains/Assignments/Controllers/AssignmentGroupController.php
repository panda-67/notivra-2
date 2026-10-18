<?php

namespace App\Domains\Assignments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use Inertia\Response;

class AssignmentGroupController extends Controller
{
    public function index(string $slug): Response
    {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $groups = AssignmentGroup::query()
            ->where('assignment_id', $assignment->id)
            ->withCount('members')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn(AssignmentGroup $group) => [
                'group_name' => $group->group_name,
                'reference_code' => $group->reference_code,
                'status' => $group->status,
                'members_count' => $group->members_count,
                'created_at' => $group->created_at?->toIso8601String(),
                'detail_url' => route('assignments.groups.show', [
                    'slug' => $assignment->slug,
                    'reference_code' => $group->reference_code,
                ]),
            ]);

        return inertia('Assignments/Groups/Index', [
            'assignment' => [
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'opens_at' => $assignment->opens_at?->toIso8601String(),
                'due_at' => $assignment->due_at?->toIso8601String(),
            ],
            'groups' => $groups,
        ]);
    }

    public function show(
        string $slug,
        string $reference_code,
    ): Response {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $group = AssignmentGroup::query()
            ->where('assignment_id', $assignment->id)
            ->where('reference_code', $reference_code)
            ->with([
                'members.student',
                'members.verification',
                'submission',
            ])
            ->firstOrFail();

        $now = now();

        $hasIncompleteMembers = $group->members
            ->contains(function ($member) {
                return ! in_array(
                    $member->verification?->status,
                    ['completed', 'expired'],
                    true,
                );
            });

        $isOpen = (
            $assignment->opens_at === null || $assignment->opens_at->lte($now)
        ) && (
            $assignment->due_at === null || $assignment->due_at->gt($now)
        );

        $canUpload = $assignment->status === 'published'
            && $isOpen
            && $group->status === 'ready_for_upload'
            && ! $hasIncompleteMembers
            && $group->submission === null;

        return inertia('Assignments/Groups/Show', [
            'assignment' => [
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'opens_at' => $assignment->opens_at?->toIso8601String(),
                'due_at' => $assignment->due_at?->toIso8601String(),
            ],
            'group' => [
                'group_name' => $group->group_name,
                'reference_code' => $group->reference_code,
                'status' => $group->status,
                'can_upload' => $canUpload,
                'members' => $group->members
                    ->map(function ($member) {
                        $verification = $member->verification;
                        $startToken = $verification?->start_token_encrypted;

                        return [
                            'student_number' => $member->student->student_number,
                            'name' => $member->student->name,
                            'program_study' => $member->student->program_study,
                            'status' => $verification?->status ?? 'pending',
                            'start_url' => $startToken
                                && $verification?->status === 'pending'
                                ? route('assignments.quiz.page', ['token' => $startToken])
                                : null,
                            'resume_url' => $verification?->status === 'in_progress'
                                ? route('assignments.quiz.recover.page', [
                                    'verification' => $verification->id,
                                ])
                                : null,
                        ];
                    })
                    ->values(),
                'submission' => $group->submission
                    ? [
                        'original_filename' => $group->submission->original_filename,
                        'file_size' => $group->submission->file_size,
                        'submitted_at' => $group->submission->submitted_at?->toIso8601String(),
                        'duplicate_status' => $group->submission->duplicate_status,
                    ]
                    : null,
            ],
        ]);
    }
}
