@extends('layouts.admin')

@section('title', 'Add Credential')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Password Manager', 'href' => route('password-manager.index')],
        ['label' => 'Create Credential', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="space-y-6 pb-24">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Add Password Manager Credential</h1>
        <p class="mt-1 text-sm text-gray-500">Save a reusable login for routers and access points in this tenant.</p>
    </div>

    <form method="POST" action="{{ route('password-manager.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Credential Details</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-ui.input.text label="Credential Name" name="name" :required="true" :value="old('name')" placeholder="e.g., Tower AP Shared Login" :error="$errors->first('name')" />
                    <x-ui.input.text label="Username" name="username" :required="true" :value="old('username')" placeholder="e.g., admin" :error="$errors->first('username')" />
                    <div class="md:col-span-2">
                        <x-ui.input.password label="Password" name="password" :required="true" placeholder="Enter password" :error="$errors->first('password')" />
                    </div>
                    <div class="md:col-span-2">
                        <x-ui.input.textarea label="Notes" name="notes" rows="4" :value="old('notes')" placeholder="Optional notes about where this credential should be used." :error="$errors->first('notes')" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-blue-950">How this works</h3>
                <ul class="mt-4 space-y-3 text-sm text-blue-900">
                    <li>Save a username and password once for the current tenant.</li>
                    <li>Select it from Router or Access Point forms when you want to reuse it.</li>
                    <li>You can still choose manual credentials on any device form when needed.</li>
                </ul>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white p-4 shadow-lg lg:left-64">
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('password-manager.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save Credential</button>
            </div>
        </div>
    </form>
</div>
@endsection
