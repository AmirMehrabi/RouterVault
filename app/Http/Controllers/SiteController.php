<?php

namespace App\Http\Controllers;

use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::query()->latest()->paginate(12);

        return view('sites.index', compact('sites'));
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
        return view('sites.create');
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

        return view('sites.edit', compact('site'));
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

    protected function authorizeTenantAccess(Site $site): void
    {
        if (auth()->check() && auth()->user()->tenant_id && $site->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'You do not have access to this site.');
        }
    }
}
