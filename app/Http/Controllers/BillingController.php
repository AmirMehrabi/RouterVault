<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Services\Saas\DummyPaymentService;
use App\Services\Saas\PlanEnforcementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(protected PlanEnforcementService $planEnforcement) {}

    public function subscription(): View
    {
        $tenant = auth()->user()->tenant;
        $currentPlan = $tenant->saasPlan;
        $plans = Plan::saasPlans()->ordered()->get();
        $usage = $this->planEnforcement->getRouterUsage($tenant);
        $limits = $this->planEnforcement->getPlanLimits($tenant);

        $subscription = TenantSubscription::where('tenant_id', $tenant->id)
            ->latest()
            ->first();

        $payments = $tenant->payments()->latest()->limit(10)->get();

        return view('billing.subscription', [
            'tenant' => $tenant,
            'currentPlan' => $currentPlan,
            'plans' => $plans,
            'usage' => $usage,
            'limits' => $limits,
            'subscription' => $subscription,
            'payments' => $payments,
        ]);
    }

    public function subscribe(Request $request, DummyPaymentService $paymentService): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $tenant = auth()->user()->tenant;

        if ($plan->price == 0) {
            return $this->activateFreePlan($tenant, $plan);
        }

        $payment = $paymentService->processPayment($tenant, (float) $plan->price, $plan);

        $tenant->update([
            'saas_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]);

        return redirect()->route('billing.payment.confirmation', $payment)
            ->with('success', 'Subscription activated successfully!');
    }

    public function upgrade(Request $request, DummyPaymentService $paymentService): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $tenant = auth()->user()->tenant;

        if ($plan->price == 0) {
            return $this->activateFreePlan($tenant, $plan);
        }

        $payment = $paymentService->processPayment($tenant, (float) $plan->price, $plan);

        $tenant->update([
            'saas_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]);

        return redirect()->route('billing.payment.confirmation', $payment)
            ->with('success', 'Plan upgraded successfully!');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        $subscription = TenantSubscription::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('reason', 'user_cancelled'),
            ]);
        }

        $tenant->update([
            'subscription_status' => 'cancelled',
        ]);

        return redirect()->route('billing.subscription')
            ->with('success', 'Subscription cancelled successfully.');
    }

    protected function activateFreePlan($tenant, $plan): RedirectResponse
    {
        $tenant->update([
            'saas_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addYears(100),
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'amount' => 0,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'current_period_start' => now(),
            'current_period_end' => now()->addYears(100),
        ]);

        return redirect()->route('billing.subscription')
            ->with('success', 'Free plan activated!');
    }
}
