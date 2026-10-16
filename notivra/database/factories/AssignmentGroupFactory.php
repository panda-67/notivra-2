<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AssignmentGroup>
 */
class AssignmentGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'group_name' => 'Kelompok ' . fake()->unique()->numberBetween(1, 999999),
            'reference_code' => Str::upper(Str::random(12)),
            'status' => 'draft',
            'submitted_at' => null,
        ];
    }
}
