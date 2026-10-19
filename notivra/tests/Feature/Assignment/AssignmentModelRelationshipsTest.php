<?php

namespace Tests\Feature\Assignment;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_group_member_verification_and_submission_relations_work(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
        ]);

        $student = Student::factory()->create();

        $member = AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        $verification = AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
        ]);

        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'uploaded_by_member_id' => $member->id,
        ]);

        $this->assertTrue(
            $assignment->groups()->whereKey($group->id)->exists()
        );

        $this->assertTrue(
            $group->members()->whereKey($member->id)->exists()
        );

        $this->assertSame(
            $student->id,
            $member->student->id
        );

        $this->assertSame(
            $verification->id,
            $member->verification->id
        );

        $this->assertSame(
            $member->id,
            $verification->member->id
        );

        $this->assertSame(
            $submission->id,
            $group->submission->id
        );

        $this->assertSame(
            $assignment->id,
            $submission->assignment->id
        );

        $this->assertSame(
            $member->id,
            $submission->uploadedByMember->id
        );
    }
}
