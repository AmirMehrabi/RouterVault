<?php

namespace App\Models;

use Database\Factories\RouterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Router extends Model
{
    /** @use HasFactory<RouterFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'password_manager_credential_id',
        'name',
        'model',
        'vendor',
        'ip_address',
        'api_port',
        'use_ssl',
        'legacy_login',
        'api_username',
        'api_password',
        'ssh_port',
        'ssh_auth_method',
        'ssh_private_key',
        'ssh_timeout',
        'location',
        'site',
        'status',
        'last_checked_at',
        'last_connected_at',
        'last_error',
        'version',
        'uptime',
        'cpu_usage',
        'memory_usage',
        'active_sessions_count',
        'total_customers',
        'enable_monitoring',
        'enable_provisioning',
        'timeout',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enable_monitoring' => 'boolean',
            'enable_provisioning' => 'boolean',
            'use_ssl' => 'boolean',
            'legacy_login' => 'boolean',
            'last_checked_at' => 'datetime',
            'last_connected_at' => 'datetime',
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

    public function passwordManagerCredential(): BelongsTo
    {
        return $this->belongsTo(PasswordManagerCredential::class, 'password_manager_credential_id');
    }

    public function accessPoints(): HasMany
    {
        return $this->hasMany(AccessPoint::class);
    }

    public function wirelessClients(): HasMany
    {
        return $this->hasMany(WirelessClient::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(RouterBackup::class);
    }

    public function latestBackup(): HasOne
    {
        return $this->hasOne(RouterBackup::class)->latestOfMany();
    }

    public function backupSchedules(): BelongsToMany
    {
        return $this->belongsToMany(BackupSchedule::class)->withTimestamps();
    }

    public function diffAlerts(): HasMany
    {
        return $this->hasMany(DiffAlert::class);
    }

    public function resolvedApiUsername(): ?string
    {
        return $this->passwordManagerCredential?->username ?? $this->api_username;
    }

    public function resolvedApiPassword(): ?string
    {
        return $this->passwordManagerCredential?->password ?? $this->api_password;
    }

    /**
     * @return array<string, mixed>
     */
    public function routerOsConfig(): array
    {
        $timeout = (int) ($this->timeout ?: 10);

        return [
            'host' => $this->ip_address,
            'user' => $this->resolvedApiUsername(),
            'pass' => $this->resolvedApiPassword() ?? '',
            'port' => (int) ($this->api_port ?: 8728),
            'ssl' => (bool) $this->use_ssl,
            'legacy' => (bool) $this->legacy_login,
            'timeout' => $timeout,
            'socket_timeout' => max($timeout, 10),
            'attempts' => 1,
            'delay' => 1,
            'ssh_port' => (int) ($this->ssh_port ?: 22),
            'ssh_private_key' => $this->ssh_private_key ?: '~/.ssh/id_rsa',
            'ssh_timeout' => (int) ($this->ssh_timeout ?: 30),
        ];
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('site', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['vendor'] ?? null, function ($query, $vendor) {
            $query->where('vendor', $vendor);
        })->when($filters['site'] ?? null, function ($query, $site) {
            $query->where('site', $site);
        });
    }

    public static function getFilterOptions(): array
    {
        $sites = self::query()
            ->whereNotNull('site')
            ->where('site', '!=', '')
            ->distinct()
            ->pluck('site')
            ->map(fn ($site) => ['value' => $site, 'label' => $site])
            ->values()
            ->toArray();

        return [
            'sites' => $sites,
        ];
    }

    public static function getStats(): array
    {
        $query = self::query();

        return [
            'total' => (clone $query)->count(),
            'online' => (clone $query)->where('status', 'online')->count(),
            'offline' => (clone $query)->where('status', 'offline')->count(),
            'activeSessions' => (clone $query)->sum('active_sessions_count') ?? 0,
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

        static::creating(function ($router) {
            if (tenant()?->id && empty($router->tenant_id)) {
                $router->tenant_id = tenant()->id;
            } elseif (auth()->check() && empty($router->tenant_id)) {
                $router->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
