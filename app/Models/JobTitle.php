<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'level',
        'min_salary',
        'max_salary',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Helpers
    public function getSalaryRangeAttribute(): string
    {
        if ($this->min_salary && $this->max_salary) {
            return number_format($this->min_salary, 0) . ' - ' . number_format($this->max_salary, 0);
        }
        if ($this->min_salary) {
            return 'From ' . number_format($this->min_salary, 0);
        }
        if ($this->max_salary) {
            return 'Up to ' . number_format($this->max_salary, 0);
        }
        return 'Not specified';
    }
}
