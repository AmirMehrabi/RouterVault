<?php

namespace App\Models;

use Database\Factories\WirelessClientManagementLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WirelessClientManagementLog extends Model
{
    /** @use HasFactory<WirelessClientManagementLogFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'wireless_client_id',
        'user_id',
        'action_key',
        'action_label',
        'status',
        'target_host',
        'request_payload',
        'command_batch',
        'response_payload',
        'summary',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'command_batch' => 'array',
            'response_payload' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function wirelessClient(): BelongsTo
    {
        return $this->belongsTo(WirelessClient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

        static::creating(function (WirelessClientManagementLog $log) {
            if (tenant()?->id && empty($log->tenant_id)) {
                $log->tenant_id = tenant()->id;
            } elseif (auth()->check() && empty($log->tenant_id)) {
                $log->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
