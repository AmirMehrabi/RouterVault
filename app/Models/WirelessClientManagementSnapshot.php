<?php

namespace App\Models;

use Database\Factories\WirelessClientManagementSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WirelessClientManagementSnapshot extends Model
{
    /** @use HasFactory<WirelessClientManagementSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'wireless_client_id',
        'action_key',
        'snapshot_type',
        'payload',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'collected_at' => 'datetime',
        ];
    }

    public function wirelessClient(): BelongsTo
    {
        return $this->belongsTo(WirelessClient::class);
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

        static::creating(function (WirelessClientManagementSnapshot $snapshot) {
            if (tenant()?->id && empty($snapshot->tenant_id)) {
                $snapshot->tenant_id = tenant()->id;
            } elseif (auth()->check() && empty($snapshot->tenant_id)) {
                $snapshot->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
