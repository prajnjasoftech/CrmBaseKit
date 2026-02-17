<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('Login', function (): void {
    it('shows login page', function (): void {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    });

    it('authenticates user with valid credentials', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    });

    it('fails with invalid credentials', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    });

    it('remembers user when checkbox checked', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    });

    it('redirects authenticated user away from login page', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    });

    it('rate limits after too many attempts', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Too many', session('errors')->get('email')[0]);
    });
});

describe('Registration', function (): void {
    it('shows registration page', function (): void {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
    });

    it('registers new user', function (): void {
        Event::fake([Registered::class]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertAuthenticatedAs($user);
        expect($user->hasRole('user'))->toBeTrue();

        Event::assertDispatched(Registered::class);
    });

    it('validates required fields', function (): void {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('validates password confirmation', function (): void {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('validates unique email', function (): void {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['email']);
    });
});

describe('Logout', function (): void {
    it('logs out authenticated user', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    });
});

describe('Forgot Password', function (): void {
    it('shows forgot password page', function (): void {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));
    });

    it('validates email field', function (): void {
        $response = $this->post('/forgot-password', []);

        $response->assertSessionHasErrors(['email']);
    });

    it('validates email format', function (): void {
        $response = $this->post('/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    });
});

describe('Protected Routes', function (): void {
    it('redirects guest to login on protected routes', function (): void {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    });

    it('allows authenticated user to access dashboard', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    });

    it('redirects guest to login for businesses', function (): void {
        $response = $this->get('/businesses');

        $response->assertRedirect('/login');
    });

    it('redirects guest to login for users', function (): void {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    });
});
