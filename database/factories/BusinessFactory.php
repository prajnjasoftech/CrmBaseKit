<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'registration_number' => fake()->unique()->numerify('BUS-######'),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->randomElement(['US', 'UK', 'CA', 'AU']),
            'industry' => fake()->randomElement([
                'Technology',
                'Healthcare',
                'Finance',
                'Retail',
                'Manufacturing',
                'Education',
                'Real Estate',
                'Consulting',
            ]),
            'status' => fake()->randomElement(['active', 'inactive', 'pending']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
