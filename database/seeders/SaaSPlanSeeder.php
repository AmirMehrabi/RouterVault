<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class SaaSPlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['internal_name' => 'saas_free'],
            [
                'name' => 'Free',
                'description' => 'Perfect for getting started with 1 router and basic monitoring.',
                'status' => 'active',
                'visibility' => 'public',
                'type' => 'saas',
                'category' => 'saas',
                'price' => 0,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'max_routers' => 1,
                'backup_retention_days' => 7,
                'alert_channels' => ['in_app'],
                'max_users' => 1,
                'is_saas_plan' => true,
                'is_extra_router' => false,
                'priority' => 10,
            ]
        );

        Plan::updateOrCreate(
            ['internal_name' => 'saas_starter'],
            [
                'name' => 'Starter',
                'description' => 'For small teams managing multiple routers with email alerts.',
                'status' => 'active',
                'visibility' => 'public',
                'type' => 'saas',
                'category' => 'saas',
                'price' => 9,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'max_routers' => 3,
                'backup_retention_days' => 30,
                'alert_channels' => ['in_app', 'email'],
                'max_users' => 3,
                'is_saas_plan' => true,
                'is_extra_router' => false,
                'priority' => 20,
            ]
        );

        Plan::updateOrCreate(
            ['internal_name' => 'saas_operator'],
            [
                'name' => 'Operator',
                'description' => 'For ISPs and operators needing full visibility and Telegram alerts.',
                'status' => 'active',
                'visibility' => 'public',
                'type' => 'saas',
                'category' => 'saas',
                'price' => 19,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'max_routers' => 10,
                'backup_retention_days' => 180,
                'alert_channels' => ['in_app', 'email', 'telegram'],
                'max_users' => 10,
                'is_saas_plan' => true,
                'is_extra_router' => false,
                'priority' => 30,
            ]
        );

        Plan::updateOrCreate(
            ['internal_name' => 'saas_extra_router'],
            [
                'name' => 'Extra Router',
                'description' => 'Add one extra router beyond your plan limit.',
                'status' => 'active',
                'visibility' => 'public',
                'type' => 'saas',
                'category' => 'saas',
                'price' => 1,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'max_routers' => 1,
                'backup_retention_days' => 0,
                'alert_channels' => [],
                'max_users' => 0,
                'is_saas_plan' => true,
                'is_extra_router' => true,
                'priority' => 5,
            ]
        );
    }
}
