@extends('layouts.admin')

@section('title', 'Wireless Clients')

@section('content')
<div class="space-y-6" x-data="wirelessClientsIndex()" x-init="init()">
    <div class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-emerald-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Wireless Tracking</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Wireless Clients</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Monitor active clients, filter by site or access point, and inspect roaming history across your tenant.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p><p class="mt-2 text-2xl font-semibold text-slate-900" x-text="stats.total"></p></div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Connected</p><p class="mt-2 text-2xl font-semibold text-emerald-800" x-text="stats.connected"></p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Disconnected</p><p class="mt-2 text-2xl font-semibold text-slate-900" x-text="stats.disconnected"></p></div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Moved Today</p><p class="mt-2 text-2xl font-semibold text-amber-800" x-text="stats.moved_today"></p></div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Search</label>
                <input x-model.debounce.400ms="filters.search" @input="fetchClients()" type="text" placeholder="MAC, hostname, SSID, AP, site..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Access Point</label>
                <select x-model="filters.access_point_id" @change="fetchClients()" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">All access points</option>
                    <template x-for="option in filterOptions.access_points" :key="option.value"><option :value="option.value" x-text="option.label"></option></template>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Site</label>
                <select x-model="filters.site_id" @change="fetchClients()" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">All sites</option>
                    <template x-for="option in filterOptions.sites" :key="option.value"><option :value="option.value" x-text="option.label"></option></template>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                <select x-model="filters.connection" @change="fetchClients()" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    <option value="">All statuses</option>
                    <template x-for="option in filterOptions.connections" :key="option.value"><option :value="option.value" x-text="option.label"></option></template>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Current AP</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Signal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Network</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Last Seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-if="isLoading"><tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Loading wireless clients...</td></tr></template>
                    <template x-if="!isLoading && clients.length === 0"><tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No wireless clients match the current filters.</td></tr></template>
                    <template x-for="client in clients" :key="client.id">
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 h-10 w-10 rounded-2xl bg-slate-100 text-center text-sm font-semibold leading-10 text-slate-700" x-text="client.mac_address.slice(-5)"></div>
                                    <div>
                                        <a :href="client.show_url" class="text-sm font-semibold text-slate-900 hover:text-cyan-700" x-text="client.host_name || client.mac_address"></a>
                                        <p class="mt-1 text-xs text-slate-500" x-text="client.mac_address"></p>
                                        <p class="mt-1 text-xs text-slate-500" x-show="client.comment" x-text="client.comment"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-slate-700">
                                <p class="font-medium text-slate-900" x-text="client.access_point || 'Unknown access point'"></p>
                                <p class="mt-1 text-xs text-slate-500" x-text="client.site || 'No site assigned'"></p>
                                <p class="mt-1 text-xs text-amber-600" x-show="client.last_moved_human" x-text="`Moved ${client.last_moved_human}`"></p>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-slate-700">
                                <p><span class="font-medium text-slate-900" x-text="client.signal_strength ?? '—'"></span> dBm</p>
                                <p class="mt-1 text-xs text-slate-500">SNR: <span x-text="client.signal_to_noise ?? '—'"></span></p>
                                <p class="mt-1 text-xs text-slate-500">TX/RX CCQ: <span x-text="client.tx_ccq ?? '—'"></span>/<span x-text="client.rx_ccq ?? '—'"></span></p>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-slate-700">
                                <p class="font-medium text-slate-900" x-text="client.ssid || '—'"></p>
                                <p class="mt-1 text-xs text-slate-500"><span x-text="client.band || '—'"></span> / <span x-text="client.frequency || '—'"></span></p>
                                <p class="mt-1 text-xs text-slate-500" x-text="client.last_ip_address || 'No IP detected'"></p>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-slate-700">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="client.is_connected ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'" x-text="client.is_connected ? 'Connected' : 'Disconnected'"></span>
                                <p class="mt-2 text-xs text-slate-500" x-text="client.last_seen_human || 'Never seen'"></p>
                                <p class="mt-1 text-xs text-slate-500" x-text="client.uptime || ''"></p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
            <p x-text="pagination.total ? `Showing ${pagination.from}-${pagination.to} of ${pagination.total} clients` : 'No results'"></p>
            <div class="flex items-center gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="rounded-xl border border-slate-200 px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                <span class="px-2" x-text="`Page ${pagination.current_page} of ${pagination.last_page || 1}`"></span>
                <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="rounded-xl border border-slate-200 px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function wirelessClientsIndex() {
        return {
            clients: [],
            stats: { total: 0, connected: 0, disconnected: 0, moved_today: 0 },
            pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
            filters: { search: '', access_point_id: '', site_id: '', band: '', connection: '' },
            filterOptions: { access_points: [], sites: [], connections: [] },
            isLoading: false,

            init() {
                this.fetchOptions();
                this.fetchStats();
                this.fetchClients();
            },

            async fetchOptions() {
                const response = await fetch(@js(route('wireless-clients.filter-options')));
                this.filterOptions = await response.json();
            },

            async fetchStats() {
                const response = await fetch(@js(route('wireless-clients.stats')));
                this.stats = await response.json();
            },

            async fetchClients(page = 1) {
                this.isLoading = true;

                const url = new URL(@js(route('wireless-clients.data')), window.location.origin);
                Object.entries({ ...this.filters, page }).forEach(([key, value]) => {
                    if (value !== '' && value !== null) {
                        url.searchParams.set(key, value);
                    }
                });

                try {
                    const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const payload = await response.json();
                    this.clients = (payload.wireless_clients || []).map((client) => ({
                        ...client,
                        show_url: @js(route('wireless-clients.index')).replace(/\/$/, '') + '/' + client.id,
                    }));
                    this.pagination = payload.pagination;
                } finally {
                    this.isLoading = false;
                }
            },

            changePage(page) {
                if (page < 1 || page > this.pagination.last_page) {
                    return;
                }

                this.fetchClients(page);
            },
        };
    }
</script>
@endpush
