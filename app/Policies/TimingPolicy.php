<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Timing;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_timings::timing');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Timing $timing): bool
    {
        return $user->can('view_timings::timing');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_timings::timing');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Timing $timing): bool
    {
        return $user->can('update_timings::timing');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Timing $timing): bool
    {
        return $user->can('delete_timings::timing');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_timings::timing');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Timing $timing): bool
    {
        return $user->can('force_delete_timings::timing');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_timings::timing');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Timing $timing): bool
    {
        return $user->can('restore_timings::timing');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_timings::timing');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Timing $timing): bool
    {
        return $user->can('replicate_timings::timing');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_timings::timing');
    }
}
