<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->optional()->dateTimeBetween('-3 months', '+1 month');
        $endDate = $startDate ? fake()->optional()->dateTimeBetween($startDate, '+1 year') : null;

        return [
            'name' => fake()->randomElement([
                'Website Redesign',
                'Mobile App Development',
                'Cloud Migration',
                'Security Audit',
                'Data Analytics Dashboard',
                'E-commerce Platform',
                'CRM Implementation',
                'API Integration',
            ]).' '.fake()->unique()->numerify('##'),
            'description' => fake()->optional()->paragraph(),
            'customer_id' => Customer::factory(),
            'service_id' => Service::factory(),
            'status' => fake()->randomElement([
                Project::STATUS_PENDING,
                Project::STATUS_IN_PROGRESS,
                Project::STATUS_ON_HOLD,
                Project::STATUS_COMPLETED,
                Project::STATUS_CANCELLED,
            ]),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budget' => fake()->optional()->randomFloat(2, 1000, 100000),
            'assigned_to' => fake()->optional()->randomElement(User::pluck('id')->toArray() ?: [null]),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_PENDING,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_IN_PROGRESS,
        ]);
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_ON_HOLD,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_COMPLETED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_CANCELLED,
        ]);
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }

    public function forService(Service $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $service->id,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }
}
