<?php

use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobTitleController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes (Super Admin Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Company Management (Super Admin)
        Route::resource('companies', AdminCompanyController::class);
        Route::post('companies/{company}/toggle-active', [AdminCompanyController::class, 'toggleActive'])
            ->name('companies.toggle-active');
        Route::put('companies/{company}/settings', [AdminCompanyController::class, 'updateSettings'])
            ->name('companies.settings');
    });

/*
|--------------------------------------------------------------------------
| Company-Scoped Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Locations
    Route::resource('locations', LocationController::class);
    Route::post('locations/{location}/set-headquarters', [LocationController::class, 'setHeadquarters'])
        ->name('locations.set-headquarters');

    // Departments
    Route::get('departments/tree', [DepartmentController::class, 'tree'])->name('departments.tree');
    Route::resource('departments', DepartmentController::class);
    Route::post('departments/{department}/move', [DepartmentController::class, 'move'])
        ->name('departments.move');
    Route::post('departments/{department}/toggle-active', [DepartmentController::class, 'toggleActive'])
        ->name('departments.toggle-active');

    // Job Titles
    Route::resource('job-titles', JobTitleController::class);
    Route::post('job-titles/{job_title}/toggle-active', [JobTitleController::class, 'toggleActive'])
        ->name('job-titles.toggle-active');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore'])
        ->name('employees.restore')
        ->withTrashed();
    Route::post('employees/{employee}/terminate', [EmployeeController::class, 'terminate'])
        ->name('employees.terminate');
    Route::patch('employees/{employee}/status', [EmployeeController::class, 'updateStatus'])
        ->name('employees.update-status');
    Route::get('employees/{employee}/direct-reports', [EmployeeController::class, 'directReports'])
        ->name('employees.direct-reports');
});

require __DIR__.'/settings.php';
