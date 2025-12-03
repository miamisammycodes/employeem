# 🚀 Employee Management System - Development Roadmap

> **Project:** Comprehensive Employee Management Software  
> **Stack:** Laravel 12 + Vue 3 + Inertia.js + MySQL (XAMPP)  
> **Start Date:** _______________  
> **Target Completion:** 12 weeks

---

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Tech Stack](#tech-stack)
3. [Database Setup](#database-setup)
4. [Phase 0: Environment Setup](#phase-0-environment-setup)
5. [Phase 1: Foundation & Multi-Tenancy](#phase-1-foundation--multi-tenancy)
6. [Phase 2: Employee Directory](#phase-2-employee-directory)
7. [Phase 3: Attendance & Leave Management](#phase-3-attendance--leave-management)
8. [Phase 4: Payroll & Documents](#phase-4-payroll--documents)
9. [Phase 5: Performance & Recruitment](#phase-5-performance--recruitment)
10. [Phase 6: Training, Reports & Polish](#phase-6-training-reports--polish)
11. [Feature Modules Summary](#feature-modules-summary)
12. [Database Schema](#database-schema)

---

## Project Overview

A comprehensive, multi-tenant employee management system with the following core features:

- ✅ Multi-company support (multi-tenancy)
- ✅ Multiple locations per company
- ✅ Hierarchical departments
- ✅ Role-based access control (RBAC)
- ✅ Employee directory with org chart
- ✅ Attendance & time tracking
- ✅ Leave/PTO management
- ✅ Payroll management
- ✅ Performance reviews
- ✅ Onboarding workflows
- ✅ Document management
- ✅ Recruitment/ATS
- ✅ Training & certifications
- ✅ Reporting & analytics
- ✅ Mobile responsive UI

---

## Tech Stack

| Layer | Technology | Version |
|-------|------------|---------|
| **Backend** | Laravel | 12.x |
| **Frontend** | Vue.js | 3.5.x |
| **Bridge** | Inertia.js | 2.x |
| **Database** | MySQL (XAMPP) | 8.x |
| **UI Components** | shadcn/vue (reka-ui) | Latest |
| **Styling** | Tailwind CSS | 4.x |
| **Auth** | Laravel Fortify | 1.x |
| **RBAC** | spatie/laravel-permission | 6.x |
| **Icons** | Lucide Vue Next | Latest |
| **Language** | TypeScript | 5.x |

---

## Database Setup

### MySQL Configuration (XAMPP)

Update your `.env` file with the following settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=employeem
DB_USERNAME=root
DB_PASSWORD=
```

### Create Database

```sql
-- Run in phpMyAdmin or MySQL CLI
CREATE DATABASE employeem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Phase 0: Environment Setup

> **Duration:** 1-2 days  
> **Goal:** Prepare development environment and install required packages

### Tasks

- [x] **0.1** Configure MySQL database connection
  - [x] Update `.env` file with MySQL credentials
  - [x] Create `employeem` database in phpMyAdmin
  - [x] Test database connection: `php artisan migrate:status`

- [x] **0.2** Install backend packages
  ```bash
  composer require spatie/laravel-permission
  composer require maatwebsite/excel
  composer require barryvdh/laravel-dompdf
  composer require spatie/laravel-medialibrary
  composer require spatie/laravel-activitylog
  ```

- [x] **0.3** Install frontend packages
  ```bash
  npm install @tanstack/vue-table
  npm install chart.js vue-chartjs
  npm install v-calendar@next
  npm install vue-advanced-cropper
  ```

- [x] **0.4** Publish package configurations
  ```bash
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
  php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
  ```

- [x] **0.5** Clear caches and verify setup
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan migrate
  ```

---

## Phase 1: Foundation & Multi-Tenancy

> **Duration:** Week 1-2  
> **Goal:** Set up core database structure, multi-tenancy, and RBAC

### Sprint 1.1: Database Migrations (Days 1-3)

- [x] **1.1.1** Create `companies` migration
  ```
  - id, name, slug, logo, email, phone, address
  - settings (JSON), is_active, created_at, updated_at
  ```

- [x] **1.1.2** Create `locations` migration
  ```
  - id, company_id (FK), name, code, address, city, state
  - country, postal_code, timezone, is_headquarters
  - phone, email, created_at, updated_at
  ```

- [x] **1.1.3** Create `departments` migration
  ```
  - id, company_id (FK), location_id (FK nullable)
  - parent_id (self-referencing FK), name, code
  - description, manager_id (FK nullable)
  - is_active, created_at, updated_at
  ```

- [x] **1.1.4** Create `job_titles` migration
  ```
  - id, company_id (FK), name, code, description
  - level, min_salary, max_salary, is_active
  - created_at, updated_at
  ```

- [x] **1.1.5** Create `employees` migration
  ```
  - id, user_id (FK), company_id (FK), department_id (FK)
  - job_title_id (FK), location_id (FK), employee_number
  - hire_date, probation_end_date, status (enum)
  - employment_type (enum), work_email, work_phone
  - salary, pay_frequency (enum), bank_account_number
  - bank_name, tax_id, date_of_birth, gender
  - marital_status, nationality, address, city
  - state, country, postal_code, personal_email
  - personal_phone, termination_date, termination_reason
  - created_at, updated_at, deleted_at (soft delete)
  ```

- [x] **1.1.6** Create `employee_managers` pivot migration
  ```
  - id, employee_id (FK), manager_id (FK)
  - is_primary, started_at, ended_at
  - created_at, updated_at
  ```

- [x] **1.1.7** Create `emergency_contacts` migration
  ```
  - id, employee_id (FK), name, relationship
  - phone, email, address, is_primary
  - created_at, updated_at
  ```

- [x] **1.1.8** Update `users` table migration
  ```
  - Add: company_id (FK nullable), avatar, phone
  - Add: is_active, last_login_at
  ```

- [x] **1.1.9** Run all migrations
  ```bash
  php artisan migrate
  ```

### Sprint 1.2: Models & Relationships (Days 4-5)

- [x] **1.2.1** Create `Company` model
  - [x] Define fillable attributes
  - [x] Add relationships: locations, departments, employees, jobTitles, users
  - [x] Add scopes: active()
  - [x] Add accessors/mutators for settings JSON

- [x] **1.2.2** Create `Location` model
  - [x] Define fillable attributes
  - [x] Add relationships: company, departments, employees
  - [x] Add scopes: headquarters(), active()

- [x] **1.2.3** Create `Department` model
  - [x] Define fillable attributes
  - [x] Add relationships: company, location, parent, children, manager, employees
  - [x] Add scopes: root(), active()
  - [x] Add method: getAncestors(), getDescendants()

- [x] **1.2.4** Create `JobTitle` model
  - [x] Define fillable attributes
  - [x] Add relationships: company, employees
  - [x] Add scopes: active()

- [x] **1.2.5** Create `Employee` model
  - [x] Define fillable attributes
  - [x] Add relationships: user, company, department, jobTitle, location
  - [x] Add relationships: managers, directReports, emergencyContacts
  - [x] Add scopes: active(), byDepartment(), byLocation()
  - [x] Add soft deletes

- [x] **1.2.6** Create `EmergencyContact` model
  - [x] Define fillable attributes
  - [x] Add relationships: employee

- [x] **1.2.7** Update `User` model
  - [x] Add company relationship
  - [x] Add employee relationship
  - [x] Add HasRoles trait from spatie/permission

- [x] **1.2.8** Create `BelongsToCompany` trait for tenant scoping
  ```php
  // Auto-scope queries to current company
  // Auto-set company_id on create
  ```

### Sprint 1.3: RBAC Setup (Days 6-7)

- [x] **1.3.1** Create roles seeder
  ```php
  // Roles: super_admin, company_admin, hr_manager, 
  //        department_manager, team_lead, employee
  ```

- [x] **1.3.2** Create permissions seeder
  ```php
  // Permissions grouped by module:
  // - companies.*, locations.*, departments.*
  // - employees.*, attendance.*, leave.*
  // - payroll.*, reviews.*, recruitment.*
  // - documents.*, training.*, reports.*
  ```

- [x] **1.3.3** Create role-permission assignments seeder

- [x] **1.3.4** Run seeders
  ```bash
  php artisan db:seed --class=RolesAndPermissionsSeeder
  ```

- [x] **1.3.5** Create authorization policies
  - [x] `CompanyPolicy`
  - [x] `LocationPolicy`
  - [x] `DepartmentPolicy`
  - [x] `EmployeePolicy`

- [x] **1.3.6** Register policies in `AppServiceProvider`

- [x] **1.3.7** Create `CheckCompanyAccess` middleware

### Sprint 1.4: Base Services & Controllers (Days 8-10)

- [x] **1.4.1** Create `CompanyService` class
  - [x] CRUD operations
  - [x] Settings management

- [x] **1.4.2** Create `LocationService` class

- [x] **1.4.3** Create `DepartmentService` class
  - [x] Hierarchy management
  - [x] Move department

- [x] **1.4.4** Create `JobTitleService` class

- [x] **1.4.5** Create base API resources
  - [x] `CompanyResource`
  - [x] `LocationResource`
  - [x] `DepartmentResource`
  - [x] `JobTitleResource`

- [x] **1.4.6** Create controllers
  - [x] `CompanyController`
  - [x] `LocationController`
  - [x] `DepartmentController`
  - [x] `JobTitleController`

- [x] **1.4.7** Define routes in `routes/web.php`

### Sprint 1.5: Frontend Foundation (Days 11-14)

- [ ] **1.5.1** Create TypeScript types
  ```typescript
  // types/company.d.ts
  // types/location.d.ts
  // types/department.d.ts
  // types/job-title.d.ts
  // types/employee.d.ts
  ```

- [ ] **1.5.2** Update navigation in `AppSidebar.vue`
  - [ ] Add all module navigation items
  - [ ] Group by category (HR, Operations, Admin)

- [ ] **1.5.3** Create reusable UI components
  - [ ] `DataTable.vue` - sortable, filterable table
  - [ ] `SearchInput.vue` - debounced search
  - [ ] `FilterDropdown.vue` - multi-select filter
  - [ ] `StatusBadge.vue` - colored status indicator
  - [ ] `ConfirmDialog.vue` - confirmation modal
  - [ ] `EmptyState.vue` - no data placeholder

- [ ] **1.5.4** Create company management pages
  - [ ] `pages/companies/Index.vue`
  - [ ] `pages/companies/Show.vue`
  - [ ] `pages/companies/Create.vue`
  - [ ] `pages/companies/Edit.vue`

- [ ] **1.5.5** Create location management pages
  - [ ] `pages/locations/Index.vue`
  - [ ] `pages/locations/Create.vue`
  - [ ] `pages/locations/Edit.vue`

- [ ] **1.5.6** Create department management pages
  - [ ] `pages/departments/Index.vue`
  - [ ] `pages/departments/Create.vue`
  - [ ] `pages/departments/Edit.vue`
  - [ ] `pages/departments/Tree.vue` (hierarchy view)

- [ ] **1.5.7** Create job title management pages
  - [ ] `pages/job-titles/Index.vue`
  - [ ] `pages/job-titles/Create.vue`
  - [ ] `pages/job-titles/Edit.vue`

---

## Phase 2: Employee Directory

> **Duration:** Week 3-4  
> **Goal:** Complete employee management with profiles and org chart

### Sprint 2.1: Employee Backend (Days 1-4)

- [ ] **2.1.1** Create `EmployeeService` class
  - [ ] CRUD operations
  - [ ] Search and filter
  - [ ] Bulk operations
  - [ ] Manager assignment

- [ ] **2.1.2** Create `EmployeeResource` with nested relations

- [ ] **2.1.3** Create `EmployeeController`
  - [ ] index (with search, filter, pagination)
  - [ ] show
  - [ ] create
  - [ ] store
  - [ ] edit
  - [ ] update
  - [ ] destroy (soft delete)
  - [ ] restore
  - [ ] forceDelete

- [ ] **2.1.4** Create form requests
  - [ ] `StoreEmployeeRequest`
  - [ ] `UpdateEmployeeRequest`

- [ ] **2.1.5** Create `EmployeePolicy`

- [ ] **2.1.6** Add employee routes

- [ ] **2.1.7** Create employee factory and seeder

### Sprint 2.2: Employee Frontend (Days 5-8)

- [ ] **2.2.1** Create employee components
  - [ ] `EmployeeCard.vue` - card view item
  - [ ] `EmployeeListItem.vue` - list view item
  - [ ] `EmployeeFilters.vue` - filter sidebar
  - [ ] `EmployeeForm.vue` - create/edit form
  - [ ] `EmployeeAvatar.vue` - avatar with upload

- [ ] **2.2.2** Create employee pages
  - [ ] `pages/employees/Index.vue`
    - [ ] Table view with sorting
    - [ ] Card/grid view toggle
    - [ ] Search functionality
    - [ ] Filters (department, location, status, type)
    - [ ] Pagination
    - [ ] Export button
  - [ ] `pages/employees/Show.vue`
    - [ ] Profile header with avatar
    - [ ] Tabs: Overview, Documents, Attendance, Leave, Reviews
    - [ ] Quick actions
  - [ ] `pages/employees/Create.vue`
    - [ ] Multi-step form
    - [ ] Personal info step
    - [ ] Employment info step
    - [ ] Emergency contacts step
  - [ ] `pages/employees/Edit.vue`

- [ ] **2.2.3** Create employee self-service pages
  - [ ] `pages/my-profile/Index.vue`
  - [ ] `pages/my-profile/Edit.vue`

### Sprint 2.3: Org Chart (Days 9-10)

- [ ] **2.3.1** Create org chart backend endpoint
  - [ ] Hierarchical data structure
  - [ ] Filter by department

- [ ] **2.3.2** Create `OrgChart.vue` component
  - [ ] Tree visualization
  - [ ] Zoom and pan
  - [ ] Click to view profile
  - [ ] Expand/collapse nodes

- [ ] **2.3.3** Create `pages/org-chart/Index.vue`

### Sprint 2.4: Employee Import/Export (Days 11-12)

- [ ] **2.4.1** Create `EmployeeExport` class (Laravel Excel)
  - [ ] Export to XLSX
  - [ ] Export to CSV
  - [ ] Column selection

- [ ] **2.4.2** Create `EmployeeImport` class
  - [ ] Validation rules
  - [ ] Error reporting
  - [ ] Batch processing

- [ ] **2.4.3** Add import/export endpoints

- [ ] **2.4.4** Create import modal component

### Sprint 2.5: Testing & Polish (Days 13-14)

- [ ] **2.5.1** Write feature tests
  - [ ] Employee CRUD tests
  - [ ] Authorization tests
  - [ ] Search and filter tests

- [ ] **2.5.2** Write unit tests
  - [ ] EmployeeService tests
  - [ ] Model relationship tests

- [ ] **2.5.3** Mobile responsiveness testing

- [ ] **2.5.4** Performance optimization
  - [ ] Eager loading
  - [ ] Query optimization
  - [ ] Caching

---

## Phase 3: Attendance & Leave Management

> **Duration:** Week 5-6  
> **Goal:** Time tracking and leave request system

### Sprint 3.1: Attendance Database (Days 1-2)

- [ ] **3.1.1** Create `attendance_records` migration
  ```
  - id, employee_id (FK), date, clock_in, clock_out
  - break_start, break_end, break_duration_minutes
  - total_hours, overtime_hours, status (enum)
  - clock_in_location, clock_out_location
  - notes, approved_by, approved_at
  - created_at, updated_at
  ```

- [ ] **3.1.2** Create `work_schedules` migration
  ```
  - id, employee_id (FK), day_of_week (0-6)
  - start_time, end_time, break_duration_minutes
  - is_working_day, effective_from, effective_to
  - created_at, updated_at
  ```

- [ ] **3.1.3** Create `overtime_requests` migration
  ```
  - id, employee_id (FK), date, planned_hours
  - actual_hours, reason, status (enum)
  - approved_by, approved_at, notes
  - created_at, updated_at
  ```

- [ ] **3.1.4** Create models: `AttendanceRecord`, `WorkSchedule`, `OvertimeRequest`

### Sprint 3.2: Attendance Backend (Days 3-5)

- [ ] **3.2.1** Create `AttendanceService`
  - [ ] Clock in/out
  - [ ] Break management
  - [ ] Calculate hours
  - [ ] Overtime detection

- [ ] **3.2.2** Create `AttendanceController`
  - [ ] clockIn
  - [ ] clockOut
  - [ ] startBreak
  - [ ] endBreak
  - [ ] index (timesheet view)
  - [ ] report

- [ ] **3.2.3** Create `WorkScheduleService`

- [ ] **3.2.4** Create `OvertimeRequestController`

- [ ] **3.2.5** Add attendance routes

### Sprint 3.3: Attendance Frontend (Days 6-8)

- [ ] **3.3.1** Create attendance components
  - [ ] `ClockWidget.vue` - clock in/out button
  - [ ] `TimesheetTable.vue` - weekly/monthly view
  - [ ] `AttendanceCalendar.vue` - calendar heatmap
  - [ ] `OvertimeRequestForm.vue`

- [ ] **3.3.2** Create attendance pages
  - [ ] `pages/attendance/Index.vue` - dashboard
  - [ ] `pages/attendance/Timesheet.vue` - detailed view
  - [ ] `pages/attendance/Team.vue` - manager view
  - [ ] `pages/my-attendance/Index.vue` - self-service

### Sprint 3.4: Leave Database (Days 9-10)

- [ ] **3.4.1** Create `leave_types` migration
  ```
  - id, company_id (FK), name, code, description
  - days_per_year, is_paid, is_carry_forward
  - max_carry_forward_days, requires_approval
  - min_notice_days, color, is_active
  - created_at, updated_at
  ```

- [ ] **3.4.2** Create `leave_balances` migration
  ```
  - id, employee_id (FK), leave_type_id (FK)
  - year, entitled_days, used_days
  - pending_days, carried_forward_days
  - adjustment_days, adjustment_reason
  - created_at, updated_at
  ```

- [ ] **3.4.3** Create `leave_requests` migration
  ```
  - id, employee_id (FK), leave_type_id (FK)
  - start_date, end_date, start_half (enum)
  - end_half (enum), total_days, reason
  - status (enum), approved_by, approved_at
  - rejection_reason, attachment_path
  - created_at, updated_at
  ```

- [ ] **3.4.4** Create `holidays` migration
  ```
  - id, company_id (FK), location_id (FK nullable)
  - name, date, is_recurring, created_at, updated_at
  ```

- [ ] **3.4.5** Create models: `LeaveType`, `LeaveBalance`, `LeaveRequest`, `Holiday`

### Sprint 3.5: Leave Backend (Days 11-12)

- [ ] **3.5.1** Create `LeaveService`
  - [ ] Request leave
  - [ ] Calculate days (excluding weekends/holidays)
  - [ ] Check balance
  - [ ] Approve/reject
  - [ ] Cancel request

- [ ] **3.5.2** Create `LeaveBalanceService`
  - [ ] Initialize yearly balances
  - [ ] Carry forward calculation
  - [ ] Adjustments

- [ ] **3.5.3** Create controllers
  - [ ] `LeaveTypeController`
  - [ ] `LeaveRequestController`
  - [ ] `LeaveBalanceController`
  - [ ] `HolidayController`

- [ ] **3.5.4** Create notifications
  - [ ] `LeaveRequestSubmitted`
  - [ ] `LeaveRequestApproved`
  - [ ] `LeaveRequestRejected`

### Sprint 3.6: Leave Frontend (Days 13-14)

- [ ] **3.6.1** Create leave components
  - [ ] `LeaveRequestForm.vue`
  - [ ] `LeaveBalanceCard.vue`
  - [ ] `LeaveCalendar.vue` - team calendar
  - [ ] `LeaveApprovalCard.vue`

- [ ] **3.6.2** Create leave pages
  - [ ] `pages/leave/Index.vue` - dashboard
  - [ ] `pages/leave/Request.vue` - submit request
  - [ ] `pages/leave/Calendar.vue` - team view
  - [ ] `pages/leave/Approvals.vue` - manager queue
  - [ ] `pages/leave/Balances.vue` - HR management
  - [ ] `pages/leave/Types.vue` - configure types
  - [ ] `pages/leave/Holidays.vue` - manage holidays
  - [ ] `pages/my-leave/Index.vue` - self-service

---

## Phase 4: Payroll & Documents

> **Duration:** Week 7-8  
> **Goal:** Payroll processing and document management

### Sprint 4.1: Payroll Database (Days 1-2)

- [ ] **4.1.1** Create `salary_components` migration
  ```
  - id, company_id (FK), name, code, type (earning/deduction)
  - calculation_type (fixed/percentage), default_amount
  - is_taxable, is_mandatory, applies_to (all/specific)
  - is_active, created_at, updated_at
  ```

- [ ] **4.1.2** Create `employee_salary_components` migration
  ```
  - id, employee_id (FK), salary_component_id (FK)
  - amount, effective_from, effective_to
  - created_at, updated_at
  ```

- [ ] **4.1.3** Create `pay_periods` migration
  ```
  - id, company_id (FK), name, start_date, end_date
  - pay_date, status (draft/processing/completed/paid)
  - created_at, updated_at
  ```

- [ ] **4.1.4** Create `payroll_runs` migration
  ```
  - id, pay_period_id (FK), status, processed_by
  - processed_at, total_gross, total_deductions
  - total_net, employee_count, notes
  - created_at, updated_at
  ```

- [ ] **4.1.5** Create `payslips` migration
  ```
  - id, payroll_run_id (FK), employee_id (FK)
  - pay_period_id (FK), basic_salary
  - earnings (JSON), deductions (JSON)
  - gross_pay, total_deductions, net_pay
  - payment_method, payment_reference
  - paid_at, created_at, updated_at
  ```

- [ ] **4.1.6** Create models

### Sprint 4.2: Payroll Backend (Days 3-5)

- [ ] **4.2.1** Create `PayrollService`
  - [ ] Generate pay period
  - [ ] Calculate payslips
  - [ ] Process payroll run
  - [ ] Mark as paid

- [ ] **4.2.2** Create `PayslipService`
  - [ ] Calculate earnings
  - [ ] Calculate deductions
  - [ ] Generate PDF

- [ ] **4.2.3** Create controllers
  - [ ] `SalaryComponentController`
  - [ ] `PayPeriodController`
  - [ ] `PayrollController`
  - [ ] `PayslipController`

- [ ] **4.2.4** Create payslip PDF template

### Sprint 4.3: Payroll Frontend (Days 6-8)

- [ ] **4.3.1** Create payroll components
  - [ ] `PayrollSummaryCard.vue`
  - [ ] `PayslipTable.vue`
  - [ ] `SalaryBreakdown.vue`

- [ ] **4.3.2** Create payroll pages
  - [ ] `pages/payroll/Index.vue` - dashboard
  - [ ] `pages/payroll/Periods.vue` - manage periods
  - [ ] `pages/payroll/Run.vue` - process payroll
  - [ ] `pages/payroll/Payslips.vue` - view all
  - [ ] `pages/payroll/Components.vue` - salary setup
  - [ ] `pages/my-payslips/Index.vue` - self-service

### Sprint 4.4: Document Management (Days 9-12)

- [ ] **4.4.1** Create `document_categories` migration
  ```
  - id, company_id (FK), name, description
  - requires_expiry, is_mandatory, is_active
  - created_at, updated_at
  ```

- [ ] **4.4.2** Create `employee_documents` migration
  ```
  - id, employee_id (FK), category_id (FK)
  - name, description, file_path, file_name
  - file_type, file_size, expiry_date
  - uploaded_by, version, is_verified
  - verified_by, verified_at, notes
  - created_at, updated_at, deleted_at
  ```

- [ ] **4.4.3** Configure Spatie Media Library

- [ ] **4.4.4** Create `DocumentService`
  - [ ] Upload with validation
  - [ ] Version management
  - [ ] Expiry tracking
  - [ ] Bulk download

- [ ] **4.4.5** Create `DocumentController`

- [ ] **4.4.6** Create document components
  - [ ] `FileUpload.vue` - drag & drop
  - [ ] `DocumentCard.vue`
  - [ ] `DocumentList.vue`
  - [ ] `ExpiryAlert.vue`

- [ ] **4.4.7** Create document pages
  - [ ] `pages/documents/Index.vue`
  - [ ] `pages/documents/Categories.vue`
  - [ ] `pages/documents/Expiring.vue`
  - [ ] `pages/my-documents/Index.vue`

### Sprint 4.5: Testing (Days 13-14)

- [ ] **4.5.1** Payroll calculation tests
- [ ] **4.5.2** Document upload tests
- [ ] **4.5.3** Integration tests

---

## Phase 5: Performance & Recruitment

> **Duration:** Week 9-10  
> **Goal:** Performance reviews and applicant tracking

### Sprint 5.1: Performance Database (Days 1-2)

- [ ] **5.1.1** Create `review_cycles` migration
  ```
  - id, company_id (FK), name, description
  - start_date, end_date, status (draft/active/completed)
  - review_type (annual/quarterly/probation)
  - created_at, updated_at
  ```

- [ ] **5.1.2** Create `review_templates` migration
  ```
  - id, company_id (FK), name, description
  - sections (JSON), rating_scale, is_default
  - is_active, created_at, updated_at
  ```

- [ ] **5.1.3** Create `performance_reviews` migration
  ```
  - id, employee_id (FK), reviewer_id (FK)
  - review_cycle_id (FK), template_id (FK)
  - status (pending/self_review/manager_review/completed)
  - self_assessment (JSON), manager_assessment (JSON)
  - overall_rating, strengths, improvements
  - manager_comments, employee_comments
  - acknowledged_at, created_at, updated_at
  ```

- [ ] **5.1.4** Create `goals` migration
  ```
  - id, employee_id (FK), review_id (FK nullable)
  - title, description, category, target_date
  - status (not_started/in_progress/completed/cancelled)
  - progress_percentage, completion_date
  - created_at, updated_at
  ```

- [ ] **5.1.5** Create models

### Sprint 5.2: Performance Backend (Days 3-5)

- [ ] **5.2.1** Create `ReviewService`
  - [ ] Create review cycle
  - [ ] Assign reviews
  - [ ] Submit assessments
  - [ ] Calculate ratings

- [ ] **5.2.2** Create `GoalService`

- [ ] **5.2.3** Create controllers
  - [ ] `ReviewCycleController`
  - [ ] `ReviewTemplateController`
  - [ ] `PerformanceReviewController`
  - [ ] `GoalController`

- [ ] **5.2.4** Create notifications
  - [ ] `ReviewAssigned`
  - [ ] `ReviewCompleted`
  - [ ] `GoalDueSoon`

### Sprint 5.3: Performance Frontend (Days 6-8)

- [ ] **5.3.1** Create performance components
  - [ ] `ReviewForm.vue`
  - [ ] `RatingInput.vue`
  - [ ] `GoalCard.vue`
  - [ ] `GoalProgress.vue`

- [ ] **5.3.2** Create performance pages
  - [ ] `pages/reviews/Index.vue` - cycles list
  - [ ] `pages/reviews/Cycle.vue` - cycle details
  - [ ] `pages/reviews/Conduct.vue` - do review
  - [ ] `pages/reviews/Templates.vue` - manage templates
  - [ ] `pages/goals/Index.vue` - all goals
  - [ ] `pages/my-reviews/Index.vue` - self-service
  - [ ] `pages/my-goals/Index.vue` - self-service

### Sprint 5.4: Recruitment Database (Days 9-10)

- [ ] **5.4.1** Create `job_postings` migration
  ```
  - id, company_id (FK), department_id (FK)
  - job_title_id (FK), location_id (FK)
  - title, slug, description, requirements
  - responsibilities, benefits, employment_type
  - experience_level, salary_min, salary_max
  - show_salary, positions_count, status
  - published_at, closes_at, created_by
  - created_at, updated_at
  ```

- [ ] **5.4.2** Create `candidates` migration
  ```
  - id, job_posting_id (FK), source
  - first_name, last_name, email, phone
  - resume_path, cover_letter, linkedin_url
  - portfolio_url, current_company, current_title
  - expected_salary, notice_period, status
  - rating, notes, referred_by
  - created_at, updated_at
  ```

- [ ] **5.4.3** Create `interviews` migration
  ```
  - id, candidate_id (FK), interviewer_id (FK)
  - scheduled_at, duration_minutes, type
  - location, meeting_link, status
  - feedback, rating, recommendation
  - created_at, updated_at
  ```

- [ ] **5.4.4** Create `candidate_notes` migration

- [ ] **5.4.5** Create models

### Sprint 5.5: Recruitment Backend & Frontend (Days 11-14)

- [ ] **5.5.1** Create `RecruitmentService`
- [ ] **5.5.2** Create controllers
- [ ] **5.5.3** Create recruitment components
  - [ ] `JobPostingCard.vue`
  - [ ] `CandidatePipeline.vue` (Kanban)
  - [ ] `InterviewScheduler.vue`
  - [ ] `CandidateProfile.vue`

- [ ] **5.5.4** Create recruitment pages
  - [ ] `pages/recruitment/Jobs.vue`
  - [ ] `pages/recruitment/Job.vue` - single posting
  - [ ] `pages/recruitment/Candidates.vue`
  - [ ] `pages/recruitment/Candidate.vue`
  - [ ] `pages/recruitment/Interviews.vue`

---

## Phase 6: Training, Reports & Polish

> **Duration:** Week 11-12  
> **Goal:** Training module, analytics, and final polish

### Sprint 6.1: Training Module (Days 1-4)

- [ ] **6.1.1** Create `training_programs` migration
  ```
  - id, company_id (FK), name, description
  - category, duration_hours, is_mandatory
  - department_id (FK nullable), job_title_id (FK nullable)
  - content_url, is_active, created_at, updated_at
  ```

- [ ] **6.1.2** Create `employee_trainings` migration
  ```
  - id, employee_id (FK), training_program_id (FK)
  - assigned_by, assigned_at, due_date
  - started_at, completed_at, status
  - score, certificate_path, notes
  - created_at, updated_at
  ```

- [ ] **6.1.3** Create `certifications` migration
  ```
  - id, employee_id (FK), name, issuing_authority
  - credential_id, issue_date, expiry_date
  - document_id (FK nullable), is_verified
  - verified_by, verified_at, notes
  - created_at, updated_at
  ```

- [ ] **6.1.4** Create models and services

- [ ] **6.1.5** Create training pages
  - [ ] `pages/training/Index.vue`
  - [ ] `pages/training/Programs.vue`
  - [ ] `pages/training/Assignments.vue`
  - [ ] `pages/certifications/Index.vue`
  - [ ] `pages/my-training/Index.vue`

### Sprint 6.2: Onboarding Module (Days 5-6)

- [ ] **6.2.1** Create `onboarding_templates` migration
  ```
  - id, company_id (FK), name, description
  - department_id (FK nullable), duration_days
  - tasks (JSON), is_active, created_at, updated_at
  ```

- [ ] **6.2.2** Create `onboarding_checklists` migration
  ```
  - id, employee_id (FK), template_id (FK)
  - started_at, due_date, completed_at
  - status, assigned_buddy_id (FK nullable)
  - notes, created_at, updated_at
  ```

- [ ] **6.2.3** Create `onboarding_tasks` migration
  ```
  - id, checklist_id (FK), title, description
  - category, assigned_to_id (FK nullable)
  - due_date, completed_at, status
  - notes, sort_order, created_at, updated_at
  ```

- [ ] **6.2.4** Create onboarding pages
  - [ ] `pages/onboarding/Index.vue`
  - [ ] `pages/onboarding/Templates.vue`
  - [ ] `pages/onboarding/Checklist.vue`

### Sprint 6.3: Reports & Analytics (Days 7-10)

- [ ] **6.3.1** Create `ReportService`
  - [ ] Headcount reports
  - [ ] Turnover analysis
  - [ ] Attendance summary
  - [ ] Leave utilization
  - [ ] Payroll summary
  - [ ] Performance distribution

- [ ] **6.3.2** Create chart components
  - [ ] `BarChart.vue`
  - [ ] `LineChart.vue`
  - [ ] `PieChart.vue`
  - [ ] `StatCard.vue`

- [ ] **6.3.3** Create report pages
  - [ ] `pages/reports/Index.vue` - dashboard
  - [ ] `pages/reports/Headcount.vue`
  - [ ] `pages/reports/Attendance.vue`
  - [ ] `pages/reports/Leave.vue`
  - [ ] `pages/reports/Payroll.vue`
  - [ ] `pages/reports/Performance.vue`

- [ ] **6.3.4** Create export functionality
  - [ ] PDF reports
  - [ ] Excel exports

### Sprint 6.4: Dashboard & Notifications (Days 11-12)

- [ ] **6.4.1** Update main dashboard
  - [ ] Quick stats widgets
  - [ ] Recent activity feed
  - [ ] Pending approvals
  - [ ] Upcoming events
  - [ ] Birthday/anniversary alerts

- [ ] **6.4.2** Create notification system
  - [ ] In-app notifications
  - [ ] Email notifications
  - [ ] Notification preferences

- [ ] **6.4.3** Create activity log viewer

### Sprint 6.5: Final Polish (Days 13-14)

- [ ] **6.5.1** Mobile responsiveness audit
  - [ ] Test all pages on mobile
  - [ ] Fix layout issues
  - [ ] Optimize touch interactions

- [ ] **6.5.2** Performance optimization
  - [ ] Database query optimization
  - [ ] Add indexes
  - [ ] Implement caching
  - [ ] Lazy loading components

- [ ] **6.5.3** Security audit
  - [ ] Review all policies
  - [ ] Test authorization
  - [ ] Input validation
  - [ ] XSS prevention

- [ ] **6.5.4** Documentation
  - [ ] API documentation
  - [ ] User guide
  - [ ] Admin guide

- [ ] **6.5.5** Final testing
  - [ ] End-to-end testing
  - [ ] User acceptance testing
  - [ ] Bug fixes

---

## Feature Modules Summary

| Module | Backend | Frontend | Tests | Status |
|--------|:-------:|:--------:|:-----:|:------:|
| Multi-tenancy | ✅ | ⬜ | ⬜ | In Progress |
| RBAC | ✅ | ⬜ | ⬜ | In Progress |
| Companies | 🟡 | ⬜ | ⬜ | In Progress |
| Locations | 🟡 | ⬜ | ⬜ | In Progress |
| Departments | 🟡 | ⬜ | ⬜ | In Progress |
| Job Titles | 🟡 | ⬜ | ⬜ | In Progress |
| Employees | 🟡 | ⬜ | ⬜ | In Progress |
| Org Chart | ⬜ | ⬜ | ⬜ | Not Started |
| Attendance | ⬜ | ⬜ | ⬜ | Not Started |
| Leave | ⬜ | ⬜ | ⬜ | Not Started |
| Payroll | ⬜ | ⬜ | ⬜ | Not Started |
| Documents | ⬜ | ⬜ | ⬜ | Not Started |
| Performance | ⬜ | ⬜ | ⬜ | Not Started |
| Recruitment | ⬜ | ⬜ | ⬜ | Not Started |
| Training | ⬜ | ⬜ | ⬜ | Not Started |
| Onboarding | ⬜ | ⬜ | ⬜ | Not Started |
| Reports | ⬜ | ⬜ | ⬜ | Not Started |
| Dashboard | ⬜ | ⬜ | ⬜ | Not Started |

**Legend:** ⬜ Not Started | 🟡 In Progress | ✅ Complete

---

## Database Schema

### Entity Relationship Overview

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  companies  │────<│  locations  │────<│ departments │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  job_titles │     │  employees  │<────│   users     │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │
       └───────────────────┤
                           │
       ┌───────────────────┼───────────────────┐
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ attendance  │     │   leave     │     │  payroll    │
│  records    │     │  requests   │     │  payslips   │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ performance │     │ recruitment │     │  training   │
│  reviews    │     │ candidates  │     │  programs   │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Table Count by Module

| Module | Tables |
|--------|--------|
| Core | 8 (companies, locations, departments, job_titles, employees, employee_managers, emergency_contacts, users) |
| Attendance | 3 (attendance_records, work_schedules, overtime_requests) |
| Leave | 4 (leave_types, leave_balances, leave_requests, holidays) |
| Payroll | 5 (salary_components, employee_salary_components, pay_periods, payroll_runs, payslips) |
| Documents | 2 (document_categories, employee_documents) |
| Performance | 4 (review_cycles, review_templates, performance_reviews, goals) |
| Recruitment | 4 (job_postings, candidates, interviews, candidate_notes) |
| Training | 3 (training_programs, employee_trainings, certifications) |
| Onboarding | 3 (onboarding_templates, onboarding_checklists, onboarding_tasks) |
| System | 3 (roles, permissions, activity_log) |
| **Total** | **~39 tables** |

---

## Quick Commands Reference

```bash
# Development
composer dev                    # Start all services

# Database
php artisan migrate             # Run migrations
php artisan migrate:fresh --seed # Fresh database with seeds
php artisan db:seed             # Run seeders

# Cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Testing
php artisan test                # Run all tests
php artisan test --filter=Employee  # Run specific tests

# Code Quality
./vendor/bin/pint               # Fix PHP code style
npm run lint                    # Fix JS/Vue code style
npm run format                  # Format with Prettier
```

---

## Notes & Decisions Log

| Date | Decision | Rationale |
|------|----------|-----------|
| 2025-12-03 | MySQL (XAMPP) for database | Local development with phpMyAdmin |
| 2025-12-03 | Single DB multi-tenancy | Simpler for 100s of employees |
| 2025-12-03 | spatie/laravel-permission for RBAC | Industry standard, well maintained |
| 2025-12-03 | shadcn/vue components | Already in use, consistent UI |

---

## Demo Users

After running `php artisan migrate:fresh --seed`, the following users are available:

| Email | Password | Role | Company |
|-------|----------|------|---------|
| admin@employeem.com | password | super_admin | None (system admin) |
| john@acme.com | password | company_admin | Acme Corporation |
| sarah@acme.com | password | hr_manager | Acme Corporation |
| mike@acme.com | password | employee | Acme Corporation |

---

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/guide)
- [Inertia.js Documentation](https://inertiajs.com)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [shadcn/vue Components](https://www.shadcn-vue.com)
- [Tailwind CSS](https://tailwindcss.com/docs)

---

*Last Updated: 2025-12-03*
