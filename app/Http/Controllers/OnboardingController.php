<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\AddRouterRequest;
use App\Models\BackupSchedule;
use App\Models\BackupToken;
use App\Models\Plan;
use App\Models\Router;
use App\Models\TenantSubscription;
use App\Services\Saas\DummyPaymentService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index', [
            'tenant' => $tenant,
        ]);
    }

    public function step(int $step): View|RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return match ($step) {
            1 => view('onboarding.step1-plan', [
                'plans' => Plan::saasPlans()->ordered()->get(),
                'tenant' => $tenant,
            ]),
            2 => view('onboarding.step2-payment', [
                'tenant' => $tenant,
                'plan' => $tenant->saasPlan,
            ]),
            3 => view('onboarding.step3-router', [
                'tenant' => $tenant,
            ]),
            4 => view('onboarding.step4-backup', [
                'tenant' => $tenant,
                'routers' => $tenant->routers()->get(),
            ]),
            default => redirect()->route('onboarding.index'),
        };
    }

    public function selectPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $tenant = auth()->user()->tenant;

        $tenant->update([
            'saas_plan_id' => $plan->id,
            'subscription_status' => 'pending',
        ]);

        if ($plan->price == 0) {
            return $this->activateFreePlan($tenant, $plan);
        }

        return redirect()->route('onboarding.step', 2);
    }

    public function processPayment(Request $request, DummyPaymentService $paymentService): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        $plan = $tenant->saasPlan;

        if (! $plan || $plan->price == 0) {
            return redirect()->route('onboarding.step', 1);
        }

        $paymentService->processPayment($tenant, (float) $plan->price, $plan);

        $tenant->update([
            'subscription_status' => 'active',
            'subscription_starts_at' => now(),
            'subscription_expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]);

        return redirect()->route('onboarding.step', 3);
    }

    public function addRouter(AddRouterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tenant = auth()->user()->tenant;

        if (! $tenant->canAddRouter()) {
            return back()->withErrors(['name' => 'Router limit reached. Please upgrade your plan or purchase extra routers.']);
        }

        try {
            $router = Router::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'ip_address' => $validated['ip_address'],
                'api_username' => $validated['api_username'],
                'api_password' => $validated['api_password'],
                'ssh_auth_method' => $validated['ssh_auth_method'] ?? 'private_key',
                'ssh_private_key' => $validated['ssh_private_key'] ?? '~/.ssh/id_rsa',
                'ssh_port' => $validated['ssh_port'] ?? 22,
                'status' => 'offline',
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput($request->except(['api_password', 'ssh_private_key']))
                ->withErrors(['ip_address' => 'A router with this IP address already exists in your account.']);
        }

        BackupToken::generateForRouter($router);

        return redirect()->route('onboarding.step', 4);
    }

    public function configureBackup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'router_ids' => ['required', 'array'],
            'router_ids.*' => ['exists:routers,id'],
            'interval_value' => ['required', 'integer', 'min:1'],
            'interval_unit' => ['required', 'string', 'in:hours,days,weeks'],
        ]);

        $tenant = auth()->user()->tenant;

        $schedule = BackupSchedule::create([
            'tenant_id' => $tenant->id,
            'name' => 'Daily Backup',
            'is_enabled' => true,
            'interval_value' => $validated['interval_value'],
            'interval_unit' => $validated['interval_unit'],
            'timezone' => $tenant->timezone ?? 'UTC',
            'retention_count' => $tenant->backup_retention_days,
            'export_mode' => 'full',
            'next_run_at' => now(),
        ]);

        $schedule->routers()->sync($validated['router_ids']);

        return redirect()->route('onboarding.complete');
    }

    public function complete(): View
    {
        $tenant = auth()->user()->tenant;
        $tenant->update(['onboarding_completed' => true]);

        return view('onboarding.complete', [
            'tenant' => $tenant,
        ]);
    }

    protected function activateFreePlan($tenant, $plan): RedirectResponse
    {
        $tenant->update([
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

        return redirect()->route('onboarding.step', 3);
    }
}
