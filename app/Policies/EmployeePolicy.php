<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
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
        return $user->can('employees.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Must be same company
        if ($user->company_id !== $employee->company_id) {
            return false;
        }

        // Can view all employees
        if ($user->can('employees.view_all')) {
            return true;
        }

        // Can view department employees
        if ($user->can('employees.view_department')) {
            $userEmployee = $user->employee;
            if ($userEmployee && $userEmployee->department_id === $employee->department_id) {
                return true;
            }
        }

        // Can view own profile
        if ($user->can('employees.view') && $user->employee?->id === $employee->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('employees.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $employee): bool
    {
        if ($user->company_id !== $employee->company_id) {
            return false;
        }

        return $user->can('employees.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        if ($user->company_id !== $employee->company_id) {
            return false;
        }

        return $user->can('employees.delete');
    }

    /**
     * Determine whether the user can view salary information.
     */
    public function viewSalary(User $user, Employee $employee): bool
    {
        if ($user->company_id !== $employee->company_id) {
            return false;
        }

        // Can view all salaries
        if ($user->can('employees.view_salary')) {
            return true;
        }

        // Can view own salary
        if ($user->employee?->id === $employee->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update salary information.
     */
    public function updateSalary(User $user, Employee $employee): bool
    {
        if ($user->company_id !== $employee->company_id) {
            return false;
        }

        return $user->can('employees.update_salary');
    }

    /**
     * Determine whether the user can import employees.
     */
    public function import(User $user): bool
    {
        return $user->can('employees.import');
    }

    /**
     * Determine whether the user can export employees.
     */
    public function export(User $user): bool
    {
        return $user->can('employees.export');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee $employee): bool
    {
        if ($user->company_id !== $employee->company_id) {
            return false;
        }

        return $user->can('employees.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }
}
