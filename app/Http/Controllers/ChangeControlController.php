<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeControl\StoreChangeRequestRequest;
use App\Http\Requests\ChangeControl\StoreMaintenanceWindowRequest;
use App\Http\Requests\ChangeControl\UpdateChangeRequestStatusRequest;
use App\Models\ChangeRequest;
use App\Models\MaintenanceWindow;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChangeControlController extends Controller
{
    public function index(): View
    {
        return view('change-control.index', [
            'changeRequests' => ChangeRequest::query()->with(['router:id,name', 'requester:id,name', 'approver:id,name'])->latest()->paginate(15, ['*'], 'changes'),
            'maintenanceWindows' => MaintenanceWindow::query()->with(['router:id,name', 'site:id,name'])->where('ends_at', '>=', now()->subDay())->orderBy('starts_at')->get(),
            'routers' => Router::query()->orderBy('name')->get(['id', 'name']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeChange(StoreChangeRequestRequest $request): RedirectResponse
    {
        ChangeRequest::query()->create($request->validated() + [
            'tenant_id' => $request->user()->tenant_id,
            'requested_by' => $request->user()->id,
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Change request submitted.');
    }

    public function updateChange(UpdateChangeRequestStatusRequest $request, ChangeRequest $changeRequest): RedirectResponse
    {
        $status = $request->validated('status');
        $attributes = [
            'status' => $status,
            'result' => $request->validated('result'),
            'approved_by' => $status === 'approved' ? $request->user()->id : $changeRequest->approved_by,
            'approved_at' => $status === 'approved' ? now() : $changeRequest->approved_at,
            'completed_at' => $status === 'completed' ? now() : null,
        ];

        if ($status === 'approved') {
            $backupId = RouterBackup::query()
                ->where('router_id', $changeRequest->router_id)
                ->whereIn('status', ['success', 'partial_success'])
                ->whereNotNull('path')
                ->latest()
                ->value('id');

            if ($backupId === null) {
                return back()->withErrors(['status' => 'A successful recoverable backup is required before approval.']);
            }

            $attributes['pre_change_backup_id'] = $backupId;
        }

        $changeRequest->update($attributes);

        return back()->with('success', 'Change request updated.');
    }

    public function storeMaintenance(StoreMaintenanceWindowRequest $request): RedirectResponse
    {
        MaintenanceWindow::query()->create($request->validated() + [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Maintenance window scheduled.');
    }
}
