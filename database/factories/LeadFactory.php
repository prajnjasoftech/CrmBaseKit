<?php

namespace Database\Factories;

use App\Enums\EntityType;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
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
            'entity_type' => EntityType::Individual,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->optional(0.7)->company(),
            'source' => fake()->randomElement([
                Lead::SOURCE_WEBSITE,
                Lead::SOURCE_REFERRAL,
                Lead::SOURCE_ADVERTISEMENT,
                Lead::SOURCE_COLD_CALL,
                Lead::SOURCE_SOCIAL_MEDIA,
                Lead::SOURCE_OTHER,
            ]),
            'status' => fake()->randomElement([
                Lead::STATUS_NEW,
                Lead::STATUS_CONTACTED,
                Lead::STATUS_QUALIFIED,
                Lead::STATUS_PROPOSAL,
                Lead::STATUS_NEGOTIATION,
            ]),
            'notes' => fake()->optional(0.5)->paragraph(),
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => EntityType::Individual,
            'name' => fake()->name(),
            'company' => fake()->optional(0.7)->company(),
        ]);
    }

    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => EntityType::Business,
            'name' => fake()->company(),
            'company' => null,
        ]);
    }

    public function statusNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_NEW,
        ]);
    }

    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_QUALIFIED,
        ]);
    }

    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_WON,
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Lead::STATUS_LOST,
        ]);
    }

    public function fromWebsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => Lead::SOURCE_WEBSITE,
        ]);
    }

    public function fromReferral(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => Lead::SOURCE_REFERRAL,
        ]);
    }
}
