@extends('layouts.admin')

@section('title', $schedule->name)

@section('content')
<div class="space-y-6" x-data="scheduleDetails()" x-init="init()">
    <div x-show="notice" x-transition class="fixed right-5 top-20 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm font-semibold shadow-lg" :class="noticeType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800'" x-text="notice"></div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">{{ $schedule->name }}</h1><p class="text-sm text-gray-500">Every {{ $schedule->interval_value }} {{ $schedule->interval_unit }}</p></div>
        <div class="flex gap-2">
            <button @click="runNow()" :disabled="running" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:bg-gray-400"><span x-text="running ? 'Running…' : 'Run Now'"></span></button>
            <form method="POST" action="{{ route('schedules.toggle', $schedule) }}">@csrf<button class="rounded-lg border border-gray-300 px-4 py-2 text-sm">{{ $schedule->is_enabled ? 'Pause' : 'Resume' }}</button></form>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold">Selected Routers</h2>
            <div class="max-h-72 space-y-2 overflow-y-auto">@foreach($schedule->routers as $router)<div class="rounded-lg bg-gray-50 px-3 py-2 text-sm">{{ $router->name }} <span class="text-gray-500">{{ $router->ip_address }}</span></div>@endforeach</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold">Recent Runs</h2>
            <div class="max-h-72 space-y-2 overflow-y-auto">
                <template x-for="run in runs" :key="run.id">
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 text-sm">
                        <div><span class="font-medium" x-text="`${run.successful_backups}/${run.total_routers} successful`"></span><p class="text-xs text-gray-500" x-text="run.started_at ? new Date(run.started_at).toLocaleString() : 'Waiting to start'"></p></div>
                        <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium" :class="badgeClass(run.status)" x-text="run.status"></span>
                    </div>
                </template>
                <div x-show="runs.length === 0" class="text-sm text-gray-500">No runs yet.</div>
            </div>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm" x-data="{ open: true }">
        <button @click="open = !open" class="flex w-full items-center justify-between px-5 py-4 text-left"><span><strong>Schedule backups</strong><span class="ml-2 text-sm font-normal text-gray-500">{{ $backups->total() }} total</span></span><span x-text="open ? 'Hide' : 'Show'" class="text-sm font-semibold text-blue-700"></span></button>
        <div x-show="open" x-transition class="border-t border-gray-200">
            <form method="GET" class="grid gap-3 border-b border-gray-200 p-4 sm:grid-cols-4">
                <select name="router_id" class="rounded-lg border-gray-300 text-sm"><option value="">All routers</option>@foreach($schedule->routers as $router)<option value="{{ $router->id }}" @selected(request('router_id') == $router->id)>{{ $router->name }}</option>@endforeach</select>
                <select name="status" class="rounded-lg border-gray-300 text-sm"><option value="">All statuses</option>@foreach(['pending','running','success','failed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                <select name="changed" class="rounded-lg border-gray-300 text-sm"><option value="">Any change state</option><option value="1" @selected(request('changed') === '1')>Changed</option><option value="0" @selected(request('changed') === '0')>Unchanged</option></select>
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply filters</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500"><tr><th class="px-4 py-3">Router</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Changed</th><th class="px-4 py-3">Created</th><th class="px-4 py-3 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">@forelse($backups as $backup)<tr><td class="px-4 py-3">{{ $backup->router?->name }}</td><td class="px-4 py-3"><x-backup-status :status="$backup->status" /></td><td class="px-4 py-3">{{ $backup->changed === null ? '—' : ($backup->changed ? 'Yes' : 'No') }}</td><td class="px-4 py-3">{{ $backup->created_at?->format('Y-m-d H:i') }}</td><td class="px-4 py-3 text-right"><a href="{{ route('backups.show', $backup) }}" class="font-semibold text-blue-700">View</a></td></tr>@empty<tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No backups match these filters.</td></tr>@endforelse</tbody>
                </table>
            </div>
            <div class="p-4">{{ $backups->links() }}</div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function scheduleDetails() {
    return {
        runs: @js($schedule->runs),
        running: false,
        notice: '', noticeType: 'success', timer: null,
        init() { this.running = this.runs.some(run => ['queued', 'running'].includes(run.status)); if (this.running) this.poll(); },
        async runNow() {
            this.running = true;
            const response = await fetch(@js(route('schedules.run', $schedule)), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) } });
            const data = await response.json();
            if (!response.ok) { this.notify(data.message ?? 'Unable to start schedule.', 'error'); this.running = false; return; }
            this.runs.unshift(data.run); this.runs = this.runs.slice(0, 10); this.poll();
        },
        poll() { clearTimeout(this.timer); this.timer = setTimeout(() => this.refreshRuns(), 1500); },
        async refreshRuns() {
            const response = await fetch(@js(route('schedules.runs', $schedule)), { headers: { Accept: 'application/json' } });
            const data = await response.json(); const wasRunning = this.running;
            this.runs = data.runs ?? []; this.running = this.runs.some(run => ['queued', 'running'].includes(run.status));
            if (this.running) this.poll(); else if (wasRunning) { const latest = this.runs[0]; this.notify(latest?.status === 'success' ? 'Schedule run completed successfully.' : 'Schedule run completed with failures.', latest?.status === 'success' ? 'success' : 'error'); }
        },
        notify(message, type) { this.notice = message; this.noticeType = type; setTimeout(() => this.notice = '', 5000); },
        badgeClass(status) { return status === 'success' ? 'border-green-200 bg-green-100 text-green-800' : status === 'failed' ? 'border-red-200 bg-red-100 text-red-800' : status === 'running' ? 'border-blue-200 bg-blue-100 text-blue-800' : 'border-yellow-200 bg-yellow-100 text-yellow-800'; },
    };
}
</script>
@endpush
