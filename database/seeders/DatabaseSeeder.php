<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create Super Admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);
        $superAdmin->assignRole('super-admin');

        // Create test users for each role
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin.user@example.com',
        ]);
        $admin->assignRole('admin');

        $manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);
        $manager->assignRole('manager');

        $sales = User::factory()->create([
            'name' => 'Sales User',
            'email' => 'sales@example.com',
        ]);
        $sales->assignRole('sales');

        // Create additional regular user
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);
        $user->assignRole('user');

        // Seed businesses, services, leads, and customers
        $this->call([
            BusinessSeeder::class,
            ServiceSeeder::class,
            LeadSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
