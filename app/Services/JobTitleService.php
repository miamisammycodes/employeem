<?php

namespace App\Services;

use App\Models\JobTitle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class JobTitleService
{
    /**
     * Get all job titles for a company.
     */
    public function getAll(int $companyId, array $filters = []): Collection
    {
        $query = JobTitle::where('company_id', $companyId);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'level';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDir)->get();
    }

    /**
     * Get paginated job titles.
     */
    public function getPaginated(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = JobTitle::where('company_id', $companyId);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'level';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Find a job title by ID.
     */
    public function find(int $id): ?JobTitle
    {
        return JobTitle::find($id);
    }

    /**
     * Find a job title by code within a company.
     */
    public function findByCode(int $companyId, string $code): ?JobTitle
    {
        return JobTitle::where('company_id', $companyId)
            ->where('code', $code)
            ->first();
    }

    /**
     * Create a new job title.
     */
    public function create(array $data): JobTitle
    {
        return JobTitle::create($data);
    }

    /**
     * Update a job title.
     */
    public function update(JobTitle $jobTitle, array $data): JobTitle
    {
        $jobTitle->update($data);
        return $jobTitle->fresh();
    }

    /**
     * Delete a job title.
     */
    public function delete(JobTitle $jobTitle): bool
    {
        // Check if job title has employees
        if ($jobTitle->employees()->exists()) {
            throw new \Exception('Cannot delete job title with assigned employees.');
        }

        return $jobTitle->delete();
    }

    /**
     * Get job title statistics.
     */
    public function getStatistics(JobTitle $jobTitle): array
    {
        $employees = $jobTitle->employees();
        
        return [
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'active')->count(),
            'avg_salary' => $employees->avg('salary'),
            'min_salary' => $employees->min('salary'),
            'max_salary' => $employees->max('salary'),
        ];
    }

    /**
     * Toggle job title active status.
     */
    public function toggleActive(JobTitle $jobTitle): JobTitle
    {
        $jobTitle->update(['is_active' => !$jobTitle->is_active]);
        return $jobTitle->fresh();
    }

    /**
     * Get unique levels for a company's job titles.
     */
    public function getLevels(int $companyId): array
    {
        return JobTitle::where('company_id', $companyId)
            ->distinct()
            ->orderBy('level')
            ->pluck('level')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get job titles grouped by level.
     */
    public function getGroupedByLevel(int $companyId): Collection
    {
        return JobTitle::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->groupBy('level');
    }

    /**
     * Update salary range for a job title.
     */
    public function updateSalaryRange(JobTitle $jobTitle, ?float $minSalary, ?float $maxSalary): JobTitle
    {
        if ($minSalary !== null && $maxSalary !== null && $minSalary > $maxSalary) {
            throw new \Exception('Minimum salary cannot be greater than maximum salary.');
        }

        $jobTitle->update([
            'min_salary' => $minSalary,
            'max_salary' => $maxSalary,
        ]);

        return $jobTitle->fresh();
    }
}
