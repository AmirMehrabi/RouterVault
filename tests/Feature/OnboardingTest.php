<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name' => 'Free',
            'internal_name' => 'test_free',
            'type' => 'saas',
            'price' => 0,
            'max_routers' => 1,
            'backup_retention_days' => 7,
            'alert_channels' => ['in_app'],
            'max_users' => 1,
            'is_saas_plan' => true,
            'status' => 'active',
        ]);

        Plan::factory()->create([
            'name' => 'Starter',
            'internal_name' => 'fixture_starter',
            'type' => 'saas',
            'is_saas_plan' => true,
            'is_extra_router' => false,
            'status' => 'active',
        ]);

        Plan::factory()->create([
            'name' => 'Operator',
            'internal_name' => 'fixture_operator',
            'type' => 'saas',
            'is_saas_plan' => true,
            'is_extra_router' => false,
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
            'subscription_status' => 'active',
            'onboarding_step' => 'router',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_onboarding_index_requires_auth(): void
    {
        $this->get(route('onboarding.index'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_onboarding_index_resumes_at_the_current_step(): void
    {
        $this->actingAs($this->user)
            ->get(route('onboarding.index'))
            ->assertRedirect(route('onboarding.step', 3));
    }

    public function test_onboarding_step_1_shows_plans(): void
    {
        $this->actingAs($this->user)
            ->get(route('onboarding.step', 1))
            ->assertOk()
            ->assertSee('Free')
            ->assertSee('Starter')
            ->assertSee('Operator');
    }

    public function test_select_free_plan_activates_immediately(): void
    {
        $this->actingAs($this->user)
            ->post(route('onboarding.plan'), ['plan_id' => Plan::where('internal_name', 'test_free')->first()->id])
            ->assertRedirect(route('onboarding.step', 3));

        $this->tenant->refresh();
        $this->assertEquals('active', $this->tenant->subscription_status);
    }

    public function test_select_paid_plan_redirects_to_payment(): void
    {
        $paidPlan = Plan::create([
            'name' => 'Starter',
            'internal_name' => 'test_starter',
            'type' => 'saas',
            'price' => 9,
            'max_routers' => 3,
            'backup_retention_days' => 30,
            'alert_channels' => ['in_app', 'email'],
            'max_users' => 3,
            'is_saas_plan' => true,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->post(route('onboarding.plan'), ['plan_id' => $paidPlan->id])
            ->assertRedirect(route('onboarding.step', 2));

        $this->tenant->refresh();
        $this->assertEquals('pending', $this->tenant->subscription_status);

        $this->actingAs($this->user)
            ->get(route('onboarding.step', 2))
            ->assertOk()
            ->assertSee('Starter');

        $this->actingAs($this->user)
            ->post(route('onboarding.payment'))
            ->assertRedirect(route('onboarding.step', 3));

        $this->assertSame($paidPlan->id, $this->tenant->refresh()->saas_plan_id);
        $this->assertSame('active', $this->tenant->subscription_status);
    }

    public function test_add_router_during_onboarding(): void
    {
        $this->actingAs($this->user)
            ->post(route('onboarding.router'), [
                'name' => 'Test Router',
                'ip_address' => '192.168.1.1',
                'api_username' => 'admin',
                'api_password' => 'password',
            ])
            ->assertRedirect(route('onboarding.step', 4));

        $this->assertDatabaseHas('routers', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Router',
        ]);
    }

    public function test_different_tenants_can_add_routers_with_the_same_ip_address(): void
    {
        $otherTenant = Tenant::create([
            'id' => 'other-tenant-id',
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'company_name' => 'Other Company',
            'email' => 'other@example.com',
            'status' => 'active',
            'saas_plan_id' => $this->tenant->saas_plan_id,
        ]);

        Router::factory()->create([
            'tenant_id' => $otherTenant->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->user)
            ->post(route('onboarding.router'), [
                'name' => 'Test Router',
                'ip_address' => '192.168.1.1',
                'api_username' => 'admin',
                'api_password' => 'password',
            ])
            ->assertRedirect(route('onboarding.step', 4))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('routers', 2);
    }

    public function test_duplicate_router_ip_address_for_the_same_tenant_returns_a_validation_error(): void
    {
        Router::factory()->create([
            'tenant_id' => $this->tenant->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->user)
            ->from(route('onboarding.step', 3))
            ->post(route('onboarding.router'), [
                'name' => 'Duplicate Router',
                'ip_address' => '192.168.1.1',
                'api_username' => 'admin',
                'api_password' => 'password',
            ])
            ->assertRedirect(route('onboarding.step', 3))
            ->assertSessionHasErrors([
                'ip_address' => 'A router with this IP address already exists in your account.',
            ]);

        $this->assertDatabaseCount('routers', 1);
    }

    public function test_complete_onboarding(): void
    {
        $this->actingAs($this->user)
            ->post(route('onboarding.router.skip'))
            ->assertRedirect(route('onboarding.completed'));

        $this->tenant->refresh();
        $this->assertTrue($this->tenant->onboarding_completed);
        $this->assertNotNull($this->tenant->onboarding_completed_at);
    }

    public function test_backup_step_can_be_skipped_with_a_post_request(): void
    {
        $this->tenant->update(['onboarding_step' => 'backups']);

        $this->actingAs($this->user)
            ->post(route('onboarding.backup.skip'))
            ->assertRedirect(route('onboarding.completed'));

        $this->assertTrue($this->tenant->refresh()->onboarding_completed);
    }
}
