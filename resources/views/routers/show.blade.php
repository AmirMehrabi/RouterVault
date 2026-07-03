@extends('layouts.admin')

@section('title', $router->name ?? 'Router')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Routers', 'href' => route('routers.index')],
        ['label' => $router->name ?? 'Router', 'current' => true],
    ]" />
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="routerShow()" x-cloak>
    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                     :class="status === 'online' ? 'bg-green-50' : 'bg-red-50'">
                    <svg class="w-7 h-7" :class="status === 'online' ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $router->name }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                              :class="status === 'online' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="status === 'online' ? 'bg-green-500' : 'bg-red-500'"></span>
                            <span x-text="status.charAt(0).toUpperCase() + status.slice(1)"></span>
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-sm text-gray-500">
                        <span class="font-mono">{{ $router->ip_address }}</span>
                        @if($router->model)
                            <span>{{ $router->model }}</span>
                        @endif
                        @if($router->vendor)
                            <span>{{ $router->vendor }}</span>
                        @endif
                        @if($router->version)
                            <span>v{{ $router->version }}</span>
                        @endif
                        @if($router->uptime)
                            <span>Up {{ $router->uptime }}</span>
                        @endif
                        @if($router->location)
                            <span>{{ $router->location }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('routers.edit', $router) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('backups.retry', ['backup' => 0]) }}" x-ref="backupForm">
                    @csrf
                </form>
                @if($router->backupsEnabled())
                <button @click="triggerBackup()" :disabled="backupRunning" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 disabled:opacity-50">
                    <svg class="w-4 h-4" :class="{'animate-spin': backupRunning}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span x-text="backupRunning ? 'Backing up...' : 'Backup Now'"></span>
                </button>
                @else
                    <span class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-500">Backups disabled</span>
                @endif
                <button @click="deleteModal.show = true" class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs font-medium text-gray-500">CPU</p>
            <div class="flex items-end gap-2 mt-1">
                <p class="text-2xl font-bold text-gray-900">{{ $router->cpu_usage ?? 0 }}%</p>
            </div>
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full transition-all duration-500 {{ ($router->cpu_usage ?? 0) > 80 ? 'bg-red-500' : (($router->cpu_usage ?? 0) > 60 ? 'bg-yellow-500' : 'bg-blue-500') }}"
                     style="width: {{ $router->cpu_usage ?? 0 }}%"></div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Memory</p>
            <div class="flex items-end gap-2 mt-1">
                <p class="text-2xl font-bold text-gray-900">{{ $router->memory_usage ?? 0 }}%</p>
            </div>
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full transition-all duration-500 {{ ($router->memory_usage ?? 0) > 80 ? 'bg-red-500' : (($router->memory_usage ?? 0) > 60 ? 'bg-yellow-500' : 'bg-green-500') }}"
                     style="width: {{ $router->memory_usage ?? 0 }}%"></div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Active Sessions</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $router->active_sessions_count ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Customers</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $router->total_customers ?? 0 }}</p>
        </div>
    </div>

    <!-- Connection & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Connection Details</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">IP Address</span>
                    <span class="text-sm font-medium text-gray-900 font-mono">{{ $router->ip_address }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">RouterOS API</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($router->enable_api ?? '1') === '1' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ ($router->enable_api ?? '1') === '1' ? 'Enabled (' . ($router->api_port ?: 8728) . ')' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">SSH</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($router->enable_ssh ?? '1') === '1' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ ($router->enable_ssh ?? '1') === '1' ? 'Enabled (' . ($router->ssh_port ?: 22) . ')' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Credential</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->passwordManagerCredential?->name ?: ($router->api_username ?: '—') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Last Connected</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->last_connected_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
                @if($router->last_error)
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Last Error</span>
                        <span class="text-sm font-medium text-red-600 max-w-[200px] truncate" title="{{ $router->last_error }}">{{ $router->last_error }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Router Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Vendor</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->vendor }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Model</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->model ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Location</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->location ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Site</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->site ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Version</span>
                    <span class="text-sm font-medium text-gray-900">{{ $router->version ?: '—' }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-sm text-gray-500">Monitoring</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $router->enable_monitoring ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $router->enable_monitoring ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Backups -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Backups</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $backupStats['total'] }} total, {{ $backupStats['successful'] }} successful, {{ $backupStats['failed'] }} failed
                        @if($backupStats['changed'] > 0)
                            <span class="text-amber-600">, {{ $backupStats['changed'] }} with changes</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if($lastBackup)
                        <div class="text-sm text-gray-500">
                            Last backup: <span class="font-medium text-gray-700">{{ $lastBackup->created_at->diffForHumans() }}</span>
                            @if($lastBackup->status === 'success' && $lastBackup->changed)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 ml-1">Changed</span>
                            @endif
                        </div>
                    @endif
                    <a href="{{ route('backups.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All</a>
                </div>
            </div>
        </div>

        @if($backups->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Changes</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Diff</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($backups as $backup)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    @if($backup->status === 'success')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Success</span>
                                    @elseif($backup->status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Running</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $backup->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if($backup->started_at && $backup->finished_at)
                                        {{ $backup->started_at->diffInSeconds($backup->finished_at) }}s
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $backup->size_bytes ? number_format($backup->size_bytes / 1024, 1) . ' KB' : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($backup->status === 'success')
                                        @if($backup->changed)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Changed</span>
                                        @else
                                            <span class="text-xs text-gray-500">No changes</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($backup->diff && $backup->status === 'success')
                                        <a href="{{ route('backups.show', $backup) }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            +{{ $backup->diff->added_lines }} / -{{ $backup->diff->removed_lines }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($backup->status === 'success' && $backup->path)
                                            <a href="{{ route('backups.show', $backup) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('backups.download', $backup) }}" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                        @endif
                                        @if($backup->status === 'failed')
                                            <form method="POST" action="{{ route('backups.retry', $backup) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Retry">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $backups->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-500">No backups yet for this router.</p>
            </div>
        @endif
    </div>

    <!-- Quick Links -->
    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <a href="{{ route('routers.sessions', $router) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm">
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="font-medium text-gray-700">Sessions</span>
            </a>
            <a href="{{ route('routers.profiles', $router) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span class="font-medium text-gray-700">Profiles</span>
            </a>
            <a href="{{ route('routers.interfaces', $router) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm">
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                </div>
                <span class="font-medium text-gray-700">Interfaces</span>
            </a>
            <a href="{{ route('routers.push-script', $router) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <span class="font-medium text-gray-700">Push Script</span>
            </a>
            <a href="{{ route('routers.logs', $router) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-sm">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="font-medium text-gray-700">Logs</span>
            </a>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="deleteModal.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="deleteModal.show = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Router</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to delete "{{ $router->name }}"? This action cannot be undone.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteRouter()" :disabled="deleteModal.deleting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!deleteModal.deleting">Delete</span>
                        <span x-show="deleteModal.deleting">Deleting...</span>
                    </button>
                    <button @click="deleteModal.show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function routerShow() {
    return {
        status: '{{ $router->status ?? "offline" }}',
        backupRunning: false,
        deleteModal: {
            show: false,
            deleting: false
        },

        async triggerBackup() {
            this.backupRunning = true;
            try {
                const response = await fetch('{{ route("routers.backup", $router) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    const data = await response.json().catch(() => null);
                    alert(data?.message || 'Failed to trigger backup.');
                }
            } catch (error) {
                alert('Failed to trigger backup.');
            } finally {
                this.backupRunning = false;
            }
        },

        async deleteRouter() {
            this.deleteModal.deleting = true;
            try {
                const response = await fetch('{{ route("routers.index") }}/{{ $router->id }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.href = '{{ route("routers.index") }}';
                } else {
                    alert('Error deleting router. Please try again.');
                }
            } catch (error) {
                alert('Error deleting router. Please try again.');
            } finally {
                this.deleteModal.deleting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
