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

        $request->merge(['_plan_limits' => $this->planEnforcement->getPlanLimits($tenant)]);
        $request->merge(['_can_add_router' => $this->planEnforcement->canAddRouter($tenant)]);
        $request->merge(['_can_add_user' => $this->planEnforcement->canAddUser($tenant)]);

        return $next($request);
    }
}
