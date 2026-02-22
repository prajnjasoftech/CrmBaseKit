<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $services = Service::where('status', 'active')->get();
        $users = User::all();

        if ($customers->isEmpty() || $services->isEmpty()) {
            $this->command->warn('No customers or services found. Please run CustomerSeeder and ServiceSeeder first.');

            return;
        }

        // Create 2-4 projects for each customer
        foreach ($customers as $customer) {
            $projectCount = rand(2, 4);

            for ($i = 0; $i < $projectCount; $i++) {
                Project::factory()
                    ->forCustomer($customer)
                    ->forService($services->random())
                    ->state([
                        'assigned_to' => $users->isNotEmpty() ? $users->random()->id : null,
                        'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
                    ])
                    ->create();
            }
        }

        $this->command->info('Projects seeded successfully.');
    }
}
