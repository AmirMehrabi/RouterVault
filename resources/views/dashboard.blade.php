@extends('layouts.admin')

@section('title', 'Network Dashboard')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'current' => true],
    ]" />
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@php
    $hero = $dashboard['highlights']['hero'];
    $overview = $dashboard['overview'];
    $charts = $dashboard['charts'];
    $tables = $dashboard['tables'];
@endphp

<div class="space-y-6 pb-10" x-data="networkDashboard(@js($dashboard))" x-cloak>
    <section class="overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-2xl shadow-slate-300/40">
        <div class="relative isolate px-5 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.35),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(251,191,36,0.25),_transparent_30%)]"></div>
            <div class="relative grid gap-6 lg:grid-cols-[1.4fr_0.9fr] lg:items-end">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-300">Tenant operations dashboard</p>
                    <h1 class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-white sm:text-4xl">{{ $hero['title'] }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">{{ $hero['subtitle'] }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($hero['items'] as $item)
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $item['label'] }}</p>
                            <p class="mt-3 text-lg font-semibold text-white">{{ $item['value'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-300">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($overview['stats'] as $stat)
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ number_format($stat['value']) }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $stat['tone'] === 'sky' ? 'bg-sky-100 text-sky-700' : ($stat['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : ($stat['tone'] === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')) }}">
                        {{ $stat['meta'] }}
                    </span>
                </div>
                <p class="mt-4 text-sm text-slate-500">{{ $stat['detail'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.25fr_0.95fr]">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600">Capacity</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Connected clients by site</h2>
                    <p class="mt-1 text-sm text-slate-500">Compare active clients against currently observed capacity across your busiest sites.</p>
                </div>
                <a href="{{ route('sites.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-800">View sites</a>
            </div>
            <div class="mt-6 overflow-x-auto pb-2">
                <div class="min-w-[640px] sm:min-w-0">
                    <div class="flex h-72 items-end gap-3 sm:gap-4" x-ref="capacityChart"></div>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600">RF mix</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Access point band distribution</h2>
                <p class="mt-1 text-sm text-slate-500">See how your AP fleet is spread across 2.4GHz, 5GHz, and other radio bands.</p>
            </div>
            <div class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div class="mx-auto flex h-52 w-52 items-center justify-center" x-ref="bandChart"></div>
                <div class="space-y-3">
                    <template x-for="item in chartData.bandDistribution" :key="item.label">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full" :style="`background:${item.color}`"></span>
                                <span class="text-sm font-medium text-slate-700" x-text="item.label"></span>
                            </div>
                            <span class="text-sm font-semibold text-slate-950" x-text="item.value"></span>
                        </div>
                    </template>
                </div>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-600">Infra load</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Router CPU vs memory</h2>
                </div>
                <a href="{{ route('routers.index') }}" class="text-sm font-medium text-amber-700 hover:text-amber-800">All routers</a>
            </div>
            <div class="mt-6 overflow-x-auto pb-2">
                <div class="min-w-[560px] sm:min-w-0">
                    <div class="flex h-72 items-end gap-4" x-ref="routerLoadChart"></div>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-rose-600">Client quality</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Signal quality buckets</h2>
            </div>
            <div class="mt-6 space-y-4" x-ref="signalBuckets"></div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">Health strip</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Average health indicators</h2>
            </div>
            <div class="mt-6 space-y-4">
                @foreach ($overview['health'] as $item)
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $item['label'] }}</span>
                            <span class="font-semibold text-slate-950">{{ $item['value'] }}{{ $item['suffix'] }}</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $item['tone'] === 'emerald' ? 'bg-emerald-500' : ($item['tone'] === 'amber' ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, max(0, $item['value'])) }}%"></div>
                        </div>
                    </div>
                @endforeach
                <div class="rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                    These indicators are built from stored model metrics, so the dashboard stays fast and works well on mobile without waiting for live polling.
                </div>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-600">Operations</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Connectivity and management state</h2>
                </div>
                <a href="{{ route('wireless-clients.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-800">Wireless clients</a>
            </div>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <template x-for="item in chartData.managementStatuses" :key="item.label">
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm font-medium text-slate-600" x-text="item.label"></span>
                            <span class="text-lg font-semibold text-slate-950" x-text="item.value"></span>
                        </div>
                        <div class="mt-4 h-2 rounded-full bg-slate-100">
                            <div class="h-full rounded-full" :style="`width:${item.percent}%; background:${item.color}`"></div>
                        </div>
                    </div>
                </template>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-violet-600">Radio leaders</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Top access points by client count</h2>
                </div>
                <a href="{{ route('access-points.index') }}" class="text-sm font-medium text-violet-700 hover:text-violet-800">All APs</a>
            </div>
            <div class="mt-6 space-y-3">
                @forelse ($tables['topAccessPoints'] as $accessPoint)
                    <a href="{{ $accessPoint['href'] }}" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3 transition hover:border-violet-200 hover:bg-violet-50/50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $accessPoint['name'] }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $accessPoint['site'] }} · {{ $accessPoint['router'] }} · {{ $accessPoint['band'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-slate-950">{{ $accessPoint['clients'] }}</p>
                            <p class="text-xs text-slate-500">{{ $accessPoint['quality'] }}% quality</p>
                        </div>
                    </a>
                @empty
                    <p class="rounded-2xl bg-slate-50 px-4 py-5 text-sm text-slate-500">No access point data available yet.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 2xl:grid-cols-[1fr_1fr_1.1fr]">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Top routers</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Session-heavy routers</h2>
                </div>
                <a href="{{ route('routers.index') }}" class="text-sm font-medium text-slate-700 hover:text-slate-900">View list</a>
            </div>
            <div class="mt-6 space-y-3">
                @forelse ($tables['topRouters'] as $router)
                    <a href="{{ $router['href'] }}" class="block rounded-2xl border border-slate-200 px-4 py-3 transition hover:border-sky-200 hover:bg-sky-50/50">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $router['name'] }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $router['site'] }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $router['status'] === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ ucfirst($router['status']) }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-3 text-xs text-slate-500">
                            <div>
                                <p>Sessions</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $router['sessions'] }}</p>
                            </div>
                            <div>
                                <p>CPU</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $router['cpu'] }}%</p>
                            </div>
                            <div>
                                <p>Memory</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $router['memory'] }}%</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="rounded-2xl bg-slate-50 px-4 py-5 text-sm text-slate-500">No routers found for this tenant.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Attention queue</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Wireless clients needing follow-up</h2>
                </div>
            </div>
            <div class="mt-6 space-y-3">
                @forelse ($tables['attentionClients'] as $client)
                    <a href="{{ $client['href'] }}" class="block rounded-2xl border border-slate-200 px-4 py-3 transition hover:border-rose-200 hover:bg-rose-50/40">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $client['name'] }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $client['site'] }} · {{ $client['access_point'] }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $client['status'] === 'connected' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ ucfirst($client['status']) }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1">Signal: {{ $client['signal'] ?? 'n/a' }} dBm</span>
                            @if ($client['management_status'])
                                <span class="rounded-full {{ $client['management_status'] === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }} px-2.5 py-1">Mgmt: {{ ucfirst($client['management_status']) }}</span>
                            @endif
                            @if ($client['last_seen'])
                                <span class="rounded-full bg-slate-100 px-2.5 py-1">Seen {{ $client['last_seen'] }}</span>
                            @endif
                        </div>
                        @if ($client['message'])
                            <p class="mt-3 text-xs leading-5 text-slate-500">{{ $client['message'] }}</p>
                        @endif
                    </a>
                @empty
                    <p class="rounded-2xl bg-slate-50 px-4 py-5 text-sm text-slate-500">No wireless clients currently need attention.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Quick links</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Jump to operational modules</h2>
                </div>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('routers.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-sky-200 hover:bg-sky-50">
                    <p class="text-sm font-semibold text-slate-900">Routers</p>
                    <p class="mt-1 text-sm text-slate-500">Audit sessions, hardware load, and provisioning state.</p>
                </a>
                <a href="{{ route('access-points.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-violet-200 hover:bg-violet-50">
                    <p class="text-sm font-semibold text-slate-900">Access points</p>
                    <p class="mt-1 text-sm text-slate-500">Inspect radio health, clients, and AP live data.</p>
                </a>
                <a href="{{ route('wireless-clients.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-cyan-200 hover:bg-cyan-50">
                    <p class="text-sm font-semibold text-slate-900">Wireless clients</p>
                    <p class="mt-1 text-sm text-slate-500">Track connectivity, signal, and management outcomes.</p>
                </a>
                <a href="{{ route('sites.topology') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-amber-200 hover:bg-amber-50">
                    <p class="text-sm font-semibold text-slate-900">Site topology</p>
                    <p class="mt-1 text-sm text-slate-500">Review location-level capacity and infrastructure placement.</p>
                </a>
            </div>
        </article>
    </section>
</div>

<script>
    function networkDashboard(payload) {
        return {
            chartData: {
                capacityBySite: (payload.charts.capacityBySite || []).map((item) => ({
                    ...item,
                    percent: item.capacity > 0 ? Math.min(100, Math.round((item.value / item.capacity) * 100)) : 0,
                })),
                bandDistribution: (payload.charts.bandDistribution || []).map((item, index) => ({
                    ...item,
                    color: ['#0f766e', '#2563eb', '#7c3aed', '#f59e0b'][index % 4],
                })),
                routerLoad: payload.charts.routerLoad || [],
                signalBuckets: (payload.charts.signalBuckets || []).map((item, index) => ({
                    ...item,
                    color: ['#16a34a', '#65a30d', '#f59e0b', '#ef4444'][index % 4],
                })),
                managementStatuses: (() => {
                    const items = payload.charts.managementStatuses || [];
                    const total = items.reduce((sum, item) => sum + item.value, 0) || 1;

                    return items.map((item, index) => ({
                        ...item,
                        percent: Math.round((item.value / total) * 100),
                        color: ['#0ea5e9', '#94a3b8', '#10b981', '#f43f5e'][index % 4],
                    }));
                })(),
            },
            init() {
                this.renderCapacityChart();
                this.renderBandChart();
                this.renderRouterLoadChart();
                this.renderSignalBuckets();
            },
            renderCapacityChart() {
                const target = this.$refs.capacityChart;
                target.innerHTML = '';

                if (!this.chartData.capacityBySite.length) {
                    target.innerHTML = '<div class="flex h-full w-full items-center justify-center rounded-3xl bg-slate-50 text-sm text-slate-500">No client capacity data available yet.</div>';
                    return;
                }

                this.chartData.capacityBySite.forEach((item) => {
                    const column = document.createElement('div');
                    column.className = 'flex min-w-[88px] flex-1 flex-col justify-end';
                    column.innerHTML = `
                        <div class="flex h-full items-end gap-2">
                            <div class="flex flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-xs font-semibold text-slate-700">${item.value}</span>
                                <div class="w-full rounded-t-[1.25rem] bg-sky-500/90" style="height:${Math.max(item.percent, 10)}%"></div>
                            </div>
                            <div class="flex flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-xs font-semibold text-slate-400">${item.capacity}</span>
                                <div class="w-full rounded-t-[1.25rem] bg-slate-200" style="height:100%"></div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <p class="truncate text-xs font-semibold text-slate-800">${item.label}</p>
                            <p class="mt-1 text-[11px] text-slate-500">${item.percent}% active</p>
                        </div>
                    `;
                    target.appendChild(column);
                });
            },
            renderBandChart() {
                const target = this.$refs.bandChart;
                target.innerHTML = '';

                if (!this.chartData.bandDistribution.length) {
                    target.innerHTML = '<div class="flex h-full w-full items-center justify-center rounded-full bg-slate-50 text-sm text-slate-500">No AP band data</div>';
                    return;
                }

                const total = this.chartData.bandDistribution.reduce((sum, item) => sum + item.value, 0) || 1;
                let current = 0;
                const segments = this.chartData.bandDistribution.map((item) => {
                    const start = current;
                    const share = (item.value / total) * 100;
                    current += share;

                    return `${item.color} ${start}% ${current}%`;
                });

                target.innerHTML = `
                    <div class="relative flex h-52 w-52 items-center justify-center rounded-full" style="background:conic-gradient(${segments.join(', ')})">
                        <div class="flex h-32 w-32 flex-col items-center justify-center rounded-full bg-white text-center shadow-inner">
                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total APs</span>
                            <span class="mt-2 text-3xl font-semibold text-slate-950">${total}</span>
                        </div>
                    </div>
                `;
            },
            renderRouterLoadChart() {
                const target = this.$refs.routerLoadChart;
                target.innerHTML = '';

                if (!this.chartData.routerLoad.length) {
                    target.innerHTML = '<div class="flex h-full w-full items-center justify-center rounded-3xl bg-slate-50 text-sm text-slate-500">No router load metrics available yet.</div>';
                    return;
                }

                this.chartData.routerLoad.forEach((item) => {
                    const column = document.createElement('div');
                    column.className = 'flex min-w-[90px] flex-1 flex-col justify-end';
                    column.innerHTML = `
                        <div class="flex h-full items-end gap-2">
                            <div class="flex flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-xs font-semibold text-slate-700">${item.cpu}%</span>
                                <div class="w-full rounded-t-[1.25rem] bg-amber-500" style="height:${Math.max(item.cpu, 8)}%"></div>
                            </div>
                            <div class="flex flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-xs font-semibold text-slate-500">${item.memory}%</span>
                                <div class="w-full rounded-t-[1.25rem] bg-slate-400" style="height:${Math.max(item.memory, 8)}%"></div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <p class="truncate text-xs font-semibold text-slate-800">${item.label}</p>
                            <p class="mt-1 text-[11px] text-slate-500">CPU vs memory</p>
                        </div>
                    `;
                    target.appendChild(column);
                });
            },
            renderSignalBuckets() {
                const target = this.$refs.signalBuckets;
                target.innerHTML = '';

                if (!this.chartData.signalBuckets.length) {
                    target.innerHTML = '<div class="rounded-2xl bg-slate-50 px-4 py-5 text-sm text-slate-500">No signal data available yet.</div>';
                    return;
                }

                const total = this.chartData.signalBuckets.reduce((sum, item) => sum + item.value, 0) || 1;

                this.chartData.signalBuckets.forEach((item) => {
                    const row = document.createElement('div');
                    const percent = Math.round((item.value / total) * 100);
                    row.innerHTML = `
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">${item.label}</span>
                            <span class="font-semibold text-slate-950">${item.value}</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full" style="width:${percent}%; background:${item.color}"></div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">${percent}% of clients with recorded signal</p>
                    `;
                    target.appendChild(row);
                });
            },
        };
    }
</script>
@endsection
