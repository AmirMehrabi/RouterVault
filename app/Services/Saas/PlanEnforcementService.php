<?php

namespace App\Services\Saas;

use App\Models\Plan;
use App\Models\Tenant;

class PlanEnforcementService
{
    public function canAddRouter(Tenant $tenant): bool
    {
        return $tenant->canAddRouter();
    }

    public function getRouterUsage(Tenant $tenant): array
    {
        return $tenant->getRouterUsage();
    }

    public function calculateOverage(Tenant $tenant): float
    {
        $usage = $this->getRouterUsage($tenant);
        $extraRouterPlan = Plan::extraRouterPlan()->first();

        if ($extraRouterPlan === null || $usage['overage'] <= 0) {
            return 0.0;
        }

        return $usage['overage'] * (float) $extraRouterPlan->price;
    }

    public function canAddUser(Tenant $tenant): bool
    {
        $currentUserCount = $tenant->users()->count();
        $maxUsers = $tenant->max_users;

        return $currentUserCount < $maxUsers;
    }

    public function getBackupRetentionDays(Tenant $tenant): int
    {
        return $tenant->backup_retention_days;
    }

    public function getAlertChannels(Tenant $tenant): array
    {
        return $tenant->alert_channels;
    }

    public function getMaxRouters(Tenant $tenant): int
    {
        return $tenant->max_routers + $tenant->extra_routers_count;
    }

    public function getPlanLimits(Tenant $tenant): array
    {
        return [
            'max_routers' => $tenant->max_routers,
            'extra_routers' => $tenant->extra_routers_count,
            'total_allowed' => $this->getMaxRouters($tenant),
            'max_users' => $tenant->max_users,
            'backup_retention_days' => $tenant->backup_retention_days,
            'alert_channels' => $tenant->alert_channels,
        ];
    }
}
