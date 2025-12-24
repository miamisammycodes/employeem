<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\DepartmentService;
use App\Services\EmployeeService;
use App\Services\JobTitleService;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService,
        protected DepartmentService $departmentService,
        protected JobTitleService $jobTitleService,
        protected LocationService $locationService,
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Employee::class);

        $user = $request->user();
        $companyId = $user->company_id;

        $filters = $request->only([
            'search',
            'status',
            'department_id',
            'location_id',
            'job_title_id',
            'employment_type',
            'sort_by',
            'sort_dir',
        ]);

        $employees = $this->employeeService->getPaginated($companyId, $filters, 15);
        $statistics = $this->employeeService->getStatistics($companyId);

        // Get filter options
        $departments = $this->departmentService->getAll($companyId);
        $locations = $this->locationService->getAll($companyId);
        $jobTitles = $this->jobTitleService->getAll($companyId);

        return Inertia::render('employees/Index', [
            'employees' => EmployeeResource::collection($employees),
            'filters' => $filters,
            'statistics' => $statistics,
            'departments' => $departments,
            'locations' => $locations,
            'jobTitles' => $jobTitles,
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Employee::class);

        $companyId = $request->user()->company_id;

        // Get options for dropdowns
        $departments = $this->departmentService->getAll($companyId);
        $locations = $this->locationService->getAll($companyId);
        $jobTitles = $this->jobTitleService->getAll($companyId);
        $managers = $this->employeeService->getAll($companyId, ['status' => 'active']);

        return Inertia::render('employees/Create', [
            'departments' => $departments,
            'locations' => $locations,
            'jobTitles' => $jobTitles,
            'managers' => EmployeeResource::collection($managers),
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $validated = $request->validated();
        $validated['company_id'] = $request->user()->company_id;

        $employee = $this->employeeService->create($validated);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): Response
    {
        Gate::authorize('view', $employee);

        $employee->load([
            'user',
            'department',
            'jobTitle',
            'location',
            'managers.user',
            'emergencyContacts',
        ]);
        $employee->loadCount('directReports');

        $directReports = $this->employeeService->getDirectReports($employee);

        return Inertia::render('employees/Show', [
            'employee' => new EmployeeResource($employee),
            'directReports' => EmployeeResource::collection($directReports),
        ]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Request $request, Employee $employee): Response
    {
        Gate::authorize('update', $employee);

        $employee->load(['user', 'department', 'jobTitle', 'location', 'managers', 'emergencyContacts']);

        $companyId = $employee->company_id;

        // Get options for dropdowns
        $departments = $this->departmentService->getAll($companyId);
        $locations = $this->locationService->getAll($companyId);
        $jobTitles = $this->jobTitleService->getAll($companyId);
        $managers = $this->employeeService->getAll($companyId, ['status' => 'active']);

        return Inertia::render('employees/Edit', [
            'employee' => new EmployeeResource($employee),
            'departments' => $departments,
            'locations' => $locations,
            'jobTitles' => $jobTitles,
            'managers' => EmployeeResource::collection($managers),
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();

        $this->employeeService->update($employee, $validated);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee (soft delete).
     */
    public function destroy(Employee $employee)
    {
        Gate::authorize('delete', $employee);

        $this->employeeService->delete($employee);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Restore a soft-deleted employee.
     */
    public function restore(Employee $employee)
    {
        Gate::authorize('restore', $employee);

        $this->employeeService->restore($employee);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee restored successfully.');
    }

    /**
     * Terminate an employee.
     */
    public function terminate(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'termination_date' => ['required', 'date'],
            'termination_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->employeeService->terminate($employee, $validated);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee terminated.');
    }

    /**
     * Update employee status.
     */
    public function updateStatus(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'status' => ['required', 'in:active,on_leave,suspended'],
        ]);

        $this->employeeService->updateStatus($employee, $validated['status']);

        return back()->with('success', 'Employee status updated.');
    }

    /**
     * Get direct reports for an employee.
     */
    public function directReports(Employee $employee): Response
    {
        Gate::authorize('view', $employee);

        $directReports = $this->employeeService->getDirectReports($employee);

        return Inertia::render('employees/DirectReports', [
            'employee' => new EmployeeResource($employee->load('user')),
            'directReports' => EmployeeResource::collection($directReports),
        ]);
    }
}
