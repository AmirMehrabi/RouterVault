@extends('layouts.admin')

@section('title', 'Wireless Clients')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Wireless Clients', 'href' => route('wireless-clients.index'), 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6" x-data="wirelessClientsIndex({ credentialOptions: @js($credentialOptions) })" x-init="init()">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            Please review the credential form values and try again.
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-emerald-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Wireless Tracking</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Wireless Clients</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Monitor active clients, assign credentials from Password Manager, and keep provisioning separate from online or offline state.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p><p class="mt-2 text-2xl font-semibold text-slate-900" x-text="stats.total"></p></div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Connected</p><p class="mt-2 text-2xl font-semibold text-emerald-800" x-text="stats.connected"></p></div>
                <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Disconnected</p><p class="mt-2 text-2xl font-semibold text-slate-900" x-text="stats.disconnected"></p></div>
                <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Provisioned</p><p class="mt-2 text-2xl font-semibold text-cyan-800" x-text="stats.provisioned"></p></div>
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

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between" x-show="selectedClientIds.length > 0" x-cloak>
        <div>
            <p class="text-sm font-semibold text-slate-900"><span x-text="selectedClientIds.length"></span> wireless client<span x-show="selectedClientIds.length !== 1">s</span> selected</p>
            <p class="text-xs text-slate-500">Apply one manual credential set or one Password Manager credential to the selected records.</p>
        </div>
        <div class="mt-3 flex gap-3 sm:mt-0">
            <button type="button" @click="openBulkCredentialModal()" class="rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Assign credentials</button>
            <button type="button" @click="clearSelection()" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear selection</button>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-4 text-left">
                            <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" :checked="clients.length > 0 && selectedClientIds.length === clients.length" @change="toggleSelectAll($event.target.checked)">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Current AP</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Signal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Provisioning</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Last Seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-if="isLoading"><tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Loading wireless clients...</td></tr></template>
                    <template x-if="!isLoading && clients.length === 0"><tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No wireless clients match the current filters.</td></tr></template>
                    <template x-for="client in clients" :key="client.id">
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-4 align-top">
                                <input type="checkbox" class="mt-2 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" :checked="selectedClientIds.includes(client.id)" @change="toggleClientSelection(client.id, $event.target.checked)">
                            </td>
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
                                <p class="mt-1 text-xs text-slate-500">SSID: <span x-text="client.ssid || '—'"></span></p>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-slate-700">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="client.is_provisioned ? 'bg-cyan-100 text-cyan-700' : 'bg-amber-100 text-amber-700'" x-text="client.provisioning_status"></span>
                                <p class="mt-2 text-xs text-slate-500" x-show="client.credential_source === 'password_manager'" x-text="client.credential_name ? `Saved: ${client.credential_name}` : 'Saved credential'"></p>
                                <p class="mt-2 text-xs text-slate-500" x-show="client.credential_source === 'manual'" x-text="client.provisioning_username ? `Manual: ${client.provisioning_username}` : 'Manual credentials'"></p>
                                <p class="mt-2 text-xs text-slate-400" x-show="client.credential_source === 'none'">No username or password assigned</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" @click="openSingleCredentialModal(client)" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Manage</button>
                                    <a :href="client.show_url" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>
                                </div>
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

    <div x-cloak x-show="credentialModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-6">
        <div @click.outside="closeCredentialModal()" class="w-full max-w-3xl rounded-3xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Credentials</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900" x-text="credentialModal.mode === 'bulk' ? 'Bulk Provision Wireless Clients' : 'Manage Wireless Client Credentials'"></h2>
                    <p class="mt-2 text-sm text-slate-500" x-text="credentialModal.mode === 'bulk' ? `${selectedClientIds.length} selected records will be updated.` : credentialModal.clientName"></p>
                </div>
                <button type="button" @click="closeCredentialModal()" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Close</button>
            </div>

            <div class="px-6 py-6">
                <div class="grid gap-4 lg:grid-cols-2">
                    <button type="button" @click="credentialModal.source = 'password_manager'" :class="credentialModal.source === 'password_manager' ? 'border-cyan-500 bg-cyan-50 text-cyan-900 ring-2 ring-cyan-100' : 'border-slate-200 bg-white text-slate-700'" class="rounded-2xl border p-4 text-left transition">
                        <p class="text-sm font-semibold">Use Password Manager</p>
                        <p class="mt-1 text-sm text-slate-500">Apply one reusable saved credential.</p>
                    </button>
                    <button type="button" @click="credentialModal.source = 'manual'" :class="credentialModal.source === 'manual' ? 'border-cyan-500 bg-cyan-50 text-cyan-900 ring-2 ring-cyan-100' : 'border-slate-200 bg-white text-slate-700'" class="rounded-2xl border p-4 text-left transition">
                        <p class="text-sm font-semibold">Enter Manually</p>
                        <p class="mt-1 text-sm text-slate-500">Store a username and password directly on the wireless client.</p>
                    </button>
                </div>

                <form x-ref="singleCredentialForm" method="POST" x-show="credentialModal.mode === 'single'" class="mt-6 space-y-6" :action="credentialModal.action">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="credential_source" :value="credentialModal.source">
                    <input type="hidden" name="redirect_route" value="index">

                    <div x-show="credentialModal.source === 'password_manager'" x-cloak>
                        <x-ui.input.select label="Saved Credential" name="password_manager_credential_id" :options="$credentialOptions" placeholder="Select a saved credential" x-model="credentialModal.passwordManagerCredentialId" />
                    </div>

                    <div class="grid gap-6 md:grid-cols-2" x-show="credentialModal.source === 'manual'" x-cloak>
                        <x-ui.input.text label="Username" name="provisioning_username" placeholder="subscriber-001" x-model="credentialModal.provisioningUsername" />
                        <x-ui.input.password label="Password" name="provisioning_password" placeholder="Leave blank to keep current password" x-model="credentialModal.provisioningPassword" />
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                        <div class="text-xs text-slate-500">
                            <span x-show="credentialModal.clientProvisioned">This wireless client is currently provisioned.</span>
                            <span x-show="!credentialModal.clientProvisioned">This wireless client is currently unprovisioned.</span>
                        </div>
                        <button type="submit" class="rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Save credentials</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('wireless-clients.credentials.bulk-update') }}" x-show="credentialModal.mode === 'bulk'" class="mt-6 space-y-6">
                    @csrf
                    <template x-for="clientId in selectedClientIds" :key="`bulk-${clientId}`">
                        <input type="hidden" name="wireless_client_ids[]" :value="clientId">
                    </template>
                    <input type="hidden" name="credential_source" :value="credentialModal.source">

                    <div x-show="credentialModal.source === 'password_manager'" x-cloak>
                        <x-ui.input.select label="Saved Credential" name="password_manager_credential_id" :options="$credentialOptions" placeholder="Select a saved credential" x-model="credentialModal.passwordManagerCredentialId" />
                    </div>

                    <div class="grid gap-6 md:grid-cols-2" x-show="credentialModal.source === 'manual'" x-cloak>
                        <x-ui.input.text label="Username" name="provisioning_username" placeholder="subscriber-bulk" x-model="credentialModal.provisioningUsername" />
                        <x-ui.input.password label="Password" name="provisioning_password" placeholder="Enter the password to apply" x-model="credentialModal.provisioningPassword" />
                    </div>

                    <div class="flex items-center justify-end border-t border-slate-200 pt-4">
                        <button type="submit" class="rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Apply to selection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function wirelessClientsIndex({ credentialOptions }) {
        return {
            clients: [],
            stats: { total: 0, connected: 0, disconnected: 0, moved_today: 0, provisioned: 0 },
            pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
            filters: { search: '', access_point_id: '', site_id: '', band: '', connection: '' },
            filterOptions: { access_points: [], sites: [], connections: [] },
            selectedClientIds: [],
            credentialOptions,
            credentialModal: {
                open: false,
                mode: 'single',
                action: '',
                clientName: '',
                clientProvisioned: false,
                source: 'manual',
                passwordManagerCredentialId: '',
                provisioningUsername: '',
                provisioningPassword: '',
            },
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
                    this.selectedClientIds = [];
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

            toggleClientSelection(clientId, checked) {
                if (checked) {
                    if (!this.selectedClientIds.includes(clientId)) {
                        this.selectedClientIds.push(clientId);
                    }

                    return;
                }

                this.selectedClientIds = this.selectedClientIds.filter((selectedId) => selectedId !== clientId);
            },

            toggleSelectAll(checked) {
                this.selectedClientIds = checked ? this.clients.map((client) => client.id) : [];
            },

            clearSelection() {
                this.selectedClientIds = [];
            },

            openSingleCredentialModal(client) {
                this.credentialModal = {
                    open: true,
                    mode: 'single',
                    action: @js(route('wireless-clients.index')).replace(/\/$/, '') + '/' + client.id + '/credentials',
                    clientName: client.host_name || client.mac_address,
                    clientProvisioned: client.is_provisioned,
                    source: client.credential_source === 'password_manager' ? 'password_manager' : 'manual',
                    passwordManagerCredentialId: client.password_manager_credential_id || '',
                    provisioningUsername: client.credential_source === 'manual' ? (client.provisioning_username || '') : '',
                    provisioningPassword: '',
                };
            },

            openBulkCredentialModal() {
                this.credentialModal = {
                    open: true,
                    mode: 'bulk',
                    action: '',
                    clientName: '',
                    clientProvisioned: false,
                    source: 'password_manager',
                    passwordManagerCredentialId: '',
                    provisioningUsername: '',
                    provisioningPassword: '',
                };
            },

            closeCredentialModal() {
                this.credentialModal.open = false;
            },
        };
    }
</script>
@endpush
