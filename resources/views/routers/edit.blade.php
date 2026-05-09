@extends('layouts.admin')

@section('title', 'Edit Router')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Routers', 'href' => route('routers.index')],
        ['label' => 'Edit Router', 'current' => true],
    ]" />
@endpush

@section('content')
@php
    $credentialSource = old('credential_source', $router->password_manager_credential_id ? 'password_manager' : 'manual');
@endphp
<div class="space-y-6 pb-24" x-data="{ credentialSource: @js($credentialSource) }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Router</h1>
            <p class="mt-1 text-sm text-gray-500">Updating {{ $router->name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('routers.update', $router) }}">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text label="Router Name" name="name" :required="true" :value="old('name', $router->name)" :error="$errors->first('name')" />
                <x-ui.input.select label="Vendor" name="vendor" :options="['Mikrotik' => 'Mikrotik', 'Cisco' => 'Cisco', 'Juniper' => 'Juniper', 'Huawei' => 'Huawei']" :value="old('vendor', $router->vendor)" placeholder="Select vendor" :required="true" :error="$errors->first('vendor')" />
                <x-ui.input.text label="Model" name="model" :value="old('model', $router->model)" :error="$errors->first('model')" />
                <x-ui.input.text label="Location" name="location" :value="old('location', $router->location)" :error="$errors->first('location')" />
                <x-ui.input.text label="Site" name="site" :value="old('site', $router->site)" :error="$errors->first('site')" />
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Connection Settings</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text type="text" label="IP Address" name="ip_address" :required="true" :value="old('ip_address', $router->ip_address)" :error="$errors->first('ip_address')" />
                <x-ui.input.text type="number" label="API Port" name="api_port" :value="old('api_port', $router->api_port)" :error="$errors->first('api_port')" hint="Default: 8728" />
                <x-ui.input.text type="number" label="SSH Port" name="ssh_port" :value="old('ssh_port', $router->ssh_port)" :error="$errors->first('ssh_port')" hint="Default: 22" />
                <div class="flex items-center pt-6">
                    <x-ui.input.checkbox label="Use SSL for RouterOS API" name="use_ssl" :checked="old('use_ssl', $router->use_ssl)" :error="$errors->first('use_ssl')" />
                </div>
                <div class="flex items-center pt-6">
                    <x-ui.input.checkbox label="Use legacy RouterOS login" name="legacy_login" :checked="old('legacy_login', $router->legacy_login)" :error="$errors->first('legacy_login')" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Credentials</h3>
                    <p class="mt-1 text-sm text-gray-500">Switch between a shared Password Manager credential and a manual router-specific login.</p>
                </div>
                <a href="{{ route('password-manager.create') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">Add Password Manager credential</a>
            </div>

            <input type="hidden" name="credential_source" x-model="credentialSource">

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <button type="button" @click="credentialSource = 'password_manager'" :class="credentialSource === 'password_manager' ? 'border-blue-500 bg-blue-50 text-blue-900 ring-2 ring-blue-100' : 'border-gray-200 bg-white text-gray-700'" class="rounded-2xl border p-4 text-left transition">
                    <p class="text-sm font-semibold">Use Password Manager</p>
                    <p class="mt-1 text-sm text-gray-500">Current: {{ $router->passwordManagerCredential?->name ?: 'No saved credential selected' }}</p>
                </button>
                <button type="button" @click="credentialSource = 'manual'" :class="credentialSource === 'manual' ? 'border-blue-500 bg-blue-50 text-blue-900 ring-2 ring-blue-100' : 'border-gray-200 bg-white text-gray-700'" class="rounded-2xl border p-4 text-left transition">
                    <p class="text-sm font-semibold">Enter Manually</p>
                    <p class="mt-1 text-sm text-gray-500">Manual username: {{ $router->api_username ?: 'Not set' }}</p>
                </button>
            </div>

            <div class="mt-6" x-show="credentialSource === 'password_manager'" x-cloak>
                <x-ui.input.select label="Saved Credential" name="password_manager_credential_id" :options="$credentialOptions" :value="old('password_manager_credential_id', $router->password_manager_credential_id)" placeholder="Select a saved credential" :error="$errors->first('password_manager_credential_id')" />
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2" x-show="credentialSource === 'manual'" x-cloak>
                <x-ui.input.text label="API Username" name="api_username" :value="old('api_username', $router->api_username)" :error="$errors->first('api_username')" />
                <x-ui.input.password label="API Password" name="api_password" placeholder="Leave blank to keep current password" :error="$errors->first('api_password')" />
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Advanced Settings</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text type="number" label="Timeout (seconds)" name="timeout" :value="old('timeout', $router->timeout ?? 30)" :error="$errors->first('timeout')" hint="Connection timeout in seconds" />
                <x-ui.input.select label="SSH Auth Method" name="ssh_auth_method" :options="['private_key' => 'Private Key', 'password' => 'Password']" :value="old('ssh_auth_method', $router->ssh_auth_method ?? 'private_key')" :error="$errors->first('ssh_auth_method')" />
                <x-ui.input.text type="number" label="SSH Timeout" name="ssh_timeout" :value="old('ssh_timeout', $router->ssh_timeout ?? 30)" :error="$errors->first('ssh_timeout')" />
                <div class="md:col-span-2 lg:col-span-3">
                    <x-ui.input.textarea label="SSH Private Key Path" name="ssh_private_key" :value="old('ssh_private_key', $router->ssh_private_key)" :error="$errors->first('ssh_private_key')" hint="Defaults to ~/.ssh/id_rsa when blank." />
                </div>
                <div class="flex items-center pt-6">
                    <x-ui.input.checkbox label="Enable Monitoring" name="enable_monitoring" :checked="old('enable_monitoring', $router->enable_monitoring ?? true)" :error="$errors->first('enable_monitoring')" />
                </div>
                <div class="flex items-center pt-6">
                    <x-ui.input.checkbox label="Enable Provisioning" name="enable_provisioning" :checked="old('enable_provisioning', $router->enable_provisioning ?? true)" :error="$errors->first('enable_provisioning')" />
                </div>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white p-4 shadow-lg lg:left-64">
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('routers.show', $router) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Update Router</button>
            </div>
        </div>
    </form>
</div>
@endsection
