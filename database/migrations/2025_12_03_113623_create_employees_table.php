<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            
            // Employment info
            $table->string('employee_number')->unique();
            $table->date('hire_date');
            $table->date('probation_end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern', 'temporary'])->default('full_time');
            
            // Work contact
            $table->string('work_email')->nullable();
            $table->string('work_phone')->nullable();
            
            // Compensation
            $table->decimal('salary', 12, 2)->nullable();
            $table->enum('pay_frequency', ['weekly', 'bi_weekly', 'semi_monthly', 'monthly'])->default('monthly');
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('tax_id')->nullable();
            
            // Personal info
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'other'])->nullable();
            $table->string('nationality')->nullable();
            
            // Personal address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            
            // Personal contact
            $table->string('personal_email')->nullable();
            $table->string('personal_phone')->nullable();
            
            // Termination
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'department_id']);
            $table->index(['company_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
