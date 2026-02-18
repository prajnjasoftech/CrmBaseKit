<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions by module
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Business Management
            'view businesses',
            'create businesses',
            'edit businesses',
            'delete businesses',

            // Lead Management
            'view leads',
            'create leads',
            'edit leads',
            'delete leads',
            'convert leads',

            // Customer Management
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',

            // Contact Person Management
            'manage contact persons',

            // Follow Up Management
            'manage follow ups',

            // News Board
            'view news',
            'create news',
            'edit news',
            'delete news',
            'publish news',

            // Settings
            'view settings',
            'edit settings',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Super Admin role with all permissions
        $superAdminRole = Role::findOrCreate('super-admin');
        $superAdminRole->givePermissionTo(Permission::all());

        // Create Admin role
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view roles',
            'view businesses', 'create businesses', 'edit businesses', 'delete businesses',
            'view leads', 'create leads', 'edit leads', 'delete leads', 'convert leads',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'manage contact persons',
            'manage follow ups',
            'view news', 'create news', 'edit news', 'delete news', 'publish news',
            'view settings',
        ]);

        // Create Manager role
        $managerRole = Role::findOrCreate('manager');
        $managerRole->givePermissionTo([
            'view users',
            'view businesses', 'create businesses', 'edit businesses',
            'view leads', 'create leads', 'edit leads', 'convert leads',
            'view customers', 'create customers', 'edit customers',
            'manage contact persons',
            'manage follow ups',
            'view news', 'create news', 'edit news',
        ]);

        // Create Sales role
        $salesRole = Role::findOrCreate('sales');
        $salesRole->givePermissionTo([
            'view leads', 'create leads', 'edit leads', 'convert leads',
            'view customers', 'create customers', 'edit customers',
            'manage contact persons',
            'manage follow ups',
            'view news',
        ]);

        // Create User role (basic)
        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo([
            'view leads',
            'view customers',
            'view news',
        ]);
    }
}
