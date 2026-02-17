<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Index', function (): void {
    it('shows users list to authorized user', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Index')
            ->has('users.data', 4) // 3 + admin
        );
    });

    it('denies access to unauthorized user', function (): void {
        $user = User::factory()->create();
        // No role assigned

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(403);
    });

    it('allows manager to view users', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $response = $this->actingAs($manager)->get('/users');

        $response->assertStatus(200);
    });
});

describe('User Search', function (): void {
    it('filters users by name', function (): void {
        $admin = User::factory()->create(['name' => 'Admin User']);
        $admin->assignRole('admin');

        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);
        User::factory()->create(['name' => 'Bob Johnson']);

        $response = $this->actingAs($admin)->get('/users?search=John');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Index')
            ->has('users.data', 2) // John Smith and Bob Johnson
            ->has('filters')
            ->where('filters.search', 'John')
        );
    });

    it('filters users by email', function (): void {
        $admin = User::factory()->create(['name' => 'Admin User', 'email' => 'admin@test.com']);
        $admin->assignRole('admin');

        User::factory()->create(['email' => 'john@acme.com']);
        User::factory()->create(['email' => 'jane@other.com']);

        $response = $this->actingAs($admin)->get('/users?search=acme');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Index')
            ->has('users.data', 1)
        );
    });

    it('returns all users when search is empty', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->count(4)->create();

        $response = $this->actingAs($admin)->get('/users?search=');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Index')
            ->has('users.data', 5) // 4 created + admin
        );
    });
});

describe('User Create', function (): void {
    it('shows create form to admin', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/users/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Create')
            ->has('roles')
        );
    });

    it('denies create form to manager', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $response = $this->actingAs($manager)->get('/users/create');

        $response->assertStatus(403);
    });

    it('stores user with valid data', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        expect($newUser->hasRole('user'))->toBeTrue();
    });

    it('validates required fields', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('validates password confirmation', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('validates unique email', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['email']);
    });
});

describe('User Show', function (): void {
    it('shows user details to admin', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get("/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Show')
            ->has('user')
            ->where('user.id', $user->id)
        );
    });

    it('allows user to view own profile', function (): void {
        $user = User::factory()->create();
        // No special role needed to view own profile

        $response = $this->actingAs($user)->get("/users/{$user->id}");

        $response->assertStatus(200);
    });

    it('denies viewing other profiles without permission', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)->get("/users/{$user2->id}");

        $response->assertStatus(403);
    });
});

describe('User Edit', function (): void {
    it('shows edit form to admin', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->has('user')
            ->has('roles')
        );
    });

    it('allows user to edit own profile', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
    });

    it('updates user with valid data', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'manager',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $user->refresh();
        expect($user->name)->toBe('Updated Name');
        expect($user->email)->toBe('updated@example.com');
        expect($user->hasRole('manager'))->toBeTrue();
    });

    it('updates password when provided', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect('/users');

        $user->refresh();
        expect($user->password)->not->toBe($oldPassword);
    });

    it('keeps password when not provided', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect('/users');

        $user->refresh();
        expect($user->password)->toBe($oldPassword);
    });
});

describe('User Delete', function (): void {
    it('deletes user as super-admin', function (): void {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $user = User::factory()->create();

        $response = $this->actingAs($superAdmin)->delete("/users/{$user->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('denies delete to admin', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$user->id}");

        $response->assertStatus(403);
    });

    it('prevents self-deletion', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });

    it('denies delete to unauthorized user', function (): void {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->delete("/users/{$target->id}");

        $response->assertStatus(403);
    });
});
