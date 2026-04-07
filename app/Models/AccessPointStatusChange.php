<?php

namespace App\Models;

use Database\Factories\AccessPointStatusChangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessPointStatusChange extends Model
{
    /** @use HasFactory<AccessPointStatusChangeFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'access_point_id',
        'previous_status',
        'current_status',
        'reason',
        'checked_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function accessPoint(): BelongsTo
    {
        return $this->belongsTo(AccessPoint::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (tenant()?->id) {
                $query->where('tenant_id', tenant()->id);
            } elseif (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }
}
