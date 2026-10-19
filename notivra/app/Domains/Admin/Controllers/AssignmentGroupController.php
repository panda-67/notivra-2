<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Assignments\Services\AssignmentGroupService;
use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentQuizDevice;
use App\Models\AssignmentVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AssignmentGroupController extends Controller
{
    public function index(string $slug): Response
    {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $groups = AssignmentGroup::query()
            ->where('assignment_id', $assignment->id)
            ->with('submission')
            ->withCount('members')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString()
            ->through(function (AssignmentGroup $group) use ($assignment) {
                $members = $group->members()
                    ->with('verification')
                    ->get();

                return [
                    'group_name' => $group->group_name,
                    'reference_code' => $group->reference_code,
                    'status' => $group->status,
                    'members_count' => $group->members_count,
                    'verification_summary' => [
                        'completed' => $members->filter(
                            fn($member) => in_array(
                                $member->verification?->status,
                                ['completed', 'expired'],
                                true
                            )
                        )->count(),
                        'incomplete' => $members->filter(
                            fn($member) => ! in_array(
                                $member->verification?->status,
                                ['completed', 'expired'],
                                true
                            )
                        )->count(),
                    ],
                    'upload_bypassed_at' =>
                    $group->upload_bypassed_at?->toIso8601String(),
                    'submission' => $group->submission ? [
                        'submitted_at' =>
                        $group->submission->submitted_at?->toIso8601String(),
                        'duplicate_status' =>
                        $group->submission->duplicate_status,
                    ] : null,
                    'detail_url' => route(
                        'admin.assignments.groups.show',
                        [
                            'slug' => $assignment->slug,
                            'reference_code' => $group->reference_code,
                        ]
                    ),
                ];
            });

        return inertia('Admin/Assignments/Groups/Index', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'status' => $assignment->status,
                'opens_at' => $assignment->opens_at?->toIso8601String(),
                'due_at' => $assignment->due_at?->toIso8601String(),
            ],
            'groups' => $groups,
        ]);
    }

    public function show(
        string $slug,
        string $reference_code
    ): Response {
        $assignment = Assignment::query()
            ->where('slug', $slug)
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

        $studentIds = $group->members
            ->pluck('student_id')
            ->filter()
            ->unique()
            ->values();

        $registeredStudentIds = AssignmentQuizDevice::query()
            ->where('assignment_id', $assignment->id)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();

        return inertia('Admin/Assignments/Groups/Show', [
            'assignment' => [
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'status' => $assignment->status,
                'opens_at' => $assignment->opens_at?->toIso8601String(),
                'due_at' => $assignment->due_at?->toIso8601String(),
            ],
            'group' => [
                'group_name' => $group->group_name,
                'reference_code' => $group->reference_code,
                'status' => $group->status,
                'upload_bypassed_at' =>
                $group->upload_bypassed_at?->toIso8601String(),
                'submission' => $group->submission ? [
                    'original_filename' =>
                    $group->submission->original_filename,
                    'file_size' => $group->submission->file_size,
                    'submitted_at' =>
                    $group->submission->submitted_at?->toIso8601String(),
                    'duplicate_status' =>
                    $group->submission->duplicate_status,
                ] : null,
                'members' => $group->members->map(function ($member) use ($registeredStudentIds) {
                    $verification = $member->verification;

                    return [
                        'id' => $member->id,
                        'student_number' =>
                        $member->student->student_number,
                        'name' => $member->student->name,
                        'program_study' =>
                        $member->student->program_study,
                        'verification_status' =>
                        $verification?->status ?? 'pending',
                        'has_verification' => $verification !== null,
                        'has_registered_device' => in_array(
                            $member->student_id,
                            $registeredStudentIds,
                            true
                        ),
                    ];
                })->values(),
            ],
        ]);
    }

    public function bypassUpload(
        Request $request,
        string $slug,
        string $reference_code,
        AssignmentGroupService $service
    ): RedirectResponse {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $group = $this->findGroup($assignment, $reference_code);

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $service->bypassUpload($group, $user);

        return back()->with(
            'success',
            'Upload wizard berhasil di-bypass untuk grup ini.'
        );
    }

    public function resetMemberSession(
        string $slug,
        string $reference_code,
        int $member,
        AssignmentVerificationService $service
    ): RedirectResponse {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $group = $this->findGroup($assignment, $reference_code);

        $groupMember = $group->members()
            ->with('verification')
            ->whereKey($member)
            ->firstOrFail();

        $verification = $groupMember->verification;

        abort_unless(
            $verification instanceof AssignmentVerification,
            404,
            'Sesi verifikasi anggota tidak ditemukan.'
        );

        $service->resetSession($verification);

        return back()->with(
            'success',
            'Sesi kuis anggota berhasil di-reset.'
        );
    }

    public function removeMemberDevice(
        string $slug,
        string $reference_code,
        int $member,
        AssignmentVerificationService $service
    ): RedirectResponse {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $group = $this->findGroup($assignment, $reference_code);

        $groupMember = $group->members()
            ->whereKey($member)
            ->firstOrFail();

        $deleted = $service->removeDevice(
            $assignment->id,
            $groupMember->student_id
        );

        return back()->with(
            'success',
            $deleted > 0
                ? 'Registrasi perangkat kuis berhasil dihapus.'
                : 'Tidak ada registrasi perangkat untuk dihapus.'
        );
    }

    private function findGroup(
        Assignment $assignment,
        string $referenceCode
    ): AssignmentGroup {
        return AssignmentGroup::query()
            ->where('assignment_id', $assignment->id)
            ->where('reference_code', $referenceCode)
            ->firstOrFail();
    }
}
