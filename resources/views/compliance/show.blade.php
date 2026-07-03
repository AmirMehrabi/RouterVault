@extends('layouts.admin')

@section('title', 'Compliance – '.$router->name)

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Compliance', 'href' => route('compliance.index')], ['label' => $router->name, 'current' => true]]" />
@endpush

@section('content')
<div class="mx-auto max-w-5xl space-y-5 pb-10">
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-950">{{ $router->name }}</h1>
            <span class="text-sm text-slate-400">{{ $router->ip_address }}</span>
        </div>
        <p class="mt-2 text-sm text-slate-500">
            Latest backup {{ $router->latestBackup?->created_at?->diffForHumans() ?? 'Never' }}
            · {{ $router->complianceFindings->count() }} findings
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <form method="POST" action="{{ route('compliance.scan', $router) }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold">Scan now</button></form>
            @if($router->latestBackup?->path && in_array($router->latestBackup->status, ['success', 'partial_success'], true))
                <form method="POST" action="{{ route('compliance.baseline', $router) }}">@csrf<input type="hidden" name="router_backup_id" value="{{ $router->latestBackup->id }}"><input type="hidden" name="label" value="Approved baseline"><button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Approve latest</button></form>
            @endif
        </div>
    </header>

    @if($router->configurationBaseline)
        <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <h2 class="text-sm font-bold text-blue-900">Approved baseline</h2>
            <p class="mt-1 text-sm text-blue-700">{{ $router->configurationBaseline->label }} · approved {{ $router->configurationBaseline->approved_at?->diffForHumans() }} by {{ $router->configurationBaseline->approver?->name ?? 'Unknown' }}</p>
            @if($router->configurationBaseline->notes)<p class="mt-2 text-sm text-blue-600">{{ $router->configurationBaseline->notes }}</p>@endif
        </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr>@foreach(['Rule', 'Status', 'Summary', 'Remediation', 'Checked'] as $h)<th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($router->complianceFindings as $finding)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $finding->rule_name }}</td>
                            <td class="px-5 py-4">
                                @if($finding->status === 'critical')
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">Critical</span>
                                @elseif($finding->status === 'warning')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Warning</span>
                                @elseif($finding->status === 'compliant')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Compliant</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ ucfirst($finding->status) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $finding->summary }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $finding->remediation ?? '—' }}</td>
                            <td class="px-5 py-4 text-xs text-slate-400">{{ $finding->checked_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No findings yet. Run a scan to check compliance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
