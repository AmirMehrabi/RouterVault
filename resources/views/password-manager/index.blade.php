@extends('layouts.admin')

@section('title', 'Password Manager')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Password Manager', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Password Manager</h1>
            <p class="mt-1 text-sm text-gray-500">Store tenant-scoped device credentials once, then reuse them across routers and access points.</p>
        </div>
        <a href="{{ route('password-manager.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Credential
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Stored Credentials</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Assigned to Routers</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['router_links'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Assigned to Access Points</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['access_point_links'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Credential</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Updated</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($credentials as $credential)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $credential->name }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $credential->notes ?: 'Reusable device credential for this tenant.' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $credential->username }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 text-xs font-medium">
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-blue-700">{{ $credential->routers_count }} router{{ $credential->routers_count === 1 ? '' : 's' }}</span>
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700">{{ $credential->access_points_count }} access point{{ $credential->access_points_count === 1 ? '' : 's' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $credential->updated_at?->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3 text-sm">
                                    <a href="{{ route('password-manager.show', $credential) }}" class="text-blue-600 hover:text-blue-700">View</a>
                                    <a href="{{ route('password-manager.edit', $credential) }}" class="text-gray-700 hover:text-gray-900">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-gray-900">No credentials saved yet</h3>
                                    <p class="mt-2 text-sm text-gray-500">Create a shared credential once, then select it from Router and Access Point forms instead of retyping usernames and passwords.</p>
                                    <a href="{{ route('password-manager.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Create first credential</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($credentials->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $credentials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
