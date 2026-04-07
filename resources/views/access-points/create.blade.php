@extends('layouts.admin')

@section('title', 'Add New Access Point')

@section('content')
<div class="space-y-6 pb-24">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Access Point</h1>
            <p class="mt-1 text-sm text-gray-500">Register an access point for monitoring and provisioning.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('access-points.store') }}">
        @csrf

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text
                    label="Access Point Name"
                    name="name"
                    placeholder="e.g., AP-Tower-01"
                    :required="true"
                    :value="old('name')"
                    :error="$errors->first('name')"
                />

                <x-ui.input.select
                    label="Vendor"
                    name="vendor"
                    :options="[
                        'Mikrotik' => 'Mikrotik',
                        'Ubiquiti' => 'Ubiquiti',
                        'Cambium' => 'Cambium',
                        'TP-Link' => 'TP-Link',
                        'Cisco' => 'Cisco',
                        'Other' => 'Other',
                    ]"
                    :value="old('vendor', 'Mikrotik')"
                    placeholder="Select vendor"
                    :required="true"
                    :error="$errors->first('vendor')"
                />

                <x-ui.input.text
                    label="Model"
                    name="model"
                    placeholder="e.g., cAP ax"
                    :value="old('model')"
                    :error="$errors->first('model')"
                />

                <x-ui.input.select
                    label="Router"
                    name="router_id"
                    :options="$routerOptions"
                    :value="old('router_id')"
                    placeholder="Select router"
                    :error="$errors->first('router_id')"
                />

                <x-ui.input.select
                    label="Site"
                    name="site_id"
                    :options="$siteOptions"
                    :value="old('site_id')"
                    placeholder="Select site"
                    :error="$errors->first('site_id')"
                />

                <x-ui.input.text
                    label="Location"
                    name="location"
                    placeholder="e.g., Rooftop sector A"
                    :value="old('location')"
                    :error="$errors->first('location')"
                />
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Wireless & Network</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text
                    label="SSID"
                    name="ssid"
                    placeholder="e.g., SkyBase-Clients"
                    :value="old('ssid')"
                    :error="$errors->first('ssid')"
                />

                <x-ui.input.select
                    label="Band"
                    name="band"
                    :options="[
                        '2.4GHz' => '2.4GHz',
                        '5GHz' => '5GHz',
                        '6GHz' => '6GHz',
                        'dual' => 'Dual Band',
                    ]"
                    :value="old('band', 'dual')"
                    placeholder="Select band"
                    :required="true"
                    :error="$errors->first('band')"
                />

                <x-ui.input.select
                    label="Status"
                    name="status"
                    :options="[
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'maintenance' => 'Maintenance',
                    ]"
                    :value="old('status', 'offline')"
                    placeholder="Select status"
                    :error="$errors->first('status')"
                />

                <x-ui.input.text
                    label="IP Address"
                    name="ip_address"
                    placeholder="192.168.88.10"
                    :value="old('ip_address')"
                    :error="$errors->first('ip_address')"
                />

                <x-ui.input.text
                    label="MAC Address"
                    name="mac_address"
                    placeholder="AA:BB:CC:DD:EE:FF"
                    :value="old('mac_address')"
                    :error="$errors->first('mac_address')"
                />

                <x-ui.input.text
                    label="Firmware Version"
                    name="firmware_version"
                    placeholder="RouterOS 7.16"
                    :value="old('firmware_version')"
                    :error="$errors->first('firmware_version')"
                />

                <x-ui.input.text
                    label="Channel"
                    name="channel"
                    placeholder="e.g., 36"
                    :value="old('channel')"
                    :error="$errors->first('channel')"
                />

                <x-ui.input.text
                    type="number"
                    label="Frequency (MHz)"
                    name="frequency"
                    :value="old('frequency')"
                    :error="$errors->first('frequency')"
                />

                <x-ui.input.text
                    type="number"
                    label="TX Power (dBm)"
                    name="tx_power"
                    :value="old('tx_power')"
                    :error="$errors->first('tx_power')"
                />
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Monitoring Snapshot</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-ui.input.text type="number" label="Connected Clients" name="connected_clients_count" :value="old('connected_clients_count', 0)" :error="$errors->first('connected_clients_count')" />
                <x-ui.input.text type="number" label="Signal Quality (%)" name="signal_quality" :value="old('signal_quality', 0)" :error="$errors->first('signal_quality')" />
                <x-ui.input.text type="number" label="CPU Usage (%)" name="cpu_usage" :value="old('cpu_usage', 0)" :error="$errors->first('cpu_usage')" />
                <x-ui.input.text type="number" label="Memory Usage (%)" name="memory_usage" :value="old('memory_usage', 0)" :error="$errors->first('memory_usage')" />
                <x-ui.input.text type="number" label="Channel Utilization (%)" name="channel_utilization" :value="old('channel_utilization', 0)" :error="$errors->first('channel_utilization')" />
                <x-ui.input.text type="number" label="Noise Floor (dBm)" name="noise_floor" :value="old('noise_floor')" :error="$errors->first('noise_floor')" />
                <x-ui.input.text label="Uptime" name="uptime" placeholder="e.g., 4d 12h" :value="old('uptime')" :error="$errors->first('uptime')" />
                <x-ui.input.text type="datetime-local" label="Last Seen At" name="last_seen_at" :value="old('last_seen_at')" :error="$errors->first('last_seen_at')" />
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="flex items-center pt-2">
                    <x-ui.input.checkbox label="Enable Monitoring" name="enable_monitoring" :checked="old('enable_monitoring', true)" :error="$errors->first('enable_monitoring')" />
                </div>
                <div class="flex items-center pt-2">
                    <x-ui.input.checkbox label="Enable Provisioning" name="enable_provisioning" :checked="old('enable_provisioning', true)" :error="$errors->first('enable_provisioning')" />
                </div>
                <div class="lg:col-span-3">
                    <x-ui.input.textarea
                        label="Notes"
                        name="notes"
                        rows="4"
                        placeholder="Installation notes, antenna details, or provisioning remarks..."
                        :value="old('notes')"
                        :error="$errors->first('notes')"
                    />
                </div>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white p-4 shadow-lg lg:left-64">
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('access-points.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Save Access Point
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
