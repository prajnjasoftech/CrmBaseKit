<?php

use App\Enums\EntityType;
use App\Exceptions\ImmutableFieldException;
use App\Models\ContactPerson;
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
            ->has('entityTypes')
            ->has('users')
            ->has('businesses')
        );
    });

    it('stores individual lead with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'John Doe',
            'entity_type' => 'individual',
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
            'entity_type' => 'individual',
            'email' => 'john@example.com',
            'source' => 'website',
            'status' => 'new',
        ]);
    });

    it('stores business lead with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'Acme Corporation',
            'entity_type' => 'business',
            'email' => 'contact@acme.com',
            'phone' => '1234567890',
            'source' => 'website',
            'status' => 'new',
        ]);

        $response->assertRedirect('/leads');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Acme Corporation',
            'entity_type' => 'business',
            'email' => 'contact@acme.com',
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', []);

        $response->assertSessionHasErrors(['name', 'source', 'entity_type']);
    });

    it('validates entity_type enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'Test Lead',
            'entity_type' => 'invalid_type',
            'source' => 'website',
        ]);

        $response->assertSessionHasErrors(['entity_type']);
    });

    it('validates source enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'Test Lead',
            'entity_type' => 'individual',
            'source' => 'invalid_source',
        ]);

        $response->assertSessionHasErrors(['source']);
    });

    it('denies create to user role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->post('/leads', [
            'name' => 'Test Lead',
            'entity_type' => 'individual',
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
            ->has('entityTypes')
            ->where('lead.id', $lead->id)
        );
    });

    it('shows business lead with contact persons', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->create();
        ContactPerson::factory()->count(2)->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
        ]);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Show')
            ->has('lead.contact_people', 2)
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
            ->has('entityTypes')
        );
    });

    it('updates lead with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->create();

        $response = $this->actingAs($user)->put("/leads/{$lead->id}", [
            'name' => 'Updated Lead',
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

describe('Lead Immutability', function (): void {
    it('prevents updating email after creation', function (): void {
        $lead = Lead::factory()->create([
            'email' => 'original@example.com',
        ]);

        $this->expectException(ImmutableFieldException::class);
        $lead->update(['email' => 'changed@example.com']);
    });

    it('prevents updating phone after creation', function (): void {
        $lead = Lead::factory()->create([
            'phone' => '1234567890',
        ]);

        $this->expectException(ImmutableFieldException::class);
        $lead->update(['phone' => '0987654321']);
    });

    it('allows setting email and phone on creation', function (): void {
        $lead = Lead::factory()->create([
            'email' => 'new@example.com',
            'phone' => '1234567890',
        ]);

        expect($lead->email)->toBe('new@example.com');
        expect($lead->phone)->toBe('1234567890');
    });

    it('allows updating null email to a value', function (): void {
        $lead = Lead::factory()->create([
            'email' => null,
        ]);

        $lead->update(['email' => 'new@example.com']);

        expect($lead->fresh()->email)->toBe('new@example.com');
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

    it('cascades delete to contact persons', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $lead = Lead::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
        ]);

        $this->actingAs($user)->delete("/leads/{$lead->id}");

        $this->assertDatabaseMissing('contact_people', ['id' => $contact->id]);
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
            ->has('entityTypes')
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

    it('copies contact persons when converting business lead', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->business()->won()->create([
            'name' => 'Business Lead',
            'email' => 'business@example.com',
        ]);
        ContactPerson::factory()->create([
            'contactable_type' => Lead::class,
            'contactable_id' => $lead->id,
            'name' => 'John Contact',
            'is_primary' => true,
        ]);

        $this->actingAs($user)->post("/leads/{$lead->id}/convert", [
            'name' => 'Business Lead',
            'email' => 'business@example.com',
            'status' => 'active',
        ]);

        $customer = Customer::where('converted_from_lead_id', $lead->id)->first();

        expect($customer->contactPeople)->toHaveCount(1);
        expect($customer->contactPeople->first()->name)->toBe('John Contact');
        expect($customer->contactPeople->first()->is_primary)->toBeTrue();
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

describe('Lead Entity Type', function (): void {
    it('creates individual lead with correct entity type', function (): void {
        $lead = Lead::factory()->individual()->create();

        expect($lead->entity_type)->toBe(EntityType::Individual);
        expect($lead->isIndividual())->toBeTrue();
        expect($lead->isBusiness())->toBeFalse();
    });

    it('creates business lead with correct entity type', function (): void {
        $lead = Lead::factory()->business()->create();

        expect($lead->entity_type)->toBe(EntityType::Business);
        expect($lead->isBusiness())->toBeTrue();
        expect($lead->isIndividual())->toBeFalse();
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
