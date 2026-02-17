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
            ->has('users')
            ->has('businesses')
            ->has('countries')
        );
    });

    it('stores customer with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Jane Doe',
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
            'email' => 'jane@example.com',
            'status' => 'active',
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', []);

        $response->assertSessionHasErrors(['name']);
    });

    it('validates status enum', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Test Customer',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    });

    it('denies create to user role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->post('/customers', [
            'name' => 'Test Customer',
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
        );
    });

    it('updates customer with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->put("/customers/{$customer->id}", [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
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

    it('denies delete to sales role', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->delete("/customers/{$customer->id}");

        $response->assertStatus(403);
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
