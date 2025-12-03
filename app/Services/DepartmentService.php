<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    /**
     * Get all departments for a company.
     */
    public function getAll(int $companyId, array $filters = []): Collection
    {
        $query = Department::where('company_id', $companyId);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['root_only']) && $filters['root_only']) {
            $query->whereNull('parent_id');
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get paginated departments.
     */
    public function getPaginated(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Department::where('company_id', $companyId);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['root_only']) && $filters['root_only']) {
            $query->whereNull('parent_id');
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Get department hierarchy tree.
     */
    public function getTree(int $companyId, ?int $locationId = null): Collection
    {
        $query = Department::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with('children.children.children'); // Load 3 levels deep

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Find a department by ID.
     */
    public function find(int $id): ?Department
    {
        return Department::find($id);
    }

    /**
     * Find a department by code within a company.
     */
    public function findByCode(int $companyId, string $code): ?Department
    {
        return Department::where('company_id', $companyId)
            ->where('code', $code)
            ->first();
    }

    /**
     * Create a new department.
     */
    public function create(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Update a department.
     */
    public function update(Department $department, array $data): Department
    {
        // Prevent setting parent to self or descendant
        if (isset($data['parent_id'])) {
            if ($data['parent_id'] == $department->id) {
                throw new \Exception('A department cannot be its own parent.');
            }

            $descendantIds = $department->getDescendants()->pluck('id')->toArray();
            if (in_array($data['parent_id'], $descendantIds)) {
                throw new \Exception('A department cannot have a descendant as its parent.');
            }
        }

        $department->update($data);
        return $department->fresh();
    }

    /**
     * Delete a department.
     */
    public function delete(Department $department): bool
    {
        // Check if department has employees
        if ($department->employees()->exists()) {
            throw new \Exception('Cannot delete department with assigned employees.');
        }

        // Check if department has children
        if ($department->children()->exists()) {
            throw new \Exception('Cannot delete department with sub-departments. Delete or move sub-departments first.');
        }

        return $department->delete();
    }

    /**
     * Move a department to a new parent.
     */
    public function move(Department $department, ?int $newParentId): Department
    {
        if ($newParentId) {
            // Validate new parent exists and is in same company
            $newParent = Department::find($newParentId);
            if (!$newParent || $newParent->company_id !== $department->company_id) {
                throw new \Exception('Invalid parent department.');
            }

            // Prevent circular reference
            if ($newParentId == $department->id) {
                throw new \Exception('A department cannot be its own parent.');
            }

            $descendantIds = $department->getDescendants()->pluck('id')->toArray();
            if (in_array($newParentId, $descendantIds)) {
                throw new \Exception('Cannot move department under its own descendant.');
            }
        }

        $department->update(['parent_id' => $newParentId]);
        return $department->fresh();
    }

    /**
     * Get department statistics.
     */
    public function getStatistics(Department $department): array
    {
        $descendants = $department->getDescendants();
        $allDepartmentIds = $descendants->pluck('id')->push($department->id)->toArray();

        return [
            'total_employees' => $department->employees()->count(),
            'active_employees' => $department->employees()->where('status', 'active')->count(),
            'total_sub_departments' => $descendants->count(),
            'total_employees_including_sub' => \App\Models\Employee::whereIn('department_id', $allDepartmentIds)->count(),
        ];
    }

    /**
     * Toggle department active status.
     */
    public function toggleActive(Department $department): Department
    {
        $department->update(['is_active' => !$department->is_active]);
        return $department->fresh();
    }

    /**
     * Set department manager.
     */
    public function setManager(Department $department, ?int $managerId): Department
    {
        $department->update(['manager_id' => $managerId]);
        return $department->fresh();
    }

    /**
     * Get departments without a manager.
     */
    public function getWithoutManager(int $companyId): Collection
    {
        return Department::where('company_id', $companyId)
            ->whereNull('manager_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
