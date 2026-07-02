@extends('layouts.admin')

@section('title', 'Backup Operations')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Dashboard', 'href' => route('dashboard'), 'current' => true]]" />
@endpush

@section('content')
@php
    $stats = $backupDashboard['stats'];
    $exceptions = $backupDashboard['exceptions'];
    $attention = $backupDashboard['attention'];
    $coverage = $backupDashboard['coverage'];
    $coverageRate = $coverage['total'] > 0 ? round(($coverage['covered'] / $coverage['total']) * 100, 1) : 0;
@endphp

<div class="mx-auto max-w-[1500px] space-y-5 pb-10" x-data="dashboardOperations()" x-init="init()">
    <div x-show="notice" x-transition class="fixed right-5 top-20 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm font-semibold shadow-lg" :class="noticeType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800'" x-text="notice"></div>
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ session('error') }}</div>
    @endif

    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-950">Backup Operations</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Urgent backup failures and configuration changes appear first so you can triage what matters.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 lg:shrink-0 lg:flex-nowrap">
            <a href="{{ route('backups.index') }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-blue-600 bg-white px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
                View backups
            </a>
            <a href="{{ route('schedules.create') }}" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" /></svg>
                New schedule
            </a>
            <a href="{{ route('backups.compare') }}" class="inline-flex h-10 items-center px-3 text-sm font-semibold text-blue-700 transition hover:text-blue-900">Compare backups</a>
        </div>
    </header>

    <section class="overflow-hidden rounded-xl border border-rose-300 bg-white" aria-labelledby="exceptions-heading">
        <h2 id="exceptions-heading" class="sr-only">Operational exceptions</h2>
        <div class="grid divide-y divide-rose-100 border-l-4 border-rose-500 md:grid-cols-3 md:divide-x md:divide-y-0">
            <a href="{{ route('backups.index') }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-rose-50/70">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.7L2.7 17a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 3.7a2 2 0 00-3.4 0z" /></svg></span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-baseline gap-3"><strong class="text-3xl font-bold text-rose-600">{{ $exceptions['failed_backups'] }}</strong><span class="text-sm font-semibold text-slate-900">Backup failures</span></span>
                    <span class="mt-0.5 block text-xs text-slate-500">Latest backup attempt needs inspection</span>
                </span>
                <svg class="h-4 w-4 text-rose-500 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
            <a href="{{ route('schedules.index') }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-amber-50/70">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-baseline gap-3"><strong class="text-3xl font-bold text-amber-600">{{ $exceptions['schedule_issues'] }}</strong><span class="text-sm font-semibold text-slate-900">Overdue / paused coverage</span></span>
                    <span class="mt-0.5 block text-xs text-slate-500">Schedules not currently meeting policy</span>
                </span>
                <svg class="h-4 w-4 text-amber-500 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
            <a href="{{ route('diff-alerts.index') }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-rose-50/70">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h11zm0 0v1a3 3 0 01-6 0v-1" /></svg></span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-baseline gap-3"><strong class="text-3xl font-bold text-rose-600">{{ $exceptions['high_severity_diffs'] }}</strong><span class="text-sm font-semibold text-slate-900">Unread high-severity diffs</span></span>
                    <span class="mt-0.5 block text-xs text-slate-500">Configuration changes waiting for review</span>
                </span>
                <svg class="h-4 w-4 text-rose-500 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Backup health summary">
        <div class="border-l-2 border-emerald-500 px-4 py-1"><p class="text-xs font-semibold text-slate-600">Backup success rate (24h)</p><p class="mt-2 text-3xl font-bold tracking-tight text-emerald-600">{{ $stats['success_rate'] === null ? '—' : $stats['success_rate'].'%' }}</p><p class="mt-1 text-xs text-slate-500">{{ $stats['successful_backups'] }} successful / {{ $stats['completed_backups'] }} completed</p></div>
        <div class="border-l-2 border-blue-500 px-4 py-1"><p class="text-xs font-semibold text-slate-600">Routers with active schedules</p><p class="mt-2 text-3xl font-bold tracking-tight text-blue-700">{{ $stats['covered_routers'] }}</p><p class="mt-1 text-xs text-slate-500">of {{ $stats['total_routers'] }} total routers</p></div>
        <div class="border-l-2 border-blue-500 px-4 py-1"><p class="text-xs font-semibold text-slate-600">Configuration changes (7d)</p><p class="mt-2 text-3xl font-bold tracking-tight text-blue-700">{{ $stats['configuration_changes'] }}</p><p class="mt-1 text-xs text-slate-500">Compared with previous successful backups</p></div>
        <div class="border-l-2 border-rose-500 px-4 py-1"><p class="text-xs font-semibold text-slate-600">Unread diff alerts</p><p class="mt-2 text-3xl font-bold tracking-tight text-rose-600">{{ $stats['unread_alerts'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $stats['high_unread_alerts'] }} high severity</p></div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_minmax(300px,0.9fr)]">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><h2 class="text-base font-bold text-slate-950">Needs attention</h2><span class="text-xs font-semibold text-slate-500">{{ $attention->count() }} shown</span></div>
            <div class="divide-y divide-slate-100">
                @forelse($attention as $item)
                    <div class="grid gap-3 px-5 py-3 transition hover:bg-slate-50 md:grid-cols-[minmax(120px,0.7fr)_minmax(150px,1.4fr)_auto_auto] md:items-center">
                        <div class="flex min-w-0 items-center gap-3"><span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $item['tone'] === 'danger' ? 'bg-rose-500' : ($item['tone'] === 'warning' ? 'bg-amber-500' : 'bg-slate-400') }}"></span><span class="truncate text-sm font-semibold text-blue-700">{{ $item['title'] }}</span></div>
                        <p class="truncate text-sm text-slate-600" title="{{ $item['summary'] }}">{{ $item['summary'] }}</p>
                        <div class="flex items-center gap-3"><x-dashboard-status :tone="$item['tone']">{{ $item['status'] }}</x-dashboard-status><span class="whitespace-nowrap text-xs text-slate-500">{{ $item['occurred_at']?->diffForHumans() ?? 'Unknown' }}</span></div>
                        <div class="grid w-full grid-cols-2 items-center gap-2 md:w-40">
                            @if($item['type'] === 'backup')
                                <a href="{{ route('backups.show', $item['model']) }}" class="inline-flex h-8 items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">Inspect</a>
                                <button @click="retryBackup(@js(route('backups.retry', $item['model'])))" class="inline-flex h-8 items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">Retry</button>
                            @elseif($item['type'] === 'alert')
                                <a href="{{ route('diff-alerts.show', $item['model']) }}" class="inline-flex h-8 items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">Review</a>
                                <form method="POST" action="{{ route('diff-alerts.status', $item['model']) }}" class="flex">@csrf<input type="hidden" name="status" value="acknowledged"><button class="inline-flex h-8 w-full items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">Acknowledge</button></form>
                            @elseif($item['type'] === 'overdue_schedule')
                                <a href="{{ route('schedules.show', $item['model']) }}" class="inline-flex h-8 items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">View</a>
                                <form method="POST" action="{{ route('schedules.run', $item['model']) }}" class="flex">@csrf<button class="inline-flex h-8 w-full items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">Run now</button></form>
                            @else
                                <a href="{{ route('schedules.show', $item['model']) }}" class="inline-flex h-8 items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">View</a>
                                <form method="POST" action="{{ route('schedules.toggle', $item['model']) }}" class="flex">@csrf<button class="inline-flex h-8 w-full items-center justify-center text-xs font-semibold text-blue-700 hover:text-blue-900">Resume</button></form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center"><span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></span><h3 class="mt-3 text-sm font-bold text-slate-900">No backup issues need attention</h3><p class="mt-1 text-sm text-slate-500">Failures, overdue schedules, and unread diffs will appear here.</p></div>
                @endforelse
            </div>
        </div>

        <aside class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="text-base font-bold text-slate-950">Backup coverage</h2></div>
            <dl class="divide-y divide-slate-100 px-5">
                <div class="flex items-center justify-between gap-4 py-4"><dt class="flex items-center gap-3 text-sm font-medium text-slate-700"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Covered routers</dt><dd class="text-sm font-bold text-slate-900">{{ $coverage['covered'] }} <span class="font-normal text-slate-500">({{ $coverageRate }}%)</span></dd></div>
                <div class="flex items-center justify-between gap-4 py-4"><dt class="flex items-center gap-3 text-sm font-medium text-slate-700"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>Uncovered routers</dt><dd class="text-sm font-bold text-slate-900">{{ $coverage['uncovered'] }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-4"><dt class="flex items-center gap-3 text-sm font-medium text-slate-700"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>Active schedules</dt><dd class="text-sm font-bold text-slate-900">{{ $coverage['active_schedules'] }}</dd></div>
                <div class="py-4">
                    <dt class="text-sm font-medium text-slate-700">Next scheduled run</dt>
                    @if($coverage['next_schedule'])
                        <dd class="mt-2 flex items-end justify-between gap-3"><span class="text-sm font-bold text-slate-900">{{ $coverage['next_schedule']->next_run_at->diffForHumans() }}</span><a href="{{ route('schedules.show', $coverage['next_schedule']) }}" class="truncate text-xs font-medium text-blue-700 hover:text-blue-900">{{ $coverage['next_schedule']->name }}</a></dd>
                    @else
                        <dd class="mt-2 text-sm text-slate-500">No upcoming run scheduled</dd>
                    @endif
                </div>
            </dl>
            <div class="border-t border-slate-200 px-5 py-4"><a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900">Manage schedules<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></a></div>
        </aside>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h2 class="text-base font-bold text-slate-950">Managed routers</h2><p class="mt-1 text-xs text-slate-500">Routers selected for dashboard access.</p></div><a href="{{ route('routers.index') }}" class="text-sm font-semibold text-blue-700">Manage routers</a></div>
        <div class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($backupDashboard['routers'] as $router)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3"><div><a href="{{ route('routers.show', $router) }}" class="font-bold text-blue-700">{{ $router->name }}</a><p class="mt-1 text-xs text-slate-500">{{ $router->ip_address }}</p></div><x-ui.badge :status="$router->status">{{ ucfirst($router->status ?? 'offline') }}</x-ui.badge></div>
                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500"><span>{{ $router->backupSchedules->count() }} active schedules</span><span>{{ $router->latestBackup?->created_at?->diffForHumans() ?? 'Never backed up' }}</span></div>
                    <div class="mt-4 grid grid-cols-3 gap-2"><a href="{{ route('routers.show', $router) }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 text-xs font-semibold">View</a><a href="{{ route('routers.edit', $router) }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 text-xs font-semibold">Edit</a><button @click="triggerRouterBackup(@js(route('routers.backup', $router)))" class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 text-xs font-semibold text-white">Backup</button></div>
                </article>
            @empty
                <div class="col-span-full py-8 text-center text-sm text-slate-500">No routers are enabled for the dashboard. Enable one from its create or edit page.</div>
            @endforelse
        </div>
        @if($backupDashboard['routers']->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $backupDashboard['routers']->links() }}</div>@endif
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="text-base font-bold text-slate-950">Recent backup activity</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[540px] table-fixed text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold text-slate-500"><tr><th class="w-[23%] px-4 py-3">Router</th><th class="w-[16%] px-3 py-3">Result</th><th class="w-[16%] px-3 py-3">Change</th><th class="w-[21%] px-3 py-3">Schedule</th><th class="w-[10%] px-3 py-3">Size</th><th class="w-[14%] px-3 py-3">Completed</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="backup in backups" :key="backup.id"><tr class="transition hover:bg-slate-50"><td class="truncate px-4 py-3"><a :href="backup.show_url" class="font-semibold text-blue-700" x-text="backup.router_name"></a></td><td class="px-3 py-3"><span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium" :class="badgeClass(backup.status)" x-text="backup.status"></span></td><td class="px-3 py-3 text-xs text-slate-500" x-text="backup.changed === null ? '—' : (backup.changed ? 'Changed' : 'No changes')"></td><td class="truncate px-3 py-3 text-xs text-slate-600" x-text="backup.schedule_name"></td><td class="px-3 py-3 text-xs text-slate-600" x-text="formatBytes(backup.size_bytes)"></td><td class="px-3 py-3 text-xs text-slate-500" x-text="backup.finished_at ? new Date(backup.finished_at).toLocaleString() : 'Queued'"></td></tr></template>
                        <tr x-show="backups.length === 0"><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No backup activity yet.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-5 py-3"><a href="{{ route('backups.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View all backup activity</a></div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="text-base font-bold text-slate-950">Recent configuration changes</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($backupDashboard['recent_changes'] as $alert)
                    @php($severityTone = $alert->severity === 'high' ? 'danger' : ($alert->severity === 'medium' ? 'warning' : 'neutral'))
                    <div class="relative grid gap-2 px-5 py-3 pl-9 transition before:absolute before:bottom-0 before:left-[1.3rem] before:top-0 before:w-px before:bg-slate-200 hover:bg-slate-50 sm:grid-cols-[minmax(120px,0.7fr)_auto_minmax(150px,1.2fr)_auto] sm:items-center">
                        <span class="absolute left-[1.05rem] top-1/2 z-10 h-2.5 w-2.5 -translate-y-1/2 rounded-full ring-4 ring-white {{ $alert->severity === 'high' ? 'bg-rose-500' : ($alert->severity === 'medium' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                        <div><p class="truncate text-sm font-semibold text-blue-700">{{ $alert->router?->name ?? 'Unknown' }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $alert->created_at?->diffForHumans() }}</p></div>
                        <x-dashboard-status :tone="$severityTone">{{ ucfirst($alert->severity) }}</x-dashboard-status>
                        <div class="min-w-0"><p class="text-xs font-semibold text-slate-700">+{{ $alert->added_lines }} / -{{ $alert->removed_lines }}</p><p class="mt-0.5 truncate text-xs text-slate-500">{{ implode(', ', $alert->sections ?? []) ?: 'General configuration' }}</p></div>
                        <a href="{{ route('diff-alerts.show', $alert) }}" class="whitespace-nowrap text-xs font-semibold text-blue-700 hover:text-blue-900">{{ $alert->status === 'unread' ? 'Review diff' : 'View diff' }}</a>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-slate-500">No configuration changes have been detected yet.</div>
                @endforelse
            </div>
            <div class="border-t border-slate-200 px-5 py-3"><a href="{{ route('diff-alerts.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View all configuration changes</a></div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function dashboardOperations() {
    return {
        backups: [], notice: '', noticeType: 'success', timer: null,
        async init() { await this.refresh(); },
        async refresh() { const response = await fetch(@js(route('dashboard.data')), { headers: { Accept: 'application/json' } }); const data = await response.json(); this.backups = data.backups ?? []; if (this.backups.some(item => ['pending','running'].includes(item.status))) this.poll(); },
        poll() { clearTimeout(this.timer); this.timer = setTimeout(async () => { const activeBefore = this.backups.filter(item => ['pending','running'].includes(item.status)).map(item => item.id); await this.refresh(); const completed = this.backups.find(item => activeBefore.includes(item.id) && !['pending','running'].includes(item.status)); if (completed) this.notify(completed.status === 'success' ? `${completed.router_name} backup completed.` : `${completed.router_name} backup failed.`, completed.status === 'success' ? 'success' : 'error'); }, 1800); },
        async post(url) { const response = await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) } }); const data = await response.json(); if (!response.ok) throw new Error(data.message ?? 'Request failed.'); return data; },
        async retryBackup(url) { try { const data = await this.post(url); this.backups.unshift(data.backup); this.backups = this.backups.slice(0, 6); this.notify(data.message, 'success'); this.poll(); } catch (error) { this.notify(error.message, 'error'); } },
        async triggerRouterBackup(url) { try { const data = await this.post(url); this.notify(data.message, 'success'); await this.refresh(); } catch (error) { this.notify(error.message, 'error'); } },
        notify(message, type) { this.notice = message; this.noticeType = type; setTimeout(() => this.notice = '', 5000); },
        badgeClass(status) { return status === 'success' ? 'border-green-200 bg-green-100 text-green-800' : status === 'failed' ? 'border-red-200 bg-red-100 text-red-800' : status === 'running' ? 'border-blue-200 bg-blue-100 text-blue-800' : 'border-yellow-200 bg-yellow-100 text-yellow-800'; },
        formatBytes(bytes) { if (!bytes) return '—'; return bytes < 1024 ? `${bytes} B` : `${(bytes / 1024).toFixed(1)} KB`; },
    };
}
</script>
@endpush
