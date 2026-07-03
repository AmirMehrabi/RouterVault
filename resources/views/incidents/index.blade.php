@extends('layouts.admin')

@section('title', 'Incidents')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Dashboard', 'href' => route('dashboard')], ['label' => 'Incidents', 'current' => true]]" />
@endpush

@section('content')
<div class="space-y-5 pb-10" x-data="{ q: '', status: 'open' }">
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-slate-950">Incidents</h1>
        <p class="mt-1 text-sm text-slate-500">Configuration and backup problems that require an operator response.</p>
    </header>

    <section class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Open', $stats['open'], 'text-red-700'],
            ['Critical', $stats['critical'], 'text-red-700'],
            ['Unassigned', $stats['unassigned'], 'text-amber-700'],
            ['Resolved today', $stats['resolvedToday'], 'text-emerald-700'],
        ] as [$label, $value, $tone])
            <div class="border-b border-slate-200 p-5 last:border-0 sm:border-r xl:border-b-0">
                <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row">
            <x-ui.input.text name="incident_search" placeholder="Search router, summary, impact…" x-model="q" class="sm:w-80" />
            <select x-model="status" class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="open">Open incidents</option>
                <option value="all">All incidents</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr>@foreach(['Severity', 'Router', 'Summary and impact', 'Assignee', 'Status', 'Age', ''] as $heading)<th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($incidents as $incident)
                        @php($search = strtolower(($incident->router?->name ?? '').' '.$incident->summary.' '.$incident->impact))
                        <tr x-show="(!q || @js($search).includes(q.toLowerCase())) && (status === 'all' || (status === 'open' && '{{ $incident->status }}' !== 'resolved') || status === '{{ $incident->status }}')" class="hover:bg-slate-50">
                            <td class="px-4 py-4"><span class="inline-flex items-center gap-2 font-semibold capitalize {{ $incident->severity === 'high' ? 'text-red-700' : ($incident->severity === 'medium' ? 'text-amber-700' : 'text-blue-700') }}"><span class="h-2 w-2 rounded-full bg-current"></span>{{ $incident->severity }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-800">{{ $incident->router?->name ?? 'Unknown' }}</td>
                            <td class="max-w-xl px-4 py-4"><a href="{{ route('incidents.show', $incident) }}" class="font-semibold text-blue-700">{{ $incident->summary }}</a><p class="mt-1 truncate text-xs text-slate-500">{{ $incident->impact }}</p></td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $incident->assignee?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-4"><x-ui.badge :status="$incident->status">{{ str($incident->status)->replace('_', ' ')->title() }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">{{ $incident->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-4 text-right"><x-ui.table-action :href="route('incidents.show', $incident)" icon="eye" tooltip="View incident" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-4 py-4">{{ $incidents->links() }}</div>
    </section>
</div>
@endsection
