<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_number' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'program_study' => fake()->randomElement([
                'Biologi',
                'Informatika',
                'Pendidikan',
            ]),
            'institution' => 'Universitas Contoh',
            'status' => 'active',
        ];
    }
}
