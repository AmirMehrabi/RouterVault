<header
    class="fixed inset-x-0 top-0 z-40 border-b border-slate-300 bg-white"
    x-data="{ mobileNavigationOpen: false }"
    @keydown.escape.window="mobileNavigationOpen = false"
>
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-8 px-5 sm:px-8 lg:px-12 xl:px-16">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center">
            <img src="{{ asset('assets/Images/Logos/wispaconcept4_symbol_black.svg') }}" alt="WISPA" class="h-9 w-auto">
        </a>

        <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 lg:flex" aria-label="Primary navigation">
            <a href="#old-vs-new" class="transition hover:text-wispa-700">Why WISPA</a>
            <a href="#how-it-works" class="transition hover:text-wispa-700">How it works</a>
            <a href="#diffs" class="transition hover:text-wispa-700">Config diffs</a>
            <a href="#pricing" class="transition hover:text-wispa-700">Pricing</a>
            <a href="#faq" class="transition hover:text-wispa-700">FAQ</a>
        </nav>

        <div class="hidden lg:block">
            <a href="{{ route('auth.register') }}" class="inline-flex items-center justify-center bg-wispa-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-wispa-700 focus:outline-none focus:ring-2 focus:ring-wispa-600 focus:ring-offset-2">
                Start Free
            </a>
        </div>

        <button
            type="button"
            class="inline-flex h-11 w-11 items-center justify-center border border-slate-300 text-slate-800 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-wispa-600 lg:hidden"
            @click="mobileNavigationOpen = ! mobileNavigationOpen"
            :aria-expanded="mobileNavigationOpen.toString()"
            aria-controls="wispa-mobile-navigation"
            aria-label="Toggle navigation"
        >
            <svg x-show="! mobileNavigationOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"></path></svg>
            <svg x-show="mobileNavigationOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"></path></svg>
        </button>
    </div>

    <nav
        id="wispa-mobile-navigation"
        x-show="mobileNavigationOpen"
        x-cloak
        x-transition
        class="border-t border-slate-300 bg-white p-5 lg:hidden"
        aria-label="Mobile navigation"
    >
        <div class="grid text-sm font-bold text-slate-800">
            @foreach ([
                ['href' => '#old-vs-new', 'label' => 'Why WISPA'],
                ['href' => '#how-it-works', 'label' => 'How it works'],
                ['href' => '#diffs', 'label' => 'Config diffs'],
                ['href' => '#pricing', 'label' => 'Pricing'],
                ['href' => '#faq', 'label' => 'FAQ'],
            ] as $item)
                <a href="{{ $item['href'] }}" class="border-b border-slate-200 py-4" @click="mobileNavigationOpen = false">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('auth.register') }}" class="mt-5 inline-flex items-center justify-center bg-wispa-600 px-5 py-3.5 text-white">Start Free — No Credit Card</a>
        </div>
    </nav>
</header>
