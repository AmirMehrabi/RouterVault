<?php

namespace App\Http\Controllers;

use App\Models\WirelessClient;
use App\Services\WirelessClientTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WirelessClientController extends Controller
{
    public function index(): View
    {
        return view('wireless-clients.index');
    }

    public function data(Request $request, WirelessClientTrackingService $wirelessClientTrackingService): JsonResponse
    {
        $filters = $request->only(['search', 'access_point_id', 'site_id', 'band', 'connection']);

        $wirelessClients = WirelessClient::query()
            ->with(['accessPoint:id,name', 'site:id,name'])
            ->filter($filters)
            ->orderByDesc('is_connected')
            ->orderBy('mac_address')
            ->paginate($request->integer('per_page', 15))
            ->through(fn (WirelessClient $wirelessClient) => $wirelessClientTrackingService->transformWirelessClient($wirelessClient));

        return response()->json([
            'wireless_clients' => $wirelessClients->items(),
            'pagination' => [
                'current_page' => $wirelessClients->currentPage(),
                'last_page' => $wirelessClients->lastPage(),
                'per_page' => $wirelessClients->perPage(),
                'total' => $wirelessClients->total(),
                'from' => $wirelessClients->firstItem(),
                'to' => $wirelessClients->lastItem(),
            ],
        ]);
    }

    public function filterOptions(): JsonResponse
    {
        return response()->json(WirelessClient::getFilterOptions());
    }

    public function stats(): JsonResponse
    {
        $query = WirelessClient::query();

        return response()->json([
            'total' => (clone $query)->count(),
            'connected' => (clone $query)->where('is_connected', true)->count(),
            'disconnected' => (clone $query)->where('is_connected', false)->count(),
            'moved_today' => (clone $query)->whereDate('last_moved_at', now()->toDateString())->count(),
        ]);
    }

    public function show(WirelessClient $wirelessClient, WirelessClientTrackingService $wirelessClientTrackingService): View
    {
        $this->authorizeTenantAccess($wirelessClient->tenant_id);

        $wirelessClient->load([
            'accessPoint:id,name',
            'site:id,name',
            'movements' => fn ($query) => $query
                ->with(['fromAccessPoint:id,name', 'toAccessPoint:id,name'])
                ->limit(20),
        ]);

        return view('wireless-clients.show', [
            'wirelessClient' => $wirelessClient,
            'client' => $wirelessClientTrackingService->transformWirelessClient($wirelessClient),
            'movements' => $wirelessClient->movements->map(function ($movement): array {
                return [
                    'from_access_point' => $movement->fromAccessPoint?->name,
                    'to_access_point' => $movement->toAccessPoint?->name,
                    'moved_at' => $movement->moved_at?->toIso8601String(),
                    'moved_human' => $movement->moved_at?->diffForHumans(),
                ];
            })->values(),
        ]);
    }

    protected function authorizeTenantAccess(string $tenantId): void
    {
        if (tenant()?->id && $tenantId !== tenant()->id) {
            abort(403, 'You do not have access to this wireless client.');
        }

        if (auth()->check() && auth()->user()->tenant_id && $tenantId !== auth()->user()->tenant_id) {
            abort(403, 'You do not have access to this wireless client.');
        }
    }
}
