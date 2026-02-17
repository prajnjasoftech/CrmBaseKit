<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\ContactPerson;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        // Get sales users for assignment
        $salesUsers = User::role(['sales', 'manager', 'admin'])->get();
        $businesses = Business::all();

        // Create individual leads in different pipeline stages
        // New leads (unassigned)
        Lead::factory()->count(3)->individual()->statusNew()->fromWebsite()->create();

        // Business leads (new, unassigned)
        $businessLeads = Lead::factory()->count(2)->business()->statusNew()->fromWebsite()->create();
        foreach ($businessLeads as $lead) {
            // Add 1-3 contact persons for each business lead
            ContactPerson::factory()
                ->count(fake()->numberBetween(1, 3))
                ->sequence(fn ($sequence) => ['is_primary' => $sequence->index === 0])
                ->create([
                    'contactable_type' => Lead::class,
                    'contactable_id' => $lead->id,
                ]);
        }

        // Contacted leads (assigned to sales)
        Lead::factory()->count(2)->individual()->state([
            'status' => Lead::STATUS_CONTACTED,
            'source' => Lead::SOURCE_REFERRAL,
        ])->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Contacted business leads
        $contactedBusinessLeads = Lead::factory()->count(2)->business()->state([
            'status' => Lead::STATUS_CONTACTED,
            'source' => Lead::SOURCE_REFERRAL,
        ])->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);
        foreach ($contactedBusinessLeads as $lead) {
            ContactPerson::factory()
                ->count(fake()->numberBetween(1, 2))
                ->sequence(fn ($sequence) => ['is_primary' => $sequence->index === 0])
                ->create([
                    'contactable_type' => Lead::class,
                    'contactable_id' => $lead->id,
                ]);
        }

        // Qualified leads
        Lead::factory()->count(3)->individual()->qualified()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Proposal stage leads
        Lead::factory()->count(3)->state([
            'status' => Lead::STATUS_PROPOSAL,
        ])->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Negotiation stage leads
        Lead::factory()->count(2)->state([
            'status' => Lead::STATUS_NEGOTIATION,
        ])->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Won leads (ready for conversion) - individual
        Lead::factory()->count(2)->individual()->won()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Won business lead with contact persons (ready for conversion)
        $wonBusinessLead = Lead::factory()->business()->won()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);
        ContactPerson::factory()->primary()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $wonBusinessLead->id,
            'name' => 'Primary Contact Person',
            'designation' => 'CEO',
        ]);
        ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $wonBusinessLead->id,
            'name' => 'Secondary Contact Person',
            'designation' => 'CFO',
        ]);

        // Lost leads
        Lead::factory()->count(2)->lost()->create([
            'assigned_to' => $salesUsers->random()->id,
        ]);

        // Create specific named leads for testing
        Lead::factory()->individual()->statusNew()->create([
            'name' => 'John Test Lead',
            'email' => 'john.lead@example.com',
            'company' => 'Test Company Inc',
            'source' => Lead::SOURCE_WEBSITE,
        ]);

        /** @var User $firstSalesUser */
        $firstSalesUser = $salesUsers->first();

        Lead::factory()->individual()->won()->create([
            'name' => 'Ready To Convert Lead',
            'email' => 'convert.me@example.com',
            'company' => 'Conversion Corp',
            'source' => Lead::SOURCE_REFERRAL,
            'assigned_to' => $firstSalesUser->id,
        ]);

        // Create a business lead ready for conversion
        $businessConvertLead = Lead::factory()->business()->won()->create([
            'name' => 'Business Ready To Convert',
            'email' => 'business.convert@example.com',
            'source' => Lead::SOURCE_REFERRAL,
            'assigned_to' => $firstSalesUser->id,
        ]);
        ContactPerson::factory()->primary()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $businessConvertLead->id,
            'name' => 'John Business Contact',
            'email' => 'john.contact@businessconvert.example.com',
            'designation' => 'Procurement Manager',
        ]);
    }
}
