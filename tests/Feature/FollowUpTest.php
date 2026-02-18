<?php

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use App\Services\FollowUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Follow Up Service', function (): void {
    it('adds follow-up to lead', function (): void {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();
        $service = new FollowUpService;

        $followUp = $service->addFollowUp($lead, [
            'follow_up_date' => '2026-03-01',
            'notes' => 'Call to discuss proposal',
            'status' => FollowUp::STATUS_PENDING,
        ], $user);

        expect($followUp->follow_up_date->format('Y-m-d'))->toBe('2026-03-01');
        expect($followUp->notes)->toBe('Call to discuss proposal');
        expect($followUp->status)->toBe(FollowUp::STATUS_PENDING);
        expect($followUp->created_by)->toBe($user->id);
        expect($lead->followUps)->toHaveCount(1);
    });

    it('adds follow-up to customer', function (): void {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $service = new FollowUpService;

        $followUp = $service->addFollowUp($customer, [
            'follow_up_date' => '2026-03-15',
            'notes' => 'Quarterly review',
        ], $user);

        expect($followUp->follow_up_date->format('Y-m-d'))->toBe('2026-03-15');
        expect($customer->followUps)->toHaveCount(1);
    });

    it('updates follow-up', function (): void {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();
        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'notes' => 'Original notes',
        ]);

        $service = new FollowUpService;
        $service->updateFollowUp($followUp, [
            'notes' => 'Updated notes',
            'follow_up_date' => '2026-04-01',
        ]);

        $followUp->refresh();

        expect($followUp->notes)->toBe('Updated notes');
        expect($followUp->follow_up_date->format('Y-m-d'))->toBe('2026-04-01');
    });

    it('marks follow-up as completed', function (): void {
        $lead = Lead::factory()->create();
        $creator = User::factory()->create();
        $completer = User::factory()->create();

        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $creator->id,
            'status' => FollowUp::STATUS_PENDING,
        ]);

        $service = new FollowUpService;
        $service->markCompleted($followUp, $completer);

        $followUp->refresh();

        expect($followUp->status)->toBe(FollowUp::STATUS_COMPLETED);
        expect($followUp->completed_by)->toBe($completer->id);
        expect($followUp->completed_at)->not->toBeNull();
    });

    it('marks follow-up as cancelled', function (): void {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'status' => FollowUp::STATUS_PENDING,
        ]);

        $service = new FollowUpService;
        $service->markCancelled($followUp);

        $followUp->refresh();

        expect($followUp->status)->toBe(FollowUp::STATUS_CANCELLED);
    });

    it('deletes follow-up', function (): void {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
        ]);

        $service = new FollowUpService;
        $service->deleteFollowUp($followUp);

        $this->assertDatabaseMissing('follow_ups', ['id' => $followUp->id]);
    });
});

describe('Follow Up Controller - Leads', function (): void {
    it('shows create form for lead follow-up', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/follow-ups/create");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('FollowUps/Create')
            ->has('parent')
            ->where('parentType', 'lead')
            ->has('statuses')
        );
    });

    it('stores follow-up for lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/follow-ups", [
            'follow_up_date' => '2026-03-01',
            'notes' => 'Test follow-up notes',
            'status' => 'pending',
        ]);

        $response->assertRedirect("/leads/{$lead->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('follow_ups', [
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'notes' => 'Test follow-up notes',
            'created_by' => $user->id,
        ]);
    });

    it('shows edit form for follow-up', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/follow-ups/{$followUp->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('FollowUps/Edit')
            ->has('followUp')
        );
    });

    it('updates follow-up', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'notes' => 'Original notes',
        ]);

        $response = $this->actingAs($user)->put("/leads/{$lead->id}/follow-ups/{$followUp->id}", [
            'follow_up_date' => '2026-04-15',
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect("/leads/{$lead->id}");

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'notes' => 'Updated notes',
        ]);
    });

    it('deletes follow-up', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/leads/{$lead->id}/follow-ups/{$followUp->id}");

        $response->assertRedirect("/leads/{$lead->id}");

        $this->assertDatabaseMissing('follow_ups', ['id' => $followUp->id]);
    });

    it('marks follow-up as completed', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'status' => FollowUp::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/follow-ups/{$followUp->id}/complete");

        $response->assertRedirect("/leads/{$lead->id}");

        $followUp->refresh();
        expect($followUp->status)->toBe(FollowUp::STATUS_COMPLETED);
        expect($followUp->completed_by)->toBe($user->id);
    });
});

describe('Follow Up Controller - Customers', function (): void {
    it('shows create form for customer follow-up', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/follow-ups/create");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('FollowUps/Create')
            ->has('parent')
            ->where('parentType', 'customer')
        );
    });

    it('stores follow-up for customer', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/follow-ups", [
            'follow_up_date' => '2026-03-15',
            'notes' => 'Customer check-in',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('follow_ups', [
            'followable_type' => Customer::class,
            'followable_id' => $customer->id,
            'notes' => 'Customer check-in',
        ]);
    });
});

describe('Follow Up Authorization', function (): void {
    it('denies access to users without manage follow ups permission', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/follow-ups/create");

        $response->assertStatus(403);
    });

    it('allows sales role to manage follow-ups', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/follow-ups/create");

        $response->assertStatus(200);
    });
});

describe('Follow Up Validation', function (): void {
    it('validates required follow_up_date field', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/follow-ups", [
            'notes' => 'Some notes',
        ]);

        $response->assertSessionHasErrors(['follow_up_date']);
    });

    it('validates date format', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/follow-ups", [
            'follow_up_date' => 'invalid-date',
            'notes' => 'Some notes',
        ]);

        $response->assertSessionHasErrors(['follow_up_date']);
    });
});

describe('Dashboard Follow-ups', function (): void {
    it('shows upcoming follow-ups on dashboard', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'follow_up_date' => now()->addDays(2),
            'status' => FollowUp::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('upcomingFollowUps')
            ->has('overdueFollowUps')
            ->has('stats')
        );
    });

    it('shows overdue follow-ups on dashboard', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();
        $followUp = FollowUp::factory()->overdue()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('stats.overdue_follow_ups', 1)
        );
    });
});

describe('Follow Up Model', function (): void {
    it('detects overdue follow-ups', function (): void {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $overdueFollowUp = FollowUp::factory()->overdue()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
        ]);

        $futureFollowUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'follow_up_date' => now()->addDays(5),
        ]);

        expect($overdueFollowUp->isOverdue())->toBeTrue();
        expect($futureFollowUp->isOverdue())->toBeFalse();
    });

    it('detects completed follow-ups not overdue', function (): void {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $completedFollowUp = FollowUp::factory()->completed()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
            'follow_up_date' => now()->subDays(5),
        ]);

        expect($completedFollowUp->isOverdue())->toBeFalse();
    });
});

describe('Cascade Delete', function (): void {
    it('deletes follow-ups when lead is deleted', function (): void {
        $user = User::factory()->create();
        $lead = Lead::factory()->create();

        $followUp = FollowUp::factory()->create([
            'followable_type' => Lead::class,
            'followable_id' => $lead->id,
            'created_by' => $user->id,
        ]);

        $lead->delete();

        $this->assertDatabaseMissing('follow_ups', ['id' => $followUp->id]);
    });

    it('deletes follow-ups when customer is deleted', function (): void {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $followUp = FollowUp::factory()->create([
            'followable_type' => Customer::class,
            'followable_id' => $customer->id,
            'created_by' => $user->id,
        ]);

        $customer->delete();

        $this->assertDatabaseMissing('follow_ups', ['id' => $followUp->id]);
    });
});
