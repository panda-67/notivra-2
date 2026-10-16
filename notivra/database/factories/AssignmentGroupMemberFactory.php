<?php

namespace Database\Factories;

use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentGroupMember>
 */
class AssignmentGroupMemberFactory extends Factory
{
    public function definition(): array
    {
        $group = AssignmentGroup::factory()->create();

        return [
            'assignment_id' => $group->assignment_id,
            'assignment_group_id' => $group->id,
            'student_id' => Student::factory(),
        ];
    }
}
