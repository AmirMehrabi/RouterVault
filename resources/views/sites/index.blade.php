@extends('layouts.admin')

@section('title', 'Sites')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Sites', 'href' => route('sites.index'), 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6 pb-24">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sites</h1>
            <p class="text-sm text-gray-500 mt-1">Manage deployment locations, field contacts, and geographic coordinates.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('sites.topology') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Topology Map</a>
            <a href="{{ route('sites.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">Add Site</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <p class="text-sm text-gray-500">Total Sites</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <p class="text-sm text-gray-500">Active</p>
            <p class="mt-2 text-3xl font-semibold text-green-700">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <p class="text-sm text-gray-500">Maintenance</p>
            <p class="mt-2 text-3xl font-semibold text-amber-700">{{ $stats['maintenance'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <p class="text-sm text-gray-500">Inactive</p>
            <p class="mt-2 text-3xl font-semibold text-gray-700">{{ $stats['inactive'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Site</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($sites as $site)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 align-top">
                                <a href="{{ route('sites.show', $site) }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $site->name }}</a>
                                <p class="text-sm text-gray-500 mt-1">{{ $site->code ?: 'No code' }}</p>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-gray-600">
                                <p>{{ collect([$site->city, $site->state, $site->country])->filter()->join(', ') ?: '—' }}</p>
                                <p class="mt-1 text-gray-400">{{ $site->address ?: 'No address added' }}</p>
                            </td>
                            <td class="px-6 py-4 align-top text-sm text-gray-600">
                                <p>{{ $site->contact_name ?: '—' }}</p>
                                <p class="mt-1 text-gray-400">{{ $site->contact_phone ?: ($site->contact_email ?: 'No contact details') }}</p>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : ($site->status === 'maintenance' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($site->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.table-action :href="route('sites.show', $site)" icon="eye" tooltip="View site" />
                                    <x-ui.table-action :href="route('sites.edit', $site)" icon="edit" tooltip="Edit site" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-lg font-semibold text-gray-900">No sites yet</p>
                                <p class="mt-2 text-sm text-gray-500">Create your first site to start organizing routers and location-based operations.</p>
                                <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                                    <a href="{{ route('sites.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">Create Site</a>
                                    <a href="{{ route('sites.topology') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Open Topology Map</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sites->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $sites->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
