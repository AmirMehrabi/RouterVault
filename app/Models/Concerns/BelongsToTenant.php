<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (tenant()?->id) {
                $query->where('tenant_id', tenant()->id);
            } elseif (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model): void {
            if (tenant()?->id && empty($model->tenant_id)) {
                $model->tenant_id = tenant()->id;
            } elseif (auth()->check() && empty($model->tenant_id)) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
