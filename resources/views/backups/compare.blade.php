@extends('layouts.admin')

@section('title', 'Compare Backups')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Compare Backups</h1>
    <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <select name="old_backup_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Older backup</option>
                @foreach($backups as $backup)<option value="{{ $backup->id }}" @selected(request('old_backup_id') == $backup->id)>#{{ $backup->id }} {{ $backup->router?->name }} {{ $backup->created_at?->format('Y-m-d H:i') }}</option>@endforeach
            </select>
            <select name="new_backup_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Newer backup</option>
                @foreach($backups as $backup)<option value="{{ $backup->id }}" @selected(request('new_backup_id') == $backup->id)>#{{ $backup->id }} {{ $backup->router?->name }} {{ $backup->created_at?->format('Y-m-d H:i') }}</option>@endforeach
            </select>
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Compare</button>
        </div>
    </form>
    @if($diff)
        @include('backups._diff', ['hunks' => $diff['hunks']])
    @endif
</div>
@endsection
