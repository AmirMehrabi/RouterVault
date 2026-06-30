<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RouterVault') - ISP Management Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Images/Logos/routervault_symbol_color.png') }}">
                @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center">
                    <x-brand-logo class="h-14" />
                </a>
                <p class="mt-2 text-gray-600 text-sm">Manage your MikroTik Routers</p>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="ml-auto"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button @click="show = false" class="ml-auto"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            @endif

            <!-- Content -->
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
