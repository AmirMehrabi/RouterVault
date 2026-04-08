@extends('layouts.admin')

@section('title', 'Edit Site')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Sites', 'href' => route('sites.index')],
        ['label' => 'Edit Site', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6 pb-24">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Site</h1>
            <p class="text-sm text-gray-500 mt-1">Update operational details, coordinates, and contact information.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('sites.update', $site) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('sites._form', ['submitLabel' => 'Update Site', 'site' => $site, 'mapLocale' => $mapLocale])
    </form>
</div>
@endsection
