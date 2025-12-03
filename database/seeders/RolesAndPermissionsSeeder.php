<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by module
        $permissions = [
            // Companies
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',
            'companies.manage_settings',

            // Locations
            'locations.view',
            'locations.create',
            'locations.update',
            'locations.delete',

            // Departments
            'departments.view',
            'departments.create',
            'departments.update',
            'departments.delete',

            // Job Titles
            'job_titles.view',
            'job_titles.create',
            'job_titles.update',
            'job_titles.delete',

            // Employees
            'employees.view',
            'employees.view_all',
            'employees.view_department',
            'employees.create',
            'employees.update',
            'employees.delete',
            'employees.view_salary',
            'employees.update_salary',
            'employees.import',
            'employees.export',

            // Attendance
            'attendance.view_own',
            'attendance.view_team',
            'attendance.view_all',
            'attendance.clock_in_out',
            'attendance.manage',
            'attendance.approve',
            'attendance.reports',

            // Leave
            'leave.view_own',
            'leave.view_team',
            'leave.view_all',
            'leave.request',
            'leave.approve',
            'leave.manage_types',
            'leave.manage_balances',
            'leave.reports',

            // Payroll
            'payroll.view_own',
            'payroll.view_all',
            'payroll.process',
            'payroll.manage_components',
            'payroll.reports',

            // Documents
            'documents.view_own',
            'documents.view_all',
            'documents.upload',
            'documents.manage',
            'documents.manage_categories',

            // Performance Reviews
            'reviews.view_own',
            'reviews.view_team',
            'reviews.view_all',
            'reviews.conduct',
            'reviews.manage_cycles',
            'reviews.manage_templates',

            // Goals
            'goals.view_own',
            'goals.view_team',
            'goals.view_all',
            'goals.create',
            'goals.manage',

            // Recruitment
            'recruitment.view',
            'recruitment.manage_jobs',
            'recruitment.manage_candidates',
            'recruitment.conduct_interviews',

            // Training
            'training.view_own',
            'training.view_all',
            'training.manage_programs',
            'training.assign',

            // Onboarding
            'onboarding.view',
            'onboarding.manage_templates',
            'onboarding.manage_checklists',

            // Reports
            'reports.view',
            'reports.export',

            // Settings
            'settings.view',
            'settings.manage',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.manage_roles',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions (via Gate::before in AuthServiceProvider)
        Role::create(['name' => 'super_admin']);

        // Company Admin - full access within their company
        $companyAdmin = Role::create(['name' => 'company_admin']);
        $companyAdmin->givePermissionTo(Permission::all());

        // HR Manager - manages employees, attendance, leave, payroll, recruitment
        $hrManager = Role::create(['name' => 'hr_manager']);
        $hrManager->givePermissionTo([
            'locations.view',
            'departments.view',
            'job_titles.view', 'job_titles.create', 'job_titles.update',
            'employees.view', 'employees.view_all', 'employees.create', 'employees.update',
            'employees.view_salary', 'employees.update_salary', 'employees.import', 'employees.export',
            'attendance.view_all', 'attendance.manage', 'attendance.approve', 'attendance.reports',
            'leave.view_all', 'leave.approve', 'leave.manage_types', 'leave.manage_balances', 'leave.reports',
            'payroll.view_all', 'payroll.process', 'payroll.manage_components', 'payroll.reports',
            'documents.view_all', 'documents.upload', 'documents.manage', 'documents.manage_categories',
            'reviews.view_all', 'reviews.manage_cycles', 'reviews.manage_templates',
            'goals.view_all', 'goals.manage',
            'recruitment.view', 'recruitment.manage_jobs', 'recruitment.manage_candidates', 'recruitment.conduct_interviews',
            'training.view_all', 'training.manage_programs', 'training.assign',
            'onboarding.view', 'onboarding.manage_templates', 'onboarding.manage_checklists',
            'reports.view', 'reports.export',
        ]);

        // Department Manager - manages their department
        $deptManager = Role::create(['name' => 'department_manager']);
        $deptManager->givePermissionTo([
            'locations.view',
            'departments.view',
            'job_titles.view',
            'employees.view', 'employees.view_department',
            'attendance.view_team', 'attendance.approve',
            'leave.view_team', 'leave.approve',
            'documents.view_own', 'documents.upload',
            'reviews.view_team', 'reviews.conduct',
            'goals.view_team', 'goals.create',
            'reports.view',
        ]);

        // Team Lead - limited management of their team
        $teamLead = Role::create(['name' => 'team_lead']);
        $teamLead->givePermissionTo([
            'locations.view',
            'departments.view',
            'job_titles.view',
            'employees.view', 'employees.view_department',
            'attendance.view_team',
            'leave.view_team',
            'documents.view_own', 'documents.upload',
            'reviews.view_team', 'reviews.conduct',
            'goals.view_team', 'goals.create',
        ]);

        // Employee - basic self-service access
        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo([
            'locations.view',
            'departments.view',
            'job_titles.view',
            'employees.view',
            'attendance.view_own', 'attendance.clock_in_out',
            'leave.view_own', 'leave.request',
            'payroll.view_own',
            'documents.view_own', 'documents.upload',
            'reviews.view_own',
            'goals.view_own', 'goals.create',
            'training.view_own',
        ]);
    }
}
