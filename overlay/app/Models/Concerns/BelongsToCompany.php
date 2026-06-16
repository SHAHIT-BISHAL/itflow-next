<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Scopes a model to the authenticated user's company and automatically
 * stamps company_id when creating new records.
 *
 * Applied to all tenant-owned models (Client, Tag, Category, ...).
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(function (Builder $builder) {
            if (Auth::check() && Auth::user()->company_id) {
                $builder->where($builder->getModel()->getTable() . '.company_id', Auth::user()->company_id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->company_id) && Auth::check()) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
