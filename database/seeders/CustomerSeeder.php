<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Get users for assignment
        $salesUsers = User::role(['sales', 'manager', 'admin'])->get();
        $businesses = Business::all();

        // Create active customers (direct, not from leads)
        Customer::factory()->count(5)->active()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Create inactive customers
        Customer::factory()->count(2)->inactive()->create([
            'assigned_to' => $salesUsers->random()->id,
        ]);

        // Create churned customers
        Customer::factory()->count(2)->churned()->create([
            'assigned_to' => $salesUsers->random()->id,
        ]);

        // Create customers converted from leads
        $wonLeads = Lead::where('status', Lead::STATUS_WON)
            ->whereDoesntHave('customer')
            ->take(2)
            ->get();

        foreach ($wonLeads as $lead) {
            Customer::factory()->active()->create([
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'converted_from_lead_id' => $lead->id,
                'assigned_to' => $lead->assigned_to,
                'business_id' => $lead->business_id,
            ]);
        }

        // Create specific named customers for testing
        /** @var User $firstSalesUser */
        $firstSalesUser = $salesUsers->first();
        /** @var Business $firstBusiness */
        $firstBusiness = $businesses->first();

        Customer::factory()->active()->create([
            'name' => 'Premium Customer Corp',
            'email' => 'premium@customer.example.com',
            'company' => 'Premium Corp',
            'assigned_to' => $firstSalesUser->id,
            'business_id' => $firstBusiness->id,
        ]);

        Customer::factory()->active()->create([
            'name' => 'Enterprise Solutions Ltd',
            'email' => 'enterprise@solutions.example.com',
            'company' => 'Enterprise Solutions',
            'assigned_to' => $firstSalesUser->id,
        ]);
    }
}
