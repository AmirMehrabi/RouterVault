@extends('layouts.admin')

@section('title', $schedule->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">{{ $schedule->name }}</h1><p class="text-sm text-gray-500">Every {{ $schedule->interval_value }} {{ $schedule->interval_unit }}</p></div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('schedules.run', $schedule) }}">@csrf<button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Run Now</button></form>
            <form method="POST" action="{{ route('schedules.toggle', $schedule) }}">@csrf<button class="rounded-lg border border-gray-300 px-4 py-2 text-sm">{{ $schedule->is_enabled ? 'Pause' : 'Resume' }}</button></form>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold">Selected Routers</h2>
            <div class="space-y-2">@foreach($schedule->routers as $router)<div class="rounded-lg bg-gray-50 px-3 py-2 text-sm">{{ $router->name }} <span class="text-gray-500">{{ $router->ip_address }}</span></div>@endforeach</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold">Recent Runs</h2>
            <div class="space-y-2">@forelse($schedule->runs as $run)<div class="rounded-lg bg-gray-50 px-3 py-2 text-sm">{{ $run->status }} · {{ $run->successful_backups }}/{{ $run->total_routers }} · {{ $run->started_at?->diffForHumans() }}</div>@empty<div class="text-sm text-gray-500">No runs yet.</div>@endforelse</div>
        </div>
    </div>
</div>
@endsection
