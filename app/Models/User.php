<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'activity_streak_count',
        'last_activity_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'name')
            ->where('tenant_id', $this->tenant_id);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function badgeAwards(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['tenant_id', 'awarded_at', 'metadata'])
            ->withTimestamps();
    }

    public function backupSchedules(): HasMany
    {
        return $this->hasMany(BackupSchedule::class, 'tenant_id', 'tenant_id');
    }

    public function routerBackups(): HasMany
    {
        return $this->hasMany(RouterBackup::class, 'tenant_id', 'tenant_id');
    }

    public function diffAlerts(): HasMany
    {
        return $this->hasMany(DiffAlert::class, 'tenant_id', 'tenant_id');
    }

    public function diffAlertSettings(): HasMany
    {
        return $this->hasMany(DiffAlertSetting::class, 'tenant_id', 'tenant_id');
    }

    public function getRoleDisplayName(): string
    {
        return match ($this->role) {
            'owner' => 'Owner',
            'admin' => 'Administrator',
            'billing' => 'Billing Manager',
            'support' => 'Support Agent',
            'noc' => 'NOC Engineer',
            default => 'User',
        };
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'owner') {
            return true;
        }

        $role = Role::where('tenant_id', $this->tenant_id)
            ->where('name', $this->role)
            ->first();

        if (! $role) {
            return false;
        }

        return in_array($permission, $role->permissions ?? [])
            || in_array('*', $role->permissions ?? []);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function canAccessTenant(string $tenantId): bool
    {
        return (string) $this->tenant_id === $tenantId;
    }

    public function hasBadge(string $badgeSlug): bool
    {
        if ($this->relationLoaded('badges')) {
            return $this->badges->contains(fn (Badge $badge): bool => $badge->slug === $badgeSlug);
        }

        return $this->badges()
            ->where('slug', $badgeSlug)
            ->exists();
    }

    public function profileBadges(): Collection
    {
        if ($this->relationLoaded('badges')) {
            return $this->badges
                ->where('display', 'profile')
                ->sortBy('name')
                ->values();
        }

        return $this->badges()
            ->where('display', 'profile')
            ->orderBy('name')
            ->get();
    }
}
