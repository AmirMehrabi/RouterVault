<?php

use App\Http\Middleware\AuditUserAction;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckTenantStatus;
use App\Http\Middleware\EnforcePlanLimits;
use App\Http\Middleware\InitializeTenancy;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'initialize_tenancy' => InitializeTenancy::class,
            'check_tenant_status' => CheckTenantStatus::class,
            'enforce_plan' => EnforcePlanLimits::class,
            'can' => CheckPermission::class,
            'audit_user_action' => AuditUserAction::class,
        ]);

        $middleware->redirectGuestsTo('/auth/login');

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
