@extends('layouts.admin')

@section('title', 'Configuration Compliance')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Dashboard', 'href' => route('dashboard')], ['label' => 'Compliance', 'current' => true]]" />
@endpush

@section('content')
<div class="space-y-5 pb-10">
    <header><h1 class="text-2xl font-bold text-slate-950">Configuration compliance</h1><p class="mt-1 text-sm text-slate-500">Recovery readiness, approved baselines, insecure services, and inventory completeness.</p></header>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Critical findings', $stats['critical'], 'text-red-700'], ['Warnings', $stats['warning'], 'text-amber-700'], ['Compliant checks', $stats['compliant'], 'text-emerald-700'], ['Approved baselines', $stats['baselines'], 'text-blue-700']] as [$label, $value, $tone])
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold {{ $tone }}">{{ $value }}</p></div>
        @endforeach
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr>@foreach(['Router', 'Latest backup', 'Baseline', 'Findings', 'Actions'] as $heading)<th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($routers as $router)
                        <tr>
                            <td class="px-5 py-4"><a href="{{ route('routers.show', $router) }}" class="font-bold text-blue-700">{{ $router->name }}</a><p class="mt-0.5 text-xs text-slate-400">{{ $router->ip_address }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $router->latestBackup?->created_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="px-5 py-4">{{ $router->configurationBaseline ? 'Approved' : 'Not set' }}</td>
                            <td class="px-5 py-4"><a href="{{ route('compliance.show', $router) }}" class="font-mono font-bold text-red-700">{{ $router->critical_findings_count }}</a><a href="{{ route('compliance.show', $router) }}" class="ml-3 font-mono font-bold text-amber-700">{{ $router->warning_findings_count }}</a></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.table-action :href="route('compliance.show', $router)" icon="eye" tooltip="View compliance findings" />
                                    <form method="POST" action="{{ route('compliance.scan', $router) }}">@csrf<button type="submit" class="inline-flex items-center justify-center rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-blue-50 hover:text-blue-600" title="Scan now"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button></form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
