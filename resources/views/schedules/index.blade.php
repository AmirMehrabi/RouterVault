@extends('layouts.admin')

@section('title', 'Backup Schedules')

@section('content')
<div class="space-y-6" x-data="{ q: '', status: '', cadence: '' }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Backup Schedules</h1>
            <p class="mt-1 text-sm text-gray-500">Interval-based RouterOS export schedules.</p>
        </div>
        <a href="{{ route('schedules.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">New Schedule</a>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        @foreach(['Active' => $stats['active'], 'Paused' => $stats['paused'], 'Routers Covered' => $stats['routers'], 'Due Soon' => $stats['dueSoon']] as $label => $value)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold">{{ $value }}</p></div>
        @endforeach
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <input x-model="q" class="rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Filter by name">
            <select x-model="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Any status</option><option value="enabled">Enabled</option><option value="paused">Paused</option></select>
            <select x-model="cadence" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Any cadence</option><option value="minutes">Minutes</option><option value="hours">Hours</option><option value="days">Days</option><option value="weeks">Weeks</option></select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Cadence</th><th class="px-4 py-3 text-left">Routers</th><th class="px-4 py-3 text-left">Next Run</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($schedules as $schedule)
                        <tr x-show="(!q || @js(strtolower($schedule->name)).includes(q.toLowerCase())) && (!status || status === '{{ $schedule->is_enabled ? 'enabled' : 'paused' }}') && (!cadence || cadence === '{{ $schedule->interval_unit }}')">
                            <td class="px-4 py-3"><a class="font-medium text-blue-600" href="{{ route('schedules.show', $schedule) }}">{{ $schedule->name }}</a><div class="text-xs text-gray-500">{{ $schedule->is_enabled ? 'Enabled' : 'Paused' }}</div></td>
                            <td class="px-4 py-3">Every {{ $schedule->interval_value }} {{ $schedule->interval_unit }}</td>
                            <td class="px-4 py-3">{{ $schedule->routers_count }}</td>
                            <td class="px-4 py-3">{{ $schedule->next_run_at?->format('Y-m-d H:i') ?? 'Not set' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.table-action :href="route('schedules.show', $schedule)" icon="eye" tooltip="View schedule" />
                                    <x-ui.table-action :href="route('schedules.edit', $schedule)" icon="edit" tooltip="Edit schedule" />
                                    <x-ui.table-action :href="route('schedules.destroy', $schedule)" icon="trash" tooltip="Delete schedule" method="DELETE" confirm="Are you sure you want to delete this schedule?" variant="danger" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
