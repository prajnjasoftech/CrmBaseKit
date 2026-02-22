<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Web Development',
                'Mobile App Development',
                'Cloud Services',
                'Data Analytics',
                'IT Consulting',
                'Cybersecurity',
                'DevOps Services',
                'UI/UX Design',
                'Digital Marketing',
                'Technical Support',
            ]).' '.fake()->unique()->numerify('##'),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
