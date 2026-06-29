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
                        <p class="mt-5 text-sm text-slate-500">
                            14-day free trial. No credit card required.
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
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

            <section class="border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid divide-y divide-slate-200 border-y border-slate-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">
                        @php
                            $metrics = [
                                ['value' => '2,400+', 'label' => 'Routers monitored', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" />'],
                                ['value' => '18,000+', 'label' => 'Backups completed', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />'],
                                ['value' => '120+', 'label' => 'Teams trust WISPA', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />'],
                                ['value' => '99.9%', 'label' => 'Service uptime', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />'],
                            ];
                        @endphp
                        @foreach ($metrics as $metric)
                            <div class="flex items-center gap-4 px-6 py-6 lg:px-8">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-wispa-200 text-wispa-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $metric['icon'] !!}</svg>
                                </span>
                                <div>
                                    <p class="text-2xl font-bold text-slate-950">{{ $metric['value'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $metric['label'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="why-wispa" class="scroll-mt-24 border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Router changes are easy to make. Harder to remember.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">
                            Most teams still rely on manual exports, scattered files, chat messages, or memory. That works until it doesn't. WISPA gives every router a clean configuration history, so your team can see what changed, when it changed, and what the previous version looked like.
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
                                @php
                                    $versions = [
                                        ['version' => 'v14', 'date' => 'Today, 14:32', 'lines' => '2,847 lines', 'summary' => '/interface wireless ... /ip address ...', 'active' => true],
                                        ['version' => 'v13', 'date' => 'Yesterday, 09:15', 'lines' => '2,844 lines', 'summary' => '/ip dhcp-server ... /ip firewall ...', 'active' => false],
                                        ['version' => 'v12', 'date' => 'Jun 26, 16:48', 'lines' => '2,841 lines', 'summary' => '/system ntp-client ... /ip dns ...', 'active' => false],
                                        ['version' => 'v11', 'date' => 'Jun 24, 11:03', 'lines' => '2,838 lines', 'summary' => '/interface bridge ... /ip route ...', 'active' => false],
                                    ];
                                @endphp
                                @foreach ($versions as $version)
                                    <div class="relative rounded-lg border bg-white p-4 {{ $version['active'] ? 'border-wispa-400' : 'border-slate-200' }}">
                                        <span class="absolute -left-[2.15rem] top-1/2 h-3 w-3 -translate-y-1/2 rounded-full border-2 {{ $version['active'] ? 'border-wispa-600 bg-wispa-600' : 'border-slate-300 bg-white' }}"></span>
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold {{ $version['active'] ? 'text-wispa-600' : 'text-slate-500' }}">{{ $version['version'] }}</span>
                                                    <span class="text-xs text-slate-400">{{ $version['date'] }}</span>
                                                </div>
                                                <p class="mt-1 text-xs text-slate-400">{{ $version['summary'] }}</p>
                                            </div>
                                            <span class="shrink-0 text-xs text-slate-400">{{ $version['lines'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid divide-y divide-slate-200 border-y border-slate-200 bg-white lg:grid-cols-3 lg:divide-x lg:divide-y-0">
                        @php
                            $features = [
                                [
                                    'title' => 'Automatic Backups',
                                    'copy' => 'WISPA regularly pulls and stores router configuration backups, so your team does not depend on manual exports.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />',
                                ],
                                [
                                    'title' => 'Visual Diffs',
                                    'copy' => 'Compare two versions and quickly see what was added, removed, or changed.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />',
                                ],
                                [
                                    'title' => 'Built for Router Teams',
                                    'copy' => 'Designed for ISPs, NOC teams, and MikroTik consultants who manage many routers.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />',
                                ],
                            ];
                        @endphp
                        @foreach ($features as $feature)
                            <article class="group relative px-1 py-8 sm:px-6 lg:px-8">
                                <span class="absolute left-0 top-0 h-0.5 w-12 bg-wispa-500 transition-all duration-300 group-hover:w-20 lg:left-8"></span>
                                <p class="text-xs font-semibold tracking-[0.16em] text-wispa-600">0{{ $loop->iteration }}</p>
                                <h3 class="mt-4 text-lg font-bold text-slate-950">{{ $feature['title'] }}</h3>
                                <p class="mt-2 min-h-20 text-sm leading-7 text-slate-600">{{ $feature['copy'] }}</p>
                                <div class="mt-6 flex h-14 items-center gap-3" aria-hidden="true">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-wispa-200 text-wispa-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $feature['icon'] !!}</svg>
                                    </span>
                                    <div class="flex-1 space-y-2">
                                        <span class="block h-2 w-3/4 rounded-full bg-wispa-100"></span>
                                        <span class="block h-2 w-1/2 rounded-full bg-wispa-50"></span>
                                    </div>
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
                                [
                                    'title' => 'Connect your routers',
                                    'copy' => 'Add your MikroTik routers to WISPA with the required access details.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />',
                                ],
                                [
                                    'title' => 'WISPA backs them up',
                                    'copy' => 'Backups run automatically on schedule, keeping a clean record of configuration changes.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />',
                                ],
                                [
                                    'title' => 'Compare any version',
                                    'copy' => 'Open the history, choose two versions, and see the difference clearly.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />',
                                ],
                            ];
                        @endphp
                        @foreach ($steps as $index => $step)
                            <article class="relative text-center">
                                <span class="relative z-10 mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-wispa-200 bg-white text-wispa-600 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $step['icon'] !!}</svg>
                                </span>
                                <h3 class="mt-6 text-xl font-bold text-slate-950">{{ $step['title'] }}</h3>
                                <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-slate-600">{{ $step['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="diffs" class="scroll-mt-24 border-b border-slate-200 bg-slate-50 py-20 sm:py-24">
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
                            <div class="space-y-1 px-2 font-mono text-xs sm:px-4">
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">1</span>
                                    <span class="text-slate-500">/interface wireless</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">2</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;set [find] disabled=no</span>
                                </div>
                                <div class="flex gap-2 rounded-md bg-rose-50 px-2 py-1.5">
                                    <span class="w-4 text-right text-rose-400">3</span>
                                    <span class="text-rose-600">-&nbsp;ssid="Office-Network"</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">4</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;frequency=5180</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">5</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;channel-width=20mhz</span>
                                </div>
                                <div class="flex gap-2 rounded-md bg-rose-50 px-2 py-1.5">
                                    <span class="w-4 text-right text-rose-400">6</span>
                                    <span class="text-rose-600">-&nbsp;security-profile="default"</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">7</span>
                                    <span class="text-slate-500">/ip address</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">8</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;add address=10.0.0.1/24</span>
                                </div>
                            </div>
                            <div class="space-y-1 px-2 font-mono text-xs sm:px-4">
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">1</span>
                                    <span class="text-slate-500">/interface wireless</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">2</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;set [find] disabled=no</span>
                                </div>
                                <div class="flex gap-2 rounded-md bg-emerald-50 px-2 py-1.5">
                                    <span class="w-4 text-right text-emerald-500">3</span>
                                    <span class="text-emerald-600">+&nbsp;ssid="Office-5G"</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">4</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;frequency=5180</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">5</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;channel-width=20mhz</span>
                                </div>
                                <div class="flex gap-2 rounded-md bg-emerald-50 px-2 py-1.5">
                                    <span class="w-4 text-right text-emerald-500">6</span>
                                    <span class="text-emerald-600">+&nbsp;security-profile="wpa2"</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">7</span>
                                    <span class="text-slate-500">/ip address</span>
                                </div>
                                <div class="flex gap-2 rounded-md px-2 py-1.5">
                                    <span class="w-4 text-right text-slate-300">8</span>
                                    <span class="text-slate-500">&nbsp;&nbsp;add address=10.0.0.1/24</span>
                                </div>
                            </div>
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
                                ['title' => 'After a RouterOS upgrade', 'copy' => 'Verify that your wireless, firewall, and routing config survived the upgrade intact.'],
                                ['title' => 'Before troubleshooting a customer', 'copy' => 'Check recent configuration changes before debugging a connectivity or performance issue.'],
                                ['title' => 'For MikroTik consultants', 'copy' => 'Keep backup history for every client engagement without managing folders and manual exports.'],
                                ['title' => 'For ISPs managing many towers', 'copy' => 'Compare configurations across similar APs to catch inconsistencies before they cause outages.'],
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

            <section id="audience" class="scroll-mt-24 border-b border-slate-200 bg-slate-50 py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Built for the people who keep networks running.
                        </h2>
                    </div>

                    <div class="mt-12 grid gap-6 sm:grid-cols-3">
                        @php
                            $audiences = [
                                [
                                    'title' => 'ISPs & WISPs',
                                    'copy' => 'Manage hundreds of MikroTik routers from one place. Know exactly what changed, when, and why.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" />',
                                ],
                                [
                                    'title' => 'NOC Teams',
                                    'copy' => 'Monitor, compare, and roll back with confidence. No more guessing what changed on a router overnight.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />',
                                ],
                                [
                                    'title' => 'MikroTik Consultants',
                                    'copy' => 'Keep a clean audit trail for every client. Compare configs across sites and show your work.',
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />',
                                ],
                            ];
                        @endphp
                        @foreach ($audiences as $audience)
                            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-wispa-200 text-wispa-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $audience['icon'] !!}</svg>
                                </span>
                                <h3 class="mt-5 text-xl font-bold text-slate-950">{{ $audience['title'] }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $audience['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                    <div>
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Not another complex NOC dashboard.
                        </h2>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                            WISPA is intentionally focused. It does not try to replace your monitoring stack, billing system, or documentation platform.
                        </p>
                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wispa-100 text-wispa-600">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                                </span>
                                <p class="text-sm font-medium text-slate-700">Automatic, scheduled backups</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wispa-100 text-wispa-600">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                                </span>
                                <p class="text-sm font-medium text-slate-700">Readable version diffs</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wispa-100 text-wispa-600">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                                </span>
                                <p class="text-sm font-medium text-slate-700">Multi-router management</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wispa-100 text-wispa-600">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                                </span>
                                <p class="text-sm font-medium text-slate-700">Built for RouterOS API</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-wispa-600">WISPA does one job well</p>
                        <p class="mt-3 text-xl font-bold leading-8 text-slate-950">
                            Keep MikroTik router configurations backed up, versioned, and easy to compare.
                        </p>
                        <div class="mt-6 flex items-center gap-2" aria-hidden="true">
                            @foreach (range(1, 5) as $index)
                                <span class="h-2 w-2 rounded-full bg-wispa-400"></span>
                                @if ($index < 5)
                                    <span class="h-px flex-1 border-t border-dashed border-wispa-200"></span>
                                @endif
                            @endforeach
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border border-emerald-200 bg-white text-emerald-500 shadow-sm">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="pricing" class="scroll-mt-24 border-b border-slate-200 bg-slate-50 py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Simple pricing for small teams and growing ISPs.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">
                            Start small, add routers as you grow, and keep your configuration history organized from day one.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 sm:grid-cols-3">
                        @php
                            $plans = [
                                [
                                    'name' => 'Starter',
                                    'price' => '$29',
                                    'period' => '/mo',
                                    'copy' => 'For consultants and small teams managing a few routers.',
                                    'features' => ['Up to 10 routers', 'Daily backups', '7-day version history', 'Email alerts'],
                                    'popular' => false,
                                ],
                                [
                                    'name' => 'ISP',
                                    'price' => '$99',
                                    'period' => '/mo',
                                    'copy' => 'For small and medium ISPs that need automatic backups across many routers.',
                                    'features' => ['Up to 100 routers', 'Hourly backups', '90-day version history', 'Slack & email alerts', 'API access'],
                                    'popular' => true,
                                ],
                                [
                                    'name' => 'Custom',
                                    'price' => 'Custom',
                                    'period' => '',
                                    'copy' => 'For larger teams, special deployment needs, or managed service providers.',
                                    'features' => ['Unlimited routers', 'Custom backup schedule', 'Unlimited history', 'Priority support', 'SSO & audit logs'],
                                    'popular' => false,
                                ],
                            ];
                        @endphp
                        @foreach ($plans as $plan)
                            <article class="relative rounded-xl border-2 bg-white p-6 shadow-sm {{ $plan['popular'] ? 'border-wispa-500 shadow-md' : 'border-slate-200' }}">
                                @if ($plan['popular'])
                                    <span class="absolute -top-3 left-6 rounded-full bg-wispa-600 px-3 py-1 text-xs font-bold text-white">Most Popular</span>
                                @endif
                                <h3 class="text-xl font-bold text-slate-950">{{ $plan['name'] }}</h3>
                                <div class="mt-4 flex items-baseline gap-1">
                                    <span class="text-4xl font-bold tracking-tight text-slate-950">{{ $plan['price'] }}</span>
                                    @if ($plan['period'])
                                        <span class="text-sm text-slate-500">{{ $plan['period'] }}</span>
                                    @endif
                                </div>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $plan['copy'] }}</p>
                                <ul class="mt-6 space-y-3">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="flex items-center gap-2 text-sm text-slate-700">
                                            <svg class="h-4 w-4 shrink-0 text-wispa-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                                <a
                                    href="{{ route('auth.register') }}"
                                    class="mt-6 inline-flex w-full items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2 {{ $plan['popular'] ? 'bg-wispa-600 text-white hover:bg-wispa-700' : 'border border-slate-300 text-slate-700 hover:border-slate-400 hover:bg-slate-50' }}"
                                >
                                    Get Started
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="text-3xl font-bold tracking-[-0.03em] text-slate-950 sm:text-4xl">
                            Your router configs are one accident away from gone.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">
                            WISPA keeps every version backed up and readable, so your team can fix problems faster and sleep better at night.
                        </p>
                        <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                            <a
                                href="{{ route('auth.register') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-wispa-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-wispa-700 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                            >
                                Start Backing Up Routers
                            </a>
                            <a
                                href="{{ route('contact-us') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-wispa-300 bg-white px-6 py-3.5 text-sm font-semibold text-wispa-700 transition hover:border-wispa-400 hover:bg-wispa-50 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                            >
                                Contact Us
                            </a>
                        </div>
                        <p class="mt-5 text-sm text-slate-500">14-day free trial. No credit card required.</p>
                    </div>
                </div>
            </section>
        </main>

        <x-marketing.wispa-footer />
    </div>
</body>
</html>
