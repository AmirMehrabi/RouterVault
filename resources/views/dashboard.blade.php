@extends('layouts.admin')

@section('title', 'Device Operations Dashboard')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'current' => true],
    ]" />
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    @keyframes dashboardPulse {
        0%, 100% { transform: scale(1); opacity: 0.55; }
        50% { transform: scale(1.08); opacity: 1; }
    }

    .dashboard-pulse {
        animation: dashboardPulse 2.4s ease-in-out infinite;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-10" x-data="deviceDashboard()" x-cloak>
    

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <template x-for="stat in primaryStats" :key="stat.label">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/80">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500" x-text="stat.label"></p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950" x-text="stat.value"></p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl" :class="stat.iconWrap">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="stat.iconTone">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" :d="stat.icon"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="stat.changeTone" x-text="stat.change"></span>
                    <span class="text-xs text-slate-500" x-text="stat.detail"></span>
                </div>
            </div>
        </template>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.45fr_0.95fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-600">Client telemetry</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Wireless clients by top site</h2>
                    <p class="mt-1 text-sm text-slate-500">Dummy load figures for your busiest towers and campus deployments.</p>
                </div>
                <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 p-1 text-xs font-medium text-slate-600">
                    <button type="button" class="rounded-full bg-slate-900 px-3 py-1.5 text-white">Live</button>
                    <button type="button" class="rounded-full px-3 py-1.5">24h</button>
                    <button type="button" class="rounded-full px-3 py-1.5">7d</button>
                </div>
            </div>

            <div class="mt-6 space-y-5">
                <template x-for="site in siteLoad" :key="site.name">
                    <div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-sm font-semibold text-slate-900" x-text="site.name"></h3>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" :class="site.statusTone" x-text="site.status"></span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500" x-text="site.description"></p>
                            </div>
                            <div class="grid grid-cols-3 gap-3 text-left sm:text-right">
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">Clients</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="site.clients"></p>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">Capacity</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="site.capacity"></p>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">Roaming</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="site.roaming"></p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full" :class="site.barTone" :style="`width: ${site.utilization}%`"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600">Provisioning board</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Config jobs waiting for push</h2>
                </div>
                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Auto-retry enabled
                </span>
            </div>

            <div class="mt-6 space-y-4">
                <template x-for="job in provisioningQueue" :key="job.name">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900" x-text="job.name"></h3>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" :class="job.priorityTone" x-text="job.priority"></span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500" x-text="job.scope"></p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="job.statusTone" x-text="job.status"></span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                            <span x-text="job.window"></span>
                            <span x-text="job.devices"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/70 overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600">Client watchlist</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Wireless clients needing attention</h2>
                        <p class="mt-1 text-sm text-slate-500">Low RSSI, sticky roaming, and excessive retransmits across APs.</p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter by AP, site, or MAC
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Client</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Site / AP</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Signal</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Traffic</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Risk</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Last Seen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="client in flaggedClients" :key="client.mac">
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white" x-text="client.initials"></div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900" x-text="client.name"></p>
                                            <p class="mt-1 font-mono text-xs text-slate-500" x-text="client.mac"></p>
                                            <p class="mt-1 text-xs text-slate-400" x-text="client.device"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-slate-600">
                                    <p class="font-medium text-slate-800" x-text="client.site"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="client.ap"></p>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <p class="text-sm font-semibold text-slate-900" x-text="client.signal"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="client.quality"></p>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-slate-600">
                                    <p x-text="client.traffic"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="client.behavior"></p>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="client.riskTone" x-text="client.risk"></span>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-slate-500" x-text="client.lastSeen"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-rose-600">AP health</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Access point pressure zones</h2>
                    </div>
                    <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">3 overloaded</span>
                </div>
                <div class="mt-5 space-y-4">
                    <template x-for="accessPoint in accessPoints" :key="accessPoint.name">
                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900" x-text="accessPoint.name"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="accessPoint.detail"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-slate-900" x-text="accessPoint.load"></p>
                                    <p class="mt-1 text-xs" :class="accessPoint.healthTone" x-text="accessPoint.health"></p>
                                </div>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full" :class="accessPoint.barTone" :style="`width: ${accessPoint.utilization}%`"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-600">Incident stream</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Recent network events</h2>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        View event log
                    </button>
                </div>
                <div class="mt-6 space-y-4">
                    <template x-for="event in timeline" :key="event.title">
                        <div class="flex gap-4">
                            <div class="mt-1 flex flex-col items-center">
                                <span class="flex h-9 w-9 items-center justify-center rounded-2xl" :class="event.dotWrap">
                                    <span class="h-2.5 w-2.5 rounded-full" :class="event.dot"></span>
                                </span>
                                <span class="mt-2 h-full w-px bg-slate-200"></span>
                            </div>
                            <div class="pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-900" x-text="event.title"></p>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="event.badgeTone" x-text="event.level"></span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500" x-text="event.detail"></p>
                                <p class="mt-2 text-xs uppercase tracking-[0.16em] text-slate-400" x-text="event.time"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    function deviceDashboard() {
        return {
            summaryPills: [
                { label: 'Wireless clients', value: '2,481 active', detail: '193 clients roaming between APs right now' },
                { label: 'Offline devices', value: '7 devices', detail: '4 routers, 3 APs across 3 sites' },
                { label: 'Pending approvals', value: '5 templates', detail: 'Router, AP, and VLAN profile changes waiting' },
            ],
            focusItems: [
                { label: 'Client churn', value: '+14%', detail: 'Higher than baseline on Riverside and North Loop sectors.', tone: 'text-amber-300' },
                { label: 'Config drift', value: '9 devices', detail: 'Running configs do not match assigned provisioning templates.', tone: 'text-rose-300' },
                { label: 'Backhaul latency', value: '12 ms', detail: 'Core links are stable with only one warning-grade route.', tone: 'text-emerald-300' },
            ],
            primaryStats: [
                {
                    label: 'Managed Sites',
                    value: '41',
                    change: '+3 this month',
                    detail: '2 greenfield deployments in staging',
                    icon: 'M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
                    iconWrap: 'bg-cyan-50',
                    iconTone: 'text-cyan-600',
                    changeTone: 'bg-cyan-50 text-cyan-700'
                },
                {
                    label: 'Online Routers',
                    value: '82 / 86',
                    change: '95.3% uptime',
                    detail: '2 core routers in maintenance mode',
                    icon: 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2',
                    iconWrap: 'bg-emerald-50',
                    iconTone: 'text-emerald-600',
                    changeTone: 'bg-emerald-50 text-emerald-700'
                },
                {
                    label: 'Provisioning Success',
                    value: '96.8%',
                    change: '28 jobs completed',
                    detail: 'Average push time 42 seconds',
                    icon: 'M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z',
                    iconWrap: 'bg-violet-50',
                    iconTone: 'text-violet-600',
                    changeTone: 'bg-violet-50 text-violet-700'
                },
                {
                    label: 'Flagged Clients',
                    value: '37',
                    change: '11 high risk',
                    detail: 'Signal or roaming anomalies in the past hour',
                    icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    iconWrap: 'bg-amber-50',
                    iconTone: 'text-amber-600',
                    changeTone: 'bg-amber-50 text-amber-700'
                }
            ],
            siteLoad: [
                { name: 'Riverside Tower', status: 'hot', statusTone: 'bg-rose-50 text-rose-700', description: 'Sector APs show heavy peak-time retries after 6 PM.', clients: '418 clients', capacity: '92% used', roaming: '41 roam events', utilization: 92, barTone: 'bg-gradient-to-r from-rose-500 to-orange-400' },
                { name: 'North Loop Campus', status: 'stable', statusTone: 'bg-emerald-50 text-emerald-700', description: 'High density deployment with balanced AP distribution.', clients: '362 clients', capacity: '76% used', roaming: '18 roam events', utilization: 76, barTone: 'bg-gradient-to-r from-emerald-500 to-cyan-500' },
                { name: 'East Market Mesh', status: 'watch', statusTone: 'bg-amber-50 text-amber-700', description: 'One backhaul link adds latency during provisioning windows.', clients: '241 clients', capacity: '67% used', roaming: '26 roam events', utilization: 67, barTone: 'bg-gradient-to-r from-amber-400 to-yellow-400' },
                { name: 'Harbor Edge', status: 'stable', statusTone: 'bg-emerald-50 text-emerald-700', description: 'Client sessions are sticky but signal quality remains clean.', clients: '193 clients', capacity: '54% used', roaming: '9 roam events', utilization: 54, barTone: 'bg-gradient-to-r from-sky-500 to-cyan-400' },
            ],
            provisioningQueue: [
                { name: 'AP SSID refresh', priority: 'high', priorityTone: 'bg-rose-50 text-rose-700', scope: '12 dual-radio APs at Riverside Tower', status: 'Awaiting approval', statusTone: 'bg-amber-50 text-amber-700', window: 'Window: 18:30 - 19:00', devices: '12 devices' },
                { name: 'Router QoS policy v3', priority: 'medium', priorityTone: 'bg-sky-50 text-sky-700', scope: 'Aggregation routers for East Market and Harbor Edge', status: 'Queued', statusTone: 'bg-slate-100 text-slate-700', window: 'Window: 22:00 - 22:30', devices: '4 devices' },
                { name: 'Guest VLAN rollout', priority: 'high', priorityTone: 'bg-rose-50 text-rose-700', scope: 'Campus access points for North Loop west wing', status: 'Template ready', statusTone: 'bg-emerald-50 text-emerald-700', window: 'Window: 01:00 - 02:00', devices: '19 devices' },
                { name: 'Firmware pin update', priority: 'low', priorityTone: 'bg-slate-100 text-slate-700', scope: 'Fallback patch set for remote relay routers', status: 'Validation running', statusTone: 'bg-cyan-50 text-cyan-700', window: 'Window: Pending', devices: '7 devices' },
            ],
            flaggedClients: [
                { initials: 'AM', name: 'Ammar Musa', mac: '84:2E:14:9C:33:8A', device: 'iPhone 15 Pro', site: 'Riverside Tower', ap: 'AP-RIV-03 / 5 GHz', signal: '-79 dBm', quality: 'Low RSSI, 24% retransmits', traffic: '182 Mbps down', behavior: 'Sticky client, refusing roam to closer AP', risk: 'High', riskTone: 'bg-rose-50 text-rose-700', lastSeen: '20 sec ago' },
                { initials: 'SK', name: 'Sarah Kim', mac: '3C:52:82:7D:A1:15', device: 'Dell Latitude 7440', site: 'North Loop Campus', ap: 'AP-NLC-11 / 5 GHz', signal: '-71 dBm', quality: 'Moderate RSSI, latency spikes', traffic: '74 Mbps symmetric', behavior: 'Repeated auth retries during handoff', risk: 'Medium', riskTone: 'bg-amber-50 text-amber-700', lastSeen: '44 sec ago' },
                { initials: 'JO', name: 'Jon Otero', mac: 'B8:27:EB:0F:9D:20', device: 'MikroTik hAP ax lite', site: 'East Market Mesh', ap: 'AP-EMM-04 / 2.4 GHz', signal: '-83 dBm', quality: 'Very low RSSI, packet loss', traffic: '12 Mbps down', behavior: 'Client likely outside intended coverage area', risk: 'High', riskTone: 'bg-rose-50 text-rose-700', lastSeen: '1 min ago' },
                { initials: 'LT', name: 'Leila Tran', mac: '60:AB:67:29:FE:55', device: 'Samsung Tab S9', site: 'Harbor Edge', ap: 'AP-HBE-02 / 5 GHz', signal: '-67 dBm', quality: 'Healthy RSSI, frequent band steering', traffic: '38 Mbps down', behavior: 'Roamed 8 times in 10 minutes', risk: 'Watch', riskTone: 'bg-sky-50 text-sky-700', lastSeen: '1 min ago' },
            ],
            accessPoints: [
                { name: 'AP-RIV-03', detail: 'Riverside Tower sector C', load: '61 clients', health: 'Overloaded', healthTone: 'text-rose-600', utilization: 94, barTone: 'bg-gradient-to-r from-rose-500 to-orange-400' },
                { name: 'AP-NLC-11', detail: 'North Loop Campus west wing', load: '44 clients', health: 'Healthy', healthTone: 'text-emerald-600', utilization: 63, barTone: 'bg-gradient-to-r from-emerald-500 to-cyan-400' },
                { name: 'AP-EMM-04', detail: 'East Market Mesh relay', load: '28 clients', health: 'Watch latency', healthTone: 'text-amber-600', utilization: 71, barTone: 'bg-gradient-to-r from-amber-400 to-yellow-300' },
                { name: 'AP-HBE-02', detail: 'Harbor Edge dock offices', load: '19 clients', health: 'Balanced', healthTone: 'text-sky-600', utilization: 48, barTone: 'bg-gradient-to-r from-sky-500 to-cyan-400' },
            ],
            timeline: [
                { title: 'Router profile pushed to Harbor Edge gateway', detail: 'QoS shaping template completed with backup route unchanged.', time: '8 minutes ago', level: 'Success', badgeTone: 'bg-emerald-50 text-emerald-700', dotWrap: 'bg-emerald-50', dot: 'bg-emerald-500' },
                { title: 'Sticky client surge detected on Riverside Tower', detail: '11 devices remained attached to AP-RIV-03 despite stronger neighbors.', time: '16 minutes ago', level: 'Warning', badgeTone: 'bg-amber-50 text-amber-700', dotWrap: 'bg-amber-50', dot: 'bg-amber-500' },
                { title: 'North Loop AP firmware validation passed', detail: 'Staged package approved for 19-campus AP rollout tonight.', time: '31 minutes ago', level: 'Info', badgeTone: 'bg-sky-50 text-sky-700', dotWrap: 'bg-sky-50', dot: 'bg-sky-500' },
                { title: 'East Market relay router went offline briefly', detail: 'Recovered after 43 seconds; monitor PSU and upstream link quality.', time: '54 minutes ago', level: 'Critical', badgeTone: 'bg-rose-50 text-rose-700', dotWrap: 'bg-rose-50', dot: 'bg-rose-500' },
            ],
        };
    }
</script>
@endpush
