<?php

namespace App\Http\Controllers;

use App\Http\Requests\Billing\PurchaseExtraRoutersRequest;
use App\Http\Requests\Billing\UpgradePlanRequest;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Services\Saas\PlanEnforcementService;
use App\Services\Saas\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected PlanEnforcementService $planEnforcement,
        protected SubscriptionService $subscriptions,
    ) {}

    public function subscription(): View
    {
        $tenant = auth()->user()->tenant;
        $currentPlan = $tenant->saasPlan;

        return view('billing.subscription', [
            'tenant' => $tenant,
            'currentPlan' => $currentPlan,
            'plans' => Plan::saasPlans()->orderBy('price')->get(),
            'extraRouterPlan' => Plan::extraRouterPlan()->first(),
            'usage' => $this->planEnforcement->getRouterUsage($tenant),
            'limits' => $this->planEnforcement->getPlanLimits($tenant),
            'teamUsage' => [
                'current' => $tenant->users()->count(),
                'limit' => $tenant->max_users,
            ],
            'subscription' => TenantSubscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->latest()
                ->first(),
            'payments' => $tenant->payments()->latest()->limit(20)->get(),
            'canManageBilling' => auth()->user()->isOwner() || auth()->user()->hasPermission('billing.manage'),
        ]);
    }

    public function subscribe(UpgradePlanRequest $request): RedirectResponse
    {
        return $this->startUpgrade($request);
    }

    public function upgrade(UpgradePlanRequest $request): RedirectResponse
    {
        return $this->startUpgrade($request);
    }

    public function purchaseExtraRouters(PurchaseExtraRoutersRequest $request): RedirectResponse
    {
        $payment = $this->subscriptions->initiateExtraRouterPurchase(
            $request->user()->tenant,
            $request->integer('quantity')
        );

        return redirect()->route('billing.payment', $payment);
    }

    protected function startUpgrade(UpgradePlanRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $plan = Plan::saasPlans()->findOrFail($request->integer('plan_id'));
        $currentPlan = $tenant->saasPlan;

        if ($currentPlan && $plan->priority <= $currentPlan->priority) {
            return back()->withErrors([
                'plan_id' => 'Choose a plan above your current plan.',
            ]);
        }

        $payment = $this->subscriptions->initiatePlanPurchase($tenant, $plan);

        return redirect()->route('billing.payment', $payment);
    }
}
