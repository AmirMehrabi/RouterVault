<?php

namespace App\Models;

use Database\Factories\AccessPointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessPoint extends Model
{
    /** @use HasFactory<AccessPointFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'router_id',
        'site_id',
        'name',
        'model',
        'vendor',
        'ip_address',
        'mac_address',
        'ssid',
        'band',
        'channel',
        'frequency',
        'tx_power',
        'location',
        'status',
        'firmware_version',
        'uptime',
        'cpu_usage',
        'memory_usage',
        'connected_clients_count',
        'signal_quality',
        'noise_floor',
        'channel_utilization',
        'enable_monitoring',
        'enable_provisioning',
        'last_seen_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'enable_monitoring' => 'boolean',
            'enable_provisioning' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%")
                    ->orWhere('ssid', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['vendor'] ?? null, function ($query, $vendor) {
            $query->where('vendor', $vendor);
        })->when($filters['band'] ?? null, function ($query, $band) {
            $query->where('band', $band);
        })->when($filters['router_id'] ?? null, function ($query, $routerId) {
            $query->where('router_id', $routerId);
        })->when($filters['site_id'] ?? null, function ($query, $siteId) {
            $query->where('site_id', $siteId);
        });
    }

    public static function getFilterOptions(): array
    {
        return [
            'routers' => Router::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Router $router) => ['value' => $router->id, 'label' => $router->name])
                ->values()
                ->toArray(),
            'sites' => Site::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Site $site) => ['value' => $site->id, 'label' => $site->name])
                ->values()
                ->toArray(),
        ];
    }

    public static function getStats(): array
    {
        $query = self::query();

        return [
            'total' => (clone $query)->count(),
            'online' => (clone $query)->where('status', 'online')->count(),
            'offline' => (clone $query)->where('status', 'offline')->count(),
            'maintenance' => (clone $query)->where('status', 'maintenance')->count(),
            'connectedClients' => (clone $query)->sum('connected_clients_count') ?? 0,
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function (AccessPoint $accessPoint) {
            if (auth()->check() && empty($accessPoint->tenant_id)) {
                $accessPoint->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
