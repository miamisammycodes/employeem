<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    /**
     * Display a listing of locations.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Location::class);

        $user = $request->user();
        $companyId = $user->company_id;

        $filters = $request->only(['search', 'country', 'is_headquarters', 'sort_by', 'sort_dir']);
        
        $locations = $this->locationService->getPaginated($companyId, $filters, 15);
        $countries = $this->locationService->getCountries($companyId);

        return Inertia::render('locations/Index', [
            'locations' => LocationResource::collection($locations),
            'filters' => $filters,
            'countries' => $countries,
        ]);
    }

    /**
     * Show the form for creating a new location.
     */
    public function create(): Response
    {
        Gate::authorize('create', Location::class);

        return Inertia::render('locations/Create');
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Location::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code,NULL,id,company_id,' . $request->user()->company_id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'is_headquarters' => 'boolean',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['company_id'] = $request->user()->company_id;

        $location = $this->locationService->create($validated);

        return redirect()
            ->route('locations.show', $location)
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified location.
     */
    public function show(Location $location): Response
    {
        Gate::authorize('view', $location);

        $location->loadCount(['departments', 'employees']);
        $statistics = $this->locationService->getStatistics($location);

        return Inertia::render('locations/Show', [
            'location' => new LocationResource($location),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Location $location): Response
    {
        Gate::authorize('update', $location);

        return Inertia::render('locations/Edit', [
            'location' => new LocationResource($location),
        ]);
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, Location $location)
    {
        Gate::authorize('update', $location);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code,' . $location->id . ',id,company_id,' . $location->company_id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'is_headquarters' => 'boolean',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $this->locationService->update($location, $validated);

        return redirect()
            ->route('locations.show', $location)
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified location.
     */
    public function destroy(Location $location)
    {
        Gate::authorize('delete', $location);

        try {
            $this->locationService->delete($location);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location deleted successfully.');
    }

    /**
     * Set location as headquarters.
     */
    public function setHeadquarters(Location $location)
    {
        Gate::authorize('update', $location);

        $this->locationService->setAsHeadquarters($location);

        return back()->with('success', 'Headquarters updated.');
    }
}
