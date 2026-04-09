@extends('layouts.admin')

@section('title', 'Credential Details')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Password Manager', 'href' => route('password-manager.index')],
        ['label' => $credential->name, 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $credential->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Reusable tenant credential for routers and access points.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('password-manager.edit', $credential) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit</a>
            <form method="POST" action="{{ route('password-manager.destroy', $credential) }}" onsubmit="return confirm('Delete this credential? Make sure it is no longer assigned to any router or access point.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Credential Information</h3>
                <dl class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Username</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $credential->username }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Password</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">Stored securely</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Notes</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $credential->notes ?: 'No notes added.' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Assigned Routers</h3>
                <div class="mt-4 space-y-3">
                    @forelse($credential->routers as $router)
                        <a href="{{ route('routers.show', $router) }}" class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 hover:border-blue-300 hover:bg-blue-50">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $router->name }}</p>
                                <p class="text-xs text-gray-500">{{ $router->ip_address }}</p>
                            </div>
                            <span class="text-sm text-blue-600">Open</span>
                        </a>
                    @empty
                        <p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-sm text-gray-500">No routers are using this credential yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Assigned Access Points</h3>
                <div class="mt-4 space-y-3">
                    @forelse($credential->accessPoints as $accessPoint)
                        <a href="{{ route('access-points.show', $accessPoint) }}" class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 hover:border-emerald-300 hover:bg-emerald-50">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $accessPoint->name }}</p>
                                <p class="text-xs text-gray-500">{{ $accessPoint->ip_address ?: 'No IP address set' }}</p>
                            </div>
                            <span class="text-sm text-emerald-600">Open</span>
                        </a>
                    @empty
                        <p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-sm text-gray-500">No access points are using this credential yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
