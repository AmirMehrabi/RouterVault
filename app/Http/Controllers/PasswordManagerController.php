<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordManager\StorePasswordManagerCredentialRequest;
use App\Http\Requests\PasswordManager\UpdatePasswordManagerCredentialRequest;
use App\Models\PasswordManagerCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordManagerController extends Controller
{
    public function index(): View
    {
        $credentials = PasswordManagerCredential::query()
            ->withCount(['routers', 'accessPoints'])
            ->latest()
            ->paginate(12);

        $stats = [
            'total' => PasswordManagerCredential::query()->count(),
            'router_links' => PasswordManagerCredential::query()->withCount('routers')->get()->sum('routers_count'),
            'access_point_links' => PasswordManagerCredential::query()->withCount('accessPoints')->get()->sum('access_points_count'),
        ];

        return view('password-manager.index', [
            'credentials' => $credentials,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('password-manager.create');
    }

    public function store(StorePasswordManagerCredentialRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (auth()->check() && empty($validated['tenant_id'])) {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        }

        $credential = PasswordManagerCredential::create($validated);

        return redirect()
            ->route('password-manager.show', $credential)
            ->with('success', 'Credential saved to Password Manager successfully.');
    }

    public function show(PasswordManagerCredential $passwordManager): View
    {
        $this->authorizeTenantAccess($passwordManager);

        $passwordManager->load([
            'routers:id,name,password_manager_credential_id,tenant_id,updated_at',
            'accessPoints:id,name,password_manager_credential_id,tenant_id,updated_at',
        ]);

        return view('password-manager.show', [
            'credential' => $passwordManager,
        ]);
    }

    public function edit(PasswordManagerCredential $passwordManager): View
    {
        $this->authorizeTenantAccess($passwordManager);

        return view('password-manager.edit', [
            'credential' => $passwordManager,
        ]);
    }

    public function update(UpdatePasswordManagerCredentialRequest $request, PasswordManagerCredential $passwordManager): RedirectResponse
    {
        $this->authorizeTenantAccess($passwordManager);

        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $passwordManager->update($validated);

        return redirect()
            ->route('password-manager.show', $passwordManager)
            ->with('success', 'Password Manager credential updated successfully.');
    }

    public function destroy(PasswordManagerCredential $passwordManager): RedirectResponse
    {
        $this->authorizeTenantAccess($passwordManager);

        $passwordManager->loadCount(['routers', 'accessPoints']);

        if (($passwordManager->routers_count + $passwordManager->access_points_count) > 0) {
            return redirect()
                ->route('password-manager.show', $passwordManager)
                ->with('error', 'This credential is currently assigned to routers or access points. Reassign them before deleting it.');
        }

        $passwordManager->delete();

        return redirect()
            ->route('password-manager.index')
            ->with('success', 'Credential deleted successfully.');
    }

    protected function authorizeTenantAccess(PasswordManagerCredential $credential): void
    {
        if (tenant()?->id && $credential->tenant_id !== tenant()->id) {
            abort(403, 'You do not have access to this credential.');
        }

        if (auth()->check() && auth()->user()->tenant_id && $credential->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You do not have access to this credential.');
        }
    }
}
