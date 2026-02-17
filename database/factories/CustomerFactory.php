<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->optional(0.7)->company(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->randomElement(['US', 'UK', 'CA', 'AU']),
            'status' => Customer::STATUS_ACTIVE,
            'notes' => fake()->optional(0.3)->paragraph(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Customer::STATUS_ACTIVE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Customer::STATUS_INACTIVE,
        ]);
    }

    public function churned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Customer::STATUS_CHURNED,
        ]);
    }
}
