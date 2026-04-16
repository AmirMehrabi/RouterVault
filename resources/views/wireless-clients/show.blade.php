@extends('layouts.admin')

@section('title', $client['host_name'] ?: $client['mac_address'])

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Wireless Clients', 'href' => route('wireless-clients.index')],
        ['label' => $client['host_name'] ?: $client['mac_address'], 'current' => true],
    ]" />
@endpush

@section('content')
@php
    $credentialSource = old('credential_source', $wirelessClient->password_manager_credential_id ? 'password_manager' : 'manual');
@endphp
<div class="space-y-6" x-data="{ credentialSource: @js($credentialSource) }">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            Please review the credential form and try again.
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <a href="{{ route('wireless-clients.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-800">← Back to Wireless Clients</a>
        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Wireless Client</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $client['host_name'] ?: $client['mac_address'] }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $client['mac_address'] }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $client['is_connected'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $client['is_connected'] ? 'Connected' : 'Disconnected' }}</span>
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $client['is_provisioned'] ? 'bg-cyan-100 text-cyan-700' : 'bg-amber-100 text-amber-700' }}">{{ $client['provisioning_status'] }}</span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Current Details</h2>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Access Point</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['access_point'] ?: 'Unknown' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Site</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['site'] ?: 'Unknown' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">SSID</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['ssid'] ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Band / Frequency</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['band'] ?: '—' }} / {{ $client['frequency'] ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Signal</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['signal_strength'] ?? '—' }} dBm</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">SNR</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['signal_to_noise'] ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">TX / RX Rate</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['tx_rate'] ?: '—' }} / {{ $client['rx_rate'] ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last IP</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['last_ip_address'] ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Provisioning Source</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['credential_source'] === 'password_manager' ? 'Password Manager' : ($client['credential_source'] === 'manual' ? 'Manual' : 'Not assigned') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Provisioning Username</dt><dd class="mt-1 text-sm text-slate-900">{{ $client['provisioning_username'] ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">First Seen</dt><dd class="mt-1 text-sm text-slate-900">{{ $wirelessClient->first_seen_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last Seen</dt><dd class="mt-1 text-sm text-slate-900">{{ $wirelessClient->last_seen_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Credential Management</h2>
                        <p class="mt-1 text-sm text-slate-500">Use a saved Password Manager credential or store a manual username and password for this wireless client.</p>
                    </div>
                    <a href="{{ route('password-manager.create') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-800">Add Password Manager credential</a>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current Assignment</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $client['provisioning_status'] }}</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if($client['credential_source'] === 'password_manager')
                            Using saved credential {{ $client['credential_name'] ?: 'from Password Manager' }}.
                        @elseif($client['credential_source'] === 'manual')
                            Manual username {{ $client['provisioning_username'] ?: 'not set' }}.
                        @else
                            No provisioning credentials are assigned yet.
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('wireless-clients.credentials.update', $wirelessClient) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="credential_source" x-model="credentialSource">
                    <input type="hidden" name="redirect_route" value="show">

                    <div class="grid gap-4 lg:grid-cols-2">
                        <button type="button" @click="credentialSource = 'password_manager'" :class="credentialSource === 'password_manager' ? 'border-cyan-500 bg-cyan-50 text-cyan-900 ring-2 ring-cyan-100' : 'border-slate-200 bg-white text-slate-700'" class="rounded-2xl border p-4 text-left transition">
                            <p class="text-sm font-semibold">Use Password Manager</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $wirelessClient->passwordManagerCredential?->name ?: 'Select a saved credential for this client.' }}</p>
                        </button>
                        <button type="button" @click="credentialSource = 'manual'" :class="credentialSource === 'manual' ? 'border-cyan-500 bg-cyan-50 text-cyan-900 ring-2 ring-cyan-100' : 'border-slate-200 bg-white text-slate-700'" class="rounded-2xl border p-4 text-left transition">
                            <p class="text-sm font-semibold">Enter Manually</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $wirelessClient->provisioning_username ?: 'Save a direct username and password.' }}</p>
                        </button>
                    </div>

                    <div x-show="credentialSource === 'password_manager'" x-cloak>
                        <x-ui.input.select label="Saved Credential" name="password_manager_credential_id" :options="$credentialOptions" :value="old('password_manager_credential_id', $wirelessClient->password_manager_credential_id)" placeholder="Select a saved credential" :error="$errors->first('password_manager_credential_id')" hint="Saved credentials are tenant-scoped and reusable across wireless clients." />
                    </div>

                    <div class="grid gap-6 md:grid-cols-2" x-show="credentialSource === 'manual'" x-cloak>
                        <x-ui.input.text label="Username" name="provisioning_username" :value="old('provisioning_username', $wirelessClient->provisioning_username)" :error="$errors->first('provisioning_username')" />
                        <x-ui.input.password label="Password" name="provisioning_password" placeholder="Leave blank to keep current password" :error="$errors->first('provisioning_password')" />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                        <button type="submit" class="rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Save credentials</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('wireless-clients.credentials.clear', $wirelessClient) }}" class="mt-4 border-t border-slate-200 pt-4">
                    @csrf
                    @method('DELETE')
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Clear credentials</p>
                            <p class="text-sm text-slate-500">Remove both Password Manager and manual credentials from this wireless client.</p>
                        </div>
                        <button type="submit" class="rounded-2xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Remove credentials</button>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Movement History</h2>
                <div class="mt-6 space-y-4">
                    @forelse($movements as $movement)
                        <div class="rounded-2xl border border-slate-200 px-4 py-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $movement['from_access_point'] ?: 'Unknown AP' }} → {{ $movement['to_access_point'] ?: 'Unknown AP' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $movement['moved_human'] ?: 'Unknown time' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No AP movement has been recorded for this client yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-sm">
                <h2 class="text-lg font-semibold">Movement Summary</h2>
                <p class="mt-2 text-sm text-slate-300">Last moved {{ $client['last_moved_human'] ?: 'not yet detected on another AP' }}.</p>
                <p class="mt-6 text-sm text-slate-300">Current AP</p>
                <p class="mt-1 text-xl font-semibold">{{ $client['access_point'] ?: 'Unknown' }}</p>
                <p class="mt-4 text-sm text-slate-300">Current site</p>
                <p class="mt-1 text-xl font-semibold">{{ $client['site'] ?: 'Unknown' }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Provisioning Snapshot</h2>
                <dl class="mt-6 space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $client['provisioning_status'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Source</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $client['credential_source'] === 'password_manager' ? 'Password Manager' : ($client['credential_source'] === 'manual' ? 'Manual' : 'Not assigned') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Saved Credential</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $client['credential_name'] ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Username</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $client['provisioning_username'] ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
