<?php

namespace App\Models;

use Database\Factories\WirelessClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WirelessClient extends Model
{
    /** @use HasFactory<WirelessClientFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'password_manager_credential_id',
        'access_point_id',
        'router_id',
        'site_id',
        'mac_address',
        'interface_name',
        'radio_name',
        'host_name',
        'device_identity',
        'device_mac_address',
        'device_version',
        'device_uptime',
        'pppoe_username',
        'comment',
        'ssid',
        'band',
        'frequency',
        'signal_strength',
        'signal_to_noise',
        'tx_rate',
        'rx_rate',
        'tx_ccq',
        'rx_ccq',
        'uptime',
        'last_ip_address',
        'management_ip_address',
        'management_port',
        'management_protocol',
        'provisioning_username',
        'provisioning_password',
        'is_connected',
        'first_seen_at',
        'last_seen_at',
        'last_discovered_at',
        'last_management_status',
        'last_management_message',
        'last_management_ran_at',
        'last_moved_at',
        'last_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'frequency' => 'integer',
            'signal_strength' => 'integer',
            'signal_to_noise' => 'integer',
            'tx_ccq' => 'integer',
            'rx_ccq' => 'integer',
            'management_port' => 'integer',
            'is_connected' => 'boolean',
            'provisioning_password' => 'encrypted',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_discovered_at' => 'datetime',
            'last_management_ran_at' => 'datetime',
            'last_moved_at' => 'datetime',
            'last_snapshot' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function accessPoint(): BelongsTo
    {
        return $this->belongsTo(AccessPoint::class);
    }

    public function passwordManagerCredential(): BelongsTo
    {
        return $this->belongsTo(PasswordManagerCredential::class, 'password_manager_credential_id');
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(WirelessClientMovement::class)->latest('moved_at');
    }

    public function managementLogs(): HasMany
    {
        return $this->hasMany(WirelessClientManagementLog::class)->latest('created_at');
    }

    public function managementSnapshots(): HasMany
    {
        return $this->hasMany(WirelessClientManagementSnapshot::class)->latest('collected_at');
    }

    public function resolvedProvisioningUsername(): ?string
    {
        return $this->passwordManagerCredential?->username ?? $this->provisioning_username;
    }

    public function resolvedProvisioningPassword(): ?string
    {
        return $this->passwordManagerCredential?->password ?? $this->provisioning_password;
    }

    public function resolvedManagementUsername(): ?string
    {
        return $this->resolvedProvisioningUsername();
    }

    public function resolvedManagementPassword(): ?string
    {
        return $this->resolvedProvisioningPassword();
    }

    public function resolvedManagementHost(): ?string
    {
        return $this->management_ip_address ?: $this->last_ip_address;
    }

    public function resolvedManagementPort(): int
    {
        return $this->management_port ?: 8728;
    }

    public function isMikrotikManageable(): bool
    {
        return true;
        $this->loadMissing(['accessPoint:id,vendor', 'router:id,vendor']);

        $vendors = collect([
            $this->accessPoint?->vendor,
            $this->router?->vendor,
        ])->filter();

        return $vendors->isNotEmpty()
            && $vendors->contains(fn (string $vendor): bool => Str::contains(Str::lower($vendor), 'mikrotik'))
            && $this->resolvedManagementHost() !== null
            && $this->resolvedManagementUsername() !== null
            && $this->resolvedManagementPassword() !== null;
    }

    public function provisioningCredentialSource(): string
    {
        if ($this->password_manager_credential_id) {
            return 'password_manager';
        }

        if ($this->provisioning_username || $this->provisioning_password) {
            return 'manual';
        }

        return 'none';
    }

    public function isProvisioned(): bool
    {
        return $this->resolvedProvisioningUsername() !== null && $this->resolvedProvisioningPassword() !== null;
    }

    public function provisioningStatusLabel(): string
    {
        return $this->isProvisioned() ? 'Provisioned' : 'Unprovisioned';
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('mac_address', 'like', "%{$search}%")
                    ->orWhere('host_name', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhere('last_ip_address', 'like', "%{$search}%")
                    ->orWhere('ssid', 'like', "%{$search}%")
                    ->orWhereHas('accessPoint', function ($accessPointQuery) use ($search) {
                        $accessPointQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('site', function ($siteQuery) use ($search) {
                        $siteQuery->where('name', 'like', "%{$search}%");
                    });
            });
        })->when($filters['access_point_id'] ?? null, function ($query, $accessPointId) {
            $query->where('access_point_id', $accessPointId);
        })->when($filters['site_id'] ?? null, function ($query, $siteId) {
            $query->where('site_id', $siteId);
        })->when($filters['band'] ?? null, function ($query, $band) {
            $query->where('band', $band);
        })->when(($filters['connection'] ?? null) === 'connected', function ($query) {
            $query->where('is_connected', true);
        })->when(($filters['connection'] ?? null) === 'disconnected', function ($query) {
            $query->where('is_connected', false);
        });
    }

    public static function getFilterOptions(): array
    {
        return [
            'access_points' => AccessPoint::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (AccessPoint $accessPoint) => ['value' => $accessPoint->id, 'label' => $accessPoint->name])
                ->values()
                ->toArray(),
            'sites' => Site::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Site $site) => ['value' => $site->id, 'label' => $site->name])
                ->values()
                ->toArray(),
            'bands' => self::query()
                ->whereNotNull('band')
                ->where('band', '!=', '')
                ->distinct()
                ->orderBy('band')
                ->pluck('band')
                ->map(fn (string $band) => ['value' => $band, 'label' => $band])
                ->values()
                ->toArray(),
            'connections' => [
                ['value' => 'connected', 'label' => 'Connected'],
                ['value' => 'disconnected', 'label' => 'Disconnected'],
            ],
        ];
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

        static::creating(function (WirelessClient $wirelessClient) {
            if (tenant()?->id && empty($wirelessClient->tenant_id)) {
                $wirelessClient->tenant_id = tenant()->id;
            } elseif (auth()->check() && empty($wirelessClient->tenant_id)) {
                $wirelessClient->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
