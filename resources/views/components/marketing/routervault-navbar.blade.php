<header
    class="fixed inset-x-0 top-0 z-40 border-b border-slate-300 bg-white/95 backdrop-blur-sm transition-shadow duration-300"
    x-data="{ mobileNavigationOpen: false, scrolled: window.scrollY > 24 }"
    x-init="scrolled = window.scrollY > 24"
    @scroll.window="scrolled = window.scrollY > 24"
    @keydown.escape.window="mobileNavigationOpen = false"
    :class="{ 'shadow-sm': scrolled }"
>
    <div
        class="mx-auto flex max-w-7xl items-center gap-8 px-5 transition-[height] duration-300 ease-out sm:px-8 lg:px-12 xl:px-16"
        :class="scrolled ? 'h-16' : 'h-20'"
    >
        <a href="{{ route('home') }}" class="flex shrink-0 items-center">
            <x-brand-logo class="transition-[width] duration-300 ease-out"  />
        </a>

        <nav class="ml-auto hidden items-center gap-7 text-sm font-semibold text-slate-950 lg:flex" aria-label="Primary navigation">
            <a href="#old-vs-new" class="transition hover:text-routervault-700">Why RouterVault</a>
            <a href="#how-it-works" class="transition hover:text-routervault-700">How it works</a>
            <a href="#diffs" class="transition hover:text-routervault-700">Config diffs</a>
            <a href="#pricing" class="transition hover:text-routervault-700">Pricing</a>
            <a href="#faq" class="transition hover:text-routervault-700">FAQ</a>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center border border-slate-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:border-slate-950 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:ring-offset-2">
                Login
            </a>
            <a href="{{ route('auth.register') }}" class="inline-flex items-center justify-center bg-routervault-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-routervault-700 focus:outline-none focus:ring-2 focus:ring-routervault-600 focus:ring-offset-2">
                Start Free
            </a>
        </div>

        <button
            type="button"
            class="inline-flex h-11 w-11 items-center justify-center border border-slate-300 text-slate-800 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-routervault-600 lg:hidden"
            @click="mobileNavigationOpen = ! mobileNavigationOpen"
            :aria-expanded="mobileNavigationOpen.toString()"
            aria-controls="routervault-mobile-navigation"
            aria-label="Toggle navigation"
        >
            <svg x-show="! mobileNavigationOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"></path></svg>
            <svg x-show="mobileNavigationOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"></path></svg>
        </button>
    </div>

    <nav
        id="routervault-mobile-navigation"
        x-show="mobileNavigationOpen"
        x-cloak
        x-transition
        class="border-t border-slate-300 bg-white p-5 lg:hidden"
        aria-label="Mobile navigation"
    >
        <div class="grid text-sm font-bold text-slate-950">
            @foreach ([
                ['href' => '#old-vs-new', 'label' => 'Why RouterVault'],
                ['href' => '#how-it-works', 'label' => 'How it works'],
                ['href' => '#diffs', 'label' => 'Config diffs'],
                ['href' => '#pricing', 'label' => 'Pricing'],
                ['href' => '#faq', 'label' => 'FAQ'],
            ] as $item)
                <a href="{{ $item['href'] }}" class="border-b border-slate-200 py-4" @click="mobileNavigationOpen = false">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('auth.login') }}" class="mt-5 inline-flex items-center justify-center border border-slate-400 px-5 py-3.5 text-slate-950">Login</a>
            <a href="{{ route('auth.register') }}" class="mt-5 inline-flex items-center justify-center bg-routervault-600 px-5 py-3.5 text-white">Start Free — No Credit Card</a>
        </div>
    </nav>
</header>
