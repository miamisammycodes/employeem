<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'timezone',
        'is_headquarters',
        'phone',
        'email',
    ];

    protected $casts = [
        'is_headquarters' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeHeadquarters($query)
    {
        return $query->where('is_headquarters', true);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('company', fn ($q) => $q->where('is_active', true));
    }

    // Helpers
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ])->filter()->implode(', ');
    }
}
