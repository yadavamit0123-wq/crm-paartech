<?php

namespace App\Models\Concerns;

use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (! $model->tenant_id && TenantService::id()) {
                $model->tenant_id = TenantService::id();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->user()?->isSuperAdmin()) {
                return;
            }

            $tenantId = TenantService::id() ?? auth()->user()?->tenant_id;

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
