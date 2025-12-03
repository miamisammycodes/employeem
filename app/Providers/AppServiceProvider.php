<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Location;
use App\Policies\CompanyPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\JobTitlePolicy;
use App\Policies\LocationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(JobTitle::class, JobTitlePolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);

        // Super admin bypass - can do everything
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
