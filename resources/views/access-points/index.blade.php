@extends('layouts.admin')

@section('title', 'Access Point Management')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')


<div class="space-y-6" x-data="accessPointsIndex()" x-cloak>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Access Point Management</h1>
            <p class="mt-1 text-sm text-gray-500">Monitor AP health, wireless load, and provisioning readiness.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="loadAccessPoints()" :disabled="loading" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            <a href="{{ route('access-points.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Access Point
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-5">
        <template x-for="card in statCards" :key="card.label">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500" x-text="card.label"></p>
                        <p class="mt-2 text-3xl font-bold text-gray-900" x-text="card.value"></p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl" :class="card.iconWrap">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="card.iconTone">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Filters</h3>
            <button x-show="hasActiveFilters()" @click="clearFilters()" class="text-sm font-medium text-blue-600 hover:text-blue-700" style="display: none;">
                Clear All
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
            <input type="text" x-model="filters.search" @input="debouncedLoadAccessPoints()" placeholder="Search name, SSID, IP, MAC..." class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 lg:col-span-2">

            <select x-model="filters.status" @change="loadAccessPoints()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="maintenance">Maintenance</option>
            </select>

            <select x-model="filters.vendor" @change="loadAccessPoints()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Vendors</option>
                <option value="Mikrotik">Mikrotik</option>
                <option value="Ubiquiti">Ubiquiti</option>
                <option value="Cambium">Cambium</option>
                <option value="TP-Link">TP-Link</option>
                <option value="Cisco">Cisco</option>
                <option value="Other">Other</option>
            </select>

            <select x-model="filters.band" @change="loadAccessPoints()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Bands</option>
                <option value="2.4GHz">2.4GHz</option>
                <option value="5GHz">5GHz</option>
                <option value="6GHz">6GHz</option>
                <option value="dual">Dual</option>
            </select>

            <select x-model="filters.router_id" @change="loadAccessPoints()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Routers</option>
                <template x-for="option in filterOptions.routers" :key="option.value">
                    <option :value="option.value" x-text="option.label"></option>
                </template>
            </select>

            <select x-model="filters.site_id" @change="loadAccessPoints()" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Sites</option>
                <template x-for="option in filterOptions.sites" :key="option.value">
                    <option :value="option.value" x-text="option.label"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Access Point</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">SSID / Band</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Router / Site</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">IP / MAC</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Clients</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Signal</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template x-for="accessPoint in accessPoints" :key="accessPoint.id">
                        <tr class="transition-colors duration-150 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900" x-text="accessPoint.name"></span>
                                    <span class="text-xs text-gray-500" x-text="accessPoint.model || '—'"></span>
                                    <span class="text-xs text-gray-400" x-text="accessPoint.location || '—'"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-700" x-text="accessPoint.ssid || '—'"></span>
                                    <span class="text-xs text-gray-500" x-text="accessPoint.band"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-700" x-text="accessPoint.router || '—'"></span>
                                    <span class="text-xs text-gray-500" x-text="accessPoint.site || '—'"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-mono text-sm text-gray-700" x-text="accessPoint.ip_address || '—'"></span>
                                    <span class="font-mono text-xs text-gray-500" x-text="accessPoint.mac_address || '—'"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700" x-text="accessPoint.connected_clients_count"></td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">SQ</span>
                                        <div class="h-1.5 flex-1 rounded-full bg-gray-200">
                                            <div class="h-1.5 rounded-full bg-blue-500" :style="`width: ${accessPoint.signal_quality}%`"></div>
                                        </div>
                                        <span class="text-xs text-gray-700" x-text="accessPoint.signal_quality + '%'"></span>
                                    </div>
                                    <p class="text-xs text-gray-500" x-text="accessPoint.noise_floor !== null ? `${accessPoint.noise_floor} dBm` : '—'"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium" :class="statusClass(accessPoint.status)" x-text="capitalize(accessPoint.status)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="urls.show + '/' + accessPoint.id" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-blue-50 hover:text-blue-600" title="View">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a :href="urls.edit + '/' + accessPoint.id + '/edit'" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-green-50 hover:text-green-600" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button @click="confirmDelete(accessPoint)" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-600" title="Delete">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="accessPoints.length === 0 && !loading" style="display: none;">
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No access points found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div x-show="loading" class="px-6 py-12 text-center" style="display: none;">
            <svg class="mx-auto h-8 w-8 animate-spin text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500">Loading access points...</p>
        </div>
    </div>

    <div x-show="deleteModal.show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Delete Access Point</h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Are you sure you want to delete "<span x-text="deleteModal.accessPoint?.name"></span>"?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button @click="deleteAccessPoint()" :disabled="deleteModal.deleting" type="button" class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button @click="deleteModal.show = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function accessPointsIndex() {
        return {
            accessPoints: [],
            stats: {
                total: 0,
                online: 0,
                offline: 0,
                maintenance: 0,
                connectedClients: 0,
            },
            filterOptions: {
                routers: [],
                sites: [],
            },
            filters: {
                search: '',
                status: '',
                vendor: '',
                band: '',
                router_id: '',
                site_id: '',
            },
            loading: true,
            debounceTimer: null,
            deleteModal: {
                show: false,
                accessPoint: null,
                deleting: false,
            },
            urls: {
                show: '{{ url('access-points') }}',
                edit: '{{ url('access-points') }}',
                destroy: '{{ url('access-points') }}',
            },
            init() {
                this.loadStats();
                this.loadFilterOptions();
                this.loadAccessPoints();
            },
            get statCards() {
                return [
                    { label: 'Total APs', value: this.stats.total, iconWrap: 'bg-blue-50', iconTone: 'text-blue-600', icon: 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0' },
                    { label: 'Online', value: this.stats.online, iconWrap: 'bg-green-50', iconTone: 'text-green-600', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
                    { label: 'Offline', value: this.stats.offline, iconWrap: 'bg-red-50', iconTone: 'text-red-600', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
                    { label: 'Maintenance', value: this.stats.maintenance, iconWrap: 'bg-amber-50', iconTone: 'text-amber-600', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
                    { label: 'Connected Clients', value: this.stats.connectedClients, iconWrap: 'bg-purple-50', iconTone: 'text-purple-600', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
                ];
            },
            async loadAccessPoints() {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    Object.entries(this.filters).forEach(([key, value]) => {
                        if (value) {
                            params.append(key, value);
                        }
                    });

                    const response = await fetch(`{{ route('access-points.data') }}?${params.toString()}`);
                    const data = await response.json();
                    console.log(data);
                    this.accessPoints = data.access_points;
                } catch (error) {
                    console.error('Error loading access points:', error);
                    alert('Error loading access points. Please try again.');
                } finally {
                    this.loading = false;
                }
            },
            async loadStats() {
                const response = await fetch('{{ route('access-points.stats') }}');
                this.stats = await response.json();
            },
            async loadFilterOptions() {
                const response = await fetch('{{ route('access-points.filter-options') }}');
                this.filterOptions = await response.json();
            },
            debouncedLoadAccessPoints() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.loadAccessPoints(), 300);
            },
            hasActiveFilters() {
                return Object.values(this.filters).some(Boolean);
            },
            clearFilters() {
                this.filters = {
                    search: '',
                    status: '',
                    vendor: '',
                    band: '',
                    router_id: '',
                    site_id: '',
                };
                this.loadAccessPoints();
            },
            capitalize(value) {
                return value.charAt(0).toUpperCase() + value.slice(1);
            },
            statusClass(status) {
                if (status === 'online') {
                    return 'border-green-200 bg-green-100 text-green-800';
                }

                if (status === 'maintenance') {
                    return 'border-amber-200 bg-amber-100 text-amber-800';
                }

                return 'border-red-200 bg-red-100 text-red-800';
            },
            confirmDelete(accessPoint) {
                this.deleteModal.accessPoint = accessPoint;
                this.deleteModal.show = true;
            },
            async deleteAccessPoint() {
                if (!this.deleteModal.accessPoint) {
                    return;
                }

                this.deleteModal.deleting = true;

                try {
                    const response = await fetch(`${this.urls.destroy}/${this.deleteModal.accessPoint.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Delete failed');
                    }

                    this.deleteModal.show = false;
                    this.loadAccessPoints();
                    this.loadStats();
                } catch (error) {
                    console.error('Error deleting access point:', error);
                    alert('Error deleting access point. Please try again.');
                } finally {
                    this.deleteModal.deleting = false;
                }
            },
        };
    }
</script>
@endpush
