@extends('layouts.admin')

@section('title', $accessPoint->name)

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Access Points', 'href' => route('access-points.index')],
        ['label' => $accessPoint->name, 'current' => true],
    ]" />
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div
    class="space-y-6"
    x-data="accessPointShow({
        endpoint: @js(route('access-points.live-data', $accessPoint)),
        accessPoint: @js([
            'status' => $accessPoint->status,
            'connected_clients_count' => $accessPoint->connected_clients_count,
            'signal_quality' => $accessPoint->signal_quality,
            'cpu_usage' => $accessPoint->cpu_usage,
            'cpu_count' => $accessPoint->cpu_count,
            'cpu_frequency' => $accessPoint->cpu_frequency,
            'memory_usage' => $accessPoint->memory_usage,
            'total_memory' => $accessPoint->total_memory,
            'free_memory' => $accessPoint->free_memory,
            'total_hdd_space' => $accessPoint->total_hdd_space,
            'free_hdd_space' => $accessPoint->free_hdd_space,
            'last_seen_human' => $accessPoint->last_seen_at?->diffForHumans() ?: 'Never',
            'vendor' => $accessPoint->vendor,
            'model' => $accessPoint->model,
            'board_name' => $accessPoint->board_name,
            'ssid' => $accessPoint->ssid,
            'band' => $accessPoint->band,
            'channel' => $accessPoint->channel,
            'frequency' => $accessPoint->frequency,
            'tx_power' => $accessPoint->tx_power,
            'router' => $accessPoint->router?->name,
            'site' => $accessPoint->site?->name,
            'ip_address' => $accessPoint->ip_address,
            'mac_address' => $accessPoint->mac_address,
            'enable_monitoring' => $accessPoint->enable_monitoring,
            'enable_provisioning' => $accessPoint->enable_provisioning,
            'firmware_version' => $accessPoint->firmware_version,
            'architecture_name' => $accessPoint->architecture_name,
            'platform' => $accessPoint->platform,
            'uptime' => $accessPoint->uptime,
            'noise_floor' => $accessPoint->noise_floor,
            'channel_utilization' => $accessPoint->channel_utilization,
        ]),
        liveData: @js([
            'online' => $liveData['online'],
            'reason' => $liveData['reason'],
            'wireless' => $liveData['wireless'],
            'resource' => $liveData['resource'],
            'clients' => $liveData['clients'],
        ]),
        statusHistory: @js($statusHistory),
        clientMovements: @js($clientMovements),
    })"
    x-init="init()"
    x-cloak
>
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $accessPoint->name }}</h1>
                        <span
                            class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium"
                            :class="statusBadgeClass(accessPoint.status)"
                            x-text="statusLabel(accessPoint.status)"
                        >
                        </span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span x-text="accessPoint.ssid || 'No SSID'"></span>
                        <span x-text="accessPoint.band || '—'"></span>
                        <span x-text="accessPoint.ip_address || 'No IP assigned'"></span>
                        <span x-text="accessPoint.site || 'No site'"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('access-points.edit', $accessPoint) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Edit Access Point
                </a>
                <button @click="deleteModal = true" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-100 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-200">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div
        class="rounded-2xl border p-4 shadow-sm"
        :class="liveData.online ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold" :class="liveData.online ? 'text-green-800' : 'text-red-800'">
                    Live RouterOS Check
                </p>
                <p class="mt-1 text-sm" :class="liveData.online ? 'text-green-700' : 'text-red-700'">
                    <span x-show="liveData.online">Device is reachable and metrics are being pulled from RouterOS.</span>
                    <span x-show="! liveData.online">Device is not reachable right now.</span>
                    <span x-show="liveData.reason" class="font-medium" x-text="liveData.reason"></span>
                </p>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span x-show="isRefreshing" class="inline-flex items-center gap-2">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-blue-500"></span>
                    Refreshing
                </span>
                <button @click="refresh()" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">
                    Refresh now
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Connected Clients</p>
            <p class="mt-3 text-3xl font-bold text-gray-900" x-text="accessPoint.connected_clients_count ?? 0"></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Signal Quality</p>
            <p class="mt-3 text-3xl font-bold text-gray-900" x-text="`${accessPoint.signal_quality ?? 0}%`"></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">CPU / Memory</p>
            <p class="mt-3 text-3xl font-bold text-gray-900" x-text="`${accessPoint.cpu_usage ?? 0}% / ${accessPoint.memory_usage ?? 0}%`"></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">RAM Available</p>
            <p class="mt-3 text-lg font-semibold text-gray-900" x-text="formatStorage(accessPoint.free_memory)"></p>
            <p class="mt-1 text-sm text-gray-500" x-text="accessPoint.total_memory ? `of ${formatStorage(accessPoint.total_memory)}` : 'No total memory reported'"></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Last Seen</p>
            <p class="mt-3 text-lg font-semibold text-gray-900" x-text="accessPoint.last_seen_human || 'Never'"></p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Radio & Network</h3>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Vendor</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.vendor"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Model</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.model || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Board Name</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.board_name || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">SSID</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.ssid || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Band</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.band || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Channel / Frequency</span><span class="text-sm font-medium text-gray-900" x-text="`${accessPoint.channel || '—'} / ${accessPoint.frequency || '—'}`"></span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-500">TX Power</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.tx_power ? `${accessPoint.tx_power} dBm` : '—'"></span></div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Assignment & Provisioning</h3>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Router</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.router || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Site</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.site || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">IP Address</span><span class="font-mono text-sm font-medium text-gray-900" x-text="accessPoint.ip_address || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">MAC Address</span><span class="font-mono text-sm font-medium text-gray-900" x-text="accessPoint.mac_address || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Monitoring</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.enable_monitoring ? 'Enabled' : 'Disabled'"></span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-500">Provisioning</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.enable_provisioning ? 'Enabled' : 'Disabled'"></span></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">System Telemetry</h3>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Firmware</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.firmware_version || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Architecture</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.architecture_name || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Platform</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.platform || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Uptime</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.uptime || '—'"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">CPU Cores / Frequency</span><span class="text-sm font-medium text-gray-900" x-text="formatCpuDetails(accessPoint.cpu_count, accessPoint.cpu_frequency)"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">RAM Usage</span><span class="text-sm font-medium text-gray-900" x-text="formatMemoryBreakdown()"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Storage</span><span class="text-sm font-medium text-gray-900" x-text="formatDiskBreakdown()"></span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Noise Floor</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.noise_floor !== null ? `${accessPoint.noise_floor} dBm` : '—'"></span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-500">Channel Utilization</span><span class="text-sm font-medium text-gray-900" x-text="accessPoint.channel_utilization !== null ? `${accessPoint.channel_utilization}%` : '—'"></span></div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Status History</h3>
                <span class="text-xs font-medium uppercase tracking-wide text-gray-400">Transitions</span>
            </div>
            <div class="space-y-3">
                <template x-if="statusHistory.length === 0">
                    <p class="text-sm text-gray-500">No status transitions recorded yet.</p>
                </template>
                <template x-for="entry in statusHistory" :key="`${entry.checked_at}-${entry.current_status}`">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900">
                                <span x-text="statusLabel(entry.previous_status || 'unknown')"></span>
                                <span class="text-gray-400">→</span>
                                <span x-text="statusLabel(entry.current_status)"></span>
                            </div>
                            <span class="text-xs text-gray-500" x-text="formatDate(entry.checked_at)"></span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600" x-text="entry.reason || 'Status changed after RouterOS polling.'"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>


    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Wireless Clients</h3>
                    <p class="mt-1 text-sm text-gray-500">Live registrations seen on this AP. Updates refresh every 30 seconds.</p>
                </div>
                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700" x-text="`${liveData.clients?.length || 0} client(s)`"></span>
            </div>

            <div class="mt-6 space-y-3" x-show="(liveData.clients?.length || 0) > 0">
                <template x-for="client in liveData.clients" :key="client.id || client.mac_address">
                    <div class="rounded-2xl border border-gray-200 p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="client.host_name || client.mac_address"></p>
                                <p class="mt-1 text-xs text-gray-500" x-text="client.mac_address"></p>
                                <p class="mt-2 text-xs text-gray-500" x-show="client.last_ip_address" x-text="`IP: ${client.last_ip_address}`"></p>
                            </div>
                            <div class="grid gap-2 text-xs text-gray-500 md:text-right">
                                <span x-text="`Signal: ${client.signal_strength ?? '—'} dBm`"></span>
                                <span x-text="`SNR: ${client.signal_to_noise ?? '—'}`"></span>
                                <span x-text="`TX/RX: ${client.tx_rate || '—'} / ${client.rx_rate || '—'}`"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="(liveData.clients?.length || 0) === 0" class="mt-6 rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                No wireless registrations are currently reported for this access point.
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Client Movement</h3>
                    <p class="mt-1 text-sm text-gray-500">Recent roams into this AP from other access points.</p>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <template x-if="clientMovements.length === 0">
                    <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                        No movement into this AP has been recorded yet.
                    </div>
                </template>

                <template x-for="movement in clientMovements" :key="`${movement.mac_address}-${movement.moved_at}`">
                    <div class="rounded-2xl border border-gray-200 p-4">
                        <p class="text-sm font-semibold text-gray-900" x-text="movement.host_name || movement.mac_address"></p>
                        <p class="mt-1 text-xs text-gray-500" x-text="movement.mac_address"></p>
                        <p class="mt-3 text-sm text-gray-700">
                            <span x-text="movement.from_access_point || 'Unknown AP'"></span>
                            <span class="mx-1">→</span>
                            <span x-text="movement.to_access_point || 'Current AP'"></span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500" x-text="formatDate(movement.moved_at)"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Notes</h3>
        <p class="text-sm leading-6 text-gray-600">{{ $accessPoint->notes ?: 'No notes added yet.' }}</p>
    </div>

    <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Delete Access Point</h3>
                    <p class="mt-2 text-sm text-gray-500">Are you sure you want to delete "{{ $accessPoint->name }}"?</p>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form method="POST" action="{{ route('access-points.destroy', $accessPoint) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto">
                            Delete
                        </button>
                    </form>
                    <button @click="deleteModal = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto">
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
    function accessPointShow(config) {
        return {
            deleteModal: false,
            endpoint: config.endpoint,
            accessPoint: config.accessPoint,
            liveData: config.liveData,
            statusHistory: config.statusHistory,
            clientMovements: config.clientMovements,
            isRefreshing: false,
            refreshTimer: null,

            init() {
                this.refreshTimer = setInterval(() => this.refresh(), 30000);
            },

            async refresh() {
                if (this.isRefreshing) {
                    return;
                }

                this.isRefreshing = true;

                try {
                    const response = await fetch(this.endpoint, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('Unable to refresh access point metrics.');
                    }

                    const payload = await response.json();
                    this.accessPoint = {
                        ...this.accessPoint,
                        ...payload.access_point,
                    };
                    this.liveData = {
                        ...this.liveData,
                        ...payload.live_data,
                    };
                    this.statusHistory = payload.status_history ?? [];
                    this.clientMovements = payload.client_movements ?? [];
                } catch (error) {
                    this.liveData = {
                        ...this.liveData,
                        online: false,
                        reason: error.message,
                    };
                } finally {
                    this.isRefreshing = false;
                }
            },

            statusLabel(status) {
                if (! status) {
                    return 'Unknown';
                }

                return status.charAt(0).toUpperCase() + status.slice(1);
            },

            statusBadgeClass(status) {
                if (status === 'online') {
                    return 'border-green-200 bg-green-100 text-green-800';
                }

                if (status === 'maintenance') {
                    return 'border-amber-200 bg-amber-100 text-amber-800';
                }

                return 'border-red-200 bg-red-100 text-red-800';
            },

            formatDate(value) {
                if (! value) {
                    return 'Unknown time';
                }

                return new Date(value).toLocaleString();
            },

            formatStorage(value) {
                if (value === null || value === undefined || Number.isNaN(Number(value))) {
                    return '—';
                }

                const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                let size = Number(value);
                let unitIndex = 0;

                while (size >= 1024 && unitIndex < units.length - 1) {
                    size /= 1024;
                    unitIndex += 1;
                }

                const precision = size >= 100 || unitIndex === 0 ? 0 : 1;

                return `${size.toFixed(precision)} ${units[unitIndex]}`;
            },

            formatCpuDetails(cpuCount, cpuFrequency) {
                const parts = [];

                if (cpuCount) {
                    parts.push(`${cpuCount} core${cpuCount === 1 ? '' : 's'}`);
                }

                if (cpuFrequency) {
                    parts.push(`${cpuFrequency} MHz`);
                }

                return parts.length ? parts.join(' / ') : '—';
            },

            formatMemoryBreakdown() {
                if (! this.accessPoint.total_memory || this.accessPoint.free_memory === null || this.accessPoint.free_memory === undefined) {
                    return `${this.accessPoint.memory_usage ?? 0}%`;
                }

                return `${this.accessPoint.memory_usage ?? 0}% (${this.formatStorage(this.accessPoint.free_memory)} free of ${this.formatStorage(this.accessPoint.total_memory)})`;
            },

            formatDiskBreakdown() {
                if (! this.accessPoint.total_hdd_space || this.accessPoint.free_hdd_space === null || this.accessPoint.free_hdd_space === undefined) {
                    return '—';
                }

                return `${this.formatStorage(this.accessPoint.free_hdd_space)} free of ${this.formatStorage(this.accessPoint.total_hdd_space)}`;
            },
        };
    }
</script>
@endpush
