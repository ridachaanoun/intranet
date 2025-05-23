<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function view(User $authUser, User $user)
    {
        // Allow viewing if the authenticated user is an admin or the same user
        return $authUser->role === 'admin' || $authUser->id === $user->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function teacher(User $user): bool
    {
        return $user->role==='teacher';
    }

    /**
     * Determine whether the user can create models.
     */
    public function admin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
                // Allow the user to update their own profile
                return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
