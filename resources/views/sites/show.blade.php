@extends('layouts.admin')

@section('title', $site->name)

@section('content')
<div class="space-y-6 pb-24">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ $site->name }}</h1>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : ($site->status === 'maintenance' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                    {{ ucfirst($site->status) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $site->code ?: 'No site code assigned' }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('sites.edit', $site) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Edit Site</a>
            <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Delete this site? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 transition">Delete</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Location Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Address</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->address ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">City</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->city ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">State / Region</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->state ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Country</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->country ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Latitude</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->latitude ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Longitude</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->longitude ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Operational Notes</h2>
                <p class="text-sm leading-6 text-gray-700">{{ $site->description ?: 'No additional notes for this site yet.' }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact</h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-500">Contact Name</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->contact_name ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Phone</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->contact_phone ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->contact_email ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Record Info</h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-500">Created</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->created_at?->format('M d, Y h:i A') ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Last Updated</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $site->updated_at?->format('M d, Y h:i A') ?: '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
