@extends('layouts.admin')

@section('title', 'Incident #'.$incident->id)

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Incidents', 'href' => route('incidents.index')], ['label' => '#'.$incident->id, 'current' => true]]" />
@endpush

@section('content')
<div class="mx-auto max-w-5xl space-y-5 pb-10">
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-3"><h1 class="text-2xl font-bold text-slate-950">{{ $incident->summary }}</h1><x-ui.badge :status="$incident->status">{{ str($incident->status)->title() }}</x-ui.badge></div>
        <p class="mt-2 text-sm text-slate-500">{{ $incident->router?->name ?? 'Unknown router' }} · {{ ucfirst($incident->severity) }} severity · detected {{ $incident->created_at?->diffForHumans() }}</p>
        @if($incident->impact)<div class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900"><strong>Potential impact:</strong> {{ $incident->impact }}</div>@endif
    </header>

    <div class="grid gap-5 lg:grid-cols-[1fr_22rem]">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Evidence</h2>
            <div class="mt-4 space-y-3 text-sm">
                @if($incident->diffAlert)<a href="{{ route('diff-alerts.show', $incident->diffAlert) }}" class="block rounded-xl border border-slate-200 p-4 font-semibold text-blue-700">Review configuration diff alert #{{ $incident->diffAlert->id }}</a>@endif
                @if($incident->backup)<a href="{{ route('backups.show', $incident->backup) }}" class="block rounded-xl border border-slate-200 p-4 font-semibold text-blue-700">Inspect backup attempt #{{ $incident->backup->id }}</a>@endif
                @if(! $incident->diffAlert && ! $incident->backup)<p class="text-slate-500">No linked evidence is available.</p>@endif
            </div>
        </section>

        <form method="POST" action="{{ route('incidents.update', $incident) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @method('PUT')
            <h2 class="text-base font-bold text-slate-900">Operator response</h2>
            <div class="mt-4 space-y-4">
                <x-ui.input.select label="Status" name="status" :options="['detected' => 'Detected', 'acknowledged' => 'Acknowledged', 'assigned' => 'Assigned', 'investigating' => 'Investigating', 'resolved' => 'Resolved']" :value="old('status', $incident->status)" :required="true" :error="$errors->first('status')" />
                <x-ui.input.select label="Assignee" name="assigned_to" :options="$assignees->pluck('name', 'id')->all()" :value="old('assigned_to', $incident->assigned_to)" placeholder="Unassigned" :error="$errors->first('assigned_to')" />
                <x-ui.input.textarea label="Resolution / handover notes" name="resolution" :value="old('resolution', $incident->resolution)" rows="6" :error="$errors->first('resolution')" />
                <button class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Update incident</button>
            </div>
        </form>
    </div>
</div>
@endsection
