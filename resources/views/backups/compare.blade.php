@extends('layouts.admin')

@section('title', 'Compare Configuration Snapshots')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Backups', 'href' => route('backups.index')],
        ['label' => 'Compare', 'current' => true],
    ]" />
@endpush

@section('content')
<div
    class="space-y-5 pb-10"
    x-data="backupCompareWorkspace()"
    x-init="init()"
>
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950">Compare configuration snapshots</h1>
            <p class="mt-1 text-sm text-slate-500">Review exactly what changed between two RouterOS text exports.</p>
        </div>
        @if($baseBackup && $comparisonBackup)
            <a href="{{ route('routers.show', $comparisonBackup->router_id) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">View router history</a>
        @endif
    </header>

    <form method="GET" class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="max-w-sm">
                <label for="compare-router" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Router</label>
                <select id="compare-router" name="router_id" x-model="routerId" @change="loadBackups()" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select a router</option>
                    @foreach($routers as $router)
                        <option value="{{ $router->id }}">{{ $router->name }}{{ $router->version ? ' · RouterOS '.$router->version : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid items-stretch gap-3 lg:grid-cols-[1fr_auto_1fr]">
            @foreach([
                ['key' => 'base', 'label' => 'Base', 'model' => 'oldBackupId', 'placeholder' => 'Choose the older snapshot'],
                ['key' => 'comparison', 'label' => 'Comparison', 'model' => 'newBackupId', 'placeholder' => 'Choose the newer snapshot'],
            ] as $selector)
                <div class="{{ $selector['key'] === 'comparison' ? 'lg:col-start-3' : '' }} rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-sm font-bold text-slate-900">{{ $selector['label'] }}</label>
                        <span class="text-xs text-slate-400" x-text="{{ $selector['key'] }}Backup ? relativeTime({{ $selector['key'] }}Backup.created_at) : ''"></span>
                    </div>
                    <select name="{{ $selector['key'] === 'base' ? 'old_backup_id' : 'new_backup_id' }}" x-model="{{ $selector['model'] }}" :disabled="loading || !routerId" class="mt-3 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                        <option value="">{{ $selector['placeholder'] }}</option>
                        <template x-for="backup in backups" :key="'{{ $selector['key'] }}-'+backup.id">
                            <option :value="String(backup.id)" x-text="optionLabel(backup)"></option>
                        </template>
                    </select>
                    <div class="mt-4 min-h-20 border-t border-slate-100 pt-4">
                        <template x-if="{{ $selector['key'] }}Backup">
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div><span class="block text-slate-400">Snapshot</span><strong class="mt-0.5 block text-sm text-blue-700" x-text="'#' + {{ $selector['key'] }}Backup.id"></strong></div>
                                <div><span class="block text-slate-400">RouterOS</span><strong class="mt-0.5 block text-sm text-slate-800" x-text="{{ $selector['key'] }}Backup.routeros_version || 'Unknown'"></strong></div>
                                <div><span class="block text-slate-400">Created</span><span class="mt-0.5 block text-slate-700" x-text="formatDate({{ $selector['key'] }}Backup.created_at)"></span></div>
                                <div><span class="block text-slate-400">Source</span><span class="mt-0.5 block text-slate-700" x-text="{{ $selector['key'] }}Backup.source"></span></div>
                            </div>
                        </template>
                        <p x-show="!{{ $selector['key'] }}Backup" class="text-sm text-slate-400">Select a snapshot to view its version and source.</p>
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-center lg:col-start-2 lg:row-start-1">
                <button type="button" @click="swap()" :disabled="!oldBackupId || !newBackupId" class="group flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:border-blue-200 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-40 lg:flex-col lg:px-3">
                    <svg class="h-5 w-5 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3 3m-3-3 3-3"/></svg>
                    <span class="text-xs">Swap</span>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <p x-show="oldBackupId && oldBackupId === newBackupId" class="mr-auto text-sm text-red-600">Choose two different snapshots.</p>
            <button :disabled="!canCompare" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                Compare snapshots
            </button>
        </div>
    </form>

    @if($diff && $baseBackup && $comparisonBackup)
        <section class="space-y-4">
            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">
                        Backup #{{ $comparisonBackup->id }}
                        <span class="font-normal text-slate-400">compared with</span>
                        #{{ $baseBackup->id }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        RouterOS {{ $baseBackup->routeros_version ?: 'unknown' }}
                        <span class="mx-1">→</span>
                        {{ $comparisonBackup->routeros_version ?: 'unknown' }}
                        · {{ $comparisonBackup->router?->name }}
                    </p>
                </div>
                <div class="flex items-center gap-4 font-mono text-sm font-bold">
                    <span class="text-emerald-700">+{{ $diff['added'] }}</span>
                    <span class="text-red-700">−{{ $diff['removed'] }}</span>
                </div>
            </div>

            @include('backups._diff', ['hunks' => $diff['hunks'], 'diff' => $diff])
        </section>
    @elseif($routerId)
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <h2 class="text-sm font-semibold text-slate-900">Select two snapshots to compare</h2>
            <p class="mt-1 text-sm text-slate-500">The newest two exports are selected automatically when available.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function backupCompareWorkspace() {
    return {
        routerId: @js((string) $routerId),
        oldBackupId: @js((string) $baseBackupId),
        newBackupId: @js((string) $comparisonBackupId),
        backups: [],
        loading: false,
        get baseBackup() { return this.backups.find(backup => String(backup.id) === String(this.oldBackupId)); },
        get comparisonBackup() { return this.backups.find(backup => String(backup.id) === String(this.newBackupId)); },
        get canCompare() { return this.routerId && this.oldBackupId && this.newBackupId && this.oldBackupId !== this.newBackupId; },
        init() { if (this.routerId) this.loadBackups(true); },
        async loadBackups(preserve = false) {
            if (!preserve) {
                this.oldBackupId = '';
                this.newBackupId = '';
            }
            this.backups = [];
            if (!this.routerId) return;
            this.loading = true;
            try {
                const response = await fetch(`{{ url('/backups/router') }}/${this.routerId}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Unable to load backup history.');
                const data = await response.json();
                this.backups = data.backups ?? [];
                if (!preserve && this.backups.length >= 2) {
                    this.newBackupId = String(this.backups[0].id);
                    this.oldBackupId = String(this.backups[1].id);
                }
            } catch (error) {
                alert(error.message);
            } finally {
                this.loading = false;
            }
        },
        swap() {
            [this.oldBackupId, this.newBackupId] = [this.newBackupId, this.oldBackupId];
        },
        optionLabel(backup) {
            return `#${backup.id} · ${backup.routeros_version || 'Unknown version'} · ${this.formatDate(backup.created_at)}`;
        },
        formatDate(value) {
            return value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—';
        },
        relativeTime(value) {
            if (!value) return '';
            const seconds = Math.round((new Date(value).getTime() - Date.now()) / 1000);
            const divisions = [[60, 'second'], [60, 'minute'], [24, 'hour'], [7, 'day'], [4.345, 'week'], [12, 'month'], [Infinity, 'year']];
            let duration = seconds;
            for (const [amount, unit] of divisions) {
                if (Math.abs(duration) < amount) return new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' }).format(Math.round(duration), unit);
                duration /= amount;
            }
        }
    };
}
</script>
@endpush
