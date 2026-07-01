<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Plan $starter;

    protected Plan $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->starter = $this->createPlan('Starter', 'saas_starter', 9, 3, 20);
        $this->operator = $this->createPlan('Operator', 'saas_operator', 19, 10, 30);
        $this->createPlan('Extra Router', 'saas_extra_router', 2, 1, 5, true);

        $this->tenant = Tenant::create([
            'id' => 'billing-tenant',
            'name' => 'Billing Tenant',
            'slug' => 'billing-tenant',
            'company_name' => 'Billing Tenant',
            'email' => 'billing@example.com',
            'status' => 'active',
            'saas_plan_id' => $this->starter->id,
            'subscription_status' => 'active',
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
            'onboarding_completed' => true,
            'onboarding_step' => 'complete',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => 'owner',
            'status' => 'active',
        ]);

        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->starter->id,
            'status' => 'active',
            'amount' => 9,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);
    }

    public function test_upgrade_stays_pending_until_checkout_is_confirmed(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('billing.upgrade'), ['plan_id' => $this->operator->id]);

        $payment = Payment::query()->where('status', 'pending')->firstOrFail();
        $response->assertRedirect(route('billing.payment', $payment));
        $this->assertSame($this->starter->id, $this->tenant->refresh()->saas_plan_id);

        $this->actingAs($this->user)
            ->patch(route('billing.payment.process', $payment))
            ->assertRedirect(route('billing.payment.confirmation', $payment));

        $this->assertSame($this->operator->id, $this->tenant->refresh()->saas_plan_id);
        $this->assertDatabaseHas('tenant_payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'currency' => 'EUR',
        ]);
    }

    public function test_extra_router_capacity_is_granted_once_after_payment(): void
    {
        $this->actingAs($this->user)
            ->post(route('billing.extra-routers.store'), ['quantity' => 2]);

        $payment = Payment::query()->where('status', 'pending')->firstOrFail();

        $this->actingAs($this->user)->patch(route('billing.payment.process', $payment));
        $this->actingAs($this->user)->patch(route('billing.payment.process', $payment));

        $this->assertSame(2, $this->tenant->refresh()->extra_routers_count);
        $this->assertSame('4.00', $payment->refresh()->amount);
    }

    public function test_plan_page_uses_real_usage_and_shows_the_limit_banner(): void
    {
        for ($index = 1; $index <= 3; $index++) {
            Router::factory()->create([
                'tenant_id' => $this->tenant->id,
                'ip_address' => "192.168.10.{$index}",
            ]);
        }

        $this->actingAs($this->user)
            ->get(route('billing.subscription'))
            ->assertOk()
            ->assertSee('Router limit reached')
            ->assertSee('3 of 3')
            ->assertSee('€19')
            ->assertSee('€2');
    }

    protected function createPlan(
        string $name,
        string $internalName,
        int $price,
        int $maxRouters,
        int $priority,
        bool $isExtraRouter = false,
    ): Plan {
        return Plan::factory()->create([
            'name' => $name,
            'internal_name' => $internalName,
            'type' => 'saas',
            'status' => 'active',
            'price' => $price,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'max_routers' => $maxRouters,
            'max_users' => 3,
            'backup_retention_days' => 30,
            'is_saas_plan' => true,
            'is_extra_router' => $isExtraRouter,
            'priority' => $priority,
        ]);
    }
}
