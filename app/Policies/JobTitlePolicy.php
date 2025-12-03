<?php

namespace App\Policies;

use App\Models\JobTitle;
use App\Models\User;

class JobTitlePolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('job_titles.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('job_titles.view') && $user->company_id === $jobTitle->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('job_titles.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('job_titles.update') && $user->company_id === $jobTitle->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('job_titles.delete') && $user->company_id === $jobTitle->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('job_titles.delete') && $user->company_id === $jobTitle->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobTitle $jobTitle): bool
    {
        return false;
    }
}
