<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    /**
     * Get all locations for a company.
     */
    public function getAll(int $companyId, array $filters = []): Collection
    {
        $query = Location::where('company_id', $companyId);

        if (isset($filters['is_headquarters'])) {
            $query->where('is_headquarters', $filters['is_headquarters']);
        }

        if (isset($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%")
                  ->orWhere('city', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get paginated locations.
     */
    public function getPaginated(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Location::where('company_id', $companyId);

        if (isset($filters['is_headquarters'])) {
            $query->where('is_headquarters', $filters['is_headquarters']);
        }

        if (isset($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%")
                  ->orWhere('city', 'like', "%{$filters['search']}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Find a location by ID.
     */
    public function find(int $id): ?Location
    {
        return Location::find($id);
    }

    /**
     * Find a location by code within a company.
     */
    public function findByCode(int $companyId, string $code): ?Location
    {
        return Location::where('company_id', $companyId)
            ->where('code', $code)
            ->first();
    }

    /**
     * Create a new location.
     */
    public function create(array $data): Location
    {
        // If this is set as headquarters, unset any existing headquarters
        if (!empty($data['is_headquarters'])) {
            Location::where('company_id', $data['company_id'])
                ->where('is_headquarters', true)
                ->update(['is_headquarters' => false]);
        }

        return Location::create($data);
    }

    /**
     * Update a location.
     */
    public function update(Location $location, array $data): Location
    {
        // If this is being set as headquarters, unset any existing headquarters
        if (!empty($data['is_headquarters']) && !$location->is_headquarters) {
            Location::where('company_id', $location->company_id)
                ->where('id', '!=', $location->id)
                ->where('is_headquarters', true)
                ->update(['is_headquarters' => false]);
        }

        $location->update($data);
        return $location->fresh();
    }

    /**
     * Delete a location.
     */
    public function delete(Location $location): bool
    {
        // Check if location has employees
        if ($location->employees()->exists()) {
            throw new \Exception('Cannot delete location with assigned employees.');
        }

        // Check if location has departments
        if ($location->departments()->exists()) {
            throw new \Exception('Cannot delete location with assigned departments.');
        }

        return $location->delete();
    }

    /**
     * Get headquarters location for a company.
     */
    public function getHeadquarters(int $companyId): ?Location
    {
        return Location::where('company_id', $companyId)
            ->where('is_headquarters', true)
            ->first();
    }

    /**
     * Set a location as headquarters.
     */
    public function setAsHeadquarters(Location $location): Location
    {
        // Unset current headquarters
        Location::where('company_id', $location->company_id)
            ->where('is_headquarters', true)
            ->update(['is_headquarters' => false]);

        $location->update(['is_headquarters' => true]);
        return $location->fresh();
    }

    /**
     * Get location statistics.
     */
    public function getStatistics(Location $location): array
    {
        return [
            'total_employees' => $location->employees()->count(),
            'active_employees' => $location->employees()->where('status', 'active')->count(),
            'total_departments' => $location->departments()->count(),
        ];
    }

    /**
     * Get unique countries for a company's locations.
     */
    public function getCountries(int $companyId): array
    {
        return Location::where('company_id', $companyId)
            ->distinct()
            ->pluck('country')
            ->filter()
            ->values()
            ->toArray();
    }
}
