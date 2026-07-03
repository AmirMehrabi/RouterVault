@extends('layouts.admin')

@section('title', $router->name ?? 'Router')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Routers', 'href' => route('routers.index')],
        ['label' => $router->name ?? 'Router', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-5 pb-10" x-data="routerShowWorkspace()" x-cloak>
    <header class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $router->isOnline() ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="truncate text-2xl font-bold tracking-tight text-slate-950">{{ $router->name }}</h1>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $router->isOnline() ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $router->isOnline() ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ ucfirst($router->status ?? 'offline') }}
                        </span>
                    </div>
                    <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                        <span class="font-mono">{{ $router->ip_address }}</span>
                        <span>{{ $router->model ?: $router->vendor }}</span>
                        <span>RouterOS {{ $router->version ?: 'unknown' }}</span>
                        @if($router->site || $router->location)<span>{{ $router->site ?: $router->location }}</span>@endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($router->backupsEnabled())
                    <button type="button" @click="triggerBackup()" :disabled="backupRunning" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="backupRunning ? 'Queuing backup…' : 'Backup now'"></span>
                    </button>
                @else
                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">Backups disabled</span>
                @endif
                @if($latestComparableBackups->count() >= 2)
                    <a href="{{ route('backups.compare', ['router_id' => $router->id]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Compare latest</a>
                @endif
                <a href="{{ route('routers.edit', $router) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit router</a>
                <button type="button" @click="deleteModal.show = true" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
            </div>
        </div>

        <dl class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4 border-t border-slate-100 pt-5 sm:grid-cols-3 xl:grid-cols-6">
            <div>
                <dt class="text-xs font-medium text-slate-400">CPU</dt>
                <dd class="mt-1 flex items-center gap-2"><strong class="text-sm text-slate-800">{{ $router->cpu_usage ?? 0 }}%</strong><span class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-blue-500" style="width: {{ min(100, $router->cpu_usage ?? 0) }}%"></span></span></dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-400">Memory</dt>
                <dd class="mt-1 flex items-center gap-2"><strong class="text-sm text-slate-800">{{ $router->memory_usage ?? 0 }}%</strong><span class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-emerald-500" style="width: {{ min(100, $router->memory_usage ?? 0) }}%"></span></span></dd>
            </div>
            <div><dt class="text-xs font-medium text-slate-400">Active sessions</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ number_format($router->active_sessions_count ?? 0) }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Uptime</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $router->uptime ?: '—' }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Last contact</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $router->last_connected_at?->diffForHumans() ?? 'Never' }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Connection</dt><dd class="mt-1 text-sm font-semibold {{ $router->last_error ? 'text-red-700' : 'text-emerald-700' }}">{{ $router->last_error ? 'Attention needed' : 'Healthy' }}</dd></div>
        </dl>
    </header>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Latest backup</p>
            <p class="mt-2 text-lg font-bold text-slate-900">{{ $lastBackup?->created_at?->diffForHumans() ?? 'No backups yet' }}</p>
            @if($lastBackup)<div class="mt-2"><x-backup-status :status="$lastBackup->status" /></div>@endif
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Backup formats</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($router->backup_rsc_enabled)<span class="rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">.rsc export</span>@endif
                @if($router->backup_binary_enabled)<span class="rounded-md bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700">.backup binary</span>@endif
                @if(! $router->backupsEnabled())<span class="text-sm font-semibold text-slate-400">Disabled</span>@endif
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Configuration changes</p>
            <p class="mt-2 text-lg font-bold text-slate-900">{{ number_format($backupStats['changed']) }}</p>
            <p class="mt-1 text-xs text-slate-500">across {{ number_format($backupStats['successful']) }} usable snapshots</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Backup health</p>
            <p class="mt-2 text-lg font-bold {{ $backupStats['failed'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $backupStats['failed'] > 0 ? $backupStats['failed'].' failed' : 'No failures' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ number_format($backupStats['total']) }} total attempts</p>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Configuration history</h2>
                <p class="mt-0.5 text-sm text-slate-500">Every stored RouterOS snapshot, its artifacts, and the change from its predecessor.</p>
            </div>
            <a href="{{ route('backups.compare', ['router_id' => $router->id]) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Open comparison workspace</a>
        </div>

        @if($backups->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Snapshot', 'Status', 'Version & source', 'Artifacts', 'Change', 'Created', ''] as $heading)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($backups as $backup)
                            <tr class="group hover:bg-slate-50/70">
                                <td class="px-5 py-4"><a href="{{ route('backups.show', $backup) }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">#{{ $backup->id }}</a></td>
                                <td class="px-5 py-4"><x-backup-status :status="$backup->status" /></td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-800">RouterOS {{ $backup->routeros_version ?: 'unknown' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $backup->schedule?->name ?? 'Manual' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($backup->artifacts->where('status', 'success') as $artifact)
                                            <span class="rounded-md bg-slate-100 px-2 py-1 font-mono text-[11px] font-semibold text-slate-600">.{{ $artifact->type === 'binary' ? 'backup' : 'rsc' }}</span>
                                        @empty
                                            @if($backup->path)<span class="rounded-md bg-slate-100 px-2 py-1 font-mono text-[11px] font-semibold text-slate-600">.rsc</span>@else<span class="text-xs text-slate-300">—</span>@endif
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($backup->diff)
                                        <a href="{{ route('backups.show', $backup) }}" class="inline-flex gap-2 font-mono text-xs font-bold"><span class="text-emerald-700">+{{ $backup->diff->added_lines }}</span><span class="text-red-700">−{{ $backup->diff->removed_lines }}</span></a>
                                    @elseif($backup->changed === false)
                                        <span class="text-xs text-slate-500">No changes</span>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="text-sm text-slate-700">{{ $backup->created_at?->format('M d, H:i') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $backup->created_at?->diffForHumans() }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($backup->previous_router_backup_id && $backup->path)
                                            <a href="{{ route('backups.compare', ['router_id' => $router->id, 'old_backup_id' => $backup->previous_router_backup_id, 'new_backup_id' => $backup->id]) }}" class="text-xs font-semibold text-slate-600 hover:text-blue-700">Compare</a>
                                        @endif
                                        <a href="{{ route('backups.show', $backup) }}" class="text-xs font-semibold text-blue-700">Details</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-5 py-4">{{ $backups->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <h3 class="text-sm font-semibold text-slate-900">No configuration history yet</h3>
                <p class="mt-1 text-sm text-slate-500">Run the first backup to establish this router’s baseline.</p>
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <button type="button" @click="detailsOpen = !detailsOpen" class="flex w-full items-center justify-between px-5 py-4 text-left">
            <div><h2 class="text-sm font-bold text-slate-900">Router and connection details</h2><p class="mt-0.5 text-xs text-slate-500">Hardware, API, SSH, credential, and monitoring information.</p></div>
            <svg class="h-5 w-5 text-slate-400 transition-transform" :class="detailsOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="detailsOpen" x-transition class="grid gap-x-10 gap-y-3 border-t border-slate-200 px-5 py-4 md:grid-cols-2">
            @foreach([
                'Vendor' => $router->vendor,
                'Model' => $router->model ?: '—',
                'Location' => $router->location ?: '—',
                'Site' => $router->site ?: '—',
                'RouterOS API' => $router->enable_api ? 'Enabled on port '.($router->api_port ?: 8728) : 'Disabled',
                'SSH' => $router->enable_ssh ? 'Enabled on port '.($router->ssh_port ?: 22) : 'Disabled',
                'Credential' => $router->passwordManagerCredential?->name ?: ($router->api_username ?: '—'),
                'Monitoring' => $router->enable_monitoring ? 'Enabled' : 'Disabled',
            ] as $label => $value)
                <div class="flex justify-between gap-5 border-b border-slate-100 py-2 text-sm"><span class="text-slate-400">{{ $label }}</span><span class="text-right font-semibold text-slate-700">{{ $value }}</span></div>
            @endforeach
            @if($router->last_error)<div class="md:col-span-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-800">{{ $router->last_error }}</div>@endif
        </div>
    </section>

    <div x-show="deleteModal.show" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-950/50" @click="deleteModal.show = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-slate-900">Delete {{ $router->name }}?</h2>
            <p class="mt-2 text-sm text-slate-500">This removes the router and its backup history. This action cannot be undone.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="deleteModal.show = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</button>
                <button type="button" @click="deleteRouter()" :disabled="deleteModal.deleting" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50" x-text="deleteModal.deleting ? 'Deleting…' : 'Delete router'"></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function routerShowWorkspace() {
    return {
        backupRunning: false,
        detailsOpen: false,
        deleteModal: { show: false, deleting: false },
        async triggerBackup() {
            this.backupRunning = true;
            try {
                const response = await fetch(@js(route('routers.backup', $router)), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': @js(csrf_token()), Accept: 'application/json' }
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Unable to queue the backup.');
                setTimeout(() => window.location.reload(), 1800);
            } catch (error) {
                alert(error.message);
                this.backupRunning = false;
            }
        },
        async deleteRouter() {
            this.deleteModal.deleting = true;
            try {
                const response = await fetch(@js(route('routers.destroy', $router)), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': @js(csrf_token()), Accept: 'application/json' }
                });
                if (!response.ok) throw new Error('Unable to delete the router.');
                window.location.href = @js(route('routers.index'));
            } catch (error) {
                alert(error.message);
                this.deleteModal.deleting = false;
            }
        }
    };
}
</script>
@endpush
