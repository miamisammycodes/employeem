<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\LocationResource;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Services\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    /**
     * Display a listing of departments.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Department::class);

        $user = $request->user();
        $companyId = $user->company_id;

        $filters = $request->only(['search', 'location_id', 'is_active', 'parent_id', 'sort_by', 'sort_dir']);
        
        $departments = $this->departmentService->getPaginated($companyId, $filters, 15);
        $departments->load(['location', 'parent', 'manager']);
        
        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();

        return Inertia::render('departments/Index', [
            'departments' => DepartmentResource::collection($departments),
            'filters' => $filters,
            'locations' => LocationResource::collection($locations),
        ]);
    }

    /**
     * Display department hierarchy tree.
     */
    public function tree(Request $request): Response
    {
        Gate::authorize('viewAny', Department::class);

        $user = $request->user();
        $companyId = $user->company_id;
        $locationId = $request->input('location_id');

        $tree = $this->departmentService->getTree($companyId, $locationId);
        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();

        return Inertia::render('departments/Tree', [
            'tree' => DepartmentResource::collection($tree),
            'locations' => LocationResource::collection($locations),
            'selectedLocationId' => $locationId,
        ]);
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Department::class);

        $user = $request->user();
        $companyId = $user->company_id;

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();
        $departments = Department::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('departments/Create', [
            'locations' => LocationResource::collection($locations),
            'departments' => DepartmentResource::collection($departments),
            'parentId' => $request->input('parent_id'),
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Department::class);

        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,NULL,id,company_id,' . $user->company_id,
            'description' => 'nullable|string|max:1000',
            'location_id' => 'nullable|exists:locations,id',
            'parent_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $user->company_id;

        $department = $this->departmentService->create($validated);

        return redirect()
            ->route('departments.show', $department)
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): Response
    {
        Gate::authorize('view', $department);

        $department->load(['location', 'parent', 'children', 'manager']);
        $department->loadCount(['employees', 'children']);
        $statistics = $this->departmentService->getStatistics($department);

        return Inertia::render('departments/Show', [
            'department' => new DepartmentResource($department),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): Response
    {
        Gate::authorize('update', $department);

        $companyId = $department->company_id;

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();
        
        // Get all departments except this one and its descendants
        $descendantIds = $department->getDescendants()->pluck('id')->toArray();
        $departments = Department::where('company_id', $companyId)
            ->where('id', '!=', $department->id)
            ->whereNotIn('id', $descendantIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get potential managers (employees in this company)
        $managers = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->with('user')
            ->orderBy('employee_number')
            ->get();

        return Inertia::render('departments/Edit', [
            'department' => new DepartmentResource($department->load(['location', 'parent', 'manager'])),
            'locations' => LocationResource::collection($locations),
            'departments' => DepartmentResource::collection($departments),
            'managers' => $managers->map(fn($e) => [
                'id' => $e->id,
                'employee_number' => $e->employee_number,
                'full_name' => $e->full_name,
            ]),
        ]);
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        Gate::authorize('update', $department);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id . ',id,company_id,' . $department->company_id,
            'description' => 'nullable|string|max:1000',
            'location_id' => 'nullable|exists:locations,id',
            'parent_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ]);

        try {
            $this->departmentService->update($department, $validated);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('departments.show', $department)
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        Gate::authorize('delete', $department);

        try {
            $this->departmentService->delete($department);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Move department to a new parent.
     */
    public function move(Request $request, Department $department)
    {
        Gate::authorize('update', $department);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        try {
            $this->departmentService->move($department, $validated['parent_id']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Department moved successfully.');
    }

    /**
     * Toggle department active status.
     */
    public function toggleActive(Department $department)
    {
        Gate::authorize('update', $department);

        $this->departmentService->toggleActive($department);

        return back()->with('success', 'Department status updated.');
    }
}
