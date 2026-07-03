@php
    $currentRoute = request()->route()->getName() ?? '';
@endphp

<!-- Main Navigation -->
<ul class="space-y-1">
    <!-- Dashboard -->
    <li>
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ $currentRoute === 'dashboard' ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="pt-4">
        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-blue-200">Attention</div>
    </li>
    <li>
        <a href="{{ route('incidents.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ str_starts_with($currentRoute, 'incidents.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 3.7a2 2 0 00-3.4 0z"/></svg>
            <span>Incidents</span>
        </a>
    </li>


    <!-- Routers -->
    <li>
        <a href="{{ route('routers.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'routers.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
            </svg>
            <span>Routers</span>
        </a>
    </li>

    <li class="pt-4">
        <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wider">Protection</div>
    </li>


    <li>
        <a href="{{ route('schedules.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'schedules.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"></path>
            </svg>
            <span>Schedules</span>
        </a>
    </li>
    <li>
        <a href="{{ route('backups.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'backups.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 4v12m0 0l-4-4m4 4l4-4"></path>
            </svg>
            <span>Backups</span>
        </a>
    </li>


    <li>
        <a href="{{ route('diff-alerts.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'diff-alerts.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"></path>
            </svg>
            <span>Diff Alerts</span>
        </a>
    </li>
    <li>
        <a href="{{ route('compliance.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ str_starts_with($currentRoute, 'compliance.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 3 4 6v5c0 5 3.4 8.5 8 10 4.6-1.5 8-5 8-10V6l-8-3Zm-3 9 2 2 4-4"/></svg>
            <span>Compliance</span>
        </a>
    </li>
    <li>
        <a href="{{ route('change-control.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ str_starts_with($currentRoute, 'change-control.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Zm3 10 2 2 4-4"/></svg>
            <span>Change Control</span>
        </a>
    </li>




    {{-- <!-- IP Address Management -->
    <li>
        <a href="{{ route('ipam.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'ipam.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
            </svg>
            <span>IP Address Management</span>
        </a>
    </li> --}}

    {{-- <!-- Bandwidth -->
    <li>
        <a href="{{ route('network.bandwidth') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'network.bandwidth') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span>Bandwidth</span>
        </a>
    </li> --}}

    {{-- <!-- Data Usage -->
    <li>
        <a href="{{ route('network.data-usage') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'network.data-usage') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Data Usage</span>
        </a>
    </li> --}}

    {{-- <!-- Network Status -->
    <li>
        <a href="{{ route('network.status') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'network.status') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
            </svg>
            <span>Network Status</span>
        </a>
    </li> --}}

    <!-- Reports Section -->
    {{-- <li class="pt-4">
        <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wider">Reports</div>
    </li> --}}

    {{-- <!-- Usage Reports -->
    <li>
        <a href="{{ route('reports.usage') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'reports.usage') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Usage Reports</span>
        </a>
    </li> --}}


    <!-- Settings Section -->
    <li class="pt-4">
        <div class="px-3 py-2 text-xs font-semibold text-blue-200 uppercase tracking-wider">Settings</div>
    </li>

    <li>
        <a href="{{ route('billing.subscription') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'billing.subscription') || str_starts_with($currentRoute, 'billing.payment') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h.01M11 15h2m-8 5h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span>Plan &amp; usage</span>
        </a>
    </li>

    <!-- Users -->
    <li>
        <a href="{{ route('admin.tenant.users.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'admin.tenant.users') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span>Users</span>
        </a>
    </li>

    <li>
        <a href="{{ route('password-manager.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'password-manager.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2zm3-10V9a3 3 0 016 0v2H9z"></path>
            </svg>
            <span>Password Manager</span>
        </a>
    </li>
    <li>
        <a href="{{ route('system-health.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ str_starts_with($currentRoute, 'system-health.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 12h4l2-6 4 12 2-6h6"/></svg>
            <span>System Health</span>
        </a>
    </li>

    <!-- Settings -->
    @if(auth()->user()?->isAdmin())
    <li>
        <a href="{{ route('settings.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ str_starts_with($currentRoute, 'settings.') ? 'bg-blue-700 text-white' : 'text-blue-50 hover:bg-blue-700 hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Settings</span>
        </a>
    </li>
    @endif
</ul>
