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

describe('Customer Index', function (): void {
    it('shows customers list to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Customer::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 3)
            ->has('statuses')
        );
    });

    it('allows user role to view customers', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(200);
    });

    it('denies access to unauthenticated user', function (): void {
        $response = $this->get('/customers');

        $response->assertRedirect('/login');
    });
});

describe('Customer Create', function (): void {
    it('shows create form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->get('/customers/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Create')
            ->has('statuses')
            ->has('entityTypes')
            ->has('users')
            ->has('businesses')
            ->has('countries')
        );
    });

    it('stores individual customer with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Jane Doe',
            'entity_type' => 'individual',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'company' => 'Doe Corp',
            'address' => '456 Customer St',
            'city' => 'Customer City',
            'state' => 'CC',
            'postal_code' => '54321',
            'country' => 'US',
            'status' => 'active',
            'notes' => 'Important customer',
        ]);

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Doe',
            'entity_type' => 'individual',
            'email' => 'jane@example.com',
            'status' => 'active',
        ]);
    });

    it('stores business customer with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Business Corp',
            'entity_type' => 'business',
            'email' => 'business@corp.com',
            'phone' => '0987654321',
            'status' => 'active',
        ]);

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Business Corp',
            'entity_type' => 'business',
            'email' => 'business@corp.com',
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', []);

        $response->assertSessionHasErrors(['name', 'entity_type']);
    });

    it('validates entity_type enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Test Customer',
            'entity_type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors(['entity_type']);
    });

    it('validates status enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Test Customer',
            'entity_type' => 'individual',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    });

    it('denies create to user role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Test Customer',
            'entity_type' => 'individual',
        ]);

        $response->assertStatus(403);
    });
});

describe('Customer Show', function (): void {
    it('shows customer details to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Show')
            ->has('customer')
            ->has('entityTypes')
            ->where('customer.id', $customer->id)
        );
    });

    it('shows customer with lead relationship', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $lead = Lead::factory()->won()->create();
        $customer = Customer::factory()->create([
            'converted_from_lead_id' => $lead->id,
        ]);

        $response = $this->actingAs($user)->get("/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Show')
            ->has('customer.lead')
        );
    });

    it('shows business customer with contact persons', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->business()->create();
        ContactPerson::factory()->count(2)->create([
            'contactable_type' => Customer::class,
            'contactable_id' => $customer->id,
        ]);

        $response = $this->actingAs($user)->get("/customers/{$customer->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Show')
            ->has('customer.contact_people', 2)
        );
    });
});

describe('Customer Edit', function (): void {
    it('shows edit form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->get("/customers/{$customer->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Edit')
            ->has('customer')
            ->has('statuses')
            ->has('entityTypes')
        );
    });

    it('updates customer with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}", [
            'name' => 'Updated Customer',
            'status' => 'inactive',
        ]);

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'status' => 'inactive',
        ]);
    });

    it('can mark customer as churned', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->active()->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}", [
            'name' => $customer->name,
            'status' => 'churned',
        ]);

        $response->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 'churned',
        ]);
    });
});

describe('Customer Immutability', function (): void {
    it('prevents updating email after creation', function (): void {
        $customer = Customer::factory()->create([
            'email' => 'original@example.com',
        ]);

        $this->expectException(ImmutableFieldException::class);
        $customer->update(['email' => 'changed@example.com']);
    });

    it('prevents updating phone after creation', function (): void {
        $customer = Customer::factory()->create([
            'phone' => '1234567890',
        ]);

        $this->expectException(ImmutableFieldException::class);
        $customer->update(['phone' => '0987654321']);
    });

    it('allows setting email and phone on creation', function (): void {
        $customer = Customer::factory()->create([
            'email' => 'new@example.com',
            'phone' => '1234567890',
        ]);

        expect($customer->email)->toBe('new@example.com');
        expect($customer->phone)->toBe('1234567890');
    });

    it('allows updating null email to a value', function (): void {
        $customer = Customer::factory()->create([
            'email' => null,
        ]);

        $customer->update(['email' => 'new@example.com']);

        expect($customer->fresh()->email)->toBe('new@example.com');
    });
});

describe('Customer Delete', function (): void {
    it('deletes customer', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->delete("/customers/{$customer->id}");

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    });

    it('cascades delete to contact persons', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->business()->create();
        $contact = ContactPerson::factory()->create([
            'contactable_type' => Customer::class,
            'contactable_id' => $customer->id,
        ]);

        $this->actingAs($user)->delete("/customers/{$customer->id}");

        $this->assertDatabaseMissing('contact_people', ['id' => $contact->id]);
    });

    it('denies delete to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->delete("/customers/{$customer->id}");

        $response->assertStatus(403);
    });
});

describe('Customer Entity Type', function (): void {
    it('creates individual customer with correct entity type', function (): void {
        $customer = Customer::factory()->individual()->create();

        expect($customer->entity_type)->toBe(EntityType::Individual);
        expect($customer->isIndividual())->toBeTrue();
        expect($customer->isBusiness())->toBeFalse();
    });

    it('creates business customer with correct entity type', function (): void {
        $customer = Customer::factory()->business()->create();

        expect($customer->entity_type)->toBe(EntityType::Business);
        expect($customer->isBusiness())->toBeTrue();
        expect($customer->isIndividual())->toBeFalse();
    });
});

describe('Customer Search', function (): void {
    it('filters customers by name', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Customer::factory()->create(['name' => 'John Smith']);
        Customer::factory()->create(['name' => 'Jane Doe']);
        Customer::factory()->create(['name' => 'Bob Johnson']);

        $response = $this->actingAs($user)->get('/customers?search=John');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 2) // John Smith and Bob Johnson
            ->has('filters')
            ->where('filters.search', 'John')
        );
    });

    it('filters customers by email', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Customer::factory()->create(['email' => 'john@acme.com']);
        Customer::factory()->create(['email' => 'jane@other.com']);

        $response = $this->actingAs($user)->get('/customers?search=acme');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 1)
        );
    });

    it('filters customers by company', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Customer::factory()->create(['company' => 'Acme Corp']);
        Customer::factory()->create(['company' => 'Tech Inc']);

        $response = $this->actingAs($user)->get('/customers?search=Acme');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 1)
        );
    });

    it('filters customers by phone', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Customer::factory()->create(['phone' => '555-1234']);
        Customer::factory()->create(['phone' => '555-5678']);

        $response = $this->actingAs($user)->get('/customers?search=1234');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 1)
        );
    });

    it('returns all customers when search is empty', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        Customer::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/customers?search=');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 5)
        );
    });
});

describe('Customer Authorization', function (): void {
    it('allows admin full access', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $customer = Customer::factory()->create();

        $this->actingAs($user)->get('/customers')->assertStatus(200);
        $this->actingAs($user)->get('/customers/create')->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}")->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/edit")->assertStatus(200);
    });

    it('allows manager full access except delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $customer = Customer::factory()->create();

        $this->actingAs($user)->get('/customers')->assertStatus(200);
        $this->actingAs($user)->get('/customers/create')->assertStatus(200);
        $this->actingAs($user)->delete("/customers/{$customer->id}")->assertStatus(403);
    });

    it('allows sales to create and edit but not delete', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $this->actingAs($user)->get('/customers')->assertStatus(200);
        $this->actingAs($user)->get('/customers/create')->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}/edit")->assertStatus(200);
        $this->actingAs($user)->delete("/customers/{$customer->id}")->assertStatus(403);
    });

    it('allows user role view only', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $customer = Customer::factory()->create();

        $this->actingAs($user)->get('/customers')->assertStatus(200);
        $this->actingAs($user)->get("/customers/{$customer->id}")->assertStatus(200);
        $this->actingAs($user)->get('/customers/create')->assertStatus(403);
        $this->actingAs($user)->get("/customers/{$customer->id}/edit")->assertStatus(403);
    });
});

describe('Customer Model', function (): void {
    it('tracks conversion from lead', function (): void {
        $lead = Lead::factory()->won()->create();

        $customer = Customer::factory()->create([
            'converted_from_lead_id' => $lead->id,
        ]);

        expect($customer->wasConvertedFromLead())->toBeTrue();
        expect($customer->lead->id)->toBe($lead->id);
    });

    it('identifies direct customers', function (): void {
        $customer = Customer::factory()->create([
            'converted_from_lead_id' => null,
        ]);

        expect($customer->wasConvertedFromLead())->toBeFalse();
    });

    it('generates full address', function (): void {
        $customer = Customer::factory()->create([
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
        ]);

        expect($customer->full_address)->toContain('123 Main St');
        expect($customer->full_address)->toContain('New York');
    });
});
