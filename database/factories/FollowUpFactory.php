<?php

namespace Database\Factories;

use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FollowUp>
 */
class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'follow_up_date' => fake()->dateTimeBetween('now', '+30 days'),
            'notes' => fake()->optional(0.8)->paragraph(),
            'status' => FollowUp::STATUS_PENDING,
            'created_by' => User::factory(),
            'completed_by' => null,
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FollowUp::STATUS_PENDING,
            'completed_by' => null,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FollowUp::STATUS_COMPLETED,
            'completed_by' => User::factory(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FollowUp::STATUS_CANCELLED,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'follow_up_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => FollowUp::STATUS_PENDING,
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'follow_up_date' => now(),
            'status' => FollowUp::STATUS_PENDING,
        ]);
    }
}
