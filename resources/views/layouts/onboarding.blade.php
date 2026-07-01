<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - RouterVault</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Images/Logos/routervault_symbol_color.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-950 antialiased">
    @php
        $steps = [
            ['number' => 1, 'label' => 'Plan', 'optional' => false],
            ['number' => 2, 'label' => 'Payment', 'optional' => false],
            ['number' => 3, 'label' => 'Router', 'optional' => true],
            ['number' => 4, 'label' => 'Backups', 'optional' => true],
            ['number' => 5, 'label' => 'Done', 'optional' => false],
        ];
        $activeNumber = $currentStep->number();
    @endphp

    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-18 max-w-6xl items-center justify-between px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3 font-bold text-slate-950">
                    <x-brand-logo variant="symbol" class="h-9" />
                    <span>RouterVault</span>
                </a>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950">Sign out</button>
                </form>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-5 py-8 sm:px-8 sm:py-12">
            <nav aria-label="Onboarding progress" class="mb-10">
                <ol class="grid grid-cols-5">
                    @foreach($steps as $step)
                        @php
                            $isComplete = $step['number'] < $activeNumber;
                            $isCurrent = $step['number'] === $activeNumber;
                        @endphp
                        <li class="relative flex flex-col items-center text-center">
                            @if(!$loop->last)
                                <span class="absolute left-1/2 top-4 h-px w-full {{ $step['number'] < $activeNumber ? 'bg-emerald-500' : 'bg-slate-200' }}" aria-hidden="true"></span>
                            @endif
                            <span @class([
                                'relative z-10 flex h-8 w-8 items-center justify-center rounded-full border text-xs font-bold',
                                'border-emerald-600 bg-emerald-600 text-white' => $isComplete,
                                'border-blue-600 bg-blue-600 text-white ring-4 ring-blue-100' => $isCurrent,
                                'border-slate-300 bg-white text-slate-500' => !$isComplete && !$isCurrent,
                            ])>
                                @if($isComplete)
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" /></svg>
                                @else
                                    {{ $step['number'] }}
                                @endif
                            </span>
                            <span class="mt-2 text-xs font-semibold {{ $isCurrent ? 'text-blue-700' : 'text-slate-600' }}">{{ $step['label'] }}</span>
                            @if($step['optional'])
                                <span class="mt-1 hidden text-[0.65rem] text-slate-400 sm:block">Optional</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>

            @if(session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    Please review the highlighted fields and try again.
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
