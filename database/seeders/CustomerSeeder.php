<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\ContactPerson;
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

        // Create active individual customers (direct, not from leads)
        Customer::factory()->count(3)->individual()->active()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Create active business customers with contact persons
        $businessCustomers = Customer::factory()->count(2)->business()->active()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);
        foreach ($businessCustomers as $customer) {
            ContactPerson::factory()
                ->count(fake()->numberBetween(1, 3))
                ->sequence(fn ($sequence) => ['is_primary' => $sequence->index === 0])
                ->create([
                    'contactable_type' => Customer::class,
                    'contactable_id' => $customer->id,
                ]);
        }

        // Create inactive customers
        Customer::factory()->count(2)->individual()->inactive()->create([
            'assigned_to' => $salesUsers->random()->id,
        ]);

        // Create churned customers
        Customer::factory()->count(2)->individual()->churned()->create([
            'assigned_to' => $salesUsers->random()->id,
        ]);

        // Create customers converted from leads
        $wonLeads = Lead::where('status', Lead::STATUS_WON)
            ->whereDoesntHave('customer')
            ->take(2)
            ->get();

        foreach ($wonLeads as $lead) {
            $customer = Customer::factory()->active()->create([
                'name' => $lead->name,
                'entity_type' => $lead->entity_type,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'converted_from_lead_id' => $lead->id,
                'assigned_to' => $lead->assigned_to,
                'business_id' => $lead->business_id,
            ]);

            // Copy contact persons from lead to customer
            foreach ($lead->contactPeople as $contact) {
                ContactPerson::factory()->create([
                    'contactable_type' => Customer::class,
                    'contactable_id' => $customer->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'mobile' => $contact->mobile,
                    'designation' => $contact->designation,
                    'is_primary' => $contact->is_primary,
                ]);
            }
        }

        // Create specific named customers for testing
        /** @var User $firstSalesUser */
        $firstSalesUser = $salesUsers->first();
        /** @var Business $firstBusiness */
        $firstBusiness = $businesses->first();

        Customer::factory()->individual()->active()->create([
            'name' => 'Premium Customer Corp',
            'email' => 'premium@customer.example.com',
            'company' => 'Premium Corp',
            'assigned_to' => $firstSalesUser->id,
            'business_id' => $firstBusiness->id,
        ]);

        // Create a business customer with multiple contacts
        $enterpriseCustomer = Customer::factory()->business()->active()->create([
            'name' => 'Enterprise Solutions Ltd',
            'email' => 'enterprise@solutions.example.com',
            'assigned_to' => $firstSalesUser->id,
        ]);
        ContactPerson::factory()->primary()->create([
            'contactable_type' => Customer::class,
            'contactable_id' => $enterpriseCustomer->id,
            'name' => 'Jane Enterprise CEO',
            'email' => 'jane.ceo@enterprise.example.com',
            'designation' => 'CEO',
        ]);
        ContactPerson::factory()->create([
            'contactable_type' => Customer::class,
            'contactable_id' => $enterpriseCustomer->id,
            'name' => 'Bob Enterprise CTO',
            'email' => 'bob.cto@enterprise.example.com',
            'designation' => 'CTO',
        ]);
    }
}
