@extends('layouts.admin')

@section('title', 'Add New Site')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Sites', 'href' => route('sites.index')],
        ['label' => 'Create Site', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6 pb-24">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Site</h1>
            <p class="text-sm text-gray-500 mt-1">Create a deployment site for routers, pools, and field operations.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('sites.store') }}" class="space-y-6">
        @csrf

        @include('sites._form', ['submitLabel' => 'Create Site', 'mapLocale' => $mapLocale])
    </form>
</div>
@endsection
