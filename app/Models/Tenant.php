<?php

namespace App\Models;

use App\Enums\OnboardingStep;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'company_name',
        'email',
        'phone',
        'country',
        'timezone',
        'status',
        'plan_id',
        'trial_ends_at',
        'saas_plan_id',
        'subscription_status',
        'subscription_starts_at',
        'subscription_expires_at',
        'extra_routers_count',
        'next_billing_at',
        'onboarding_completed',
        'onboarding_step',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_starts_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'onboarding_completed' => 'boolean',
            'onboarding_step' => OnboardingStep::class,
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function saasPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'saas_plan_id');
    }

    public function tenantSubscription(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function routers(): HasMany
    {
        return $this->hasMany(Router::class);
    }

    public function accessPoints(): HasMany
    {
        return $this->hasMany(AccessPoint::class);
    }

    public function backupSchedules(): HasMany
    {
        return $this->hasMany(BackupSchedule::class);
    }

    public function routerBackups(): HasMany
    {
        return $this->hasMany(RouterBackup::class);
    }

    public function diffAlerts(): HasMany
    {
        return $this->hasMany(DiffAlert::class);
    }

    public function diffAlertSetting(): HasMany
    {
        return $this->hasMany(DiffAlertSetting::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasExpiredTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function getMaxRoutersAttribute(): int
    {
        return $this->saasPlan?->max_routers ?? 1;
    }

    public function getBackupRetentionDaysAttribute(): int
    {
        return $this->saasPlan?->backup_retention_days ?? 7;
    }

    public function getAlertChannelsAttribute(): array
    {
        return $this->saasPlan?->alert_channels ?? ['in_app'];
    }

    public function getMaxUsersAttribute(): int
    {
        return $this->saasPlan?->max_users ?? 1;
    }

    public function canAddRouter(): bool
    {
        $currentCount = $this->routers()->count();
        $maxRouters = $this->max_routers + $this->extra_routers_count;

        return $currentCount < $maxRouters;
    }

    public function isSubscriptionActive(): bool
    {
        return $this->subscription_status === 'active'
            && $this->subscription_expires_at
            && $this->subscription_expires_at->isFuture();
    }

    public function getRouterUsage(): array
    {
        $current = $this->routers()->count();
        $limit = $this->max_routers;
        $extra = $this->extra_routers_count;

        return [
            'current' => $current,
            'limit' => $limit,
            'extra' => $extra,
            'total_allowed' => $limit + $extra,
            'can_add' => $current < ($limit + $extra),
            'overage' => max(0, $current - $limit),
        ];
    }
}
