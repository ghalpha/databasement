<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view the user list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view user details.
     */
    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Only admins can create users.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Only admins can update users.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins can delete users, with restrictions.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Only admins can delete
        if (! $user->isAdmin()) {
            return false;
        }

        // Cannot delete the last admin
        if ($model->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() === 1) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can copy the invitation link.
     * Only admins can copy invitation links for pending users.
     */
    public function copyInvitationLink(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->invitation_token !== null;
    }
}
