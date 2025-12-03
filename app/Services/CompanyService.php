<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CompanyService
{
    /**
     * Get all companies with optional filtering.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Company::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get paginated companies.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Company::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Find a company by ID.
     */
    public function find(int $id): ?Company
    {
        return Company::find($id);
    }

    /**
     * Find a company by slug.
     */
    public function findBySlug(string $slug): ?Company
    {
        return Company::where('slug', $slug)->first();
    }

    /**
     * Create a new company.
     */
    public function create(array $data): Company
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        
        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Company::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        return Company::create($data);
    }

    /**
     * Update a company.
     */
    public function update(Company $company, array $data): Company
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            
            // Ensure unique slug (excluding current company)
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Company::where('slug', $data['slug'])->where('id', '!=', $company->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        $company->update($data);
        return $company->fresh();
    }

    /**
     * Delete a company.
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    /**
     * Get company settings.
     */
    public function getSettings(Company $company): array
    {
        return $company->settings ?? [];
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Company $company, array $settings): Company
    {
        $currentSettings = $company->settings ?? [];
        $mergedSettings = array_merge($currentSettings, $settings);
        
        $company->update(['settings' => $mergedSettings]);
        return $company->fresh();
    }

    /**
     * Set a specific setting value.
     */
    public function setSetting(Company $company, string $key, mixed $value): Company
    {
        $company->setSetting($key, $value);
        return $company->fresh();
    }

    /**
     * Get company statistics.
     */
    public function getStatistics(Company $company): array
    {
        return [
            'total_employees' => $company->employees()->count(),
            'active_employees' => $company->employees()->where('status', 'active')->count(),
            'total_departments' => $company->departments()->count(),
            'total_locations' => $company->locations()->count(),
            'total_job_titles' => $company->jobTitles()->count(),
        ];
    }

    /**
     * Toggle company active status.
     */
    public function toggleActive(Company $company): Company
    {
        $company->update(['is_active' => !$company->is_active]);
        return $company->fresh();
    }
}
