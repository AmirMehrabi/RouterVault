@extends('layouts.admin')

@section('title', 'Compare Backups')

@section('content')
<div class="space-y-6" x-data="backupCompare()" x-init="init()">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Compare Backups</h1>
        <p class="mt-1 text-sm text-gray-500">Choose a router first, then select two successful backups from its history.</p>
    </div>
    <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <select name="router_id" x-model="routerId" @change="loadBackups()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Select router</option>
                @foreach($routers as $router)<option value="{{ $router->id }}">{{ $router->name }}</option>@endforeach
            </select>
            <select name="old_backup_id" x-model="oldBackupId" :disabled="loading || !routerId" class="rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                <option value="">Older backup</option>
                <template x-for="backup in backups" :key="'old-'+backup.id"><option :value="backup.id" x-text="label(backup)"></option></template>
            </select>
            <select name="new_backup_id" x-model="newBackupId" :disabled="loading || !routerId" class="rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100">
                <option value="">Newer backup</option>
                <template x-for="backup in backups" :key="'new-'+backup.id"><option :value="backup.id" x-text="label(backup)"></option></template>
            </select>
            <button :disabled="!canCompare" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-300">Compare</button>
        </div>
        <p x-show="oldBackupId && oldBackupId === newBackupId" class="mt-3 text-sm text-red-600">Choose two different backups.</p>
    </form>
    @if($diff)
        @include('backups._diff', ['hunks' => $diff['hunks']])
    @endif
</div>
@endsection

@push('scripts')
<script>
function backupCompare() {
    return {
        routerId: @js((string) request('router_id', '')),
        oldBackupId: @js((string) request('old_backup_id', '')),
        newBackupId: @js((string) request('new_backup_id', '')),
        backups: [],
        loading: false,
        get canCompare() { return this.routerId && this.oldBackupId && this.newBackupId && this.oldBackupId !== this.newBackupId; },
        init() { if (this.routerId) this.loadBackups(true); },
        async loadBackups(preserve = false) {
            if (!preserve) { this.oldBackupId = ''; this.newBackupId = ''; }
            this.backups = [];
            if (!this.routerId) return;
            this.loading = true;
            const response = await fetch(`/backups/router/${this.routerId}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            this.backups = data.backups ?? [];
            this.loading = false;
        },
        label(backup) { return `#${backup.id} · ${new Date(backup.created_at).toLocaleString()}`; },
    };
}
</script>
@endpush
