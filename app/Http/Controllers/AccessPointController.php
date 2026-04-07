<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessPoint\StoreAccessPointRequest;
use App\Http\Requests\AccessPoint\UpdateAccessPointRequest;
use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\Site;
use App\Services\AccessPointStatusService;
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
                'board_name' => $accessPoint->board_name,
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
                'architecture_name' => $accessPoint->architecture_name,
                'platform' => $accessPoint->platform,
                'uptime' => $accessPoint->uptime,
                'cpu_usage' => $accessPoint->cpu_usage ?? 0,
                'cpu_count' => $accessPoint->cpu_count,
                'cpu_frequency' => $accessPoint->cpu_frequency,
                'memory_usage' => $accessPoint->memory_usage ?? 0,
                'total_memory' => $accessPoint->total_memory,
                'free_memory' => $accessPoint->free_memory,
                'total_hdd_space' => $accessPoint->total_hdd_space,
                'free_hdd_space' => $accessPoint->free_hdd_space,
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

    public function show(AccessPoint $accessPoint, AccessPointStatusService $accessPointStatusService): View
    {
        $this->authorizeTenantAccess($accessPoint);

        $accessPoint = $accessPoint->load(['router:id,name', 'site:id,name']);
        $liveData = $accessPoint->enable_monitoring
            ? $accessPointStatusService->refresh($accessPoint)
            : [
                'online' => $accessPoint->isOnline(),
                'status' => $accessPoint->status,
                'reason' => null,
                'resource' => [],
                'wireless' => null,
                'clients' => [],
                'metrics' => [],
                'access_point' => $accessPoint,
            ];

        return view('access-points.show', [
            'accessPoint' => $liveData['access_point'],
            'liveData' => $liveData,
            'statusHistory' => $accessPointStatusService->latestStatusSummary($liveData['access_point'])['status_history'],
        ]);
    }

    public function liveData(AccessPoint $accessPoint, AccessPointStatusService $accessPointStatusService): JsonResponse
    {
        $this->authorizeTenantAccess($accessPoint);

        $payload = $accessPointStatusService->refresh($accessPoint->load(['router:id,name', 'site:id,name']));
        $history = $accessPointStatusService->latestStatusSummary($payload['access_point']);

        return response()->json([
            'access_point' => [
                'id' => $payload['access_point']->id,
                'name' => $payload['access_point']->name,
                'status' => $payload['access_point']->status,
                'last_seen_at' => $payload['access_point']->last_seen_at?->toIso8601String(),
                'last_seen_human' => $payload['access_point']->last_seen_at?->diffForHumans(),
                'board_name' => $payload['access_point']->board_name,
                'connected_clients_count' => $payload['access_point']->connected_clients_count,
                'signal_quality' => $payload['access_point']->signal_quality,
                'cpu_usage' => $payload['access_point']->cpu_usage,
                'cpu_count' => $payload['access_point']->cpu_count,
                'cpu_frequency' => $payload['access_point']->cpu_frequency,
                'memory_usage' => $payload['access_point']->memory_usage,
                'total_memory' => $payload['access_point']->total_memory,
                'free_memory' => $payload['access_point']->free_memory,
                'total_hdd_space' => $payload['access_point']->total_hdd_space,
                'free_hdd_space' => $payload['access_point']->free_hdd_space,
                'firmware_version' => $payload['access_point']->firmware_version,
                'architecture_name' => $payload['access_point']->architecture_name,
                'platform' => $payload['access_point']->platform,
                'uptime' => $payload['access_point']->uptime,
                'ssid' => $payload['access_point']->ssid,
                'band' => $payload['access_point']->band,
                'channel' => $payload['access_point']->channel,
                'frequency' => $payload['access_point']->frequency,
                'tx_power' => $payload['access_point']->tx_power,
                'noise_floor' => $payload['access_point']->noise_floor,
                'channel_utilization' => $payload['access_point']->channel_utilization,
            ],
            'live_data' => [
                'online' => $payload['online'],
                'reason' => $payload['reason'],
                'resource' => $payload['resource'],
                'wireless' => $payload['wireless'],
                'clients' => $payload['clients'],
            ],
            'status_history' => $history['status_history'],
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
        if (tenant()?->id && $accessPoint->tenant_id !== tenant()->id) {
            abort(403, 'You do not have access to this access point.');
        }

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
