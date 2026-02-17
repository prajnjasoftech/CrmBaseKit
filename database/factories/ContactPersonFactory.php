<?php

namespace Database\Factories;

use App\Models\ContactPerson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactPerson>
 */
class ContactPersonFactory extends Factory
{
    protected $model = ContactPerson::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->optional(0.9)->safeEmail(),
            'mobile' => fake()->optional(0.8)->phoneNumber(),
            'designation' => fake()->optional(0.7)->randomElement([
                'CEO',
                'CTO',
                'CFO',
                'Director',
                'Manager',
                'Team Lead',
                'Account Manager',
                'Procurement Officer',
            ]),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
