<?php

namespace App\Http\Controllers;

use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::query()->latest()->paginate(12);
        $stats = Site::getStats();

        return view('sites.index', compact('sites', 'stats'));
    }

    public function data(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);

        $sites = Site::filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15))
            ->through(fn ($site) => [
                'id' => $site->id,
                'name' => $site->name,
                'code' => $site->code,
                'city' => $site->city,
                'state' => $site->state,
                'country' => $site->country,
                'status' => $site->status,
                'latitude' => $site->latitude,
                'longitude' => $site->longitude,
                'created_at' => $site->created_at?->format('M d, Y'),
            ]);

        return response()->json([
            'sites' => $sites->items(),
            'pagination' => [
                'current_page' => $sites->currentPage(),
                'last_page' => $sites->lastPage(),
                'per_page' => $sites->perPage(),
                'total' => $sites->total(),
                'from' => $sites->firstItem(),
                'to' => $sites->lastItem(),
            ],
        ]);
    }

    public function filterOptions(): JsonResponse
    {
        return response()->json(Site::getFilterOptions());
    }

    public function stats(): JsonResponse
    {
        return response()->json(Site::getStats());
    }

    public function create(): View
    {
        return view('sites.create', [
            'mapLocale' => $this->resolveMapLocale(),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (auth()->check() && empty($validated['tenant_id'])) {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        }

        $site = Site::create($validated);

        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Site created successfully.');
    }

    public function show(Site $site): View
    {
        $this->authorizeTenantAccess($site);

        return view('sites.show', compact('site'));
    }

    public function edit(Site $site): View
    {
        $this->authorizeTenantAccess($site);

        return view('sites.edit', [
            'site' => $site,
            'mapLocale' => $this->resolveMapLocale(),
        ]);
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $this->authorizeTenantAccess($site);

        $site->update($request->validated());

        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $this->authorizeTenantAccess($site);

        $site->delete();

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site deleted successfully.');
    }

    public function topology(): View
    {
        $sites = Site::query()
            ->select([
                'id',
                'name',
                'code',
                'city',
                'state',
                'country',
                'status',
                'latitude',
                'longitude',
                'contact_name',
                'contact_phone',
                'contact_email',
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();

        $sitesPayload = $sites->map(function (Site $site): array {
            return [
                'id' => $site->id,
                'name' => $site->name,
                'code' => $site->code,
                'city' => $site->city,
                'state' => $site->state,
                'country' => $site->country,
                'status' => $site->status,
                'latitude' => $site->latitude !== null ? (float) $site->latitude : null,
                'longitude' => $site->longitude !== null ? (float) $site->longitude : null,
                'contact_name' => $site->contact_name,
                'contact_phone' => $site->contact_phone,
                'contact_email' => $site->contact_email,
                'show_url' => route('sites.show', $site),
            ];
        })->values();

        $stats = Site::getStats();
        $coverage = [
            'mapped' => $sitesPayload->count(),
            'without_coordinates' => max($stats['total'] - $sitesPayload->count(), 0),
            'countries' => $sites->pluck('country')->filter()->unique()->count(),
            'cities' => $sites->map(fn (Site $site) => $this->siteLocationLabel($site))->filter()->unique()->count(),
        ];

        return view('sites.topology', [
            'sites' => $sitesPayload,
            'stats' => $stats,
            'coverage' => $coverage,
        ]);
    }

    protected function authorizeTenantAccess(Site $site): void
    {
        if (auth()->check() && auth()->user()->tenant_id && $site->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You do not have access to this site.');
        }
    }

    protected function resolveMapLocale(): string
    {
        $locale = tenant()?->locale ?? app()->getLocale();

        return is_string($locale) && $locale !== '' ? str_replace('_', '-', $locale) : 'en';
    }

    protected function siteLocationLabel(Site $site): ?string
    {
        $parts = Collection::make([$site->city, $site->state, $site->country])->filter()->values();

        return $parts->isNotEmpty() ? $parts->join(', ') : null;
    }
}
