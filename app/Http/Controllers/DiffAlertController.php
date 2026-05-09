<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiffAlert\StoreDiffAlertNoteRequest;
use App\Http\Requests\DiffAlert\UpdateDiffAlertSettingsRequest;
use App\Models\DiffAlert;
use App\Models\DiffAlertSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiffAlertController extends Controller
{
    public function index(): View
    {
        $alerts = DiffAlert::query()->with('router:id,name')->latest()->paginate(25);
        $base = DiffAlert::query();
        $stats = [
            'unread' => (clone $base)->where('status', 'unread')->count(),
            'high' => (clone $base)->where('severity', 'high')->count(),
            'acknowledgedToday' => (clone $base)->whereDate('acknowledged_at', today())->count(),
            'ignored' => (clone $base)->where('status', 'ignored')->count(),
        ];

        return view('diff-alerts.index', compact('alerts', 'stats'));
    }

    public function show(DiffAlert $alert): View
    {
        $this->authorizeTenant($alert->tenant_id);

        return view('diff-alerts.show', [
            'alert' => $alert->load(['router', 'backup', 'previousBackup', 'diff', 'notes']),
        ]);
    }

    public function status(Request $request, DiffAlert $alert): RedirectResponse
    {
        $this->authorizeTenant($alert->tenant_id);
        $validated = $request->validate(['status' => ['required', 'string', 'in:unread,read,acknowledged,ignored']]);
        $status = $validated['status'];

        $alert->forceFill([
            'status' => $status,
            'read_at' => in_array($status, ['read', 'acknowledged', 'ignored'], true) ? now() : null,
            'acknowledged_at' => $status === 'acknowledged' ? now() : $alert->acknowledged_at,
        ])->save();

        return back()->with('success', 'Alert status updated.');
    }

    public function note(StoreDiffAlertNoteRequest $request, DiffAlert $alert): RedirectResponse
    {
        $this->authorizeTenant($alert->tenant_id);
        $alert->notes()->create([
            'tenant_id' => $alert->tenant_id,
            'body' => $request->validated('body'),
        ]);

        return back()->with('success', 'Alert note added.');
    }

    public function settings(): View
    {
        $setting = DiffAlertSetting::forTenant(auth()->user()->tenant_id);

        return view('diff-alerts.settings', compact('setting'));
    }

    public function updateSettings(UpdateDiffAlertSettingsRequest $request): RedirectResponse
    {
        $setting = DiffAlertSetting::forTenant(auth()->user()->tenant_id);
        $setting->update($request->normalized());

        return back()->with('success', 'Diff alert settings updated.');
    }

    protected function authorizeTenant(string $tenantId): void
    {
        if (auth()->user()?->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
