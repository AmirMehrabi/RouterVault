@extends('layouts.admin')

@section('title', 'Diff Alert #'.$alert->id)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">{{ $alert->summary }}</h1><p class="text-sm text-gray-500">{{ $alert->severity }} · {{ $alert->status }} · {{ $alert->router?->name }}</p></div>
        <div class="flex gap-2">
            @foreach(['acknowledged' => 'Acknowledge', 'ignored' => 'Ignore', 'unread' => 'Mark Unread'] as $status => $label)
                <form method="POST" action="{{ route('diff-alerts.status', $alert) }}">@csrf<input type="hidden" name="status" value="{{ $status }}"><button class="rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $label }}</button></form>
            @endforeach
        </div>
    </div>
    @include('backups._diff', ['hunks' => $alert->diff?->hunks ?? []])
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="mb-3 font-semibold">Metadata</h2>
            <div class="space-y-2 text-sm"><div>Sections: {{ implode(', ', $alert->sections ?? []) }}</div><div>Lines: +{{ $alert->added_lines }} / -{{ $alert->removed_lines }}</div><div><a class="text-blue-600" href="{{ route('backups.show', $alert->backup) }}">View backup</a></div><div><a class="text-blue-600" href="{{ route('backups.download', $alert->backup) }}">Download backup</a></div></div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-3 font-semibold">Notes</h2>
            <div class="mb-4 space-y-2">@foreach($alert->notes as $note)<div class="rounded-lg bg-gray-50 px-3 py-2 text-sm">{{ $note->body }}<div class="text-xs text-gray-500">{{ $note->created_at?->diffForHumans() }}</div></div>@endforeach</div>
            <form method="POST" action="{{ route('diff-alerts.notes.store', $alert) }}">@csrf<textarea name="body" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea><button class="mt-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Add Note</button></form>
        </div>
    </div>
</div>
@endsection
