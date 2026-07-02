@extends('layouts.admin')

@section('title', 'Backup #'.$backup->id)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">Backup #{{ $backup->id }}</h1><p class="text-sm text-gray-500">{{ $backup->router?->name }} · {{ $backup->created_at?->format('Y-m-d H:i') }}</p></div>
        @if($backup->status === 'success')
            <a href="{{ route('backups.download', $backup) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Download</a>
        @endif
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Status</p><p class="mt-2"><x-backup-status :status="$backup->status" /></p></div>
        @foreach(['Changed' => $backup->changed === null ? '—' : ($backup->changed ? 'Yes' : 'No'), 'Size' => $backup->size_bytes ? number_format((int) $backup->size_bytes).' bytes' : '—', 'Checksum' => $backup->checksum ? substr($backup->checksum, 0, 12) : '—'] as $label => $value)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 break-all text-lg font-semibold">{{ $value }}</p></div>
        @endforeach
    </div>
    @if($backup->status === 'failed' && $backup->error_message)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm">
            <h2 class="mb-2 text-lg font-semibold text-red-900">Backup Error</h2>
            <pre class="whitespace-pre-wrap break-words text-sm text-red-800">{{ $backup->error_message }}</pre>
        </div>
    @endif
    <div>
        <h2 class="mb-3 text-lg font-semibold text-gray-900">Diff</h2>
        @include('backups._diff', ['hunks' => $backup->diff?->hunks ?? []])
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold text-gray-900">Export Preview</h2>
        <pre class="max-h-96 overflow-auto rounded-lg bg-gray-950 p-4 text-xs text-gray-100">{{ $preview }}</pre>
    </div>
</div>
@endsection
