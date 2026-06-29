<?php

namespace App\Services\Saas;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;

class DummyPaymentService
{
    public function processPayment(Tenant $tenant, float $amount, Plan $plan): Payment
    {
        $subscription = TenantSubscription::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
            ],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'amount' => $amount,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]
        );

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'tenant_subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'dummy',
            'transaction_id' => 'DUMMY-'.strtoupper(uniqid()),
            'paid_at' => now(),
        ]);

        return $payment;
    }

    public function processExtraRouterPayment(Tenant $tenant, int $quantity = 1): Payment
    {
        $extraRouterPlan = Plan::extraRouterPlan()->first();

        if (! $extraRouterPlan) {
            throw new \RuntimeException('Extra router plan not found.');
        }

        $amount = (float) $extraRouterPlan->price * $quantity;

        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->first();

        if (! $subscription) {
            throw new \RuntimeException('No active subscription found.');
        }

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'tenant_subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'dummy',
            'transaction_id' => 'DUMMY-EXTRA-'.strtoupper(uniqid()),
            'metadata' => [
                'type' => 'extra_router',
                'quantity' => $quantity,
            ],
            'paid_at' => now(),
        ]);

        $tenant->increment('extra_routers_count', $quantity);

        return $payment;
    }
}
