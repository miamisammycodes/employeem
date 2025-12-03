<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobTitleResource;
use App\Models\JobTitle;
use App\Services\JobTitleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class JobTitleController extends Controller
{
    public function __construct(
        protected JobTitleService $jobTitleService
    ) {}

    /**
     * Display a listing of job titles.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', JobTitle::class);

        $user = $request->user();
        $companyId = $user->company_id;

        $filters = $request->only(['search', 'level', 'is_active', 'sort_by', 'sort_dir']);
        
        $jobTitles = $this->jobTitleService->getPaginated($companyId, $filters, 15);
        $jobTitles->loadCount('employees');
        
        $levels = $this->jobTitleService->getLevels($companyId);

        return Inertia::render('job-titles/Index', [
            'jobTitles' => JobTitleResource::collection($jobTitles),
            'filters' => $filters,
            'levels' => $levels,
        ]);
    }

    /**
     * Show the form for creating a new job title.
     */
    public function create(): Response
    {
        Gate::authorize('create', JobTitle::class);

        return Inertia::render('job-titles/Create');
    }

    /**
     * Store a newly created job title.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', JobTitle::class);

        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:job_titles,code,NULL,id,company_id,' . $user->company_id,
            'description' => 'nullable|string|max:1000',
            'level' => 'nullable|integer|min:1|max:20',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $user->company_id;

        $jobTitle = $this->jobTitleService->create($validated);

        return redirect()
            ->route('job-titles.show', $jobTitle)
            ->with('success', 'Job title created successfully.');
    }

    /**
     * Display the specified job title.
     */
    public function show(JobTitle $jobTitle): Response
    {
        Gate::authorize('view', $jobTitle);

        $jobTitle->loadCount('employees');
        $statistics = $this->jobTitleService->getStatistics($jobTitle);

        return Inertia::render('job-titles/Show', [
            'jobTitle' => new JobTitleResource($jobTitle),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified job title.
     */
    public function edit(JobTitle $jobTitle): Response
    {
        Gate::authorize('update', $jobTitle);

        return Inertia::render('job-titles/Edit', [
            'jobTitle' => new JobTitleResource($jobTitle),
        ]);
    }

    /**
     * Update the specified job title.
     */
    public function update(Request $request, JobTitle $jobTitle)
    {
        Gate::authorize('update', $jobTitle);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:job_titles,code,' . $jobTitle->id . ',id,company_id,' . $jobTitle->company_id,
            'description' => 'nullable|string|max:1000',
            'level' => 'nullable|integer|min:1|max:20',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'is_active' => 'boolean',
        ]);

        $this->jobTitleService->update($jobTitle, $validated);

        return redirect()
            ->route('job-titles.show', $jobTitle)
            ->with('success', 'Job title updated successfully.');
    }

    /**
     * Remove the specified job title.
     */
    public function destroy(JobTitle $jobTitle)
    {
        Gate::authorize('delete', $jobTitle);

        try {
            $this->jobTitleService->delete($jobTitle);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('job-titles.index')
            ->with('success', 'Job title deleted successfully.');
    }

    /**
     * Toggle job title active status.
     */
    public function toggleActive(JobTitle $jobTitle)
    {
        Gate::authorize('update', $jobTitle);

        $this->jobTitleService->toggleActive($jobTitle);

        return back()->with('success', 'Job title status updated.');
    }
}
