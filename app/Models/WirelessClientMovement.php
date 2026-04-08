<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WirelessClientMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'wireless_client_id',
        'from_access_point_id',
        'to_access_point_id',
        'from_site_id',
        'to_site_id',
        'from_router_id',
        'to_router_id',
        'moved_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function wirelessClient(): BelongsTo
    {
        return $this->belongsTo(WirelessClient::class);
    }

    public function fromAccessPoint(): BelongsTo
    {
        return $this->belongsTo(AccessPoint::class, 'from_access_point_id');
    }

    public function toAccessPoint(): BelongsTo
    {
        return $this->belongsTo(AccessPoint::class, 'to_access_point_id');
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
