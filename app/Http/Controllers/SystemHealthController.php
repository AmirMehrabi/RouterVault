<?php

namespace App\Http\Controllers;

use App\Services\Operations\SystemHealthService;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function __invoke(SystemHealthService $healthService): View
    {
        $checks = $healthService->checks();

        return view('operations.system-health', [
            'checks' => $checks,
            'summary' => $healthService->summary($checks),
        ]);
    }
}
