<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessPoint\StoreAccessPointRequest;
use App\Http\Requests\AccessPoint\UpdateAccessPointRequest;
use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessPointController extends Controller
{
    public function index(): View
    {
        return view('access-points.index');
    }

    public function data(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'vendor', 'band', 'router_id', 'site_id']);

        $accessPoints = AccessPoint::query()
            ->with(['router:id,name', 'site:id,name'])
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15))
            ->through(fn (AccessPoint $accessPoint) => [
                'id' => $accessPoint->id,
                'name' => $accessPoint->name,
                'model' => $accessPoint->model,
                'vendor' => $accessPoint->vendor,
                'ip_address' => $accessPoint->ip_address,
                'mac_address' => $accessPoint->mac_address,
                'ssid' => $accessPoint->ssid,
                'band' => $accessPoint->band,
                'channel' => $accessPoint->channel,
                'frequency' => $accessPoint->frequency,
                'tx_power' => $accessPoint->tx_power,
                'location' => $accessPoint->location,
                'status' => $accessPoint->status ?? 'offline',
                'firmware_version' => $accessPoint->firmware_version,
                'uptime' => $accessPoint->uptime,
                'cpu_usage' => $accessPoint->cpu_usage ?? 0,
                'memory_usage' => $accessPoint->memory_usage ?? 0,
                'connected_clients_count' => $accessPoint->connected_clients_count ?? 0,
                'signal_quality' => $accessPoint->signal_quality ?? 0,
                'noise_floor' => $accessPoint->noise_floor,
                'channel_utilization' => $accessPoint->channel_utilization ?? 0,
                'enable_monitoring' => $accessPoint->enable_monitoring,
                'enable_provisioning' => $accessPoint->enable_provisioning,
                'last_seen_at' => $accessPoint->last_seen_at?->diffForHumans(),
                'router' => $accessPoint->router?->name,
                'site' => $accessPoint->site?->name,
                'created_at' => $accessPoint->created_at?->format('M d, Y'),
            ]);

        return response()->json([
            'access_points' => $accessPoints->items(),
            'pagination' => [
                'current_page' => $accessPoints->currentPage(),
                'last_page' => $accessPoints->lastPage(),
                'per_page' => $accessPoints->perPage(),
                'total' => $accessPoints->total(),
                'from' => $accessPoints->firstItem(),
                'to' => $accessPoints->lastItem(),
            ],
        ]);
    }

    public function filterOptions(): JsonResponse
    {
        return response()->json(AccessPoint::getFilterOptions());
    }

    public function stats(): JsonResponse
    {
        return response()->json(AccessPoint::getStats());
    }

    public function create(): View
    {
        return view('access-points.create', $this->formOptions());
    }

    public function store(StoreAccessPointRequest $request): JsonResponse|RedirectResponse
    {
        $accessPoint = AccessPoint::create($this->payloadWithDefaults($request->validated()));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access point created successfully.',
                'access_point' => $accessPoint,
            ], 201);
        }

        return redirect()->route('access-points.show', $accessPoint)->with('success', 'Access point created successfully.');
    }

    public function show(AccessPoint $accessPoint): View
    {
        $this->authorizeTenantAccess($accessPoint);

        return view('access-points.show', [
            'accessPoint' => $accessPoint->load(['router:id,name', 'site:id,name']),
        ]);
    }

    public function edit(AccessPoint $accessPoint): View
    {
        $this->authorizeTenantAccess($accessPoint);

        return view('access-points.edit', [
            'accessPoint' => $accessPoint,
        ] + $this->formOptions());
    }

    public function update(UpdateAccessPointRequest $request, AccessPoint $accessPoint): JsonResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($accessPoint);

        $accessPoint->update($this->payloadWithDefaults($request->validated()));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access point updated successfully.',
                'access_point' => $accessPoint->fresh(['router:id,name', 'site:id,name']),
            ]);
        }

        return redirect()->route('access-points.show', $accessPoint)->with('success', 'Access point updated successfully.');
    }

    public function destroy(Request $request, AccessPoint $accessPoint): JsonResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($accessPoint);

        $accessPoint->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access point deleted successfully.',
            ]);
        }

        return redirect()->route('access-points.index')->with('success', 'Access point deleted successfully.');
    }

    protected function authorizeTenantAccess(AccessPoint $accessPoint): void
    {
        if (auth()->check() && auth()->user()->tenant_id && $accessPoint->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You do not have access to this access point.');
        }
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    protected function formOptions(): array
    {
        return [
            'routerOptions' => Router::query()->orderBy('name')->pluck('name', 'id')->toArray(),
            'siteOptions' => Site::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function payloadWithDefaults(array $validated): array
    {
        if (auth()->check() && empty($validated['tenant_id'])) {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        }

        $validated['status'] = $validated['status'] ?? 'offline';
        $validated['cpu_usage'] = $validated['cpu_usage'] ?? 0;
        $validated['memory_usage'] = $validated['memory_usage'] ?? 0;
        $validated['connected_clients_count'] = $validated['connected_clients_count'] ?? 0;
        $validated['signal_quality'] = $validated['signal_quality'] ?? 0;
        $validated['channel_utilization'] = $validated['channel_utilization'] ?? 0;
        $validated['enable_monitoring'] = $validated['enable_monitoring'] ?? false;
        $validated['enable_provisioning'] = $validated['enable_provisioning'] ?? false;

        return $validated;
    }
}
