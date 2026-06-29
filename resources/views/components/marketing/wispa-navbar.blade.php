<header
    class="fixed inset-x-0 top-0 z-40"
    x-data="{ mobileNavigationOpen: false, compact: false }"
    x-init="compact = window.scrollY > 32"
    @scroll.window="compact = window.scrollY > 32"
    @keydown.escape.window="mobileNavigationOpen = false"
>
    <div
        class="mx-auto max-w-7xl px-3 pt-4 transition-all duration-300 sm:px-6 lg:px-8"
        :style="compact ? { paddingTop: '0.5rem' } : {}"
    >
        <div
            class="flex items-center justify-between gap-5 rounded-full border border-white/70 bg-white/70 px-3 shadow-sm shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 sm:px-4"
            :class="compact ? 'h-14 border-slate-200/80 bg-white/90 shadow-md' : 'h-18'"
        >
            <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                <img
                    src="{{ asset('assets/Images/Logos/wispaconcept4_horizontal_color.svg') }}"
                    alt="WISPA"
                    class="w-auto transition-all duration-300"
                    :class="compact ? 'h-8' : 'h-10'"
                >
            </a>

            <nav class="hidden items-center gap-1 rounded-full border border-white/80 bg-white/35 p-1 text-sm font-medium text-slate-600 lg:flex" aria-label="Primary navigation">
                <a href="#features" class="rounded-full px-4 py-2 transition hover:bg-white/80 hover:text-slate-950">Features</a>
                <a href="#how-it-works" class="rounded-full px-4 py-2 transition hover:bg-white/80 hover:text-slate-950">How it works</a>
                <a href="#use-cases" class="rounded-full px-4 py-2 transition hover:bg-white/80 hover:text-slate-950">Use cases</a>
                <a href="#pricing" class="rounded-full px-4 py-2 transition hover:bg-white/80 hover:text-slate-950">Pricing</a>
            </nav>

            <div class="hidden lg:block">
                <a
                    href="{{ route('auth.register') }}"
                    class="inline-flex items-center justify-center rounded-full border border-wispa-300 bg-white/25 px-5 py-2.5 text-sm font-semibold text-wispa-700 transition hover:border-wispa-400 hover:bg-white/80 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2"
                >
                    Start Backing Up Routers
                </a>
            </div>

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white/30 text-slate-700 transition hover:bg-white/80 focus:outline-none focus:ring-2 focus:ring-wispa-600 lg:hidden"
                @click="mobileNavigationOpen = ! mobileNavigationOpen"
                :aria-expanded="mobileNavigationOpen.toString()"
                aria-controls="wispa-mobile-navigation"
                aria-label="Toggle navigation"
            >
                <svg x-show="! mobileNavigationOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"></path>
                </svg>
                <svg x-show="mobileNavigationOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"></path>
                </svg>
            </button>
        </div>

        <nav
            id="wispa-mobile-navigation"
            x-show="mobileNavigationOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="mt-2 rounded-3xl border border-slate-200 bg-white/95 p-3 shadow-lg backdrop-blur-xl lg:hidden"
            aria-label="Mobile navigation"
        >
            <div class="grid gap-1 text-sm font-medium text-slate-700">
                @foreach ([
                    ['href' => '#features', 'label' => 'Features'],
                    ['href' => '#how-it-works', 'label' => 'How it works'],
                    ['href' => '#use-cases', 'label' => 'Use cases'],
                    ['href' => '#pricing', 'label' => 'Pricing'],
                ] as $item)
                    <a href="{{ $item['href'] }}" class="rounded-full px-4 py-2.5 hover:bg-slate-100" @click="mobileNavigationOpen = false">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('auth.register') }}" class="mt-2 inline-flex items-center justify-center rounded-full border border-wispa-300 bg-wispa-50 px-5 py-3 text-sm font-semibold text-wispa-700">
                    Start Backing Up Routers
                </a>
            </div>
        </nav>
    </div>
</header>
