<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentGroupService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentGroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(
        Assignment $assignment,
        AssignmentGroup $group,
        string $verificationStatus,
    ): AssignmentGroupMember {
        $student = Student::factory()->create();

        $member = AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
            'status' => $verificationStatus,
        ]);

        return $member;
    }

    public function test_group_becomes_ready_when_all_members_are_verified(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $this->createMember($assignment, $group, 'completed');
        $this->createMember($assignment, $group, 'completed');

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('ready_for_upload', $result->status);
    }

    public function test_group_is_not_ready_if_any_member_is_unverified(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'draft',
        ]);

        $this->createMember($assignment, $group, 'completed');
        $this->createMember($assignment, $group, 'pending');

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('verification_in_progress', $result->status);
    }

    public function test_empty_group_cannot_be_ready_for_upload(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'ready_for_upload',
        ]);

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('draft', $result->status);
    }

    public function test_submitted_group_status_is_not_overwritten(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'submitted',
        ]);

        $this->createMember($assignment, $group, 'completed');

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('submitted', $result->status);
    }
}
