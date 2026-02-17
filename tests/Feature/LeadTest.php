<?php

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Lead Index', function (): void {
    it('shows leads list to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Lead::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/leads');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Index')
            ->has('leads.data', 3)
            ->has('statuses')
            ->has('sources')
        );
    });

    it('allows user role to view leads', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/leads');

        $response->assertStatus(200);
    });

    it('denies access to unauthenticated user', function (): void {
        $response = $this->get('/leads');

        $response->assertRedirect('/login');
    });
});

describe('Lead Create', function (): void {
    it('shows create form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->get('/leads/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Create')
            ->has('statuses')
            ->has('sources')
            ->has('users')
            ->has('businesses')
        );
    });

    it('stores lead with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'company' => 'Acme Inc',
            'source' => 'website',
            'status' => 'new',
            'notes' => 'Interested in our product',
        ]);

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'source' => 'website',
            'status' => 'new',
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', []);

        $response->assertSessionHasErrors(['name', 'source']);
    });

    it('validates source enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'Test Lead',
            'source' => 'invalid_source',
        ]);

        $response->assertSessionHasErrors(['source']);
    });

    it('denies create to user role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'Test Lead',
            'source' => 'website',
        ]);

        $response->assertStatus(403);
    });
});

describe('Lead Show', function (): void {
    it('shows lead details to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Show')
            ->has('lead')
            ->where('lead.id', $lead->id)
        );
    });
});

describe('Lead Edit', function (): void {
    it('shows edit form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Edit')
            ->has('lead')
            ->has('statuses')
            ->has('sources')
        );
    });

    it('updates lead with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->put("/leads/{$lead->id}", [
            'name' => 'Updated Lead',
            'email' => 'updated@example.com',
            'source' => 'referral',
            'status' => 'qualified',
        ]);

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'name' => 'Updated Lead',
            'status' => 'qualified',
        ]);
    });
});

describe('Lead Delete', function (): void {
    it('deletes lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->delete("/leads/{$lead->id}");

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    });

    it('denies delete to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->delete("/leads/{$lead->id}");

        $response->assertStatus(403);
    });
});

describe('Lead Conversion', function (): void {
    it('shows convert form for won lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->won()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/convert");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Convert')
            ->has('lead')
            ->has('customerStatuses')
        );
    });

    it('converts lead to customer', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->won()->create([
            'name' => 'Won Lead',
            'email' => 'won@example.com',
        ]);

        $response = $this->actingAs($user)->post("/leads/{$lead->id}/convert", [
            'name' => 'Won Lead',
            'email' => 'won@example.com',
            'status' => 'active',
        ]);

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Won Lead',
            'email' => 'won@example.com',
            'converted_from_lead_id' => $lead->id,
        ]);
    });

    it('denies conversion of non-won lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->statusNew()->create();

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/convert");

        $response->assertStatus(403);
    });

    it('denies conversion of already converted lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->won()->create();
        Customer::factory()->create(['converted_from_lead_id' => $lead->id]);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}/convert");

        $response->assertStatus(403);
    });
});

describe('Lead Authorization', function (): void {
    it('allows admin full access', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $lead = Lead::factory()->create();

        $this->actingAs($user)->get('/leads')->assertStatus(200);
        $this->actingAs($user)->get('/leads/create')->assertStatus(200);
        $this->actingAs($user)->get("/leads/{$lead->id}")->assertStatus(200);
        $this->actingAs($user)->get("/leads/{$lead->id}/edit")->assertStatus(200);
    });

    it('allows manager full access except delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $lead = Lead::factory()->create();

        $this->actingAs($user)->get('/leads')->assertStatus(200);
        $this->actingAs($user)->get('/leads/create')->assertStatus(200);
        $this->actingAs($user)->delete("/leads/{$lead->id}")->assertStatus(403);
    });

    it('allows sales to create and edit but not delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $this->actingAs($user)->get('/leads')->assertStatus(200);
        $this->actingAs($user)->get('/leads/create')->assertStatus(200);
        $this->actingAs($user)->get("/leads/{$lead->id}/edit")->assertStatus(200);
        $this->actingAs($user)->delete("/leads/{$lead->id}")->assertStatus(403);
    });

    it('allows user role view only', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $lead = Lead::factory()->create();

        $this->actingAs($user)->get('/leads')->assertStatus(200);
        $this->actingAs($user)->get("/leads/{$lead->id}")->assertStatus(200);
        $this->actingAs($user)->get('/leads/create')->assertStatus(403);
        $this->actingAs($user)->get("/leads/{$lead->id}/edit")->assertStatus(403);
    });
});
