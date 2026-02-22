<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view projects');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('view projects');
    }

    public function create(User $user): bool
    {
        return $user->can('create projects');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('edit projects');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('delete projects');
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->can('delete projects');
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->hasRole('super-admin');
    }
}
