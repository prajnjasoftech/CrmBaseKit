<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view services');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->can('view services');
    }

    public function create(User $user): bool
    {
        return $user->can('create services');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('edit services');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('delete services');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->can('delete services');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->hasRole('super-admin');
    }
}
