<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateBrandingSettingRequest;
use App\Http\Requests\Setting\UpdateGeneralSettingRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $tenant = $this->getTenant();

        $timezones = \DateTimeZone::listIdentifiers();

        return view('settings.index', compact('tenant', 'timezones'));
    }

    public function updateGeneral(UpdateGeneralSettingRequest $request): RedirectResponse
    {
        $tenant = $this->getTenant();

        DB::transaction(function () use ($request, $tenant): void {
            $tenant->update(['timezone' => $request->validated('timezone')]);
            $tenant->backupSchedules()->update(['timezone' => $request->validated('timezone')]);
        });

        return redirect()
            ->route('settings.index', ['tab' => 'general'])
            ->with('success', 'General settings updated successfully.');
    }

    public function updateBranding(UpdateBrandingSettingRequest $request): RedirectResponse
    {
        $tenant = $this->getTenant();

        // Handle file uploads
        $files = [
            'company_logo' => $request->file('company_logo'),
            'company_logo_dark' => $request->file('company_logo_dark'),
            'favicon' => $request->file('favicon'),
            'login_logo' => $request->file('login_logo'),
            'email_header_logo' => $request->file('email_header_logo'),
            'email_footer_logo' => $request->file('email_footer_logo'),
            'invoice_logo' => $request->file('invoice_logo'),
            'login_background' => $request->file('login_background'),
        ];

        foreach ($files as $field => $file) {
            if ($file) {
                // Delete old file if exists
                if ($tenant->$field) {
                    Storage::disk('public')->delete($tenant->$field);
                }

                $path = $file->store('settings/'.$tenant->id, 'public');
                $tenant->$field = $path;
            }
        }

        // Update branding fields
        $tenant->update([
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'accent_color' => $request->accent_color,
            'dark_mode_enabled' => $request->boolean('dark_mode_enabled'),
            'custom_css' => $request->custom_css,
        ]);

        // Update file paths separately (already set above)
        $tenant->save();

        return redirect()
            ->route('settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding settings updated successfully.');
    }

    public function deleteAsset(string $asset): RedirectResponse
    {
        $tenant = $this->getTenant();

        $allowedAssets = [
            'company_logo',
            'company_logo_dark',
            'favicon',
            'login_logo',
            'email_header_logo',
            'email_footer_logo',
            'invoice_logo',
            'login_background',
        ];

        if (! \in_array($asset, $allowedAssets, true)) {
            return redirect()
                ->route('settings.index', ['tab' => 'branding'])
                ->with('error', 'Invalid asset.');
        }

        if ($tenant->$asset) {
            Storage::disk('public')->delete($tenant->$asset);
            $tenant->$asset = null;
            $tenant->save();
        }

        return redirect()
            ->route('settings.index', ['tab' => 'branding'])
            ->with('success', 'Asset deleted successfully.');
    }

    private function getTenant(): Tenant
    {
        $tenant = tenant();

        if (! $tenant && auth()->check() && auth()->user()->tenant_id) {
            $tenant = Tenant::find(auth()->user()->tenant_id);
        }

        return $tenant ?? throw new \Exception('Tenant not found.');
    }
}
