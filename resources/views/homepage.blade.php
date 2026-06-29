<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        name="description"
        content="WISPA automatically backs up MikroTik router configurations, keeps every version, and makes configuration changes easy to compare."
    >
    <title>WISPA | MikroTik Configuration Backups and Version History</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-slate-950 antialiased">
    <div class="min-h-screen">
        <x-marketing.wispa-navbar />

        <main>
            <section class="relative overflow-hidden border-b border-slate-200 bg-white">
                <img
                    src="{{ asset('assets/Images/hero.png') }}"
                    alt=""
                    class="absolute inset-0 hidden h-full w-full object-cover object-center lg:block"
                    aria-hidden="true"
                >
                <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-32 sm:px-6 sm:pt-36 lg:flex lg:min-h-[800px] lg:items-center lg:px-8 lg:py-24">
                    <div class="relative z-10 max-w-2xl lg:w-[52%]">
                        <h1 class="max-w-3xl text-4xl font-bold leading-[1.08] tracking-[-0.04em] text-slate-950 sm:text-5xl lg:text-[3.5rem]">
                            Router config backups, with version history you can actually understand.
                        </h1>
                        <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            WISPA automatically backs up your MikroTik router configurations, keeps a clean history of every version, and makes it easy to compare changes before they become outages.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('auth.register') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-wispa-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-wispa-700 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                            >
                                Start Backing Up Routers
                            </a>
                            <a
                                href="#how-it-works"
                                class="inline-flex items-center justify-center rounded-xl border border-wispa-300 bg-white px-6 py-3.5 text-sm font-semibold text-wispa-700 transition hover:border-wispa-400 hover:bg-wispa-50 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                            >
                                See How It Works
                            </a>
                        </div>
                        <p class="mt-7 text-sm text-slate-500">
                            Built for ISPs, network admins, and MikroTik consultants.
                        </p>
                    </div>

                    <div class="relative mt-8 min-h-[300px] sm:min-h-[430px] lg:hidden">
                        <img
                            src="{{ asset('assets/Images/hero.png') }}"
                            alt="Abstract layered router configuration versions with highlighted additions and removals"
                            class="absolute left-1/2 top-1/2 w-[145%] max-w-none -translate-x-1/2 -translate-y-1/2 [mask-image:radial-gradient(ellipse_at_center,black_68%,transparent_100%)] sm:w-[125%]"
                        >
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-white py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Router changes are easy to make. Harder to remember.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">
                            Most teams still rely on manual exports, scattered files, chat messages, or memory. That works until it doesn’t. WISPA gives every router a clean configuration history, so your team can see what changed, when it changed, and what the previous version looked like.
                        </p>
                    </div>

                    <div class="mt-12 grid divide-y divide-slate-200 border-y border-slate-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">
                        @foreach (['What changed?', 'Who touched this router?', 'Do we have the old config?', 'Can we roll back safely?'] as $question)
                            <div class="flex items-center gap-4 px-4 py-6 lg:px-6">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-wispa-200 text-wispa-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path stroke-linecap="round" d="M9.8 9a2.3 2.3 0 0 1 4.4 1c0 1.7-2.2 1.8-2.2 3.4"></path>
                                        <path stroke-linecap="round" d="M12 17h.01"></path>
                                    </svg>
                                </span>
                                <p class="text-sm font-semibold text-slate-800">{{ $question }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="features" class="scroll-mt-24 border-b border-slate-200 bg-slate-50 py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            A simple workflow for safer router changes.
                        </h2>
                    </div>

                    <div class="mt-12 overflow-hidden rounded-xl border border-slate-200 border-t-2 border-t-wispa-500 bg-white">
                        <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[0.72fr_1.28fr] lg:p-10">
                            <div>
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-wispa-200 text-wispa-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2"></path>
                                        <circle cx="12" cy="12" r="9"></circle>
                                    </svg>
                                </span>
                                <h3 class="mt-5 text-2xl font-bold text-slate-950">Version History</h3>
                                <p class="mt-3 max-w-sm text-sm leading-7 text-slate-600">
                                    Every backup becomes part of a readable timeline. Go back and inspect previous versions anytime.
                                </p>
                            </div>
                            <div class="relative space-y-3 border-l border-slate-200 pl-7">
                                @foreach ([true, false, false, false] as $active)
                                            <div class="relative rounded-lg border bg-white p-4 {{ $active ? 'border-wispa-400' : 'border-slate-200' }}">
                                        <span class="absolute -left-[2.15rem] top-1/2 h-3 w-3 -translate-y-1/2 rounded-full border-2 {{ $active ? 'border-wispa-600 bg-wispa-600' : 'border-slate-300 bg-white' }}"></span>
                                        <div class="flex items-center justify-between gap-6">
                                            <div class="flex flex-1 gap-3">
                                                <span class="h-2 w-20 rounded-full bg-slate-200"></span>
                                                <span class="h-2 w-32 rounded-full bg-slate-100"></span>
                                            </div>
                                            <span class="h-2 w-12 rounded-full bg-slate-100"></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid divide-y divide-slate-200 border-y border-slate-200 bg-white lg:grid-cols-3 lg:divide-x lg:divide-y-0">
                        @php
                            $features = [
                                ['title' => 'Automatic Backups', 'copy' => 'WISPA regularly pulls and stores router configuration backups, so your team does not depend on manual exports.', 'type' => 'backup'],
                                ['title' => 'Visual Diffs', 'copy' => 'Compare two versions and quickly see what was added, removed, or changed.', 'type' => 'diff'],
                                ['title' => 'Built for Router Teams', 'copy' => 'Designed for ISPs, NOC teams, and MikroTik consultants who manage many routers.', 'type' => 'team'],
                            ];
                        @endphp
                        @foreach ($features as $feature)
                            <article class="group relative px-1 py-8 sm:px-6 lg:px-8">
                                <span class="absolute left-0 top-0 h-0.5 w-12 bg-wispa-500 transition-all duration-300 group-hover:w-20 lg:left-8"></span>
                                <p class="text-xs font-semibold tracking-[0.16em] text-wispa-600">0{{ $loop->iteration }}</p>
                                <h3 class="mt-4 text-lg font-bold text-slate-950">{{ $feature['title'] }}</h3>
                                <p class="mt-2 min-h-20 text-sm leading-7 text-slate-600">{{ $feature['copy'] }}</p>
                                <div class="mt-6 flex h-14 items-center gap-2" aria-hidden="true">
                                    @if ($feature['type'] === 'backup')
                                        @foreach (range(1, 6) as $index)
                                            <span class="h-2 w-2 rounded-full {{ $index < 5 ? 'bg-wispa-400' : ($index === 5 ? 'bg-cyan-400' : 'bg-emerald-400') }}"></span>
                                            @if ($index < 6)
                                                <span class="h-px flex-1 bg-slate-200"></span>
                                            @endif
                                        @endforeach
                                    @elseif ($feature['type'] === 'diff')
                                        <div class="w-1/2 space-y-2 border-l-2 border-rose-300 bg-rose-50 p-3">
                                            <span class="block h-2 w-4/5 rounded-full bg-rose-200"></span>
                                            <span class="block h-2 w-3/5 rounded-full bg-rose-300"></span>
                                        </div>
                                        <div class="w-1/2 space-y-2 border-l-2 border-emerald-300 bg-emerald-50 p-3">
                                            <span class="block h-2 w-3/5 rounded-full bg-emerald-300"></span>
                                            <span class="block h-2 w-4/5 rounded-full bg-emerald-200"></span>
                                        </div>
                                    @else
                                        <span class="h-8 w-8 rounded-full bg-wispa-100"></span>
                                        <span class="h-11 w-11 rounded-full bg-wispa-500"></span>
                                        <span class="h-8 w-8 rounded-full bg-wispa-100"></span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="scroll-mt-24 border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            From router to history, automatically.
                        </h2>
                    </div>

                    <div class="relative mt-14 grid gap-10 lg:grid-cols-3 lg:gap-12">
                        <div class="absolute left-[17%] right-[17%] top-6 hidden border-t border-dashed border-wispa-300 lg:block"></div>
                        @php
                            $steps = [
                                ['title' => 'Connect your routers', 'copy' => 'Add your MikroTik routers to WISPA with the required access details.'],
                                ['title' => 'WISPA backs them up', 'copy' => 'Backups run automatically on schedule, keeping a clean record of configuration changes.'],
                                ['title' => 'Compare any version', 'copy' => 'Open the history, choose two versions, and see the difference clearly.'],
                            ];
                        @endphp
                        @foreach ($steps as $index => $step)
                            <article class="relative text-center">
                                <span class="relative z-10 mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-wispa-200 bg-white text-sm font-bold text-wispa-600 shadow-sm">
                                    {{ $index + 1 }}
                                </span>
                                <h3 class="mt-6 text-xl font-bold text-slate-950">{{ $step['title'] }}</h3>
                                <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-600">{{ $step['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-50 py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[0.72fr_1.28fr] lg:px-8">
                    <div>
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            See the change, not just the backup file.
                        </h2>
                        <p class="mt-5 text-lg leading-8 text-slate-600">
                            A backup is useful.<br>
                            <span class="font-semibold text-slate-950">A readable diff is better.</span>
                        </p>
                        <p class="mt-4 text-sm leading-7 text-slate-600">
                            WISPA helps you understand what changed between two router configuration versions without digging through long export files manually.
                        </p>
                        <ul class="mt-8 space-y-4">
                            @foreach (['See added and removed lines', 'Compare any two versions', 'Review changes after maintenance work', 'Investigate unexpected router behavior', 'Keep a cleaner audit trail for your team'] as $item)
                                <li class="flex gap-3 text-sm font-medium text-slate-700">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-wispa-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.2 2.2 4.8-5"></path>
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-slate-200 border-t-2 border-t-wispa-500 bg-white">
                        <div class="grid grid-cols-2 divide-x divide-slate-200 border-b border-slate-200">
                            <div class="px-5 py-4 text-sm font-semibold text-rose-500">Older version</div>
                            <div class="px-5 py-4 text-sm font-semibold text-emerald-600">Newer version</div>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-slate-200 p-3 sm:p-5">
                            @foreach (['removed', 'added'] as $side)
                                <div class="space-y-3 px-2 sm:px-4">
                                    @foreach (range(1, 11) as $line)
                                        @php
                                            $highlighted = in_array($line, [4, 5, 8], true);
                                        @endphp
                                        <div @class([
                                            'flex items-center gap-2 rounded-md px-2 py-1.5',
                                            'bg-rose-50' => $side === 'removed' && $highlighted,
                                            'bg-emerald-50' => $side === 'added' && $highlighted,
                                        ])>
                                            <span @class([
                                                'text-xs font-semibold',
                                                'text-rose-400' => $side === 'removed' && $highlighted,
                                                'text-emerald-500' => $side === 'added' && $highlighted,
                                                'text-transparent' => ! $highlighted,
                                            ])>{{ $side === 'removed' ? '−' : '+' }}</span>
                                            <span @class([
                                                'h-2 rounded-full',
                                                $line % 3 === 0 ? 'w-2/3' : ($line % 2 === 0 ? 'w-4/5' : 'w-1/2'),
                                                'bg-rose-200' => $side === 'removed' && $highlighted,
                                                'bg-emerald-200' => $side === 'added' && $highlighted,
                                                'bg-slate-100' => ! $highlighted,
                                            ])></span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="use-cases" class="scroll-mt-24 border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
                    <div>
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Made for real router work.
                        </h2>
                        <p class="mt-5 max-w-md text-base leading-8 text-slate-600">
                            Practical configuration history for the moments when your team needs a reliable answer quickly.
                        </p>
                    </div>
                    <div class="divide-y divide-slate-200 border-y border-slate-200">
                        @php
                            $useCases = [
                                ['title' => 'After maintenance', 'copy' => 'Check exactly what changed after planned router work.'],
                                ['title' => 'Before troubleshooting', 'copy' => 'Look at recent configuration changes before debugging a customer or network issue.'],
                                ['title' => 'For consultants', 'copy' => 'Keep backup history for multiple clients without managing folders and manual exports.'],
                                ['title' => 'For small ISPs', 'copy' => 'Bring order to router configuration management without buying a heavy enterprise system.'],
                            ];
                        @endphp
                        @foreach ($useCases as $useCase)
                            <article class="grid gap-2 py-6 sm:grid-cols-[0.42fr_0.58fr] sm:gap-8">
                                <h3 class="font-bold text-slate-950">{{ $useCase['title'] }}</h3>
                                <p class="text-sm leading-7 text-slate-600">{{ $useCase['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-50 py-20">
                <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                    <div>
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Not another complex NOC dashboard.
                        </h2>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                            WISPA is intentionally focused. It does not try to replace your monitoring stack, billing system, or documentation platform.
                        </p>
                        <p class="mt-4 text-sm font-semibold text-slate-600">It does one job well:</p>
                        <p class="mt-2 max-w-2xl text-xl font-bold leading-8 text-wispa-700">
                            Keep MikroTik router configurations backed up, versioned, and easy to compare.
                        </p>
                    </div>
                    <div class="flex items-center" aria-hidden="true">
                        @foreach (range(1, 6) as $index)
                            <span class="h-3 w-3 rounded-full {{ $index === 6 ? 'bg-emerald-400' : 'bg-wispa-500' }}"></span>
                            @if ($index < 6)
                                <span class="h-px flex-1 border-t border-dashed border-wispa-200"></span>
                            @endif
                        @endforeach
                        <span class="ml-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-200 bg-white text-emerald-500 shadow-sm">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7 12 3 3 7-7"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </section>

            <section id="pricing" class="scroll-mt-24 border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-10 lg:grid-cols-[0.72fr_1.28fr]">
                        <div>
                            <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                                Simple pricing for small teams and growing ISPs.
                            </h2>
                            <p class="mt-5 max-w-lg text-base leading-8 text-slate-600">
                                Start small, add routers as you grow, and keep your configuration history organized from day one.
                            </p>
                        </div>
                        <div class="grid overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-3 sm:divide-x sm:divide-slate-200">
                            @php
                                $plans = [
                                    ['name' => 'Starter', 'copy' => 'For consultants and small teams managing a few routers.'],
                                    ['name' => 'ISP', 'copy' => 'For small and medium ISPs that need automatic backups across many routers.'],
                                    ['name' => 'Custom', 'copy' => 'For larger teams, special deployment needs, or managed service providers.'],
                                ];
                            @endphp
                            @foreach ($plans as $plan)
                                <article class="relative border-t-2 p-6 text-left {{ $plan['name'] === 'ISP' ? 'border-t-wispa-500 bg-wispa-50/40' : 'border-t-transparent' }}">
                                    <h3 class="text-xl font-bold {{ $plan['name'] === 'ISP' ? 'text-wispa-600' : 'text-slate-950' }}">{{ $plan['name'] }}</h3>
                                    <p class="mt-4 text-sm leading-7 text-slate-600">{{ $plan['copy'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-16 grid items-center gap-8 rounded-xl border border-wispa-200 border-l-4 border-l-wispa-500 bg-wispa-50/60 p-7 sm:p-10 lg:grid-cols-[1fr_auto]">
                        <div>
                            <h2 class="text-2xl font-bold tracking-[-0.02em] text-slate-950 sm:text-3xl">
                                Stop guessing what changed on your routers.
                            </h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                                Let WISPA keep a clean backup history for every router, so your team can work with more confidence.
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('auth.register') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-wispa-600 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-wispa-700 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                            >
                                Start Backing Up Routers
                            </a>
                            <a
                                href="{{ route('contact-us') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-wispa-300 bg-white px-6 py-3.5 text-sm font-semibold text-wispa-700 transition hover:bg-wispa-50 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                            >
                                Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <x-marketing.wispa-footer />
    </div>
</body>
</html>
