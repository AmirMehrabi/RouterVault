@extends('layouts.admin')

@section('title', $client['host_name'] ?: $client['mac_address'])

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Wireless Clients', 'href' => route('wireless-clients.index')],
        ['label' => $client['host_name'] ?: $client['mac_address'], 'current' => true],
    ]" />
@endpush

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
@php
    $credentialSource = old('credential_source', $wirelessClient->password_manager_credential_id ? 'password_manager' : 'manual');
    $activeManagementAction = session('management_action', old('management_action_key', ''));
    $latestSnapshot = $latestManagementSnapshot ?? [];
    $latestDns = data_get($latestSnapshot, 'dns', []);
    $latestNtp = data_get($latestSnapshot, 'ntp_client', []);
    $latestClock = data_get($latestSnapshot, 'clock', []);
    $latestSnmp = data_get($latestSnapshot, 'snmp', []);
    $latestSnmpCommunities = data_get($latestSnapshot, 'snmp_communities', []);
    $latestLease = data_get($latestSnapshot, 'dhcp_lease', []);
    $latestSignal = data_get($latestSnapshot, 'registration_entry', []);
    $snmpCommunity = collect($latestSnmpCommunities)->firstWhere('name', old('snmp_community'))
        ?? collect($latestSnmpCommunities)->first()
        ?? [];
@endphp
<div
    class="space-y-6"
    x-data="wirelessClientManagementShow({
        initialTab: 'overview',
        activeAction: @js($activeManagementAction),
        shouldOpenActionModal: @js($errors->any() && $activeManagementAction !== ''),
    })"
    x-init="init()"
>
    @if(session('success'))
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-3xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.16),_transparent_34%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.16),_transparent_30%),linear-gradient(135deg,_#0f172a,_#1e293b_55%,_#0f766e)] px-6 py-8 text-white lg:px-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('wireless-clients.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-100 transition hover:text-white">
                        <span aria-hidden="true">&larr;</span>
                        <span>Back to Wireless Clients</span>
                    </a>
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-cyan-100">
                            Radio Management
                        </span>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $wirelessClient->isMikrotikManageable() ? 'bg-emerald-400/20 text-emerald-100 ring-1 ring-emerald-300/30' : 'bg-amber-400/20 text-amber-100 ring-1 ring-amber-300/30' }}">
                            {{ $wirelessClient->isMikrotikManageable() ? 'MikroTik Manageable' : 'Read-only / Not Reachable' }}
                        </span>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $client['is_connected'] ? 'bg-emerald-400/20 text-emerald-100 ring-1 ring-emerald-300/30' : 'bg-slate-200/20 text-slate-100 ring-1 ring-slate-300/30' }}">
                            {{ $client['is_connected'] ? 'Connected' : 'Disconnected' }}
                        </span>
                    </div>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $wirelessClient->device_identity ?: ($client['host_name'] ?: $client['mac_address']) }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200 sm:text-base">
                        Work from one operations page: discover live radio state, store snapshots, review history, and run controlled RouterOS administrative actions without opening Winbox.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[26rem]">
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-300">Management Host</p>
                        <p class="mt-3 font-mono text-lg text-white">{{ $wirelessClient->resolvedManagementHost() ?: 'Not assigned' }}</p>
                        <p class="mt-1 text-sm text-slate-300">Port {{ $wirelessClient->resolvedManagementPort() }} via {{ strtoupper($wirelessClient->management_protocol ?? 'routeros_api') }}</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-300">Last Discovery</p>
                        <p class="mt-3 text-lg font-semibold text-white">{{ $wirelessClient->last_discovered_at?->diffForHumans() ?: 'Never' }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ $wirelessClient->last_discovered_at?->format('M d, Y H:i') ?: 'Run discovery to populate device facts.' }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-300">Identity</p>
                    <p class="mt-3 text-xl font-semibold text-white">{{ $wirelessClient->device_identity ?: 'Unknown' }}</p>
                    <p class="mt-1 text-sm text-slate-300">PPPoE {{ $wirelessClient->pppoe_username ?: 'not discovered' }}</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-300">Version / Uptime</p>
                    <p class="mt-3 text-xl font-semibold text-white">{{ $wirelessClient->device_version ?: 'Unknown' }}</p>
                    <p class="mt-1 text-sm text-slate-300">{{ $wirelessClient->device_uptime ?: 'No uptime reported' }}</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-300">Signal / SNR</p>
                    <p class="mt-3 text-xl font-semibold text-white">{{ $wirelessClient->signal_strength ?? '—' }} dBm</p>
                    <p class="mt-1 text-sm text-slate-300">SNR {{ $wirelessClient->signal_to_noise ?? '—' }} / TX CCQ {{ $wirelessClient->tx_ccq ?? '—' }}</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-300">Last Result</p>
                    <p class="mt-3 text-xl font-semibold text-white">{{ ucfirst($wirelessClient->last_management_status ?: 'idle') }}</p>
                    <p class="mt-1 text-sm text-slate-300">{{ $wirelessClient->last_management_message ?: 'No administrative commands have been run yet.' }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.95fr)]">
        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Operational Summary</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-900">Everything the NOC needs for this radio</h2>
                        <p class="mt-2 text-sm text-slate-500">Fast facts from the database and the latest management snapshots are kept together so operators can decide before taking action.</p>
                    </div>
                    <button
                        type="button"
                        @click="openAction('discovery')"
                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50"
                        {{ $wirelessClient->isMikrotikManageable() ? '' : 'disabled' }}
                    >
                        Run Discovery Now
                    </button>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Access Path</p>
                        <p class="mt-3 text-lg font-semibold text-slate-900">{{ $client['access_point'] ?: 'Unknown AP' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Router {{ $wirelessClient->router?->name ?: 'Unknown' }} / Site {{ $client['site'] ?: 'Unknown' }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Customer IP</p>
                        <p class="mt-3 font-mono text-lg font-semibold text-slate-900">{{ $wirelessClient->last_ip_address ?: 'Unavailable' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Mgmt {{ $wirelessClient->resolvedManagementHost() ?: 'Not assigned' }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Credentials</p>
                        <p class="mt-3 text-lg font-semibold text-slate-900">{{ $client['provisioning_status'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $client['credential_source'] === 'password_manager' ? 'Shared password manager credential' : ($client['credential_source'] === 'manual' ? 'Manual device credential' : 'No management credential saved') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Administrative Functions</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-900">Predefined actions with variables, guardrails, and audit trail</h2>
                    </div>
                    <p class="max-w-sm text-sm text-slate-500">Every action goes through validation, tenant checks, command logging, and snapshot storage so administrators can work safely at scale.</p>
                </div>

                <div class="mt-6 space-y-6">
                    @foreach($managementActionGroups as $group)
                        <div>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $group['label'] }}</h3>
                                <span class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ count($group['actions']) }} action(s)</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach($group['actions'] as $action)
                                    @php
                                        $tone = match ($action['danger_level']) {
                                            'danger' => 'border-rose-200 bg-rose-50 text-rose-900',
                                            'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
                                            default => 'border-cyan-200 bg-cyan-50 text-slate-900',
                                        };
                                    @endphp
                                    <button
                                        type="button"
                                        @click="openAction(@js($action['key']))"
                                        class="group rounded-3xl border p-5 text-left transition hover:-translate-y-0.5 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 {{ $tone }}"
                                        {{ $wirelessClient->isMikrotikManageable() ? '' : 'disabled' }}
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-current/70">{{ $action['group'] }}</p>
                                                <h4 class="mt-3 text-lg font-semibold">{{ $action['label'] }}</h4>
                                            </div>
                                            @if($action['requires_confirmation'])
                                                <span class="rounded-full bg-white/80 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]">Confirm</span>
                                            @endif
                                        </div>
                                        <p class="mt-3 text-sm leading-6 text-current/80">{{ $action['description'] }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Credentials</p>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-900">Management login source</h2>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $client['is_provisioned'] ? 'bg-cyan-100 text-cyan-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $client['provisioning_status'] }}
                    </span>
                </div>

                @if($errors->hasAny(['credential_source', 'password_manager_credential_id', 'provisioning_username', 'provisioning_password']))
                    <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        Review the credential fields and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('wireless-clients.credentials.update', $wirelessClient) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_route" value="show">

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="rounded-3xl border p-4 transition {{ $credentialSource === 'password_manager' ? 'border-cyan-400 bg-cyan-50' : 'border-slate-200 bg-white' }}">
                            <input type="radio" name="credential_source" value="password_manager" class="sr-only" @checked($credentialSource === 'password_manager')>
                            <span class="text-sm font-semibold text-slate-900">Password Manager</span>
                            <span class="mt-1 block text-sm text-slate-500">Reuse a saved credential shared across devices.</span>
                        </label>
                        <label class="rounded-3xl border p-4 transition {{ $credentialSource === 'manual' ? 'border-cyan-400 bg-cyan-50' : 'border-slate-200 bg-white' }}">
                            <input type="radio" name="credential_source" value="manual" class="sr-only" @checked($credentialSource === 'manual')>
                            <span class="text-sm font-semibold text-slate-900">Manual</span>
                            <span class="mt-1 block text-sm text-slate-500">Store a device-specific management username and password.</span>
                        </label>
                    </div>

                    <div class="grid gap-4">
                        <x-ui.input.select
                            label="Saved Credential"
                            name="password_manager_credential_id"
                            :options="$credentialOptions"
                            placeholder="Choose a saved credential"
                            :value="old('password_manager_credential_id', $wirelessClient->password_manager_credential_id)"
                        />

                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.input.text
                                label="Manual Username"
                                name="provisioning_username"
                                :value="old('provisioning_username', $wirelessClient->provisioning_username)"
                                placeholder="admin"
                            />
                            <x-ui.input.password
                                label="Manual Password"
                                name="provisioning_password"
                                placeholder="Leave blank to keep the current password"
                                showToggle="true"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                        <p class="text-sm text-slate-500">These credentials are also used for RouterOS management actions on this page.</p>
                        <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Save credentials
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Latest Snapshot</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-900">Most recent discovered state</h2>
                <div class="mt-6 space-y-4 text-sm">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">Identity</span><span class="font-medium text-slate-900">{{ $wirelessClient->device_identity ?: '—' }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">PPPoE Username</span><span class="font-medium text-slate-900">{{ $wirelessClient->pppoe_username ?: '—' }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">Device MAC</span><span class="font-mono font-medium text-slate-900">{{ $wirelessClient->device_mac_address ?: '—' }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">RouterOS Version</span><span class="font-medium text-slate-900">{{ $wirelessClient->device_version ?: '—' }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">Uptime</span><span class="font-medium text-slate-900">{{ $wirelessClient->device_uptime ?: '—' }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">DNS Servers</span><span class="font-medium text-slate-900">{{ data_get($latestDns, 'servers', '—') }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">Timezone</span><span class="font-medium text-slate-900">{{ data_get($latestClock, 'time-zone-name', '—') }}</span></div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-3"><span class="text-slate-500">SNMP</span><span class="font-medium text-slate-900">{{ data_get($latestSnmp, 'enabled') ?: '—' }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-slate-500">DHCP Lease</span><span class="font-medium text-slate-900">{{ data_get($latestLease, 'address', $wirelessClient->last_ip_address ?: '—') }}</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap gap-3 border-b border-slate-200 pb-4">
            <button type="button" @click="activeTab = 'overview'" :class="tabClass('overview')" class="rounded-full px-4 py-2 text-sm font-semibold transition">Overview</button>
            <button type="button" @click="activeTab = 'history'" :class="tabClass('history')" class="rounded-full px-4 py-2 text-sm font-semibold transition">History</button>
            <button type="button" @click="activeTab = 'snapshots'" :class="tabClass('snapshots')" class="rounded-full px-4 py-2 text-sm font-semibold transition">Snapshots</button>
            <button type="button" @click="activeTab = 'movement'" :class="tabClass('movement')" class="rounded-full px-4 py-2 text-sm font-semibold transition">Movement</button>
        </div>

        <div class="mt-6" x-show="activeTab === 'overview'">
            <div class="grid gap-6 xl:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Wireless</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">SSID</dt><dd class="font-medium text-slate-900">{{ $client['ssid'] ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Band / Frequency</dt><dd class="font-medium text-slate-900">{{ $client['band'] ?: '—' }} / {{ $client['frequency'] ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">TX / RX</dt><dd class="font-medium text-slate-900">{{ $client['tx_rate'] ?: '—' }} / {{ $client['rx_rate'] ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Signal / SNR</dt><dd class="font-medium text-slate-900">{{ $client['signal_strength'] ?? '—' }} / {{ $client['signal_to_noise'] ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Registration TX/RX CCQ</dt><dd class="font-medium text-slate-900">{{ $client['tx_ccq'] ?? '—' }} / {{ $client['rx_ccq'] ?? '—' }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Discovery Extract</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">DNS</dt><dd class="font-medium text-right text-slate-900">{{ data_get($latestDns, 'servers', '—') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Allow Remote Requests</dt><dd class="font-medium text-slate-900">{{ data_get($latestDns, 'allow-remote-requests', '—') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">NTP Servers</dt><dd class="font-medium text-right text-slate-900">{{ data_get($latestNtp, 'servers', '—') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Timezone</dt><dd class="font-medium text-slate-900">{{ data_get($latestClock, 'time-zone-name', '—') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">SNMP Community</dt><dd class="font-medium text-slate-900">{{ data_get($snmpCommunity, 'name', '—') }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Timestamps</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">First Seen</dt><dd class="font-medium text-slate-900">{{ $wirelessClient->first_seen_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Last Seen</dt><dd class="font-medium text-slate-900">{{ $wirelessClient->last_seen_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Last Discovery</dt><dd class="font-medium text-slate-900">{{ $wirelessClient->last_discovered_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Last Management Run</dt><dd class="font-medium text-slate-900">{{ $wirelessClient->last_management_ran_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Last Move</dt><dd class="font-medium text-slate-900">{{ $wirelessClient->last_moved_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="mt-6" x-show="activeTab === 'history'" x-cloak>
            <div class="space-y-3">
                @forelse($managementLogs as $log)
                    <div class="rounded-3xl border px-5 py-4 {{ $log['status'] === 'success' ? 'border-emerald-200 bg-emerald-50/70' : 'border-rose-200 bg-rose-50/70' }}">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-900">{{ $log['action_label'] }}</p>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] {{ $log['status'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $log['status'] }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-slate-600">{{ $log['summary'] ?: $log['error_message'] ?: 'No summary recorded.' }}</p>
                                <div class="mt-2 flex flex-wrap gap-4 text-xs text-slate-500">
                                    <span>Target {{ $log['target_host'] ?: '—' }}</span>
                                    <span>Operator {{ $log['user_name'] ?: 'System' }}</span>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ $log['created_human'] ?: 'Just now' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">
                        No management history has been recorded yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6" x-show="activeTab === 'snapshots'" x-cloak>
            <div class="space-y-3">
                @forelse($managementSnapshots as $snapshot)
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-700">{{ $snapshot['snapshot_type'] }}</p>
                                    @if($snapshot['action_key'])
                                        <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $snapshot['action_key'] }}</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Captured {{ $snapshot['collected_human'] ?: 'recently' }}</p>
                            </div>
                            <p class="text-sm text-slate-500">{{ $snapshot['collected_at'] }}</p>
                        </div>
                        <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-cyan-100">{{ json_encode($snapshot['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">
                        No discovery snapshots have been saved yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6" x-show="activeTab === 'movement'" x-cloak>
            <div class="space-y-3">
                @forelse($movements as $movement)
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $movement['from_access_point'] ?: 'Unknown AP' }} &rarr; {{ $movement['to_access_point'] ?: 'Unknown AP' }}</p>
                                <p class="mt-1 text-sm text-slate-500">Moved {{ $movement['moved_human'] ?: 'recently' }}</p>
                            </div>
                            <p class="text-sm text-slate-500">{{ $movement['moved_at'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">
                        This client has not been observed moving between access points yet.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <div x-cloak x-show="actionModalOpen" class="fixed inset-0 z-50 flex items-start justify-center bg-slate-950/70 px-4 py-8 backdrop-blur-sm sm:items-center">
        <div @click.outside="closeActionModal()" class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-700">Management Action</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900" x-text="actionTitle()"></h2>
                        <p class="mt-2 text-sm text-slate-500" x-text="actionDescription()"></p>
                    </div>
                    <button type="button" @click="closeActionModal()" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                        Close
                    </button>
                </div>
            </div>

            <div class="max-h-[80vh] overflow-y-auto px-6 py-6">
                <template x-if="!canManage()">
                    <div class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                        This wireless client is not ready for MikroTik management. Assign a management IP and device credential first.
                    </div>
                </template>

                <form method="POST" :action="actionUrl()" class="space-y-6" x-show="selectedAction && canManage()">
                    @csrf
                    <input type="hidden" name="management_action_key" :value="selectedAction">

                    <div x-show="selectedAction === 'discovery' || selectedAction === 'refresh_signal' || selectedAction === 'refresh_dhcp_lease'" class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        This action executes immediately and stores a fresh snapshot plus a history entry.
                    </div>

                    <div x-show="selectedAction === 'set_identity'" x-cloak>
                        <label class="block text-sm font-medium text-slate-700" for="identity">Identity</label>
                        <input id="identity" name="identity" type="text" value="{{ old('identity', $wirelessClient->device_identity) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="Tower1-AP3-912xxxx">
                        @error('identity')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="selectedAction === 'set_dns'" x-cloak class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700" for="dns_servers">DNS Servers</label>
                            <input id="dns_servers" name="dns_servers" type="text" value="{{ old('dns_servers', data_get($latestDns, 'servers', '8.8.8.8,1.1.1.1')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="8.8.8.8,1.1.1.1">
                            @error('dns_servers')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <input type="hidden" name="allow_remote_requests" value="0">
                            <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="allow_remote_requests" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" {{ old('allow_remote_requests', data_get($latestDns, 'allow-remote-requests') === 'true') ? 'checked' : '' }}>
                                Allow remote requests
                            </label>
                            @error('allow_remote_requests')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div x-show="selectedAction === 'set_ntp'" x-cloak class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700" for="ntp_servers">NTP Servers</label>
                            <input id="ntp_servers" name="ntp_servers" type="text" value="{{ old('ntp_servers', data_get($latestNtp, 'servers', 'pool.ntp.org')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="pool.ntp.org,time.google.com">
                            @error('ntp_servers')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <input type="hidden" name="ntp_enabled" value="0">
                            <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="ntp_enabled" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" {{ old('ntp_enabled', data_get($latestNtp, 'enabled') !== 'false') ? 'checked' : '' }}>
                                Enable NTP client
                            </label>
                            @error('ntp_enabled')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div x-show="selectedAction === 'set_timezone'" x-cloak>
                        <label class="block text-sm font-medium text-slate-700" for="time_zone_name">Timezone Name</label>
                        <input id="time_zone_name" name="time_zone_name" type="text" value="{{ old('time_zone_name', data_get($latestClock, 'time-zone-name', 'Asia/Tehran')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="Asia/Tehran">
                        @error('time_zone_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="selectedAction === 'set_snmp'" x-cloak class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <input type="hidden" name="snmp_enabled" value="0">
                            <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="snmp_enabled" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" {{ old('snmp_enabled', data_get($latestSnmp, 'enabled') !== 'false') ? 'checked' : '' }}>
                                Enable SNMP
                            </label>
                            @error('snmp_enabled')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="snmp_community">Community Name</label>
                            <input id="snmp_community" name="snmp_community" type="text" value="{{ old('snmp_community', data_get($snmpCommunity, 'name', 'nms')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="nms">
                            @error('snmp_community')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="snmp_addresses">Allowed Addresses</label>
                            <input id="snmp_addresses" name="snmp_addresses" type="text" value="{{ old('snmp_addresses', data_get($snmpCommunity, 'addresses', '10.0.0.10/32')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="10.0.0.10/32">
                            @error('snmp_addresses')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div x-show="selectedAction === 'set_password'" x-cloak class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="password">New Password</label>
                            <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="New password">
                            @error('password')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="password_confirmation">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" placeholder="Repeat password">
                        </div>
                        <div class="md:col-span-2 rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            If this device uses a shared password manager credential, changing the device password may desynchronize the shared record.
                        </div>
                    </div>

                    <div x-show="selectedAction === 'reboot'" x-cloak class="rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                        Reboot interrupts the customer radio immediately. Use it only when you are sure the maintenance window and service impact are acceptable.
                    </div>

                    <div x-show="requiresConfirmation()" x-cloak class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <input type="hidden" name="confirm_action" value="0">
                        <label class="flex items-start gap-3 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="confirm_action" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" {{ old('confirm_action') ? 'checked' : '' }}>
                            <span>I understand this action changes device configuration and will be recorded in the audit log.</span>
                        </label>
                        @error('confirm_action')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-slate-500">
                            Current management IP: <span class="font-mono text-slate-700">{{ $wirelessClient->resolvedManagementHost() ?: 'Not assigned' }}</span>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Run action
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function wirelessClientManagementShow({ initialTab, activeAction, shouldOpenActionModal }) {
        return {
            activeTab: initialTab,
            selectedAction: activeAction || '',
            actionModalOpen: false,
            actions: @json(collect($managementActionGroups)->flatMap(fn ($group) => $group['actions'])->keyBy('key')->all()),
            manageable: @json($wirelessClient->isMikrotikManageable()),
            actionEndpointTemplate: @json(route('wireless-clients.management-actions.run', ['wirelessClient' => $wirelessClient, 'action' => '__ACTION__'])),

            init() {
                if (shouldOpenActionModal && this.selectedAction) {
                    this.actionModalOpen = true;
                }
            },

            canManage() {
                return this.manageable;
            },

            openAction(actionKey) {
                this.selectedAction = actionKey;
                this.actionModalOpen = true;
            },

            closeActionModal() {
                this.actionModalOpen = false;
            },

            actionUrl() {
                return this.actionEndpointTemplate.replace('__ACTION__', this.selectedAction || 'discovery');
            },

            actionDefinition() {
                return this.actions[this.selectedAction] || { label: 'Management Action', description: '', requires_confirmation: false };
            },

            actionTitle() {
                return this.actionDefinition().label;
            },

            actionDescription() {
                return this.actionDefinition().description;
            },

            requiresConfirmation() {
                return Boolean(this.actionDefinition().requires_confirmation);
            },

            tabClass(tab) {
                return this.activeTab === tab
                    ? 'bg-slate-900 text-white shadow-sm'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
            },
        };
    }
</script>
@endpush
