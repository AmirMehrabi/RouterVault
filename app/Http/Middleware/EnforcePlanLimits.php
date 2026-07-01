<?php

namespace App\Http\Middleware;

use App\Services\Saas\PlanEnforcementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimits
{
    public function __construct(protected PlanEnforcementService $planEnforcement) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (! $user->tenant_id) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            return $next($request);
        }

        $request->attributes->set('plan_context', $this->planEnforcement->getPlanContext($tenant));

        return $next($request);
    }
}
