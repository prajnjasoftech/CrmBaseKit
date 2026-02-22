<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        $search = $request->string('search')->toString();

        $roles = Role::query()
            ->withCount('permissions', 'users')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('Roles/Create', [
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role): Response
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users()->count(),
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ],
            'groupedPermissions' => $this->getGroupedPermissions(),
        ]);
    }

    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        $role->load('permissions');

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        // Prevent editing super-admin role name
        $nameRules = ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id];
        if ($role->name === 'super-admin') {
            $nameRules = ['required', 'string', 'in:super-admin'];
        }

        $validated = $request->validate([
            'name' => $nameRules,
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        // Prevent deleting system roles
        if (in_array($role->name, ['super-admin', 'admin', 'user'])) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete system roles.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function getGroupedPermissions(): array
    {
        /** @var \Illuminate\Support\Collection<int, string> $permissions */
        $permissions = Permission::orderBy('name')->pluck('name');

        /** @var array<string, array<int, string>> $grouped */
        $grouped = [];
        foreach ($permissions as $permission) {
            /** @var string $permission */
            $parts = explode(' ', $permission);
            $module = ucfirst($parts[1] ?? $parts[0]);
            $grouped[$module][] = $permission;
        }

        ksort($grouped);

        return $grouped;
    }
}
