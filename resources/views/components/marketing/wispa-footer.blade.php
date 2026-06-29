<footer class="bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center">
            <img
                src="{{ asset('assets/Images/Logos/wispaconcept4_horizontal_color.svg') }}"
                alt="WISPA"
                class="h-10 w-auto"
            >
        </a>
        <nav class="flex flex-wrap gap-x-7 gap-y-3 text-sm font-medium text-slate-600" aria-label="Footer navigation">
            <a href="#features" class="transition hover:text-slate-950">Features</a>
            <a href="#how-it-works" class="transition hover:text-slate-950">How it works</a>
            <a href="#use-cases" class="transition hover:text-slate-950">Use cases</a>
            <a href="#pricing" class="transition hover:text-slate-950">Pricing</a>
        </nav>
        <p class="text-sm text-slate-500">© {{ now()->year }} WISPA. All rights reserved.</p>
    </div>
</footer>
