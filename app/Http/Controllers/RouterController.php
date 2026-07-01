<?php

namespace App\Http\Controllers;

use App\Http\Requests\Router\StoreRouterRequest;
use App\Http\Requests\Router\UpdateRouterRequest;
use App\Models\PasswordManagerCredential;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Services\Backups\RouterPushScriptGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouterController extends Controller
{
    public function index(): View
    {
        return view('routers.index');
    }

    public function data(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'vendor', 'site']);

        $routers = Router::query()
            ->with('passwordManagerCredential:id,name')
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15))
            ->through(fn (Router $router) => [
                'id' => $router->id,
                'name' => $router->name,
                'model' => $router->model,
                'vendor' => $router->vendor,
                'ip_address' => $router->ip_address,
                'api_port' => $router->api_port,
                'ssh_port' => $router->ssh_port,
                'location' => $router->location,
                'site' => $router->site,
                'status' => $router->status ?? 'offline',
                'version' => $router->version,
                'uptime' => $router->uptime,
                'cpu_usage' => $router->cpu_usage ?? 0,
                'memory_usage' => $router->memory_usage ?? 0,
                'active_sessions_count' => $router->active_sessions_count ?? 0,
                'total_customers' => $router->total_customers ?? 0,
                'enable_monitoring' => $router->enable_monitoring,
                'enable_provisioning' => $router->enable_provisioning,
                'credential_name' => $router->passwordManagerCredential?->name,
                'created_at' => $router->created_at?->format('M d, Y'),
            ]);

        return response()->json([
            'routers' => $routers->items(),
            'pagination' => [
                'current_page' => $routers->currentPage(),
                'last_page' => $routers->lastPage(),
                'per_page' => $routers->perPage(),
                'total' => $routers->total(),
                'from' => $routers->firstItem(),
                'to' => $routers->lastItem(),
            ],
        ]);
    }

    public function filterOptions(): JsonResponse
    {
        return response()->json(Router::getFilterOptions());
    }

    public function stats(): JsonResponse
    {
        return response()->json(Router::getStats());
    }

    public function create(): View
    {
        return view('routers.create', $this->formOptions());
    }

    public function store(StoreRouterRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $this->preparePayload($request->validated());

        $validated['status'] = $validated['status'] ?? 'offline';
        $validated['cpu_usage'] = 0;
        $validated['memory_usage'] = 0;
        $validated['active_sessions_count'] = 0;
        $validated['total_customers'] = 0;

        $router = Router::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Router created successfully.',
                'router' => $router->load('passwordManagerCredential:id,name'),
            ], 201);
        }

        return redirect()
            ->route('routers.show', $router)
            ->with('success', 'Router created successfully.');
    }

    public function show(Router $router): View
    {
        $this->authorizeTenantAccess($router);

        $backups = RouterBackup::query()
            ->where('tenant_id', $router->tenant_id)
            ->where('router_id', $router->id)
            ->with('diff:id,router_backup_id,added_lines,removed_lines')
            ->latest()
            ->paginate(10);

        $backupStats = [
            'total' => RouterBackup::query()->where('tenant_id', $router->tenant_id)->where('router_id', $router->id)->count(),
            'successful' => RouterBackup::query()->where('tenant_id', $router->tenant_id)->where('router_id', $router->id)->where('status', 'success')->count(),
            'failed' => RouterBackup::query()->where('tenant_id', $router->tenant_id)->where('router_id', $router->id)->where('status', 'failed')->count(),
            'changed' => RouterBackup::query()->where('tenant_id', $router->tenant_id)->where('router_id', $router->id)->where('status', 'success')->where('changed', true)->count(),
        ];

        $lastBackup = RouterBackup::query()
            ->where('tenant_id', $router->tenant_id)
            ->where('router_id', $router->id)
            ->latest()
            ->first();

        return view('routers.show', [
            'router' => $router->load('passwordManagerCredential:id,name,username'),
            'backups' => $backups,
            'backupStats' => $backupStats,
            'lastBackup' => $lastBackup,
        ]);
    }

    public function edit(Router $router): View
    {
        $this->authorizeTenantAccess($router);

        return view('routers.edit', [
            'router' => $router->load('passwordManagerCredential:id,name,username'),
        ] + $this->formOptions());
    }

    public function update(UpdateRouterRequest $request, Router $router): JsonResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($router);

        $router->update($this->preparePayload($request->validated(), $router));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Router updated successfully.',
                'router' => $router->fresh('passwordManagerCredential:id,name'),
            ]);
        }

        return redirect()
            ->route('routers.show', $router)
            ->with('success', 'Router updated successfully.');
    }

    public function destroy(Request $request, Router $router): JsonResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($router);

        $router->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Router deleted successfully.',
            ]);
        }

        return redirect()
            ->route('routers.index')
            ->with('success', 'Router deleted successfully.');
    }

    protected function authorizeTenantAccess(Router $router): void
    {
        if (tenant()?->id && $router->tenant_id !== tenant()->id) {
            abort(403, 'You do not have access to this router.');
        }

        if (auth()->check() && auth()->user()->tenant_id && $router->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You do not have access to this router.');
        }
    }

    public function sessions(Router $router): View
    {
        return view('routers.sessions', compact('router'));
    }

    public function queues(Router $router): View
    {
        return view('routers.queues', compact('router'));
    }

    public function profiles(Router $router): View
    {
        return view('routers.profiles', compact('router'));
    }

    public function interfaces(Router $router): View
    {
        return view('routers.interfaces', compact('router'));
    }

    public function ipPools(Router $router): View
    {
        return view('routers.ip-pools', compact('router'));
    }

    public function logs(Router $router): View
    {
        return view('routers.logs', compact('router'));
    }

    public function pushScript(Router $router, RouterPushScriptGenerator $generator): View
    {
        $this->authorizeTenantAccess($router);

        $scriptData = $generator->generateForDisplay($router);

        return view('routers.push-script', [
            'router' => $router,
            'script' => $scriptData['script'],
            'token' => $scriptData['token'],
            'uploadUrl' => $scriptData['upload_url'],
        ]);
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    protected function formOptions(): array
    {
        return [
            'credentialOptions' => PasswordManagerCredential::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function preparePayload(array $validated, ?Router $router = null): array
    {
        if (auth()->check() && empty($validated['tenant_id'])) {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        }

        $validated['enable_api'] = (bool) ($validated['enable_api'] ?? true);
        $validated['enable_ssh'] = (bool) ($validated['enable_ssh'] ?? true);
        $validated['use_ssl'] = (bool) ($validated['use_ssl'] ?? false);
        $validated['legacy_login'] = (bool) ($validated['legacy_login'] ?? false);
        $validated['ssh_auth_method'] = $validated['ssh_auth_method'] ?? 'private_key';
        $validated['ssh_timeout'] = $validated['ssh_timeout'] ?? 30;

        $credentialSource = $validated['credential_source'] ?? null;
        unset($validated['credential_source']);

        if ($credentialSource === 'password_manager') {
            $validated['password_manager_credential_id'] = $validated['password_manager_credential_id'] ?? null;
        } else {
            $validated['password_manager_credential_id'] = null;

            if (($validated['api_password'] ?? '') === '' && $router !== null) {
                unset($validated['api_password']);
            }
        }

        return $validated;
    }
}
