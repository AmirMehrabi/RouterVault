@extends('layouts.admin')

@section('title', 'Router Management')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Routers', 'href' => route('routers.index'), 'current' => true],
    ]" />
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="routersIndex()" x-cloak>
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Router Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and provision your network infrastructure</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="loadRouters()" :disabled="loading" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            <a href="{{ route('routers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Router
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Routers -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Total Routers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.total"></p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Online Routers -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Online Routers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.online"></p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Offline Routers -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Offline Routers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.offline"></p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Active Sessions -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">Total Active Sessions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.activeSessions"></p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Filters</h3>
            <button x-show="hasActiveFilters()" @click="clearFilters()" class="text-sm text-blue-600 hover:text-blue-700 font-medium" style="display: none;">
                Clear All
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <input type="text" x-model="filters.search" @input="debouncedLoadRouters" placeholder="Search by name, IP, site..." class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2.5 px-3 border">
            </div>

            <select x-model="filters.status" @change="applyFilters" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2.5 px-3 border bg-white">
                <option value="">All Statuses</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>

            <select x-model="filters.vendor" @change="applyFilters" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2.5 px-3 border bg-white">
                <option value="">All Vendors</option>
                <template x-for="option in filterOptions.vendors" :key="option.value">
                    <option :value="option.value" x-text="option.label"></option>
                </template>
            </select>

            <select x-model="filters.site" @change="applyFilters" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2.5 px-3 border bg-white">
                <option value="">All Sites</option>
                <template x-for="option in filterOptions.sites" :key="option.value">
                    <option :value="option.value" x-text="option.label"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Router Cards -->
    <div class="relative">
        <div x-show="loading" class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/75 backdrop-blur-sm" style="display: none;">
            <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <template x-for="router in routers" :key="router.id">
                <article class="group flex min-h-80 flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" :class="router.status === 'online' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                            </div>
                            <div class="min-w-0">
                                <a :href="router.show_url" class="block truncate text-base font-bold text-gray-900 transition group-hover:text-blue-700" x-text="router.name"></a>
                                <p class="mt-0.5 truncate font-mono text-xs text-gray-500" x-text="router.ip_address"></p>
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold" :class="router.status === 'online' ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'"><span class="h-1.5 w-1.5 rounded-full" :class="router.status === 'online' ? 'bg-green-500' : 'bg-red-500'"></span><span x-text="router.status.charAt(0).toUpperCase() + router.status.slice(1)"></span></span>
                    </div>

                    <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 border-y border-gray-100 py-4 text-sm">
                        <div><dt class="text-xs text-gray-500">Model</dt><dd class="mt-1 truncate font-medium text-gray-800" x-text="router.model || '—'"></dd></div>
                        <div><dt class="text-xs text-gray-500">Vendor</dt><dd class="mt-1 truncate font-medium text-gray-800" x-text="router.vendor || '—'"></dd></div>
                        <div><dt class="text-xs text-gray-500">Site</dt><dd class="mt-1 truncate font-medium text-gray-800" x-text="router.site || '—'"></dd></div>
                        <div><dt class="text-xs text-gray-500">Uptime</dt><dd class="mt-1 truncate font-medium text-gray-800" x-text="router.uptime || '—'"></dd></div>
                    </dl>

                    <div class="mt-4 space-y-3">
                        <div class="grid grid-cols-[4.5rem_1fr_2.5rem] items-center gap-2 text-xs"><span class="text-gray-500">CPU</span><div class="h-1.5 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full transition-all duration-500" :class="usageColor(router.cpu_usage)" :style="`width: ${Math.min(router.cpu_usage, 100)}%`"></div></div><span class="text-right font-semibold text-gray-700" x-text="router.cpu_usage + '%'"></span></div>
                        <div class="grid grid-cols-[4.5rem_1fr_2.5rem] items-center gap-2 text-xs"><span class="text-gray-500">Memory</span><div class="h-1.5 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full bg-blue-500 transition-all duration-500" :style="`width: ${Math.min(router.memory_usage, 100)}%`"></div></div><span class="text-right font-semibold text-gray-700" x-text="router.memory_usage + '%'"></span></div>
                    </div>

                    <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500"><span class="font-bold text-gray-900" x-text="router.active_sessions_count"></span> active sessions</div>
                        <div class="flex items-center gap-1">
                            <a :href="router.show_url" class="rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">View</a>
                            <a :href="router.edit_url" class="rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">Edit</a>
                            <button @click="confirmDelete(router)" class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Delete router"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </div>
                </article>
            </template>
        </div>

        <div x-show="routers.length === 0 && !loading" class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center" style="display: none;">
            <p class="text-sm font-semibold text-gray-900">No routers found</p>
            <p class="mt-1 text-sm text-gray-500">Adjust your filters or add a new router.</p>
            <button @click="clearFilters()" x-show="hasActiveFilters()" class="mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700">Clear filters</button>
        </div>

        <div x-show="pagination.total > 0" class="mt-5 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span>Showing <strong class="text-gray-800" x-text="pagination.from"></strong>–<strong class="text-gray-800" x-text="pagination.to"></strong> of <strong class="text-gray-800" x-text="pagination.total"></strong></span>
                <select x-model.number="pagination.per_page" @change="changePerPage" class="rounded-lg border border-gray-300 bg-white py-1.5 pl-2 pr-7 text-xs focus:border-blue-500 focus:ring-blue-500"><option :value="6">6</option><option :value="12">12</option><option :value="24">24</option></select>
            </div>
            <nav class="flex items-center gap-1" aria-label="Router pagination">
                <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Previous</button>
                <template x-for="page in visiblePages()" :key="page"><button @click="goToPage(page)" class="h-9 min-w-9 rounded-lg px-2 text-xs font-semibold" :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50'" x-text="page"></button></template>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40">Next</button>
            </nav>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="deleteModal.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="deleteModal.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete Router</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to delete "<span x-text="deleteModal.router?.name"></span>"? This action cannot be undone.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteRouter" :disabled="deleteModal.deleting" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!deleteModal.deleting">Delete</span>
                        <span x-show="deleteModal.deleting">Deleting...</span>
                    </button>
                    <button @click="deleteModal.show = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function routersIndex() {
    return {
        routers: [],
        stats: {
            total: 0,
            online: 0,
            offline: 0,
            activeSessions: 0
        },
        filterOptions: {
            sites: [],
            vendors: []
        },
        filters: {
            search: '',
            status: '',
            vendor: '',
            site: ''
        },
        loading: true,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 0,
            from: 0,
            to: 0
        },
        deleteModal: {
            show: false,
            router: null,
            deleting: false
        },
        debounceTimer: null,
        requestController: null,
        urls: {
            show: '{{ url('routers') }}',
            destroy: '{{ url('routers') }}'
        },

        init() {
            this.loadStats();
            this.loadFilterOptions();
            this.loadRouters();
        },

        async loadRouters() {
            this.requestController?.abort();
            const requestController = new AbortController();
            this.requestController = requestController;
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filters.search) params.append('search', this.filters.search);
                if (this.filters.status) params.append('status', this.filters.status);
                if (this.filters.vendor) params.append('vendor', this.filters.vendor);
                if (this.filters.site) params.append('site', this.filters.site);
                params.append('page', this.pagination.current_page);
                params.append('per_page', this.pagination.per_page);

                const response = await fetch('{{ route('routers.data') }}?' + params.toString(), {
                    headers: { Accept: 'application/json' },
                    signal: requestController.signal
                });
                if (!response.ok) throw new Error('Unable to load routers.');
                const data = await response.json();
                this.routers = data.routers;
                this.pagination = { ...this.pagination, ...data.pagination };
            } catch (error) {
                if (error.name === 'AbortError') return;
                console.error('Error loading routers:', error);
                alert('Error loading routers. Please try again.');
            } finally {
                if (this.requestController === requestController) this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('{{ route('routers.stats') }}');
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async loadFilterOptions() {
            try {
                const response = await fetch('{{ route('routers.filter-options') }}');
                const data = await response.json();
                this.filterOptions = data;
            } catch (error) {
                console.error('Error loading filter options:', error);
            }
        },

        debouncedLoadRouters() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.applyFilters();
            }, 250);
        },

        applyFilters() {
            this.pagination.current_page = 1;
            this.loadRouters();
        },

        goToPage(page) {
            if (page < 1 || page > this.pagination.last_page || page === this.pagination.current_page) return;
            this.pagination.current_page = page;
            this.loadRouters();
        },

        changePerPage() {
            this.pagination.current_page = 1;
            this.loadRouters();
        },

        visiblePages() {
            const start = Math.max(1, Math.min(this.pagination.current_page - 2, this.pagination.last_page - 4));
            const end = Math.min(this.pagination.last_page, start + 4);
            return Array.from({ length: Math.max(0, end - start + 1) }, (_, index) => start + index);
        },

        usageColor(value) {
            return value > 80 ? 'bg-red-500' : (value > 60 ? 'bg-yellow-500' : 'bg-green-500');
        },

        hasActiveFilters() {
            return this.filters.search || this.filters.status || this.filters.vendor || this.filters.site;
        },

        clearFilters() {
            this.filters = {
                search: '',
                status: '',
                vendor: '',
                site: ''
            };
            this.applyFilters();
        },

        confirmDelete(router) {
            this.deleteModal.router = router;
            this.deleteModal.show = true;
        },

        async deleteRouter() {
            if (!this.deleteModal.router) return;

            this.deleteModal.deleting = true;
            try {
                const response = await fetch(`${this.urls.destroy}/${this.deleteModal.router.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.deleteModal.show = false;
                    this.loadRouters();
                    this.loadStats();
                    alert('Router deleted successfully.');
                } else {
                    alert('Error deleting router. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting router:', error);
                alert('Error deleting router. Please try again.');
            } finally {
                this.deleteModal.deleting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
