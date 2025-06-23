<?php

namespace App\Policies;

use App\Models\GoogleTask;
use App\Models\User;

class GoogleTaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own tasks
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GoogleTask $googleTask): bool
    {
        return $user->id === $googleTask->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Users can create tasks
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GoogleTask $googleTask): bool
    {
        return $user->id === $googleTask->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GoogleTask $googleTask): bool
    {
        return $user->id === $googleTask->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GoogleTask $googleTask): bool
    {
        return $user->id === $googleTask->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GoogleTask $googleTask): bool
    {
        return $user->id === $googleTask->user_id;
    }
} 