<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
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

        // Create leads in different pipeline stages
        // New leads (unassigned)
        Lead::factory()->count(5)->statusNew()->fromWebsite()->create();

        // Contacted leads (assigned to sales)
        Lead::factory()->count(4)->state([
            'status' => Lead::STATUS_CONTACTED,
            'source' => Lead::SOURCE_REFERRAL,
        ])->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Qualified leads
        Lead::factory()->count(3)->qualified()->create([
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

        // Won leads (ready for conversion)
        Lead::factory()->count(3)->won()->create([
            'assigned_to' => $salesUsers->random()->id,
            'business_id' => $businesses->random()->id,
        ]);

        // Lost leads
        Lead::factory()->count(2)->lost()->create([
            'assigned_to' => $salesUsers->random()->id,
        ]);

        // Create specific named leads for testing
        Lead::factory()->statusNew()->create([
            'name' => 'John Test Lead',
            'email' => 'john.lead@example.com',
            'company' => 'Test Company Inc',
            'source' => Lead::SOURCE_WEBSITE,
        ]);

        /** @var User $firstSalesUser */
        $firstSalesUser = $salesUsers->first();

        Lead::factory()->won()->create([
            'name' => 'Ready To Convert Lead',
            'email' => 'convert.me@example.com',
            'company' => 'Conversion Corp',
            'source' => Lead::SOURCE_REFERRAL,
            'assigned_to' => $firstSalesUser->id,
        ]);
    }
}
