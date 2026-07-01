<?php

namespace App\Services\Saas;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(protected DummyPaymentService $gateway) {}

    public function initiatePlanPurchase(Tenant $tenant, Plan $plan, string $source = 'billing'): Payment
    {
        return DB::transaction(function () use ($tenant, $plan, $source): Payment {
            $existingPayment = Payment::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->where('metadata->type', 'plan')
                ->where('metadata->plan_id', $plan->id)
                ->latest()
                ->first();

            if ($existingPayment) {
                return $existingPayment;
            }

            $this->cancelPendingPlanPurchases($tenant);

            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'billing_cycle' => $plan->billing_cycle,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            return Payment::create([
                'tenant_id' => $tenant->id,
                'tenant_subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'status' => 'pending',
                'payment_method' => 'dummy',
                'metadata' => [
                    'type' => 'plan',
                    'plan_id' => $plan->id,
                    'source' => $source,
                ],
            ]);
        });
    }

    public function initiateExtraRouterPurchase(Tenant $tenant, int $quantity): Payment
    {
        $extraRouterPlan = Plan::extraRouterPlan()->first();
        $activeSubscription = $tenant->tenantSubscription()
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $extraRouterPlan || ! $activeSubscription) {
            throw ValidationException::withMessages([
                'quantity' => 'An active subscription is required before adding router capacity.',
            ]);
        }

        return Payment::create([
            'tenant_id' => $tenant->id,
            'tenant_subscription_id' => $activeSubscription->id,
            'amount' => (float) $extraRouterPlan->price * $quantity,
            'currency' => $extraRouterPlan->currency,
            'status' => 'pending',
            'payment_method' => 'dummy',
            'metadata' => [
                'type' => 'extra_router',
                'plan_id' => $extraRouterPlan->id,
                'quantity' => $quantity,
            ],
        ]);
    }

    public function activateFreePlan(Tenant $tenant, Plan $plan): TenantSubscription
    {
        return DB::transaction(function () use ($tenant, $plan): TenantSubscription {
            $this->cancelPendingPlanPurchases($tenant);
            $this->cancelActiveSubscriptions($tenant);
            $periodEnd = now()->addYears(100);

            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'amount' => 0,
                'currency' => $plan->currency,
                'billing_cycle' => $plan->billing_cycle,
                'current_period_start' => now(),
                'current_period_end' => $periodEnd,
            ]);

            $tenant->update([
                'saas_plan_id' => $plan->id,
                'subscription_status' => 'active',
                'subscription_starts_at' => now(),
                'subscription_expires_at' => $periodEnd,
                'next_billing_at' => null,
            ]);

            return $subscription;
        });
    }

    public function completePayment(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->isCompleted()) {
                return $lockedPayment;
            }

            if (! $lockedPayment->isPending()) {
                throw ValidationException::withMessages([
                    'payment' => 'This payment can no longer be processed.',
                ]);
            }

            $tenant = Tenant::query()->findOrFail($lockedPayment->tenant_id);
            $type = data_get($lockedPayment->metadata, 'type');

            if ($type === 'plan') {
                $subscription = TenantSubscription::query()
                    ->lockForUpdate()
                    ->findOrFail($lockedPayment->tenant_subscription_id);

                $this->cancelActiveSubscriptions($tenant, $subscription->id);
                $periodStart = now();
                $periodEnd = now()->addMonth();

                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                ]);

                $tenant->update([
                    'saas_plan_id' => $subscription->plan_id,
                    'subscription_status' => 'active',
                    'subscription_starts_at' => $periodStart,
                    'subscription_expires_at' => $periodEnd,
                    'next_billing_at' => $periodEnd,
                ]);
            } elseif ($type === 'extra_router') {
                $quantity = max(1, (int) data_get($lockedPayment->metadata, 'quantity', 1));
                $tenant->increment('extra_routers_count', $quantity);
            } else {
                throw ValidationException::withMessages([
                    'payment' => 'The payment purpose is invalid.',
                ]);
            }

            $lockedPayment->update([
                'status' => 'completed',
                'transaction_id' => $this->gateway->charge($lockedPayment),
                'paid_at' => now(),
            ]);

            return $lockedPayment->refresh();
        });
    }

    protected function cancelActiveSubscriptions(Tenant $tenant, ?int $exceptId = null): void
    {
        $query = TenantSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active');

        if ($exceptId !== null) {
            $query->whereKeyNot($exceptId);
        }

        $query->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'plan_changed',
        ]);
    }

    protected function cancelPendingPlanPurchases(Tenant $tenant): void
    {
        $payments = Payment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->where('metadata->type', 'plan')
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        Payment::query()
            ->whereKey($payments->modelKeys())
            ->update(['status' => 'cancelled']);

        TenantSubscription::query()
            ->whereIn('id', $payments->pluck('tenant_subscription_id'))
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'checkout_replaced',
            ]);
    }
}
