<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentVerificationGroupIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(
        Assignment $assignment,
        AssignmentGroup $group,
        string $status,
    ): AssignmentVerification {
        $student = Student::factory()->create();

        $member = AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        return AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
            'status' => $status,
        ]);
    }

    public function test_completing_last_member_makes_group_ready_for_upload(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $this->createMember($assignment, $group, 'completed');

        $pendingVerification = $this->createMember(
            $assignment,
            $group,
            'in_progress',
        );

        app(AssignmentVerificationService::class)
            ->complete($pendingVerification);

        $this->assertSame(
            'ready_for_upload',
            $group->fresh()->status,
        );
    }

    public function test_group_stays_in_verification_when_another_member_is_pending(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $verification = $this->createMember(
            $assignment,
            $group,
            'in_progress',
        );

        $this->createMember($assignment, $group, 'pending');

        app(AssignmentVerificationService::class)
            ->complete($verification);

        $this->assertSame(
            'verification_in_progress',
            $group->fresh()->status,
        );
    }
}
