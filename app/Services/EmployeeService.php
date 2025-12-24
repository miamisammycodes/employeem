<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    /**
     * Get all employees for a company.
     */
    public function getAll(int $companyId, array $filters = []): Collection
    {
        $query = Employee::where('company_id', $companyId);

        $this->applyFilters($query, $filters);

        return $query->with(['user', 'department', 'jobTitle', 'location'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated employees.
     */
    public function getPaginated(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::where('company_id', $companyId);

        $this->applyFilters($query, $filters);

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $query->with(['user', 'department', 'jobTitle', 'location'])
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (isset($filters['job_title_id'])) {
            $query->where('job_title_id', $filters['job_title_id']);
        }

        if (isset($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                  ->orWhere('work_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Find an employee by ID.
     */
    public function find(int $id): ?Employee
    {
        return Employee::with(['user', 'department', 'jobTitle', 'location', 'managers', 'emergencyContacts'])
            ->find($id);
    }

    /**
     * Find an employee by employee number within a company.
     */
    public function findByEmployeeNumber(int $companyId, string $employeeNumber): ?Employee
    {
        return Employee::where('company_id', $companyId)
            ->where('employee_number', $employeeNumber)
            ->first();
    }

    /**
     * Create a new employee with associated user account.
     */
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // Create user account if not provided
            if (!isset($data['user_id'])) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password'] ?? Str::random(16)),
                    'company_id' => $data['company_id'],
                    'phone' => $data['personal_phone'] ?? null,
                    'is_active' => true,
                ]);
                $data['user_id'] = $user->id;
            }

            // Generate employee number if not provided
            if (!isset($data['employee_number'])) {
                $data['employee_number'] = $this->generateEmployeeNumber($data['company_id']);
            }

            // Remove fields that don't belong to employee table
            unset($data['name'], $data['email'], $data['password']);

            $employee = Employee::create($data);

            // Handle managers if provided
            if (isset($data['manager_ids'])) {
                $this->syncManagers($employee, $data['manager_ids'], $data['primary_manager_id'] ?? null);
            }

            return $employee->load(['user', 'department', 'jobTitle', 'location']);
        });
    }

    /**
     * Update an employee.
     */
    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            // Update associated user if user fields are provided
            if (isset($data['name']) || isset($data['email'])) {
                $userData = [];
                if (isset($data['name'])) {
                    $userData['name'] = $data['name'];
                }
                if (isset($data['email'])) {
                    $userData['email'] = $data['email'];
                }
                $employee->user->update($userData);
            }

            // Remove fields that don't belong to employee table
            unset($data['name'], $data['email'], $data['password']);

            $employee->update($data);

            // Handle managers if provided
            if (isset($data['manager_ids'])) {
                $this->syncManagers($employee, $data['manager_ids'], $data['primary_manager_id'] ?? null);
            }

            return $employee->fresh(['user', 'department', 'jobTitle', 'location', 'managers']);
        });
    }

    /**
     * Delete an employee (soft delete).
     */
    public function delete(Employee $employee): bool
    {
        return DB::transaction(function () use ($employee) {
            // Deactivate the associated user account
            if ($employee->user) {
                $employee->user->update(['is_active' => false]);
            }

            return $employee->delete();
        });
    }

    /**
     * Restore a soft-deleted employee.
     */
    public function restore(Employee $employee): Employee
    {
        return DB::transaction(function () use ($employee) {
            $employee->restore();

            // Reactivate the associated user account
            if ($employee->user) {
                $employee->user->update(['is_active' => true]);
            }

            return $employee->fresh();
        });
    }

    /**
     * Terminate an employee.
     */
    public function terminate(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update([
                'status' => 'terminated',
                'termination_date' => $data['termination_date'],
                'termination_reason' => $data['termination_reason'] ?? null,
            ]);

            // Deactivate the associated user account
            if ($employee->user) {
                $employee->user->update(['is_active' => false]);
            }

            return $employee->fresh();
        });
    }

    /**
     * Sync employee managers.
     */
    public function syncManagers(Employee $employee, array $managerIds, ?int $primaryManagerId = null): void
    {
        $syncData = [];
        foreach ($managerIds as $managerId) {
            $syncData[$managerId] = [
                'is_primary' => $managerId === $primaryManagerId,
                'started_at' => now(),
            ];
        }
        $employee->managers()->sync($syncData);
    }

    /**
     * Generate a unique employee number.
     */
    public function generateEmployeeNumber(int $companyId): string
    {
        $prefix = 'EMP';
        $lastEmployee = Employee::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEmployee && preg_match('/(\d+)$/', $lastEmployee->employee_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get employee statistics for a company.
     */
    public function getStatistics(int $companyId): array
    {
        $baseQuery = Employee::where('company_id', $companyId);

        return [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'on_leave' => (clone $baseQuery)->where('status', 'on_leave')->count(),
            'terminated' => (clone $baseQuery)->where('status', 'terminated')->count(),
            'on_probation' => (clone $baseQuery)->where('status', 'active')
                ->where('probation_end_date', '>', now())
                ->count(),
            'by_department' => (clone $baseQuery)
                ->selectRaw('department_id, count(*) as count')
                ->groupBy('department_id')
                ->pluck('count', 'department_id')
                ->toArray(),
            'by_employment_type' => (clone $baseQuery)
                ->selectRaw('employment_type, count(*) as count')
                ->groupBy('employment_type')
                ->pluck('count', 'employment_type')
                ->toArray(),
        ];
    }

    /**
     * Get employees by department.
     */
    public function getByDepartment(int $companyId, int $departmentId): Collection
    {
        return Employee::where('company_id', $companyId)
            ->where('department_id', $departmentId)
            ->with(['user', 'jobTitle'])
            ->get();
    }

    /**
     * Get employees by manager.
     */
    public function getByManager(int $managerId): Collection
    {
        return Employee::whereHas('managers', function ($query) use ($managerId) {
            $query->where('manager_id', $managerId);
        })->with(['user', 'department', 'jobTitle'])->get();
    }

    /**
     * Get direct reports for an employee.
     */
    public function getDirectReports(Employee $employee): Collection
    {
        return $employee->directReports()
            ->with(['user', 'department', 'jobTitle'])
            ->get();
    }

    /**
     * Update employee status.
     */
    public function updateStatus(Employee $employee, string $status): Employee
    {
        $employee->update(['status' => $status]);
        return $employee->fresh();
    }
}
