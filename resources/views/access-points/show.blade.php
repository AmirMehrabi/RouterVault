@extends('layouts.admin')

@section('title', $accessPoint->name)

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="accessPointShow()" x-cloak>
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $accessPoint->name }}</h1>
                        <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $accessPoint->status === 'online' ? 'border-green-200 bg-green-100 text-green-800' : ($accessPoint->status === 'maintenance' ? 'border-amber-200 bg-amber-100 text-amber-800' : 'border-red-200 bg-red-100 text-red-800') }}">
                            {{ ucfirst($accessPoint->status) }}
                        </span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span>{{ $accessPoint->ssid ?: 'No SSID' }}</span>
                        <span>{{ $accessPoint->band }}</span>
                        <span>{{ $accessPoint->ip_address ?: 'No IP assigned' }}</span>
                        <span>{{ $accessPoint->site?->name ?: 'No site' }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('access-points.edit', $accessPoint) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Edit Access Point
                </a>
                <button @click="deleteModal = true" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-100 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-200">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Connected Clients</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $accessPoint->connected_clients_count }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Signal Quality</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $accessPoint->signal_quality }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">CPU / Memory</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $accessPoint->cpu_usage }}% / {{ $accessPoint->memory_usage }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Last Seen</p>
            <p class="mt-3 text-lg font-semibold text-gray-900">{{ $accessPoint->last_seen_at?->diffForHumans() ?: 'Never' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Radio & Network</h3>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Vendor</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->vendor }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Model</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->model ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">SSID</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->ssid ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Band</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->band }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Channel / Frequency</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->channel ?: '—' }} / {{ $accessPoint->frequency ?: '—' }}</span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-500">TX Power</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->tx_power ? $accessPoint->tx_power.' dBm' : '—' }}</span></div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Assignment & Provisioning</h3>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Router</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->router?->name ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Site</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->site?->name ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">IP Address</span><span class="font-mono text-sm font-medium text-gray-900">{{ $accessPoint->ip_address ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">MAC Address</span><span class="font-mono text-sm font-medium text-gray-900">{{ $accessPoint->mac_address ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-gray-100 py-2"><span class="text-sm text-gray-500">Monitoring</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->enable_monitoring ? 'Enabled' : 'Disabled' }}</span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-500">Provisioning</span><span class="text-sm font-medium text-gray-900">{{ $accessPoint->enable_provisioning ? 'Enabled' : 'Disabled' }}</span></div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Notes</h3>
        <p class="text-sm leading-6 text-gray-600">{{ $accessPoint->notes ?: 'No notes added yet.' }}</p>
    </div>

    <div x-show="deleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Delete Access Point</h3>
                    <p class="mt-2 text-sm text-gray-500">Are you sure you want to delete "{{ $accessPoint->name }}"?</p>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form method="POST" action="{{ route('access-points.destroy', $accessPoint) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto">
                            Delete
                        </button>
                    </form>
                    <button @click="deleteModal = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function accessPointShow() {
        return {
            deleteModal: false,
        };
    }
</script>
@endpush
