<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Business Index', function (): void {
    it('shows businesses list to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/businesses');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Index')
            ->has('businesses.data', 3)
        );
    });

    it('denies access to unauthorized user', function (): void {
        $user = User::factory()->create();
        // User without any role/permission

        $response = $this->actingAs($user)->get('/businesses');

        $response->assertStatus(403);
    });
});

describe('Business Create', function (): void {
    it('shows create form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/businesses/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Create')
            ->has('industries')
            ->has('countries')
        );
    });

    it('stores business with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/businesses', [
            'name' => 'Test Business',
            'email' => 'test@business.com',
            'phone' => '1234567890',
            'website' => 'https://test.com',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'postal_code' => '12345',
            'country' => 'US',
            'industry' => 'Technology',
            'status' => 'active',
        ]);

        $response->assertRedirect('/businesses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Business',
            'email' => 'test@business.com',
            'created_by' => $user->id,
        ]);
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/businesses', []);

        $response->assertSessionHasErrors(['name', 'email']);
    });

    it('validates email format', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/businesses', [
            'name' => 'Test Business',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    it('validates unique email', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->create(['email' => 'existing@business.com']);

        $response = $this->actingAs($user)->post('/businesses', [
            'name' => 'Test Business',
            'email' => 'existing@business.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    });
});

describe('Business Show', function (): void {
    it('shows business details to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $business = Business::factory()->create();

        $response = $this->actingAs($user)->get("/businesses/{$business->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Show')
            ->has('business')
            ->where('business.id', $business->id)
        );
    });
});

describe('Business Edit', function (): void {
    it('shows edit form to authorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $business = Business::factory()->create();

        $response = $this->actingAs($user)->get("/businesses/{$business->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Edit')
            ->has('business')
            ->has('industries')
            ->has('countries')
        );
    });

    it('updates business with valid data', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $business = Business::factory()->create();

        $response = $this->actingAs($user)->put("/businesses/{$business->id}", [
            'name' => 'Updated Business',
            'email' => 'updated@business.com',
            'status' => 'inactive',
        ]);

        $response->assertRedirect('/businesses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Updated Business',
            'email' => 'updated@business.com',
            'status' => 'inactive',
        ]);
    });

    it('validates unique email on update excluding self', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $business1 = Business::factory()->create(['email' => 'first@business.com']);
        $business2 = Business::factory()->create(['email' => 'second@business.com']);

        // Try to update business2 with business1's email
        $response = $this->actingAs($user)->put("/businesses/{$business2->id}", [
            'name' => 'Updated',
            'email' => 'first@business.com',
        ]);

        $response->assertSessionHasErrors(['email']);

        // Updating with own email should work
        $response = $this->actingAs($user)->put("/businesses/{$business2->id}", [
            'name' => 'Updated',
            'email' => 'second@business.com',
        ]);

        $response->assertRedirect('/businesses');
    });
});

describe('Business Delete', function (): void {
    it('deletes business', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $business = Business::factory()->create();

        $response = $this->actingAs($user)->delete("/businesses/{$business->id}");

        $response->assertRedirect('/businesses');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('businesses', ['id' => $business->id]);
    });

    it('denies delete to unauthorized user', function (): void {
        $user = User::factory()->create();
        $user->assignRole('user'); // User role has no delete permission

        $business = Business::factory()->create();

        $response = $this->actingAs($user)->delete("/businesses/{$business->id}");

        $response->assertStatus(403);
    });
});

describe('Business Search', function (): void {
    it('filters businesses by name', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->create(['name' => 'Acme Corp']);
        Business::factory()->create(['name' => 'Tech Solutions']);
        Business::factory()->create(['name' => 'Acme Industries']);

        $response = $this->actingAs($user)->get('/businesses?search=Acme');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Index')
            ->has('businesses.data', 2)
            ->has('filters')
            ->where('filters.search', 'Acme')
        );
    });

    it('filters businesses by email', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->create(['email' => 'info@acme.com']);
        Business::factory()->create(['email' => 'contact@tech.com']);

        $response = $this->actingAs($user)->get('/businesses?search=acme');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Index')
            ->has('businesses.data', 1)
        );
    });

    it('filters businesses by phone', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->create(['phone' => '555-1234']);
        Business::factory()->create(['phone' => '555-5678']);

        $response = $this->actingAs($user)->get('/businesses?search=1234');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Index')
            ->has('businesses.data', 1)
        );
    });

    it('filters businesses by industry', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->create(['industry' => 'Technology']);
        Business::factory()->create(['industry' => 'Healthcare']);
        Business::factory()->create(['industry' => 'Technology']);

        $response = $this->actingAs($user)->get('/businesses?search=Technology');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Index')
            ->has('businesses.data', 2)
        );
    });

    it('returns all businesses when search is empty', function (): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Business::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/businesses?search=');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Businesses/Index')
            ->has('businesses.data', 5)
        );
    });
});

describe('Business Authorization', function (): void {
    it('allows manager to view businesses', function (): void {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get('/businesses');

        $response->assertStatus(200);
    });

    it('denies sales from viewing businesses', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $response = $this->actingAs($user)->get('/businesses');

        $response->assertStatus(403);
    });

    it('denies sales from deleting businesses', function (): void {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $business = Business::factory()->create();

        $response = $this->actingAs($user)->delete("/businesses/{$business->id}");

        $response->assertStatus(403);
    });
});
