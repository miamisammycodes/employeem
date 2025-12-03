<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService
    ) {}

    /**
     * Display a listing of companies.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Company::class);

        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_dir']);
        
        $companies = $this->companyService->getPaginated($filters, 15);

        return Inertia::render('admin/companies/Index', [
            'companies' => CompanyResource::collection($companies),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): Response
    {
        Gate::authorize('create', Company::class);

        return Inertia::render('admin/companies/Create');
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Company::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:companies,slug',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $company = $this->companyService->create($validated);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): Response
    {
        Gate::authorize('view', $company);

        $company->loadCount(['locations', 'departments', 'employees', 'jobTitles', 'users']);
        $statistics = $this->companyService->getStatistics($company);

        return Inertia::render('admin/companies/Show', [
            'company' => new CompanyResource($company),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): Response
    {
        Gate::authorize('update', $company);

        return Inertia::render('admin/companies/Edit', [
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company)
    {
        Gate::authorize('update', $company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:companies,slug,' . $company->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $this->companyService->update($company, $validated);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        Gate::authorize('delete', $company);

        // Check for related data
        if ($company->employees()->exists()) {
            return back()->with('error', 'Cannot delete company with employees.');
        }

        $this->companyService->delete($company);

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    /**
     * Toggle company active status.
     */
    public function toggleActive(Company $company)
    {
        Gate::authorize('update', $company);

        $this->companyService->toggleActive($company);

        return back()->with('success', 'Company status updated.');
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Request $request, Company $company)
    {
        Gate::authorize('manageSettings', $company);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $this->companyService->updateSettings($company, $validated['settings']);

        return back()->with('success', 'Settings updated successfully.');
    }
}
