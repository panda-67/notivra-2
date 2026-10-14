<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'slug' => Str::slug($name),

            'icon' => 'briefcase',

            'title' => [
                'en' => fake()->sentence(3),
                'id' => fake()->sentence(3),
            ],

            'summary' => [
                'en' => fake()->sentence(10),
                'id' => fake()->sentence(10),
            ],

            'description' => [
                'en' => fake()->paragraph(),
                'id' => fake()->paragraph(),
            ],

            'features' => [
                'en' => [
                    fake()->sentence(4),
                    fake()->sentence(4),
                ],
                'id' => [
                    fake()->sentence(4),
                    fake()->sentence(4),
                ],
            ],
        ];
    }
}
