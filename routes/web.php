<?php

use App\Http\Controllers\AccessPointController;
use App\Http\Controllers\Admin\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Admin\Tenant\UserController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\BackupScheduleController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiffAlertController;
use App\Http\Controllers\DummyPaymentController;
use App\Http\Controllers\IpamController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PasswordManagerController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RouterBackupController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WirelessClientController;
use Illuminate\Support\Facades\Route;

// Landing page - redirect authenticated users to dashboard
Route::get('/', [PagesController::class, 'index'])->name('home');

// Pricing page
Route::get('/pricing', [PagesController::class, 'pricing'])->name('pricing');

// About page
Route::get('/about-us', [PagesController::class, 'aboutUs'])->name('about-us');

// Contact page
Route::get('/contact-us', [PagesController::class, 'contactUs'])->name('contact-us');

// Features page
Route::get('/features', [PagesController::class, 'features'])->name('features');

// Authentication Routes (Guest only)
Route::middleware(['guest'])->prefix('auth')->name('auth.')->group(function () {
    Route::get('/register', [TenantRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [TenantRegistrationController::class, 'register'])->name('register.store');
    Route::get('/login', [TenantLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [TenantLoginController::class, 'login'])->name('login.store');
});

// Logout route (authenticated only)
Route::post('/auth/logout', [TenantLoginController::class, 'logout'])->name('auth.logout')->middleware('auth');

// Onboarding Routes
Route::middleware(['auth', 'initialize_tenancy'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [OnboardingController::class, 'index'])->name('index');
    Route::get('/step/{step}', [OnboardingController::class, 'step'])->name('step');
    Route::post('/plan', [OnboardingController::class, 'selectPlan'])->name('plan');
    Route::post('/payment', [OnboardingController::class, 'processPayment'])->name('payment');
    Route::post('/router', [OnboardingController::class, 'addRouter'])->name('router');
    Route::post('/backup', [OnboardingController::class, 'configureBackup'])->name('backup');
    Route::get('/complete', [OnboardingController::class, 'complete'])->name('complete');
});

// Protected Routes (Require Authentication & Tenancy)
Route::middleware(['auth', 'initialize_tenancy', 'check_tenant_status'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Tenant User Management
    Route::prefix('settings/users')->name('admin.tenant.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/general', [SettingController::class, 'updateGeneral'])->name('update.general');
        Route::put('/branding', [SettingController::class, 'updateBranding'])->name('update.branding');
        Route::delete('/assets/{asset}', [SettingController::class, 'deleteAsset'])->name('delete.asset');
    });

    // Customer Management Routes
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/data', [CustomerController::class, 'data'])->name('data');
        Route::get('/filter-options', [CustomerController::class, 'filterOptions'])->name('filter-options');
        Route::get('/stats', [CustomerController::class, 'stats'])->name('stats');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::post('/{customer}/suspend', [CustomerController::class, 'suspend'])->name('suspend');
        Route::post('/{customer}/activate', [CustomerController::class, 'activate'])->name('activate');
    });

    // Subscription Management Routes
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/data', [SubscriptionController::class, 'data'])->name('data');
        Route::get('/stats', [SubscriptionController::class, 'stats'])->name('stats');
        Route::get('/check-pppoe-username', [SubscriptionController::class, 'checkPppoeUsername'])->name('check-pppoe-username');
        Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
        Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');
        Route::post('/{subscription}/suspend', [SubscriptionController::class, 'suspend'])->name('suspend');
        Route::post('/{subscription}/activate', [SubscriptionController::class, 'activate'])->name('activate');
        Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    });

    // Plan Management Routes
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
    });

    // Router Management Routes
    Route::prefix('routers')->name('routers.')->group(function () {
        Route::get('/', [RouterController::class, 'index'])->name('index');
        Route::get('/data', [RouterController::class, 'data'])->name('data');
        Route::get('/filter-options', [RouterController::class, 'filterOptions'])->name('filter-options');
        Route::get('/stats', [RouterController::class, 'stats'])->name('stats');
        Route::get('/create', [RouterController::class, 'create'])->name('create');
        Route::post('/', [RouterController::class, 'store'])->name('store');
        Route::get('/{router}', [RouterController::class, 'show'])->name('show');
        Route::get('/{router}/edit', [RouterController::class, 'edit'])->name('edit');
        Route::put('/{router}', [RouterController::class, 'update'])->name('update');
        Route::delete('/{router}', [RouterController::class, 'destroy'])->name('destroy');
        Route::get('/{router}/push-script', [RouterController::class, 'pushScript'])->name('push-script');
        Route::get('/{router}/sessions', [RouterController::class, 'sessions'])->name('sessions');
        Route::get('/{router}/queues', [RouterController::class, 'queues'])->name('queues');
        Route::get('/{router}/profiles', [RouterController::class, 'profiles'])->name('profiles');
        Route::get('/{router}/interfaces', [RouterController::class, 'interfaces'])->name('interfaces');
        Route::get('/{router}/ip-pools', [RouterController::class, 'ipPools'])->name('ip-pools');
        Route::get('/{router}/logs', [RouterController::class, 'logs'])->name('logs');
    });

    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [BackupScheduleController::class, 'index'])->name('index');
        Route::get('/create', [BackupScheduleController::class, 'create'])->name('create');
        Route::post('/', [BackupScheduleController::class, 'store'])->name('store');
        Route::get('/{schedule}', [BackupScheduleController::class, 'show'])->name('show');
        Route::get('/{schedule}/edit', [BackupScheduleController::class, 'edit'])->name('edit');
        Route::put('/{schedule}', [BackupScheduleController::class, 'update'])->name('update');
        Route::delete('/{schedule}', [BackupScheduleController::class, 'destroy'])->name('destroy');
        Route::post('/{schedule}/run', [BackupScheduleController::class, 'run'])->name('run');
        Route::post('/{schedule}/toggle', [BackupScheduleController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [RouterBackupController::class, 'index'])->name('index');
        Route::get('/compare', [RouterBackupController::class, 'compare'])->name('compare');
        Route::post('/{backup}/retry', [RouterBackupController::class, 'retry'])->name('retry');
        Route::get('/{backup}', [RouterBackupController::class, 'show'])->name('show');
        Route::get('/{backup}/download', [RouterBackupController::class, 'download'])->name('download');
    });

    Route::prefix('diff-alerts')->name('diff-alerts.')->group(function () {
        Route::get('/', [DiffAlertController::class, 'index'])->name('index');
        Route::get('/settings', [DiffAlertController::class, 'settings'])->name('settings');
        Route::put('/settings', [DiffAlertController::class, 'updateSettings'])->name('settings.update');
        Route::get('/{alert}', [DiffAlertController::class, 'show'])->name('show');
        Route::post('/{alert}/status', [DiffAlertController::class, 'status'])->name('status');
        Route::post('/{alert}/notes', [DiffAlertController::class, 'note'])->name('notes.store');
    });

    Route::prefix('password-manager')->name('password-manager.')->group(function () {
        Route::get('/', [PasswordManagerController::class, 'index'])->name('index');
        Route::get('/create', [PasswordManagerController::class, 'create'])->name('create');
        Route::post('/', [PasswordManagerController::class, 'store'])->name('store');
        Route::get('/{passwordManager}', [PasswordManagerController::class, 'show'])->name('show');
        Route::get('/{passwordManager}/edit', [PasswordManagerController::class, 'edit'])->name('edit');
        Route::put('/{passwordManager}', [PasswordManagerController::class, 'update'])->name('update');
        Route::delete('/{passwordManager}', [PasswordManagerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('access-points')->name('access-points.')->group(function () {
        Route::get('/', [AccessPointController::class, 'index'])->name('index');
        Route::get('/data', [AccessPointController::class, 'data'])->name('data');
        Route::get('/filter-options', [AccessPointController::class, 'filterOptions'])->name('filter-options');
        Route::get('/stats', [AccessPointController::class, 'stats'])->name('stats');
        Route::get('/create', [AccessPointController::class, 'create'])->name('create');
        Route::post('/', [AccessPointController::class, 'store'])->name('store');
        Route::get('/{accessPoint}', [AccessPointController::class, 'show'])->name('show');
        Route::get('/{accessPoint}/live-data', [AccessPointController::class, 'liveData'])->name('live-data');
        Route::get('/{accessPoint}/edit', [AccessPointController::class, 'edit'])->name('edit');
        Route::put('/{accessPoint}', [AccessPointController::class, 'update'])->name('update');
        Route::delete('/{accessPoint}', [AccessPointController::class, 'destroy'])->name('destroy');
    });

    // Site Management Routes

    Route::prefix('wireless-clients')->name('wireless-clients.')->group(function () {
        Route::get('/', [WirelessClientController::class, 'index'])->name('index');
        Route::get('/data', [WirelessClientController::class, 'data'])->name('data');
        Route::get('/filter-options', [WirelessClientController::class, 'filterOptions'])->name('filter-options');
        Route::get('/stats', [WirelessClientController::class, 'stats'])->name('stats');
        Route::post('/credentials/bulk', [WirelessClientController::class, 'bulkUpdateCredentials'])->name('credentials.bulk-update');
        Route::get('/{wirelessClient}', [WirelessClientController::class, 'show'])->name('show');
        Route::post('/{wirelessClient}/management-actions/{action}', [WirelessClientController::class, 'runManagementAction'])->name('management-actions.run');
        Route::put('/{wirelessClient}/credentials', [WirelessClientController::class, 'updateCredentials'])->name('credentials.update');
        Route::delete('/{wirelessClient}/credentials', [WirelessClientController::class, 'clearCredentials'])->name('credentials.clear');
    });

    // Site Management Routes
    Route::prefix('sites')->name('sites.')->group(function () {
        Route::get('/', [SiteController::class, 'index'])->name('index');
        Route::get('/topology', [SiteController::class, 'topology'])->name('topology');
        Route::get('/data', [SiteController::class, 'data'])->name('data');
        Route::get('/filter-options', [SiteController::class, 'filterOptions'])->name('filter-options');
        Route::get('/stats', [SiteController::class, 'stats'])->name('stats');
        Route::get('/create', [SiteController::class, 'create'])->name('create');
        Route::post('/', [SiteController::class, 'store'])->name('store');
        Route::get('/{site}', [SiteController::class, 'show'])->name('show');
        Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('/{site}', [SiteController::class, 'update'])->name('update');
        Route::delete('/{site}', [SiteController::class, 'destroy'])->name('destroy');
    });

    // IP Address Management (IPAM) Routes
    Route::prefix('ipam')->name('ipam.')->group(function () {
        Route::get('/', [IpamController::class, 'dashboard'])->name('dashboard');
        Route::get('/check-ip', [IpamController::class, 'checkIp'])->name('check-ip');

        Route::prefix('pools')->name('pools.')->group(function () {
            Route::get('/', [IpamController::class, 'index'])->name('index');
            Route::get('/create', [IpamController::class, 'create'])->name('create');
            Route::post('/', [IpamController::class, 'store'])->name('store');
            Route::get('/{pool}', [IpamController::class, 'show'])->name('show');
            Route::get('/{pool}/edit', [IpamController::class, 'edit'])->name('edit');
            Route::put('/{pool}', [IpamController::class, 'update'])->name('update');
            Route::delete('/{pool}', [IpamController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('ips')->name('ips.')->group(function () {
            Route::get('/', fn () => view('ipam.ips.index'))->name('index');
            Route::get('/{ip}', fn ($ip) => view('ipam.ips.show', compact('ip')))->name('show');
        });
    });

    // Billing Routes
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/dashboard', fn () => view('billing.dashboard'))->name('dashboard');

        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', fn () => view('billing.invoices.index'))->name('index');
            Route::get('/create', fn () => view('billing.invoices.create'))->name('create');
            Route::get('/{invoice}', fn ($invoice) => view('billing.invoices.show', compact('invoice')))->name('show');
            Route::get('/{invoice}/edit', fn ($invoice) => view('billing.invoices.edit', compact('invoice')))->name('edit');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', fn () => view('billing.payments.index'))->name('index');
            Route::get('/{payment}', fn ($payment) => view('billing.payments.show', compact('payment')))->name('show');
        });

        // SaaS Payment Routes
        Route::get('/payment/{payment}', [DummyPaymentController::class, 'show'])->name('payment');
        Route::patch('/payment/{payment}/process', [DummyPaymentController::class, 'process'])->name('payment.process');
        Route::get('/payment/{payment}/confirmation', [DummyPaymentController::class, 'confirmation'])->name('payment.confirmation');

        // SaaS Subscription Routes
        Route::get('/subscription', [BillingController::class, 'subscription'])->name('subscription');
        Route::post('/subscribe', [BillingController::class, 'subscribe'])->name('subscribe');
        Route::post('/upgrade', [BillingController::class, 'upgrade'])->name('upgrade');
        Route::post('/cancel', [BillingController::class, 'cancel'])->name('cancel');

        Route::get('/credits', fn () => view('billing.credits'))->name('credits');
        Route::get('/reports', fn () => view('billing.reports'))->name('reports');
    });

    // Network Routes
    Route::prefix('network')->name('network.')->group(function () {
        Route::get('/data-usage', fn () => view('network.data-usage'))->name('data-usage');
        Route::get('/bandwidth', fn () => view('network.bandwidth'))->name('bandwidth');
        Route::get('/status', fn () => view('network.status'))->name('status');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/usage', fn () => view('reports.usage'))->name('usage');
        Route::get('/financial', fn () => view('reports.financial'))->name('financial');
    });
});

// Super Admin Routes (Separate from tenant routes)
Route::middleware(['auth'])->prefix('admin/super-admin')->name('admin.super-admin.')->group(function () {
    Route::get('/tenants', [SuperAdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
    Route::get('/tenants/{tenant}/edit', [SuperAdminTenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/tenants/{tenant}', [SuperAdminTenantController::class, 'update'])->name('tenants.update');
    Route::post('/tenants/{tenant}/suspend', [SuperAdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('/tenants/{tenant}/activate', [SuperAdminTenantController::class, 'activate'])->name('tenants.activate');
    Route::delete('/tenants/{tenant}', [SuperAdminTenantController::class, 'destroy'])->name('tenants.destroy');
});
