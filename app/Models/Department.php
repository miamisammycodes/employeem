<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'location_id',
        'parent_id',
        'name',
        'code',
        'description',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Hierarchy helpers
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        return $ancestors;
    }

    public function getDescendants()
    {
        $descendants = collect();
        $this->loadMissing('children');
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }
        return $descendants;
    }

    public function getAllDescendantIds(): array
    {
        return $this->getDescendants()->pluck('id')->toArray();
    }

    public function getDepth(): int
    {
        return $this->getAncestors()->count();
    }

    public function getPath(): string
    {
        $path = $this->getAncestors()->reverse()->pluck('name')->push($this->name);
        return $path->implode(' > ');
    }
}
