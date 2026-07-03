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
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('compliance.scan', $router) }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold">Scan now</button></form>
                                    @if($router->latestBackup?->path && in_array($router->latestBackup->status, ['success', 'partial_success'], true))
                                        <form method="POST" action="{{ route('compliance.baseline', $router) }}">@csrf<input type="hidden" name="router_backup_id" value="{{ $router->latestBackup->id }}"><input type="hidden" name="label" value="Approved baseline"><button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Approve latest</button></form>
                                    @endif
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
