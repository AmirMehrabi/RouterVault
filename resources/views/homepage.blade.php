<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        name="description"
        content="RouterVault automatically backs up MikroTik router configurations, keeps every version, and makes configuration changes easy to compare."
    >
    <title>RouterVault | MikroTik Configuration Backups and Version History</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Images/Logos/routervault_symbol_color.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-slate-950 antialiased">
    <div class="min-h-screen overflow-hidden">
        <x-marketing.routervault-navbar />

        <main>
            <section class="border-b border-slate-300 pt-28 sm:pt-32">
                <div class="mx-auto grid max-w-7xl lg:grid-cols-[1.08fr_0.92fr]">
                    <div class="flex flex-col justify-center px-5 py-16 sm:px-8 sm:py-20 lg:min-h-[650px] lg:border-r lg:border-slate-300 lg:px-12 xl:px-16">
                        <h1 class="max-w-3xl text-[3.4rem] font-bold leading-[0.92] tracking-[-0.065em] text-slate-950 sm:text-7xl lg:text-[5.8rem]">
                            One router<br>is fine.<br><span class="text-routervault-600">Fifty is chaos.</span>
                        </h1>
                        <p class="mt-8 max-w-xl text-lg leading-8 text-slate-900 sm:text-xl">
                            RouterVault keeps every MikroTik config backed up, versioned, and ready to roll back — so 2am emergencies don't become 2am disasters.
                        </p>
                        <div class="mt-10 flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                            <a
                                href="{{ route('auth.register') }}"
                                class="inline-flex min-h-13 items-center justify-center bg-routervault-600 px-7 py-3.5 text-sm font-bold text-white transition hover:bg-routervault-700 focus:outline-none focus:ring-2 focus:ring-routervault-600 focus:ring-offset-2"
                            >
                                Start Free — No Credit Card
                            </a>
                            <a
                                href="#how-it-works"
                                class="group inline-flex items-center gap-2 py-3 text-sm font-bold text-slate-950 transition hover:text-routervault-700 focus:outline-none focus:ring-2 focus:ring-routervault-600 focus:ring-offset-4"
                            >
                                See How It Works
                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center bg-slate-50 px-5 py-12 sm:px-8 lg:px-12">
                        <div class="w-full border border-slate-700 bg-slate-950 text-slate-300 shadow-[12px_12px_0_#bfdbfe]">
                            <div class="flex items-center justify-between border-b border-slate-700 px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                                    <span class="font-mono text-xs font-bold uppercase tracking-[0.16em] text-slate-100">Backup status</span>
                                </div>
                                <span class="font-mono text-xs text-slate-500">LIVE</span>
                            </div>
                            <div class="p-5 font-mono text-xs leading-7 sm:p-8 sm:text-sm">
                                <div class="grid grid-cols-[1fr_auto] gap-x-8 border-b border-slate-800 pb-6">
                                    <span class="text-slate-500">routers online</span><span class="text-emerald-400">48 / 50</span>
                                    <span class="text-slate-500">backed up today</span><span class="text-white">48</span>
                                    <span class="text-slate-500">changes detected</span><span class="text-amber-300">7</span>
                                </div>
                                <div class="pt-6">
                                    <p class="mb-3 text-slate-500"># last change</p>
                                    <div class="grid grid-cols-[5rem_1fr] gap-x-5">
                                        <span class="text-slate-500">router</span><span class="text-white">BR-01</span>
                                        <span class="text-slate-500">user</span><span class="text-white">admin</span>
                                        <span class="text-slate-500">time</span><span class="text-white">02:14:17</span>
                                        <span class="text-slate-500">change</span><span class="text-rose-300">+ firewall rule</span>
                                        <span class="text-slate-500">backup</span><span class="text-routervault-300">v2025.05.20-021417</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-slate-700 bg-slate-900 px-5 py-4 font-mono text-xs text-slate-400 sm:px-8">
                                Next scheduled backup in <span class="text-white">04:32</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-300 bg-slate-950 text-white">
                <div class="mx-auto grid max-w-7xl lg:grid-cols-[0.72fr_1.28fr]">
                    <div class="border-b border-slate-700 px-5 py-14 sm:px-8 lg:border-b-0 lg:border-r lg:px-12 lg:py-20 xl:px-16">
                        <p class="text-3xl font-bold leading-tight tracking-[-0.04em] sm:text-4xl">
                            The outage isn't the worst part.
                        </p>
                        <p class="mt-5 text-base leading-7 text-slate-400">Not knowing what changed is.</p>
                    </div>
                    <div class="grid gap-8 px-5 py-14 text-base leading-8 text-slate-300 sm:px-8 md:grid-cols-2 lg:px-12 lg:py-20">
                        <div>
                            <p>You upgrade RouterOS at 10pm. Everything looks fine. At 6am, half your APs are offline. The config is gone. You have no previous version.</p>
                            <p class="mt-6 text-lg font-bold text-white">Now you're rebuilding from memory — and your customers are calling.</p>
                        </div>
                        <div>
                            <p>Someone changed the firewall rules last night. Nobody logged it. Nobody remembers. You're staring at a live production router with no idea what changed or why.</p>
                            <p class="mt-6 border-l-2 border-routervault-400 pl-5 font-bold text-white">This isn't a technology problem. It's a systems problem.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="old-vs-new" class="scroll-mt-20 border-b border-slate-300 py-20 sm:py-28">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12 xl:px-16">
                    <div class="flex items-end justify-between gap-8 border-b-2 border-slate-950 pb-6">
                        <h2 class="max-w-3xl text-4xl font-bold tracking-[-0.045em] sm:text-5xl">The old way vs. the new way.</h2>
                        <span class="hidden font-mono text-xs uppercase tracking-[0.18em] text-slate-500 sm:block">A better operating system</span>
                    </div>
                    <div class="grid lg:grid-cols-2">
                        <div class="border-b border-slate-300 py-10 lg:border-b-0 lg:border-r lg:pr-12">
                            <p class="font-mono text-xs font-bold uppercase tracking-[0.18em] text-rose-600">The old way</p>
                            <ul class="mt-8 space-y-0 divide-y divide-slate-200 border-y border-slate-200">
                                @foreach ([
                                    'Manual /export files scattered across desktops',
                                    '"Who changed this?" with no answer',
                                    'Winbox open on one laptop, one tech, one location',
                                    'Hope nothing breaks',
                                ] as $item)
                                    <li class="flex gap-4 py-5 text-slate-600">
                                        <span class="font-mono text-rose-500">—</span><span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="mt-8 border border-rose-300 bg-rose-50 p-5 font-mono text-sm leading-6 text-rose-900">Slow, inconsistent, and one mistake away from an outage.</p>
                        </div>
                        <div class="py-10 lg:pl-12">
                            <p class="font-mono text-xs font-bold uppercase tracking-[0.18em] text-routervault-600">With RouterVault</p>
                            <ul class="mt-8 space-y-0 divide-y divide-slate-200 border-y border-slate-200">
                                @foreach ([
                                    'Automatic backups on a schedule, every time',
                                    'Every version tagged with who and when',
                                    'Web dashboard, access from anywhere',
                                    'Roll back any version in seconds',
                                ] as $item)
                                    <li class="flex gap-4 py-5 font-semibold text-slate-900">
                                        <span class="font-mono text-routervault-600">✓</span><span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="mt-8 border border-routervault-300 bg-routervault-50 p-5 font-mono text-sm leading-6 text-routervault-900">Always current. Always accountable. Always recoverable.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="scroll-mt-20 border-b border-slate-300 bg-slate-50 py-20 sm:py-28">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12 xl:px-16">
                    <h2 class="text-4xl font-bold tracking-[-0.045em] sm:text-5xl">Three steps. That's it.</h2>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">No agents to install. No complex setup. Connect your routers and RouterVault handles the rest.</p>
                    <div class="mt-12 grid border-y-2 border-slate-950 md:grid-cols-3">
                        @foreach ([
                            ['01', 'Connect your routers', 'Add your MikroTik routers via API. Takes about 30 seconds per router.'],
                            ['02', 'RouterVault backs them up', 'Backups run automatically on schedule. Every change is captured.'],
                            ['03', 'Compare and roll back', 'See what changed between versions. Revert in one click when something goes wrong.'],
                        ] as [$number, $title, $description])
                            <article class="py-9 md:px-8 md:first:pl-0 md:last:pr-0 md:not-last:border-r md:not-last:border-slate-300">
                                <span class="block text-6xl font-bold tracking-[-0.06em] text-routervault-600">{{ $number }}</span>
                                <h3 class="mt-8 max-w-xs text-xl font-bold tracking-tight">{{ $title }}</h3>
                                <p class="mt-3 max-w-sm text-sm leading-7 text-slate-600">{{ $description }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="diffs" class="scroll-mt-20 border-b border-slate-300 py-20 sm:py-28">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12 xl:px-16">
                    <div class="grid items-end gap-6 lg:grid-cols-[1fr_0.7fr]">
                        <h2 class="max-w-3xl text-4xl font-bold tracking-[-0.045em] sm:text-5xl">See the change, not just the backup.</h2>
                        <p class="max-w-lg text-base leading-8 text-slate-600 lg:justify-self-end">A backup is useful. <span class="font-bold text-slate-950">A readable diff is better.</span> RouterVault shows exactly what changed — line by line.</p>
                    </div>
                    <div class="mt-12 border border-slate-700 bg-slate-950 text-slate-300">
                        <div class="grid grid-cols-2 border-b border-slate-700 font-mono text-[0.65rem] sm:text-xs">
                            <div class="px-3 py-4 sm:px-6"><span class="text-slate-500">OLDER</span> &nbsp; v2025.05.19</div>
                            <div class="border-l border-slate-700 px-3 py-4 sm:px-6"><span class="text-slate-500">NEWER</span> &nbsp; v2025.05.20</div>
                        </div>
                        <div class="grid grid-cols-2 font-mono text-[0.56rem] leading-5 sm:text-xs sm:leading-6">
                            <div class="overflow-hidden py-4 sm:py-6">
                                @foreach ([
                                    ['1', '/ip firewall filter', ''],
                                    ['2', 'add chain=input action=accept', ''],
                                    ['3', 'drop in-interface=ether1', 'removed'],
                                    ['4', 'drop connection-state=invalid', ''],
                                    ['5', '/ip service', ''],
                                    ['6', 'set ssh port=2222', 'removed'],
                                ] as [$line, $code, $state])
                                    <div @class(['flex min-w-max gap-3 px-3 sm:px-6', 'bg-rose-950/70 text-rose-200' => $state === 'removed'])><span class="w-4 text-right text-slate-600">{{ $line }}</span><span>{{ $state === 'removed' ? '-' : ' ' }} {{ $code }}</span></div>
                                @endforeach
                            </div>
                            <div class="overflow-hidden border-l border-slate-700 py-4 sm:py-6">
                                @foreach ([
                                    ['1', '/ip firewall filter', ''],
                                    ['2', 'add chain=input action=accept', ''],
                                    ['3', 'accept in-interface=ether1', 'added'],
                                    ['4', 'drop connection-state=invalid', ''],
                                    ['5', '/ip service', ''],
                                    ['6', 'set winbox disabled=yes', 'added'],
                                ] as [$line, $code, $state])
                                    <div @class(['flex min-w-max gap-3 px-3 sm:px-6', 'bg-emerald-950/70 text-emerald-200' => $state === 'added'])><span class="w-4 text-right text-slate-600">{{ $line }}</span><span>{{ $state === 'added' ? '+' : ' ' }} {{ $code }}</span></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex justify-end border-t border-slate-700 p-4 sm:px-6">
                            <span class="font-mono text-xs font-bold text-routervault-300">2 changes found</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="benefits" class="scroll-mt-20 border-b border-slate-300">
                <div class="mx-auto grid max-w-7xl lg:grid-cols-[0.6fr_1.4fr]">
                    <div class="border-b border-slate-300 bg-routervault-600 px-5 py-14 text-white sm:px-8 lg:border-b-0 lg:border-r lg:px-12 lg:py-20 xl:px-16">
                        <h2 class="text-4xl font-bold leading-[1.05] tracking-[-0.045em] sm:text-5xl">Built for network admins. Designed for real life.</h2>
                    </div>
                    <div class="grid sm:grid-cols-2">
                        @foreach ([
                            ['Sleep better', 'Every config is backed up before you touch it.'],
                            ['Blame less', 'See exactly who changed what and when.'],
                            ['Fix faster', 'Compare any two versions and revert the bad lines.'],
                            ['Work anywhere', 'Check a config from your phone or laptop.'],
                            ['Scale confidently', 'Manage 10 or 1000 routers the same way.'],
                            ['Keep control', 'Your history stays searchable and recoverable.'],
                        ] as $index => [$title, $description])
                            <article @class([
                                'min-h-44 border-slate-300 p-7 sm:p-9',
                                'border-b',
                                'sm:border-r' => $index % 2 === 0,
                                'sm:border-b-0' => $index > 3,
                            ])>
                                <span class="font-mono text-xs font-bold text-routervault-600">0{{ $index + 1 }}</span>
                                <h3 class="mt-5 text-lg font-bold">{{ $title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="pricing" class="scroll-mt-20 border-b border-slate-300 bg-slate-50 py-20 sm:py-28">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12 xl:px-16">
                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                        <div>
                            <h2 class="text-4xl font-bold tracking-[-0.045em] sm:text-5xl">Start free. Pay when you're ready.</h2>
                            <p class="mt-5 max-w-md text-lg leading-8 text-slate-600">One router on us. Forever. When you need more, the pricing is simple.</p>
                            <a href="{{ route('auth.register') }}" class="mt-8 inline-flex bg-routervault-600 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-routervault-700">Create Free Account</a>
                        </div>
                        <div class="border-t-2 border-slate-950">
                            @foreach ([
                                ['Free', '1 router, 7-day history', '€0', ''],
                                ['Starter', '3 routers, 30-day history', '€9', '/mo'],
                                ['Operator', '10 routers, 180-day history', '€19', '/mo'],
                                ['Extra routers', 'Simple expansion, per router', '€2', '/router/mo'],
                            ] as [$name, $detail, $price, $period])
                                <div class="grid grid-cols-[1fr_auto] items-center gap-5 border-b border-slate-300 py-6">
                                    <div><p class="font-bold">{{ $name }}</p><p class="mt-1 text-sm text-slate-500">{{ $detail }}</p></div>
                                    <p class="text-3xl font-bold tracking-tight">{{ $price }}<span class="text-xs font-normal text-slate-500">{{ $period }}</span></p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="faq" class="scroll-mt-20 border-b border-slate-300 py-20 sm:py-28" x-data="{ open: 0 }">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 sm:px-8 lg:grid-cols-[0.65fr_1.35fr] lg:px-12 xl:px-16">
                    <h2 class="text-4xl font-bold tracking-[-0.045em] sm:text-5xl">Questions?<br>Answers.</h2>
                    <div class="border-t-2 border-slate-950">
                        @foreach ([
                            ['Is my data secure?', 'Yes. Every tenant is fully isolated. Backups are stored separately and accessed only via API — no open ports, no exposed services.'],
                            ['Does it work with RouterOS v6 and v7?', 'Yes, both. RouterVault connects via the RouterOS API, which is available on all modern versions.'],
                            ['What if I need more than 10 routers?', 'Add extra routers at €2 per router per month. No limits, no tiers to jump between.'],
                            ['Can I self-host?', 'Not yet, but it is on the roadmap. The cloud version is the fastest way to get started.'],
                            ['What happens to my configs if I cancel?', 'You can export all your data at any time. Your configs are yours.'],
                        ] as $index => [$question, $answer])
                            <div class="border-b border-slate-300">
                                <button type="button" class="flex w-full items-center justify-between gap-6 py-6 text-left font-bold" @click="open = open === {{ $index }} ? -1 : {{ $index }}" :aria-expanded="(open === {{ $index }}).toString()">
                                    <span>{{ $question }}</span>
                                    <svg class="h-5 w-5 shrink-0 transition-transform" :class="open === {{ $index }} && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
                                </button>
                                <div x-show="open === {{ $index }}" x-transition.opacity class="pb-6 pr-10 text-sm leading-7 text-slate-600">{{ $answer }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-routervault-600 text-white">
                <div class="mx-auto grid max-w-7xl items-center gap-8 px-5 py-16 sm:px-8 lg:grid-cols-[1fr_auto] lg:px-12 xl:px-16">
                    <div>
                        <h2 class="text-4xl font-bold tracking-[-0.045em] sm:text-5xl">Stop hoping nothing breaks.</h2>
                        <p class="mt-4 text-base text-routervault-100">Start backing up your MikroTik configs for free. No credit card required.</p>
                    </div>
                    <a href="{{ route('auth.register') }}" class="inline-flex min-h-13 items-center justify-center bg-white px-7 py-3.5 text-sm font-bold text-routervault-700 transition hover:bg-routervault-50">Create Free Account</a>
                </div>
            </section>
        </main>

        <x-marketing.routervault-footer />
    </div>
</body>
</html>
