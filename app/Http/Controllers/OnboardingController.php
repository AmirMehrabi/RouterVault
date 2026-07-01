<?php

namespace App\Http\Controllers;

use App\Enums\OnboardingStep;
use App\Http\Requests\Onboarding\AddRouterRequest;
use App\Http\Requests\Onboarding\ConfigureBackupRequest;
use App\Http\Requests\Onboarding\SelectPlanRequest;
use App\Models\BackupSchedule;
use App\Models\BackupToken;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Saas\OnboardingService;
use App\Services\Saas\SubscriptionService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        protected OnboardingService $onboarding,
        protected SubscriptionService $subscriptions,
    ) {}

    public function index(): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('onboarding.step', $this->onboarding->currentStep($tenant)->number());
    }

    public function step(int $step): View|RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        $requestedStep = OnboardingStep::fromLegacyStep($step);

        if (! $this->onboarding->canView($tenant, $requestedStep)) {
            return redirect()->route('onboarding.step', $this->onboarding->currentStep($tenant)->number());
        }

        $shared = [
            'tenant' => $tenant,
            'currentStep' => $requestedStep,
        ];

        return match ($requestedStep) {
            OnboardingStep::Plan => view('onboarding.step1-plan', $shared + [
                'plans' => Plan::saasPlans()->orderBy('price')->get(),
            ]),
            OnboardingStep::Payment => view('onboarding.step2-payment', $shared + [
                'payment' => $this->pendingOnboardingPayment($tenant->id),
            ]),
            OnboardingStep::Router => view('onboarding.step3-router', $shared),
            OnboardingStep::Backups => view('onboarding.step4-backup', $shared + [
                'routers' => $tenant->routers()->orderBy('name')->get(),
            ]),
            OnboardingStep::Complete => redirect()->route('onboarding.completed'),
        };
    }

    public function selectPlan(SelectPlanRequest $request): RedirectResponse
    {
        $plan = Plan::saasPlans()->findOrFail($request->integer('plan_id'));
        $tenant = $request->user()->tenant;

        if ((float) $plan->price === 0.0) {
            $this->subscriptions->activateFreePlan($tenant, $plan);
            $this->onboarding->advanceTo($tenant, OnboardingStep::Router);

            return redirect()->route('onboarding.step', OnboardingStep::Router->number());
        }

        $this->subscriptions->initiatePlanPurchase($tenant, $plan, 'onboarding');
        $tenant->update(['subscription_status' => 'pending']);
        $this->onboarding->advanceTo($tenant, OnboardingStep::Payment);

        return redirect()->route('onboarding.step', OnboardingStep::Payment->number());
    }

    public function processPayment(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $payment = $this->pendingOnboardingPayment($tenant->id);

        if (! $payment) {
            return redirect()->route('onboarding.step', OnboardingStep::Plan->number())
                ->withErrors(['plan_id' => 'Choose a plan before continuing to payment.']);
        }

        $this->subscriptions->completePayment($payment);
        $this->onboarding->advanceTo($tenant, OnboardingStep::Router);

        return redirect()->route('onboarding.step', OnboardingStep::Router->number())
            ->with('success', 'Payment confirmed. Your plan is active.');
    }

    public function addRouter(AddRouterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tenant = $request->user()->tenant;

        if ($tenant->subscription_status !== 'active' || ! $tenant->saas_plan_id) {
            return redirect()->route('onboarding.step', OnboardingStep::Plan->number())
                ->withErrors(['plan_id' => 'Activate a plan before adding a router.']);
        }

        if (! $tenant->canAddRouter()) {
            return back()->withErrors(['name' => 'Router limit reached. Upgrade your plan or purchase extra router capacity.']);
        }

        try {
            $router = Router::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'ip_address' => $validated['ip_address'],
                'api_username' => $validated['api_username'],
                'api_password' => $validated['api_password'],
                'ssh_auth_method' => $validated['ssh_auth_method'] ?? 'password',
                'ssh_private_key' => $validated['ssh_private_key'] ?? null,
                'ssh_port' => $validated['ssh_port'] ?? 22,
                'status' => 'offline',
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput($request->except(['api_password', 'ssh_private_key']))
                ->withErrors(['ip_address' => 'A router with this IP address already exists in your account.']);
        }

        BackupToken::generateForRouter($router);
        $this->onboarding->advanceTo($tenant, OnboardingStep::Backups);

        return redirect()->route('onboarding.step', OnboardingStep::Backups->number());
    }

    public function skipRouter(Request $request): RedirectResponse
    {
        if ($request->user()->tenant->subscription_status !== 'active') {
            return redirect()->route('onboarding.step', OnboardingStep::Plan->number())
                ->withErrors(['plan_id' => 'Activate a plan before finishing setup.']);
        }

        $this->onboarding->complete($request->user()->tenant);

        return redirect()->route('onboarding.completed');
    }

    public function configureBackup(ConfigureBackupRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tenant = $request->user()->tenant;

        $schedule = BackupSchedule::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Onboarding backup schedule',
            ],
            [
                'is_enabled' => true,
                'interval_value' => $validated['interval_value'],
                'interval_unit' => $validated['interval_unit'],
                'timezone' => $tenant->timezone ?? 'UTC',
                'retention_count' => $tenant->backup_retention_days,
                'export_mode' => 'full',
                'next_run_at' => now(),
            ]
        );

        $schedule->routers()->sync($validated['router_ids']);
        $this->onboarding->complete($tenant);

        return redirect()->route('onboarding.completed');
    }

    public function skipBackup(Request $request): RedirectResponse
    {
        $this->onboarding->complete($request->user()->tenant);

        return redirect()->route('onboarding.completed');
    }

    public function complete(): RedirectResponse
    {
        return redirect()->route('onboarding.index');
    }

    public function completed(Request $request): View|RedirectResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant->onboarding_completed) {
            return redirect()->route('onboarding.index');
        }

        return view('onboarding.complete', [
            'tenant' => $tenant,
            'currentStep' => OnboardingStep::Complete,
        ]);
    }

    protected function pendingOnboardingPayment(string $tenantId): ?Payment
    {
        return Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->where('metadata->source', 'onboarding')
            ->with('subscription.plan')
            ->latest()
            ->first();
    }
}
