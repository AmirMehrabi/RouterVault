@extends('layouts.admin')

@section('title', 'Change Control')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Dashboard', 'href' => route('dashboard')], ['label' => 'Change Control', 'current' => true]]" />
@endpush

@section('content')
<div class="space-y-6 pb-10" x-data="{ changeForm: false, maintenanceForm: false }">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-950">Change control</h1>
            <p class="mt-1 text-sm text-slate-500">Record intent, approval, recovery evidence, and planned maintenance.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" @click="maintenanceForm = !maintenanceForm" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Schedule maintenance</button>
            <button type="button" @click="changeForm = !changeForm" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">New change request</button>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <section x-show="changeForm" x-cloak class="rounded-2xl border border-blue-200 bg-white p-5 shadow-sm">
        <h2 class="font-bold text-slate-950">Submit change request</h2>
        <form method="POST" action="{{ route('change-control.changes.store') }}" class="mt-4 grid gap-4 lg:grid-cols-2">
            @csrf
            <x-ui.input.select label="Router" name="router_id" :options="$routers->pluck('name', 'id')" :value="old('router_id')" required />
            <x-ui.input.text label="Title" name="title" :value="old('title')" required />
            <x-ui.input.text label="Ticket reference" name="ticket_reference" :value="old('ticket_reference')" />
            <div></div>
            <x-ui.input.textarea label="Reason and expected impact" name="reason" :value="old('reason')" required />
            <x-ui.input.textarea label="Implementation and rollback plan" name="implementation_plan" :value="old('implementation_plan')" required />
            <div class="lg:col-span-2"><button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Submit for approval</button></div>
        </form>
    </section>

    <section x-show="maintenanceForm" x-cloak class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
        <h2 class="font-bold text-slate-950">Schedule maintenance window</h2>
        <form method="POST" action="{{ route('change-control.maintenance.store') }}" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @csrf
            <x-ui.input.text label="Window name" name="name" :value="old('name')" required />
            <x-ui.input.select label="Router (optional)" name="router_id" :options="$routers->pluck('name', 'id')" :value="old('router_id')" />
            <x-ui.input.select label="Site (optional)" name="site_id" :options="$sites->pluck('name', 'id')" :value="old('site_id')" />
            <x-ui.input.text label="Starts" name="starts_at" type="datetime-local" :value="old('starts_at')" required />
            <x-ui.input.text label="Ends" name="ends_at" type="datetime-local" :value="old('ends_at')" required />
            <x-ui.input.text label="Reason" name="reason" :value="old('reason')" />
            <div class="xl:col-span-3"><button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Schedule window</button></div>
        </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between"><h2 class="font-bold text-slate-950">Upcoming maintenance</h2><span class="text-xs text-slate-500">{{ $maintenanceWindows->count() }} windows</span></div>
        <div class="mt-4 grid gap-3 lg:grid-cols-2">
            @forelse($maintenanceWindows as $window)
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex justify-between gap-3"><p class="font-semibold text-slate-900">{{ $window->name }}</p><x-ui.badge :value="$window->status" /></div>
                    <p class="mt-2 text-sm text-slate-600">{{ $window->starts_at->format('M j, Y H:i') }} – {{ $window->ends_at->format('M j, Y H:i') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $window->router?->name ?? $window->site?->name ?? 'All infrastructure' }}</p>
                </article>
            @empty
                <p class="text-sm text-slate-500">No upcoming maintenance windows.</p>
            @endforelse
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold text-slate-950">Change requests</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr>@foreach(['Change', 'Router', 'Owner', 'Status', 'Progress'] as $heading)<th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($changeRequests as $change)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-900">{{ $change->title }}</p><p class="text-xs text-slate-500">{{ $change->ticket_reference ?: 'No ticket reference' }}</p></td>
                            <td class="px-5 py-4 text-slate-700">{{ $change->router->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $change->requester?->name ?? 'System' }}</td>
                            <td class="px-5 py-4"><x-ui.badge :value="$change->status" /></td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('change-control.changes.update', $change) }}" class="flex min-w-80 gap-2">
                                    @csrf @method('PUT')
                                    <x-ui.input.select :id="'status-'.$change->id" name="status" :options="collect(['submitted', 'approved', 'in_progress', 'completed', 'cancelled'])->mapWithKeys(fn ($status) => [$status => str($status)->replace('_', ' ')->title()->toString()])" :value="$change->status" class="text-xs" />
                                    <x-ui.input.text :id="'result-'.$change->id" name="result" :value="$change->result" placeholder="Result if completed" class="min-w-40 text-xs" />
                                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No change requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($changeRequests->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $changeRequests->links() }}</div>@endif
    </section>
</div>
@endsection
