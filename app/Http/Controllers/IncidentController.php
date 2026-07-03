<?php

namespace App\Http\Controllers;

use App\Http\Requests\Incident\UpdateIncidentStatusRequest;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $incidents = Incident::query()
            ->with(['router:id,name,site', 'assignee:id,name'])
            ->orderByRaw("CASE status WHEN 'detected' THEN 0 WHEN 'acknowledged' THEN 1 WHEN 'assigned' THEN 2 WHEN 'investigating' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(25);
        $base = Incident::query();

        return view('incidents.index', [
            'incidents' => $incidents,
            'stats' => [
                'open' => (clone $base)->where('status', '!=', 'resolved')->count(),
                'critical' => (clone $base)->where('severity', 'high')->where('status', '!=', 'resolved')->count(),
                'unassigned' => (clone $base)->whereNull('assigned_to')->where('status', '!=', 'resolved')->count(),
                'resolvedToday' => (clone $base)->whereDate('resolved_at', today())->count(),
            ],
        ]);
    }

    public function show(Incident $incident): View
    {
        return view('incidents.show', [
            'incident' => $incident->load(['router', 'assignee', 'diffAlert', 'backup']),
            'assignees' => User::query()->whereIn('role', ['owner', 'admin', 'noc'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateIncidentStatusRequest $request, Incident $incident): RedirectResponse
    {
        $status = $request->validated('status');
        $incident->update([
            'status' => $status,
            'assigned_to' => $request->validated('assigned_to'),
            'resolution' => $request->validated('resolution'),
            'acknowledged_at' => in_array($status, ['acknowledged', 'assigned', 'investigating', 'resolved'], true)
                ? ($incident->acknowledged_at ?? now())
                : null,
            'resolved_at' => $status === 'resolved' ? now() : null,
        ]);

        return back()->with('success', 'Incident updated.');
    }
}
