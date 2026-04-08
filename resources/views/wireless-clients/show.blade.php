@extends('layouts.admin')

@section('title', $client['host_name'] ?: $client['mac_address'])

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <a href="{{ route('wireless-clients.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-800">← Back to Wireless Clients</a>
        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Wireless Client</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $client['host_name'] ?: $client['mac_address'] }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $client['mac_address'] }}</p>
            </div>
            <div><span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $client['is_connected'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $client['is_connected'] ? 'Connected' : 'Disconnected' }}</span></div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
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
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">First Seen</dt><dd class="mt-1 text-sm text-slate-900">{{ $wirelessClient->first_seen_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last Seen</dt><dd class="mt-1 text-sm text-slate-900">{{ $wirelessClient->last_seen_at?->format('M d, Y H:i') ?: '—' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-900 p-6 text-white shadow-sm">
            <h2 class="text-lg font-semibold">Movement Summary</h2>
            <p class="mt-2 text-sm text-slate-300">Last moved {{ $client['last_moved_human'] ?: 'not yet detected on another AP' }}.</p>
            <p class="mt-6 text-sm text-slate-300">Current AP</p>
            <p class="mt-1 text-xl font-semibold">{{ $client['access_point'] ?: 'Unknown' }}</p>
            <p class="mt-4 text-sm text-slate-300">Current site</p>
            <p class="mt-1 text-xl font-semibold">{{ $client['site'] ?: 'Unknown' }}</p>
        </div>
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
@endsection
