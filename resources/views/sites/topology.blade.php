@extends('layouts.admin')

@section('title', 'Site Topology')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Sites', 'href' => route('sites.index')],
        ['label' => 'Topology', 'current' => true],
    ]" />
@endpush

@section('content')
@php
    $topologyConfig = [
        'sites' => $sites,
        'statusColors' => [
            'active' => '#16a34a',
            'maintenance' => '#d97706',
            'inactive' => '#64748b',
        ],
    ];
@endphp

<div class="space-y-6 pb-24" x-data='sitesTopologyMap(@json($topologyConfig))'>
    <div class="rounded-3xl border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.14),_transparent_30%),linear-gradient(135deg,_#f8fafc,_#e2e8f0)] p-6 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Topology Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Site topology map</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">Use this dedicated network view to survey every mapped site, focus by status, and jump directly into the site profile without changing the existing list workflow.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('sites.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Back to Sites</a>
                <a href="{{ route('sites.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">Add Site</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Mapped Sites</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $coverage['mapped'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Sites with valid coordinates available for topology mapping.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Without Coordinates</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $coverage['without_coordinates'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Add coordinates from the site form to improve topology coverage.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Cities Covered</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $coverage['cities'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Distinct mapped metro areas in the current tenant footprint.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Active Sites</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $stats['active'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Healthy, operational sites currently marked as active.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(320px,0.9fr)_minmax(0,1.6fr)]">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5">
                <div class="flex flex-col gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Topology controls</h2>
                        <p class="mt-1 text-sm text-slate-500">Filter the list, locate a site instantly, and keep the map focused on what matters.</p>
                    </div>
                    <div class="relative">
                        <input type="search" x-model.debounce.250ms="search" placeholder="Search by site, code, or city" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="setStatusFilter('all')" :class="statusFilter === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'" class="rounded-full border px-3 py-2 text-sm font-medium transition">All</button>
                        <button type="button" @click="setStatusFilter('active')" :class="statusFilter === 'active' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'" class="rounded-full border px-3 py-2 text-sm font-medium transition">Active</button>
                        <button type="button" @click="setStatusFilter('maintenance')" :class="statusFilter === 'maintenance' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'" class="rounded-full border px-3 py-2 text-sm font-medium transition">Maintenance</button>
                        <button type="button" @click="setStatusFilter('inactive')" :class="statusFilter === 'inactive' ? 'bg-slate-500 text-white border-slate-500' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'" class="rounded-full border px-3 py-2 text-sm font-medium transition">Inactive</button>
                    </div>
                </div>
            </div>

            <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-600">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span><span>Active</span></div>
                    <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-amber-500"></span><span>Maintenance</span></div>
                    <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-500"></span><span>Inactive</span></div>
                </div>
            </div>

            <div class="max-h-[720px] overflow-y-auto p-3">
                <template x-if="filteredSites.length">
                    <div class="space-y-3">
                        <template x-for="site in filteredSites" :key="site.id">
                            <button type="button" @click="focusSite(site.id)" class="block w-full rounded-2xl border px-4 py-4 text-left transition"
                                :class="activeSiteId === site.id ? 'border-blue-500 bg-blue-50 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-2.5 w-2.5 rounded-full" :style="`background-color: ${statusColor(site.status)}`"></span>
                                            <p class="font-semibold text-slate-950" x-text="site.name"></p>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500" x-text="site.code || 'No site code'"></p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                        :style="`background-color: ${statusColor(site.status)}15; color: ${statusColor(site.status)};`"
                                        x-text="site.status"></span>
                                </div>
                                <p class="mt-3 text-sm text-slate-600" x-text="locationLabel(site)"></p>
                                <p class="mt-2 font-mono text-xs text-slate-400" x-text="coordinateLabel(site)"></p>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="!filteredSites.length">
                    <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center text-sm text-slate-500">
                        No mapped sites match the current filters.
                    </div>
                </template>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Interactive topology map</h2>
                        <p class="mt-1 text-sm text-slate-500">Best practice defaults: fit to bounds, status-aware markers, and direct drill-through to each site.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="fitFilteredSites()" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Fit filtered sites</button>
                        <button type="button" @click="resetView()" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Reset view</button>
                    </div>
                </div>
            </div>
            <div id="sites-topology-map" class="h-[720px] w-full"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .topology-marker {
            border: 3px solid rgba(255, 255, 255, 0.95);
            border-radius: 9999px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.22);
            height: 18px;
            width: 18px;
        }

        .topology-popup .leaflet-popup-content-wrapper {
            border-radius: 20px;
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.18);
        }

        .leaflet-container {
            font-family: inherit;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sitesTopologyMap', (config) => ({
                config,
                map: null,
                markers: new Map(),
                statusFilter: 'all',
                search: '',
                activeSiteId: null,
                defaultCenter: [18, 10],
                defaultZoom: 2,

                init() {
                    this.$nextTick(() => {
                        this.initializeMap();
                        this.renderMarkers();
                        this.fitFilteredSites();
                    });

                    this.$watch('search', () => {
                        this.renderMarkers();
                    });

                    this.$watch('statusFilter', () => {
                        this.renderMarkers();
                    });
                },

                get filteredSites() {
                    const query = this.search.trim().toLowerCase();

                    return this.config.sites.filter((site) => {
                        if (this.statusFilter !== 'all' && site.status !== this.statusFilter) {
                            return false;
                        }

                        if (!query) {
                            return true;
                        }

                        return [site.name, site.code, site.city, site.state, site.country]
                            .filter(Boolean)
                            .some((value) => value.toLowerCase().includes(query));
                    });
                },

                initializeMap() {
                    this.map = L.map('sites-topology-map', {
                        zoomControl: true,
                        scrollWheelZoom: true,
                    }).setView(this.defaultCenter, this.defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(this.map);
                },

                renderMarkers() {
                    this.markers.forEach((marker) => this.map.removeLayer(marker));
                    this.markers.clear();

                    this.filteredSites.forEach((site) => {
                        if (!this.hasCoordinates(site)) {
                            return;
                        }

                        const marker = L.circleMarker([site.latitude, site.longitude], {
                            radius: this.activeSiteId === site.id ? 10 : 8,
                            color: '#ffffff',
                            weight: 2,
                            fillColor: this.statusColor(site.status),
                            fillOpacity: 0.92,
                        }).addTo(this.map);

                        marker.bindPopup(this.popupMarkup(site), {
                            className: 'topology-popup',
                        });

                        marker.on('click', () => {
                            this.activeSiteId = site.id;
                        });

                        this.markers.set(site.id, marker);
                    });

                    if (this.filteredSites.length) {
                        this.fitFilteredSites();
                    } else {
                        this.resetView();
                    }
                },

                setStatusFilter(value) {
                    this.statusFilter = value;
                },

                fitFilteredSites() {
                    const coordinates = this.filteredSites
                        .filter((site) => this.hasCoordinates(site))
                        .map((site) => [site.latitude, site.longitude]);

                    if (!coordinates.length) {
                        return;
                    }

                    const bounds = L.latLngBounds(coordinates);
                    this.map.fitBounds(bounds, {
                        padding: [36, 36],
                        maxZoom: 12,
                    });
                },

                resetView() {
                    this.activeSiteId = null;
                    this.map.setView(this.defaultCenter, this.defaultZoom);
                },

                focusSite(siteId) {
                    const site = this.config.sites.find((item) => item.id === siteId);

                    if (!site || !this.hasCoordinates(site)) {
                        return;
                    }

                    this.activeSiteId = site.id;
                    this.renderMarkers();
                    this.map.flyTo([site.latitude, site.longitude], 13, {
                        duration: 0.8,
                    });

                    const marker = this.markers.get(site.id);
                    if (marker) {
                        marker.openPopup();
                    }
                },

                hasCoordinates(site) {
                    return Number.isFinite(site.latitude) && Number.isFinite(site.longitude);
                },

                statusColor(status) {
                    return this.config.statusColors[status] || '#2563eb';
                },

                locationLabel(site) {
                    return [site.city, site.state, site.country].filter(Boolean).join(', ') || 'Location not provided';
                },

                coordinateLabel(site) {
                    return `${Number(site.latitude).toFixed(6)}, ${Number(site.longitude).toFixed(6)}`;
                },

                popupMarkup(site) {
                    return `
                        <div class="space-y-3">
                            <div>
                                <p style="margin: 0; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;">${site.status} site</p>
                                <p style="margin: 6px 0 0; font-size: 16px; font-weight: 700; color: #0f172a;">${site.name}</p>
                                <p style="margin: 4px 0 0; color: #475569;">${this.locationLabel(site)}</p>
                            </div>
                            <p style="margin: 0; font-family: ui-monospace, SFMono-Regular, monospace; color: #334155;">${this.coordinateLabel(site)}</p>
                            <a href="${site.show_url}" style="display: inline-flex; align-items: center; justify-content: center; border-radius: 9999px; background: #2563eb; color: #ffffff; font-size: 13px; font-weight: 600; padding: 10px 14px; text-decoration: none;">Open site details</a>
                        </div>
                    `;
                },
            }));
        });
    </script>
@endpush
