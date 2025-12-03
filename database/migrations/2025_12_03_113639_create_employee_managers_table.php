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
        Schema::create('employee_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('employees')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique(['employee_id', 'manager_id']);
            $table->index(['employee_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_managers');
    }
};
