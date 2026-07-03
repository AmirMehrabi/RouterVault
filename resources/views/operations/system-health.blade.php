@extends('layouts.admin')

@section('title', 'System Health')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Dashboard', 'href' => route('dashboard')], ['label' => 'System Health', 'current' => true]]" />
@endpush

@section('content')
<div class="space-y-5 pb-10">
    <header><h1 class="text-2xl font-bold text-slate-950">System health</h1><p class="mt-1 text-sm text-slate-500">Verifies that RouterVault is actually collecting, scheduling, queuing, and storing backups.</p></header>
    <section class="grid gap-4 sm:grid-cols-3">
        @foreach([['Healthy', $summary['healthy'], 'text-emerald-700'], ['Warning', $summary['warning'], 'text-amber-700'], ['Critical', $summary['critical'], 'text-red-700']] as [$label, $value, $tone])
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold {{ $tone }}">{{ $value }}</p></div>
        @endforeach
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">
            @foreach($checks as $check)
                <div class="grid gap-3 px-5 py-4 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                    <span class="h-3 w-3 rounded-full {{ $check['status'] === 'healthy' ? 'bg-emerald-500' : ($check['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                    <div><p class="text-sm font-bold text-slate-900">{{ $check['name'] }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $check['message'] }}</p></div>
                    <x-ui.badge :status="$check['status']">{{ ucfirst($check['status']) }}</x-ui.badge>
                </div>
            @endforeach
        </div>
    </section>
    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">Supervisor must run both <code>php artisan schedule:work</code> and <code>php artisan queue:work</code>. Missing heartbeats turn critical automatically.</div>
</div>
@endsection
