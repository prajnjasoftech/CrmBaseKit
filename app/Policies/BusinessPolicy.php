<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view businesses');
    }

    public function view(User $user, Business $business): bool
    {
        return $user->can('view businesses');
    }

    public function create(User $user): bool
    {
        return $user->can('create businesses');
    }

    public function update(User $user, Business $business): bool
    {
        return $user->can('edit businesses');
    }

    public function delete(User $user, Business $business): bool
    {
        return $user->can('delete businesses');
    }

    public function restore(User $user, Business $business): bool
    {
        return $user->can('delete businesses');
    }

    public function forceDelete(User $user, Business $business): bool
    {
        return $user->hasRole('super-admin');
    }
}
