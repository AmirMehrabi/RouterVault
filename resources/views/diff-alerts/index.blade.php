@extends('layouts.admin')

@section('title', 'Configuration Change Alerts')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Diff Alerts', 'current' => true],
    ]" />
@endpush

@section('content')
@php
    $alertStatuses = $alerts->getCollection()->mapWithKeys(fn ($alert) => [$alert->id => $alert->status]);
@endphp
<div
    class="space-y-5 pb-10"
    x-data="diffAlertInbox(@js($alertStatuses), @js($stats))"
>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">Configuration change alerts</h1>
                <p class="mt-1 text-sm text-slate-500">Review configuration changes detected between RouterOS snapshots.</p>
            </div>
        </div>
        <a href="{{ route('diff-alerts.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 .6 1.7 1.7 0 00-.4 1.1V21h-4v-.09A1.7 1.7 0 008.6 19.4a1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-.6-1 1.7 1.7 0 00-1.1-.4H3v-4h.09A1.7 1.7 0 004.6 8.6a1.7 1.7 0 00-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-.6 1.7 1.7 0 00.4-1.1V3h4v.09A1.7 1.7 0 0015.4 4.6a1.7 1.7 0 001.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0019.4 9c.1.38.3.72.6 1 .3.28.68.4 1.1.4h.09v4h-.09a1.7 1.7 0 00-1.7.6z"/></svg>
            Alert settings
        </a>
    </header>

    <section class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['key' => 'unread', 'label' => 'Unread', 'hint' => 'Require review', 'color' => 'text-red-600', 'icon' => 'alert'],
            ['key' => 'high', 'label' => 'High severity', 'hint' => 'Across all alerts', 'color' => 'text-amber-600', 'icon' => 'warning'],
            ['key' => 'affectedRouters', 'label' => 'Affected routers', 'hint' => 'With unread alerts', 'color' => 'text-blue-600', 'icon' => 'router'],
            ['key' => 'acknowledged', 'label' => 'Acknowledged', 'hint' => 'Reviewed alerts', 'color' => 'text-emerald-600', 'icon' => 'check'],
            ['key' => 'ignored', 'label' => 'Ignored', 'hint' => 'Suppressed manually', 'color' => 'text-slate-500', 'icon' => 'clock'],
        ] as $metric)
            <div class="flex items-center gap-3 border-b border-slate-200 p-4 last:border-b-0 sm:border-r xl:border-b-0">
                <div class="{{ $metric['color'] }}">
                    @if($metric['icon'] === 'alert')<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 3.7a2 2 0 00-3.4 0z"/></svg>@endif
                    @if($metric['icon'] === 'warning')<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M12 8v5m0 3h.01"/></svg>@endif
                    @if($metric['icon'] === 'router')<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M4 7h16v5H4zM4 12h16v5H4zM7 9.5h.01M7 14.5h.01"/></svg>@endif
                    @if($metric['icon'] === 'check')<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="m8 12 2.5 2.5L16 9"/></svg>@endif
                    @if($metric['icon'] === 'clock')<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M12 7v5l3 2"/></svg>@endif
                </div>
                <div><p class="text-xl font-bold text-slate-900" x-text="stats[@js($metric['key'])]"></p><p class="text-xs font-semibold text-slate-700">{{ $metric['label'] }}</p><p class="text-[11px] text-slate-400">{{ $metric['hint'] }}</p></div>
            </div>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 pt-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <nav class="-mb-px flex gap-5 overflow-x-auto">
                    @foreach(['unread' => 'Unread', 'acknowledged' => 'Acknowledged', 'ignored' => 'Ignored', 'all' => 'All alerts'] as $key => $label)
                        <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500'" class="flex items-center gap-2 whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-semibold">
                            {{ $label }}
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600" x-text="{{ $key === 'all' ? 'stats.total' : ($key === 'unread' ? 'stats.unread' : ($key === 'acknowledged' ? 'stats.acknowledged' : 'stats.ignored')) }}"></span>
                        </button>
                    @endforeach
                </nav>
                <label class="relative mb-3 block w-full lg:w-72">
                    <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m21 21-4.3-4.3m1.3-5.7a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input x-model.debounce.150ms="q" type="search" placeholder="Search alerts…" class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        @foreach(['', 'Router', 'Change summary', 'Sections', 'Lines', 'Detected', 'Status', ''] as $heading)
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($alerts as $alert)
                        @php($search = strtolower(($alert->router?->name ?? '').' '.$alert->summary.' '.implode(' ', $alert->sections ?? [])))
                        <tr x-show="visible({{ $alert->id }}, @js($search))" x-transition.opacity class="hover:bg-slate-50/70">
                            <td class="px-4 py-4">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $alert->severity === 'high' ? 'bg-red-50 text-red-600' : ($alert->severity === 'medium' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600') }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 3.7a2 2 0 00-3.4 0z"/></svg>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-800">{{ $alert->router?->name ?: 'Unknown router' }}</td>
                            <td class="max-w-sm px-4 py-4"><a href="{{ route('diff-alerts.show', $alert) }}" class="text-sm font-semibold text-slate-900 hover:text-blue-700">{{ $alert->summary }}</a><p class="mt-0.5 text-xs capitalize text-slate-400">{{ $alert->severity }} severity</p></td>
                            <td class="px-4 py-4"><div class="flex max-w-xs flex-wrap gap-1">@foreach(array_slice($alert->sections ?? [], 0, 3) as $section)<span class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 font-mono text-[10px] text-slate-600">{{ $section }}</span>@endforeach</div></td>
                            <td class="whitespace-nowrap px-4 py-4 font-mono text-xs font-bold"><span class="text-emerald-700">+{{ $alert->added_lines }}</span><span class="ml-2 text-red-700">−{{ $alert->removed_lines }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4"><p class="text-xs text-slate-700">{{ $alert->created_at?->diffForHumans() }}</p><p class="mt-0.5 text-[11px] text-slate-400">{{ $alert->created_at?->format('M d, H:i') }}</p></td>
                            <td class="px-4 py-4"><span x-text="statusLabel({{ $alert->id }})" :class="statusClass({{ $alert->id }})" class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold capitalize"></span></td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('diff-alerts.show', $alert) }}" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50 hover:text-blue-700" title="View alert"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="2.5" stroke-width="1.8"/></svg></a>
                                    <button x-show="statuses[{{ $alert->id }}] !== 'acknowledged'" type="button" @click="acknowledge({{ $alert->id }}, @js(route('diff-alerts.status', $alert)))" :disabled="busy === {{ $alert->id }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
                                        Acknowledge
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-4 py-4">{{ $alerts->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function diffAlertInbox(initialStatuses, initialStats) {
    return {
        tab: 'unread',
        q: '',
        statuses: initialStatuses,
        stats: initialStats,
        busy: null,
        visible(id, search) {
            const matchesTab = this.tab === 'all' || this.statuses[id] === this.tab;
            return matchesTab && (!this.q || search.includes(this.q.toLowerCase()));
        },
        statusLabel(id) {
            return String(this.statuses[id] || '').replace('_', ' ');
        },
        statusClass(id) {
            return this.statuses[id] === 'unread'
                ? 'border-blue-200 bg-blue-50 text-blue-700'
                : (this.statuses[id] === 'acknowledged'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-slate-200 bg-slate-50 text-slate-600');
        },
        async acknowledge(id, url) {
            this.busy = id;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @js(csrf_token()),
                        Accept: 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: 'acknowledged' })
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Unable to acknowledge this alert.');
                const previous = this.statuses[id];
                this.statuses[id] = 'acknowledged';
                if (previous === 'unread') this.stats.unread = Math.max(0, this.stats.unread - 1);
                if (previous !== 'acknowledged') this.stats.acknowledged++;
            } catch (error) {
                alert(error.message);
            } finally {
                this.busy = null;
            }
        }
    };
}
</script>
@endpush
