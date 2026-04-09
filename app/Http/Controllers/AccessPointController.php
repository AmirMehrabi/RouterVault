<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessPoint\StoreAccessPointRequest;
use App\Http\Requests\AccessPoint\UpdateAccessPointRequest;
use App\Models\AccessPoint;
use App\Models\PasswordManagerCredential;
use App\Models\Router;
use App\Models\Site;
use App\Models\WirelessClientMovement;
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
            ->with(['router:id,name', 'site:id,name', 'passwordManagerCredential:id,name'])
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15))
            ->through(fn (AccessPoint $accessPoint) => $this->transformAccessPoint($accessPoint));

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

        $accessPoint = $accessPoint->load(['router:id,name', 'site:id,name', 'passwordManagerCredential:id,name,username']);
        $clientMovements = WirelessClientMovement::query()
            ->with(['wirelessClient:id,mac_address,host_name', 'fromAccessPoint:id,name', 'toAccessPoint:id,name'])
            ->where('to_access_point_id', $accessPoint->id)
            ->latest('moved_at')
            ->limit(15)
            ->get()
            ->map(function (WirelessClientMovement $movement): array {
                return [
                    'mac_address' => $movement->wirelessClient?->mac_address,
                    'host_name' => $movement->wirelessClient?->host_name,
                    'from_access_point' => $movement->fromAccessPoint?->name,
                    'to_access_point' => $movement->toAccessPoint?->name,
                    'moved_at' => $movement->moved_at?->toIso8601String(),
                ];
            })
            ->values();
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
            'clientMovements' => $clientMovements,
        ]);
    }

    public function liveData(AccessPoint $accessPoint, AccessPointStatusService $accessPointStatusService): JsonResponse
    {
        $this->authorizeTenantAccess($accessPoint);

        $payload = $accessPointStatusService->refresh($accessPoint->load(['router:id,name', 'site:id,name', 'passwordManagerCredential:id,name']));
        $history = $accessPointStatusService->latestStatusSummary($payload['access_point']);

        return response()->json([
            'access_point' => $this->transformAccessPoint($payload['access_point'], includeDates: true),
            'live_data' => [
                'online' => $payload['online'],
                'reason' => $payload['reason'],
                'resource' => $payload['resource'],
                'wireless' => $payload['wireless'],
                'clients' => $payload['clients'],
            ],
            'status_history' => $history['status_history'],
            'client_movements' => WirelessClientMovement::query()
                ->with(['wirelessClient:id,mac_address,host_name', 'fromAccessPoint:id,name', 'toAccessPoint:id,name'])
                ->where('to_access_point_id', $accessPoint->id)
                ->latest('moved_at')
                ->limit(15)
                ->get()
                ->map(function (WirelessClientMovement $movement): array {
                    return [
                        'mac_address' => $movement->wirelessClient?->mac_address,
                        'host_name' => $movement->wirelessClient?->host_name,
                        'from_access_point' => $movement->fromAccessPoint?->name,
                        'to_access_point' => $movement->toAccessPoint?->name,
                        'moved_at' => $movement->moved_at?->toIso8601String(),
                    ];
                })
                ->values(),
        ]);
    }

    public function edit(AccessPoint $accessPoint): View
    {
        $this->authorizeTenantAccess($accessPoint);

        return view('access-points.edit', [
            'accessPoint' => $accessPoint->load('passwordManagerCredential:id,name,username'),
        ] + $this->formOptions());
    }

    public function update(UpdateAccessPointRequest $request, AccessPoint $accessPoint): JsonResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($accessPoint);

        $accessPoint->update($this->payloadWithDefaults($request->validated(), $accessPoint));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access point updated successfully.',
                'access_point' => $accessPoint->fresh(['router:id,name', 'site:id,name', 'passwordManagerCredential:id,name']),
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
            'credentialOptions' => PasswordManagerCredential::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function payloadWithDefaults(array $validated, ?AccessPoint $accessPoint = null): array
    {
        if (auth()->check() && empty($validated['tenant_id'])) {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        }

        $credentialSource = $validated['credential_source'] ?? null;
        unset($validated['credential_source']);

        if ($credentialSource === 'password_manager') {
            $validated['password_manager_credential_id'] = $validated['password_manager_credential_id'] ?? null;
        } else {
            $validated['password_manager_credential_id'] = null;

            if (($validated['api_password'] ?? '') === '' && $accessPoint !== null) {
                unset($validated['api_password']);
            }
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

    /**
     * @return array<string, mixed>
     */
    protected function transformAccessPoint(AccessPoint $accessPoint, bool $includeDates = false): array
    {
        $attributes = $accessPoint->getAttributes();

        return [
            'id' => $accessPoint->getKey(),
            'name' => $this->attributeValue($attributes, 'name'),
            'model' => $this->attributeValue($attributes, 'model'),
            'board_name' => $this->attributeValue($attributes, 'board_name'),
            'vendor' => $this->attributeValue($attributes, 'vendor'),
            'ip_address' => $this->attributeValue($attributes, 'ip_address'),
            'mac_address' => $this->attributeValue($attributes, 'mac_address'),
            'ssid' => $this->attributeValue($attributes, 'ssid'),
            'band' => $this->attributeValue($attributes, 'band'),
            'channel' => $this->attributeValue($attributes, 'channel'),
            'frequency' => $this->attributeValue($attributes, 'frequency'),
            'tx_power' => $this->attributeValue($attributes, 'tx_power'),
            'location' => $this->attributeValue($attributes, 'location'),
            'status' => $this->attributeValue($attributes, 'status', 'offline'),
            'firmware_version' => $this->attributeValue($attributes, 'firmware_version'),
            'architecture_name' => $this->attributeValue($attributes, 'architecture_name'),
            'platform' => $this->attributeValue($attributes, 'platform'),
            'uptime' => $this->attributeValue($attributes, 'uptime'),
            'cpu_usage' => $this->attributeValue($attributes, 'cpu_usage', 0),
            'cpu_count' => $this->attributeValue($attributes, 'cpu_count'),
            'cpu_frequency' => $this->attributeValue($attributes, 'cpu_frequency'),
            'memory_usage' => $this->attributeValue($attributes, 'memory_usage', 0),
            'total_memory' => $this->attributeValue($attributes, 'total_memory'),
            'free_memory' => $this->attributeValue($attributes, 'free_memory'),
            'total_hdd_space' => $this->attributeValue($attributes, 'total_hdd_space'),
            'free_hdd_space' => $this->attributeValue($attributes, 'free_hdd_space'),
            'connected_clients_count' => $this->attributeValue($attributes, 'connected_clients_count', 0),
            'signal_quality' => $this->attributeValue($attributes, 'signal_quality', 0),
            'noise_floor' => $this->attributeValue($attributes, 'noise_floor'),
            'channel_utilization' => $this->attributeValue($attributes, 'channel_utilization', 0),
            'enable_monitoring' => $accessPoint->enable_monitoring,
            'enable_provisioning' => $accessPoint->enable_provisioning,
            'router' => $accessPoint->router?->name,
            'site' => $accessPoint->site?->name,
            'credential_name' => $accessPoint->passwordManagerCredential?->name,
            'created_at' => $accessPoint->created_at?->format('M d, Y'),
            'last_seen_at' => $includeDates ? $accessPoint->last_seen_at?->toIso8601String() : $accessPoint->last_seen_at?->diffForHumans(),
            'last_seen_human' => $includeDates ? $accessPoint->last_seen_at?->diffForHumans() : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function attributeValue(array $attributes, string $key, mixed $default = null): mixed
    {
        if (! array_key_exists($key, $attributes)) {
            return $default;
        }

        return $attributes[$key] ?? $default;
    }
}
