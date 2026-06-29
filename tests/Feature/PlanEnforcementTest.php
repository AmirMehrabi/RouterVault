<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Saas\PlanEnforcementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected PlanEnforcementService $service;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PlanEnforcementService::class);

        $plan = Plan::create([
            'name' => 'Starter',
            'internal_name' => 'test_starter',
            'price' => 9,
            'max_routers' => 3,
            'backup_retention_days' => 30,
            'alert_channels' => ['in_app', 'email'],
            'max_users' => 3,
            'is_saas_plan' => true,
            'status' => 'active',
        ]);

        $this->tenant = Tenant::create([
            'id' => 'test-tenant-id',
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'company_name' => 'Test Company',
            'email' => 'test@example.com',
            'status' => 'active',
            'saas_plan_id' => $plan->id,
        ]);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_can_add_router_within_limit(): void
    {
        Router::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router 1',
            'ip_address' => '192.168.1.1',
            'status' => 'offline',
        ]);

        $this->assertTrue($this->service->canAddRouter($this->tenant));
    }

    public function test_cannot_add_router_beyond_limit(): void
    {
        for ($i = 0; $i < 3; $i++) {
            Router::create([
                'tenant_id' => $this->tenant->id,
                'name' => 'Router '.$i,
                'ip_address' => '192.168.1.'.($i + 1),
                'status' => 'offline',
            ]);
        }

        $this->assertFalse($this->service->canAddRouter($this->tenant));
    }

    public function test_can_add_router_with_extra_routers(): void
    {
        $this->tenant->update(['extra_routers_count' => 2]);

        for ($i = 0; $i < 4; $i++) {
            Router::create([
                'tenant_id' => $this->tenant->id,
                'name' => 'Router '.$i,
                'ip_address' => '192.168.1.'.($i + 1),
                'status' => 'offline',
            ]);
        }

        $this->assertTrue($this->service->canAddRouter($this->tenant));
    }

    public function test_router_usage_tracking(): void
    {
        Router::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router 1',
            'ip_address' => '192.168.1.1',
            'status' => 'offline',
        ]);

        $usage = $this->service->getRouterUsage($this->tenant);

        $this->assertEquals(1, $usage['current']);
        $this->assertEquals(3, $usage['limit']);
        $this->assertTrue($usage['can_add']);
        $this->assertEquals(0, $usage['overage']);
    }

    public function test_overage_calculation(): void
    {
        $extraPlan = Plan::create([
            'name' => 'Extra Router',
            'internal_name' => 'test_extra',
            'price' => 1,
            'is_saas_plan' => true,
            'is_extra_router' => true,
            'status' => 'active',
        ]);

        for ($i = 0; $i < 5; $i++) {
            Router::create([
                'tenant_id' => $this->tenant->id,
                'name' => 'Router '.$i,
                'ip_address' => '192.168.1.'.($i + 1),
                'status' => 'offline',
            ]);
        }

        $overage = $this->service->calculateOverage($this->tenant);

        $this->assertEquals(2.0, $overage);
    }

    public function test_plan_limits_retrieval(): void
    {
        $limits = $this->service->getPlanLimits($this->tenant);

        $this->assertEquals(3, $limits['max_routers']);
        $this->assertEquals(30, $limits['backup_retention_days']);
        $this->assertEquals(['in_app', 'email'], $limits['alert_channels']);
        $this->assertEquals(3, $limits['max_users']);
    }
}
