<?php

namespace App\Http\Controllers;

use App\Http\Requests\WirelessClient\BulkUpdateWirelessClientCredentialRequest;
use App\Http\Requests\WirelessClient\UpdateWirelessClientCredentialRequest;
use App\Models\PasswordManagerCredential;
use App\Models\WirelessClient;
use App\Services\WirelessClientTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WirelessClientController extends Controller
{
    public function index(): View
    {
        return view('wireless-clients.index', [
            'credentialOptions' => PasswordManagerCredential::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    public function data(Request $request, WirelessClientTrackingService $wirelessClientTrackingService): JsonResponse
    {
        $filters = $request->only(['search', 'access_point_id', 'site_id', 'band', 'connection']);

        $wirelessClients = WirelessClient::query()
            ->with([
                'accessPoint:id,name',
                'site:id,name',
                'passwordManagerCredential:id,name,username',
            ])
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
            'provisioned' => (clone $query)
                ->where(function ($wirelessClientQuery) {
                    $wirelessClientQuery->whereNotNull('password_manager_credential_id')
                        ->orWhere(function ($manualCredentialQuery) {
                            $manualCredentialQuery->whereNotNull('provisioning_username')
                                ->whereNotNull('provisioning_password');
                        });
                })
                ->count(),
        ]);
    }

    public function show(WirelessClient $wirelessClient, WirelessClientTrackingService $wirelessClientTrackingService): View
    {
        $this->authorizeTenantAccess($wirelessClient->tenant_id);

        $wirelessClient->load([
            'accessPoint:id,name',
            'site:id,name',
            'passwordManagerCredential:id,name,username',
            'movements' => fn ($query) => $query
                ->with(['fromAccessPoint:id,name', 'toAccessPoint:id,name'])
                ->limit(20),
        ]);

        return view('wireless-clients.show', [
            'wirelessClient' => $wirelessClient,
            'client' => $wirelessClientTrackingService->transformWirelessClient($wirelessClient),
            'credentialOptions' => PasswordManagerCredential::query()->orderBy('name')->pluck('name', 'id')->toArray(),
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

    public function updateCredentials(UpdateWirelessClientCredentialRequest $request, WirelessClient $wirelessClient): RedirectResponse
    {
        $this->authorizeTenantAccess($wirelessClient->tenant_id);

        $validated = $request->validated();
        $wirelessClient->update($this->credentialPayload($validated, $wirelessClient));

        $redirectRoute = $validated['redirect_route'] ?? 'show';

        return redirect()
            ->route($redirectRoute === 'index' ? 'wireless-clients.index' : 'wireless-clients.show', $redirectRoute === 'index' ? [] : $wirelessClient)
            ->with('success', 'Wireless client credentials updated successfully.');
    }

    public function bulkUpdateCredentials(BulkUpdateWirelessClientCredentialRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $wirelessClientIds = $validated['wireless_client_ids'];

        $wirelessClients = WirelessClient::query()
            ->whereIn('id', $wirelessClientIds)
            ->get();

        foreach ($wirelessClients as $wirelessClient) {
            $this->authorizeTenantAccess($wirelessClient->tenant_id);
            $wirelessClient->update($this->credentialPayload($validated));
        }

        return redirect()
            ->route('wireless-clients.index')
            ->with('success', 'Credentials applied to the selected wireless clients successfully.');
    }

    public function clearCredentials(WirelessClient $wirelessClient): RedirectResponse
    {
        $this->authorizeTenantAccess($wirelessClient->tenant_id);

        $wirelessClient->update([
            'password_manager_credential_id' => null,
            'provisioning_username' => null,
            'provisioning_password' => null,
        ]);

        return redirect()
            ->route('wireless-clients.show', $wirelessClient)
            ->with('success', 'Wireless client credentials removed successfully.');
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

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function credentialPayload(array $validated, ?WirelessClient $wirelessClient = null): array
    {
        $credentialSource = $validated['credential_source'];
        unset($validated['credential_source'], $validated['redirect_route'], $validated['wireless_client_ids']);

        if ($credentialSource === 'password_manager') {
            return [
                'password_manager_credential_id' => $validated['password_manager_credential_id'] ?? null,
                'provisioning_username' => null,
                'provisioning_password' => null,
            ];
        }

        $payload = [
            'password_manager_credential_id' => null,
            'provisioning_username' => $validated['provisioning_username'] ?? $wirelessClient?->provisioning_username,
        ];

        if (($validated['provisioning_password'] ?? '') === '' && $wirelessClient !== null) {
            $payload['provisioning_password'] = $wirelessClient->provisioning_password;
        } else {
            $payload['provisioning_password'] = $validated['provisioning_password'] ?? null;
        }

        return $payload;
    }
}
