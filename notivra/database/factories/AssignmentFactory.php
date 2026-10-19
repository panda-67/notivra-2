<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(8)),
            'description' => fake()->paragraph(),
            'instructions' => fake()->paragraphs(2, true),
            'opens_at' => now()->subDay(),
            'due_at' => now()->addDays(7),
            'published_at' => now(),
            'status' => 'published',
            'created_by' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn() => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
