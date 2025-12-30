<?php

namespace App\Policies;

use App\Models\DatabaseServer;
use App\Models\User;

class DatabaseServerPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view the list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view details.
     */
    public function view(User $user, DatabaseServer $databaseServer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Viewers cannot create.
     */
    public function create(User $user): bool
    {
        return $user->canPerformActions();
    }

    /**
     * Determine whether the user can update the model.
     * Viewers cannot update.
     */
    public function update(User $user, DatabaseServer $databaseServer): bool
    {
        return $user->canPerformActions();
    }

    /**
     * Determine whether the user can delete the model.
     * Viewers cannot delete nor demo users.
     */
    public function delete(User $user, DatabaseServer $databaseServer): bool
    {
        return $user->canPerformActions() && ! $user->isDemo();
    }

    /**
     * Determine whether the user can run a backup.
     * Viewers cannot run backups.
     */
    public function backup(User $user, DatabaseServer $databaseServer): bool
    {
        return $user->canPerformActions();
    }

    /**
     * Determine whether the user can restore to a server.
     * Viewers cannot restore.
     */
    public function restore(User $user, DatabaseServer $databaseServer): bool
    {
        return $user->canPerformActions();
    }
}
