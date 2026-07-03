@extends('layouts.admin')

@section('title', 'Backup #'.$backup->id)

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Backups', 'href' => route('backups.index')],
        ['label' => 'Backup #'.$backup->id, 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-5 pb-10" x-data="{ previewOpen: false, copied: false }">
    <header class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950">Configuration snapshot #{{ $backup->id }}</h1>
                    <x-backup-status :status="$backup->status" />
                    @if($backup->changed)
                        <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800">Configuration changed</span>
                    @endif
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                    <a href="{{ route('routers.show', $backup->router_id) }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ $backup->router?->name }}</a>
                    <span>RouterOS {{ $backup->routeros_version ?: 'unknown' }}</span>
                    <span>{{ $backup->created_at?->format('M d, Y · H:i:s') }}</span>
                    <span>{{ $backup->schedule?->name ?? 'Manual backup' }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($previousBackup?->path)
                    <a href="{{ route('backups.compare', ['router_id' => $backup->router_id, 'old_backup_id' => $previousBackup->id, 'new_backup_id' => $backup->id]) }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Compare with previous</a>
                @endif
                <a href="{{ route('backups.compare', ['router_id' => $backup->router_id, 'new_backup_id' => $backup->id]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Choose comparison</a>
                @if(in_array($backup->status, ['failed', 'partial_success'], true))
                    <form method="POST" action="{{ route('backups.retry', $backup) }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Retry</button>
                    </form>
                @endif
            </div>
        </div>

        <dl class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4 border-t border-slate-100 pt-5 sm:grid-cols-3 xl:grid-cols-6">
            <div><dt class="text-xs font-medium text-slate-400">Created</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $backup->created_at?->diffForHumans() }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Duration</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $backup->started_at && $backup->finished_at ? $backup->started_at->diffInSeconds($backup->finished_at).'s' : '—' }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Export size</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $backup->size_bytes ? number_format($backup->size_bytes / 1024, 1).' KB' : '—' }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Added</dt><dd class="mt-1 font-mono text-sm font-bold text-emerald-700">+{{ $backup->diff?->added_lines ?? 0 }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Removed</dt><dd class="mt-1 font-mono text-sm font-bold text-red-700">−{{ $backup->diff?->removed_lines ?? 0 }}</dd></div>
            <div><dt class="text-xs font-medium text-slate-400">Checksum</dt><dd class="mt-1 truncate font-mono text-xs font-semibold text-slate-700" title="{{ $backup->checksum }}">{{ $backup->checksum ? substr($backup->checksum, 0, 16).'…' : '—' }}</dd></div>
        </dl>
    </header>

    @if(in_array($backup->status, ['failed', 'partial_success'], true) && $backup->error_message)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-sm font-semibold text-red-900">Backup completed with an error</p>
            <pre class="mt-1 whitespace-pre-wrap break-words text-xs text-red-800">{{ $backup->error_message }}</pre>
        </div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_19rem]">
        <main class="min-w-0 space-y-5">
            <section>
                <div class="mb-3 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Changes from previous snapshot</h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            @if($previousBackup)
                                Snapshot #{{ $previousBackup->id }} → #{{ $backup->id }}
                            @else
                                This is the first available text export for the router.
                            @endif
                        </p>
                    </div>
                </div>
                @include('backups._diff', [
                    'hunks' => $displayDiff,
                    'diff' => [
                        'added' => $backup->diff?->added_lines ?? 0,
                        'removed' => $backup->diff?->removed_lines ?? 0,
                    ],
                ])
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <button type="button" @click="previewOpen = !previewOpen" class="flex w-full items-center justify-between px-5 py-4 text-left">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Raw RouterOS export</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Inspect or copy the stored `.rsc` content.</p>
                    </div>
                    <svg class="h-5 w-5 text-slate-400 transition-transform" :class="previewOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="previewOpen" x-transition class="border-t border-slate-200">
                    <div class="flex items-center justify-end border-b border-slate-200 bg-slate-50 px-4 py-2">
                        <button type="button" @click="navigator.clipboard.writeText($refs.preview.textContent); copied = true; setTimeout(() => copied = false, 1500)" class="text-xs font-semibold text-blue-700"><span x-text="copied ? 'Copied' : 'Copy export'"></span></button>
                    </div>
                    <pre x-ref="preview" class="max-h-[34rem] overflow-auto bg-slate-950 p-5 font-mono text-xs leading-5 text-slate-100">{{ $preview }}</pre>
                </div>
            </section>
        </main>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">Stored artifacts</h2>
                <div class="mt-3 space-y-3">
                    @forelse($backup->artifacts as $artifact)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $artifact->type === 'binary' ? 'Binary backup' : 'Text export' }}</p>
                                    <p class="mt-0.5 font-mono text-xs text-slate-400">.{{ $artifact->type === 'binary' ? 'backup' : 'rsc' }}</p>
                                </div>
                                <x-backup-status :status="$artifact->status" />
                            </div>
                            <dl class="mt-3 space-y-1.5 text-xs">
                                <div class="flex justify-between gap-3"><dt class="text-slate-400">Size</dt><dd class="font-medium text-slate-700">{{ $artifact->size_bytes ? number_format($artifact->size_bytes / 1024, 1).' KB' : '—' }}</dd></div>
                                <div class="flex justify-between gap-3"><dt class="text-slate-400">SHA-256</dt><dd class="truncate font-mono text-slate-700" title="{{ $artifact->checksum }}">{{ $artifact->checksum ? substr($artifact->checksum, 0, 10).'…' : '—' }}</dd></div>
                            </dl>
                            @if($artifact->cleanup_error)<p class="mt-2 text-xs text-amber-700">Router cleanup warning: {{ $artifact->cleanup_error }}</p>@endif
                            @if($artifact->status === 'success')
                                <a href="{{ route('backups.artifacts.download', [$backup, $artifact]) }}" class="mt-3 block rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-blue-700 hover:bg-blue-50">Download</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No artifact metadata is available for this legacy backup.</p>
                        @if($backup->status === 'success' && $backup->path)
                            <a href="{{ route('backups.download', $backup) }}" class="mt-3 block rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-blue-700">Download export</a>
                        @endif
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">Snapshot navigation</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @if($previousHistoryBackup)
                        <a href="{{ route('backups.show', $previousHistoryBackup) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">← #{{ $previousHistoryBackup->id }}</a>
                    @else
                        <span class="rounded-lg border border-slate-100 px-3 py-2 text-xs text-slate-300">No previous</span>
                    @endif
                    @if($nextHistoryBackup)
                        <a href="{{ route('backups.show', $nextHistoryBackup) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-right text-xs font-semibold text-slate-700 hover:bg-slate-50">#{{ $nextHistoryBackup->id }} →</a>
                    @else
                        <span class="rounded-lg border border-slate-100 px-3 py-2 text-right text-xs text-slate-300">Latest</span>
                    @endif
                </div>
                <a href="{{ route('routers.show', $backup->router_id) }}" class="mt-3 block text-center text-xs font-semibold text-blue-700">View complete router history</a>
            </section>
        </aside>
    </div>
</div>
@endsection
