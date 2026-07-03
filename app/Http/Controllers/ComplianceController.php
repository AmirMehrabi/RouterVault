<?php

namespace App\Http\Controllers;

use App\Http\Requests\Compliance\StoreBaselineRequest;
use App\Models\ComplianceFinding;
use App\Models\ConfigurationBaseline;
use App\Models\Router;
use App\Services\Operations\ComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComplianceController extends Controller
{
    public function index(): View
    {
        return view('compliance.index', [
            'routers' => Router::query()
                ->with(['latestBackup:id,status,path,created_at', 'configurationBaseline.backup:id,checksum'])
                ->withCount([
                    'complianceFindings as critical_findings_count' => fn ($query) => $query->where('status', 'critical'),
                    'complianceFindings as warning_findings_count' => fn ($query) => $query->where('status', 'warning'),
                ])
                ->orderBy('name')
                ->get(),
            'stats' => [
                'critical' => ComplianceFinding::query()->where('status', 'critical')->count(),
                'warning' => ComplianceFinding::query()->where('status', 'warning')->count(),
                'compliant' => ComplianceFinding::query()->where('status', 'compliant')->count(),
                'baselines' => ConfigurationBaseline::query()->count(),
            ],
        ]);
    }

    public function show(Router $router): View
    {
        $router->load([
            'latestBackup:id,status,path,created_at',
            'configurationBaseline' => fn ($q) => $q->with('approver:id,name'),
            'complianceFindings' => fn ($q) => $q->orderByRaw("FIELD(status, 'critical', 'warning', 'unknown', 'compliant')")->orderByDesc('checked_at'),
        ]);

        return view('compliance.show', [
            'router' => $router,
        ]);
    }

    public function scan(Router $router, ComplianceService $complianceService): RedirectResponse
    {
        $complianceService->evaluate($router);

        return back()->with('success', "{$router->name} compliance scan completed.");
    }

    public function baseline(StoreBaselineRequest $request, Router $router): RedirectResponse
    {
        ConfigurationBaseline::query()->updateOrCreate(
            ['router_id' => $router->id],
            [
                'tenant_id' => $router->tenant_id,
                'router_backup_id' => $request->integer('router_backup_id'),
                'approved_by' => $request->user()->id,
                'label' => $request->validated('label'),
                'notes' => $request->validated('notes'),
                'approved_at' => now(),
            ]
        );

        return back()->with('success', 'Approved baseline updated.');
    }
}
