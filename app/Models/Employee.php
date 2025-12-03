<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'job_title_id',
        'location_id',
        'employee_number',
        'hire_date',
        'probation_end_date',
        'status',
        'employment_type',
        'work_email',
        'work_phone',
        'salary',
        'pay_frequency',
        'bank_account_number',
        'bank_name',
        'tax_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'personal_email',
        'personal_phone',
        'termination_date',
        'termination_reason',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'date_of_birth' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
    ];

    protected $hidden = [
        'salary',
        'bank_account_number',
        'bank_name',
        'tax_id',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_managers', 'employee_id', 'manager_id')
            ->withPivot(['is_primary', 'started_at', 'ended_at'])
            ->withTimestamps();
    }

    public function directReports(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_managers', 'manager_id', 'employee_id')
            ->withPivot(['is_primary', 'started_at', 'ended_at'])
            ->withTimestamps();
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    public function getPrimaryManagerAttribute(): ?Employee
    {
        return $this->managers()->wherePivot('is_primary', true)->first();
    }

    public function getPrimaryEmergencyContactAttribute(): ?EmergencyContact
    {
        return $this->emergencyContacts()->where('is_primary', true)->first();
    }

    // Helpers
    public function isOnProbation(): bool
    {
        return $this->probation_end_date && $this->probation_end_date->isFuture();
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function getYearsOfService(): float
    {
        $endDate = $this->termination_date ?? now();
        return $this->hire_date->diffInYears($endDate);
    }
}
