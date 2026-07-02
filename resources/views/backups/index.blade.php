@extends('layouts.admin')

@section('title', 'Router Backups')

@section('content')
<div class="space-y-6" x-data="{ q: '', status: '', changed: '' }">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">Router Backups</h1><p class="mt-1 text-sm text-gray-500">Private RouterOS export history and diffs.</p></div>
        <a href="{{ route('backups.compare') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium">Compare</a>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        @foreach(['Total' => $stats['total'], 'Successful' => $stats['successful'], 'Changed' => $stats['changed'], 'Failed' => $stats['failed']] as $label => $value)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold">{{ $value }}</p></div>
        @endforeach
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <input x-model="q" class="rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Filter by router or schedule">
            <select x-model="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Any status</option><option>success</option><option>failed</option><option>running</option></select>
            <select x-model="changed" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Changed or unchanged</option><option value="1">Changed</option><option value="0">Unchanged</option></select>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Router</th><th class="px-4 py-3 text-left">Schedule</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Changed</th><th class="px-4 py-3 text-left">Created</th><th class="px-4 py-3 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($backups as $backup)
                    @php($search = strtolower(($backup->router?->name ?? '').' '.($backup->schedule?->name ?? '')))
                    <tr x-show="(!q || @js($search).includes(q.toLowerCase())) && (!status || status === '{{ $backup->status }}') && (!changed || changed === '{{ (int) $backup->changed }}')">
                        <td class="px-4 py-3"><a class="font-medium text-blue-600" href="{{ route('backups.show', $backup) }}">{{ $backup->router?->name }}</a></td>
                        <td class="px-4 py-3">{{ $backup->schedule?->name ?? 'Manual' }}</td>
                        <td class="px-4 py-3"><x-backup-status :status="$backup->status" /></td>
                        <td class="px-4 py-3">{{ $backup->changed ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3">{{ $backup->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('backups.show', $backup) }}" class="font-semibold text-blue-700 hover:text-blue-900">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $backups->links() }}</div>
    </div>
</div>
@endsection
