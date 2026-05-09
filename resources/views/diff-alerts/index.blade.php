@extends('layouts.admin')

@section('title', 'Diff Alerts')

@section('content')
<div class="space-y-6" x-data="{ tab: 'all', q: '' }">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">Diff Alerts</h1><p class="text-sm text-gray-500">Router configuration changes that need review.</p></div>
        <a href="{{ route('diff-alerts.settings') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium">Settings</a>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        @foreach(['Unread' => $stats['unread'], 'High Severity' => $stats['high'], 'Acknowledged Today' => $stats['acknowledgedToday'], 'Ignored' => $stats['ignored']] as $label => $value)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold">{{ $value }}</p></div>
        @endforeach
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach(['all' => 'All', 'unread' => 'Unread', 'high' => 'High', 'acknowledged' => 'Acknowledged', 'ignored' => 'Ignored'] as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="rounded-lg px-3 py-2 text-sm">{{ $label }}</button>
            @endforeach
            <input x-model="q" class="ml-auto rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Filter alerts">
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Router</th><th class="px-4 py-3 text-left">Summary</th><th class="px-4 py-3 text-left">Severity</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Lines</th><th></th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($alerts as $alert)
                    @php($search = strtolower(($alert->router?->name ?? '').' '.$alert->summary.' '.implode(' ', $alert->sections ?? [])))
                    <tr x-show="(!q || @js($search).includes(q.toLowerCase())) && (tab === 'all' || tab === '{{ $alert->status }}' || tab === '{{ $alert->severity }}')">
                        <td class="px-4 py-3">{{ $alert->router?->name }}</td>
                        <td class="px-4 py-3"><a class="font-medium text-blue-600" href="{{ route('diff-alerts.show', $alert) }}">{{ $alert->summary }}</a><div class="text-xs text-gray-500">{{ implode(', ', $alert->sections ?? []) }}</div></td>
                        <td class="px-4 py-3">{{ $alert->severity }}</td>
                        <td class="px-4 py-3">{{ $alert->status }}</td>
                        <td class="px-4 py-3">+{{ $alert->added_lines }} / -{{ $alert->removed_lines }}</td>
                        <td class="px-4 py-3 text-right"><form method="POST" action="{{ route('diff-alerts.status', $alert) }}">@csrf<input type="hidden" name="status" value="acknowledged"><button class="text-blue-600">Acknowledge</button></form></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $alerts->links() }}</div>
    </div>
</div>
@endsection
