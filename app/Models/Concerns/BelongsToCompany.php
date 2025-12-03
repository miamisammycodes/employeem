<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToCompany(): void
    {
        // Auto-scope queries to current company when authenticated
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where($builder->getModel()->getTable() . '.company_id', auth()->user()->company_id);
            }
        });

        // Auto-set company_id on create
        static::creating(function (Model $model) {
            if (auth()->check() && auth()->user()->company_id && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    /**
     * Get the company that owns this model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to a specific company.
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('company')->where('company_id', $companyId);
    }

    /**
     * Remove the company scope for this query.
     */
    public function scopeWithoutCompanyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }
}
