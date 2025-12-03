<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run roles and permissions seeder first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create a demo company
        $company = Company::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
            'email' => 'info@acme.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Business Ave, Suite 100, New York, NY 10001',
            'settings' => [
                'timezone' => 'America/New_York',
                'date_format' => 'Y-m-d',
                'currency' => 'USD',
            ],
            'is_active' => true,
        ]);

        // Create headquarters location
        $headquarters = Location::create([
            'company_id' => $company->id,
            'name' => 'Headquarters',
            'code' => 'HQ',
            'address' => '123 Business Ave, Suite 100',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'postal_code' => '10001',
            'timezone' => 'America/New_York',
            'is_headquarters' => true,
            'phone' => '+1 (555) 123-4567',
            'email' => 'hq@acme.com',
        ]);

        // Create departments
        $engineering = Department::create([
            'company_id' => $company->id,
            'location_id' => $headquarters->id,
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Software development and engineering',
            'is_active' => true,
        ]);

        $hr = Department::create([
            'company_id' => $company->id,
            'location_id' => $headquarters->id,
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'Human resources and people operations',
            'is_active' => true,
        ]);

        $sales = Department::create([
            'company_id' => $company->id,
            'location_id' => $headquarters->id,
            'name' => 'Sales',
            'code' => 'SALES',
            'description' => 'Sales and business development',
            'is_active' => true,
        ]);

        // Create job titles
        $ceo = JobTitle::create([
            'company_id' => $company->id,
            'name' => 'Chief Executive Officer',
            'code' => 'CEO',
            'level' => 1,
            'min_salary' => 200000,
            'max_salary' => 500000,
            'is_active' => true,
        ]);

        $seniorEngineer = JobTitle::create([
            'company_id' => $company->id,
            'name' => 'Senior Software Engineer',
            'code' => 'SSE',
            'level' => 4,
            'min_salary' => 120000,
            'max_salary' => 180000,
            'is_active' => true,
        ]);

        $hrManager = JobTitle::create([
            'company_id' => $company->id,
            'name' => 'HR Manager',
            'code' => 'HRM',
            'level' => 3,
            'min_salary' => 80000,
            'max_salary' => 120000,
            'is_active' => true,
        ]);

        $salesRep = JobTitle::create([
            'company_id' => $company->id,
            'name' => 'Sales Representative',
            'code' => 'SR',
            'level' => 5,
            'min_salary' => 50000,
            'max_salary' => 80000,
            'is_active' => true,
        ]);

        // Create super admin user (no company)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@employeem.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // Create company admin user
        $companyAdmin = User::create([
            'company_id' => $company->id,
            'name' => 'John Smith',
            'email' => 'john@acme.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $companyAdmin->assignRole('company_admin');

        // Create employee record for company admin
        Employee::create([
            'user_id' => $companyAdmin->id,
            'company_id' => $company->id,
            'department_id' => null,
            'job_title_id' => $ceo->id,
            'location_id' => $headquarters->id,
            'employee_number' => 'EMP001',
            'hire_date' => now()->subYears(5),
            'status' => 'active',
            'employment_type' => 'full_time',
            'work_email' => 'john@acme.com',
            'salary' => 300000,
            'pay_frequency' => 'monthly',
        ]);

        // Create HR manager user
        $hrUser = User::create([
            'company_id' => $company->id,
            'name' => 'Sarah Johnson',
            'email' => 'sarah@acme.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $hrUser->assignRole('hr_manager');

        Employee::create([
            'user_id' => $hrUser->id,
            'company_id' => $company->id,
            'department_id' => $hr->id,
            'job_title_id' => $hrManager->id,
            'location_id' => $headquarters->id,
            'employee_number' => 'EMP002',
            'hire_date' => now()->subYears(3),
            'status' => 'active',
            'employment_type' => 'full_time',
            'work_email' => 'sarah@acme.com',
            'salary' => 95000,
            'pay_frequency' => 'monthly',
        ]);

        // Create regular employee
        $employeeUser = User::create([
            'company_id' => $company->id,
            'name' => 'Mike Wilson',
            'email' => 'mike@acme.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $employeeUser->assignRole('employee');

        Employee::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'department_id' => $engineering->id,
            'job_title_id' => $seniorEngineer->id,
            'location_id' => $headquarters->id,
            'employee_number' => 'EMP003',
            'hire_date' => now()->subYears(2),
            'status' => 'active',
            'employment_type' => 'full_time',
            'work_email' => 'mike@acme.com',
            'salary' => 150000,
            'pay_frequency' => 'monthly',
        ]);
    }
}
