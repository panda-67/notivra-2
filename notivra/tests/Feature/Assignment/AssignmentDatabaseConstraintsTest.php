<?php

namespace Tests\Feature\Assignment;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentDatabaseConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_number_must_be_unique(): void
    {
        Student::factory()->create([
            'student_number' => '20260001',
        ]);

        $this->expectException(QueryException::class);

        Student::factory()->create([
            'student_number' => '20260001',
        ]);
    }

    public function test_student_cannot_join_two_groups_in_the_same_assignment(): void
    {
        $assignment = Assignment::factory()->create();

        $firstGroup = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
        ]);

        $secondGroup = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
        ]);

        $student = Student::factory()->create();

        AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $firstGroup->id,
            'student_id' => $student->id,
        ]);

        $this->expectException(QueryException::class);

        AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $secondGroup->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_group_cannot_have_two_submissions(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
        ]);

        $this->expectException(QueryException::class);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
        ]);
    }
}
