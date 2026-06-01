<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = static::currentTenantId();

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model) {
            $tenantId = static::currentTenantId();

            if ($tenantId && ! $model->getAttribute('tenant_id')) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }

    protected static function currentTenantId(): ?string
    {
        if (! function_exists('tenancy') || ! tenancy()->initialized) {
            return null;
        }

        return tenant('id');
    }
}
