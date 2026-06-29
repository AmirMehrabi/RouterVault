<footer class="border-t border-slate-700 bg-slate-950 text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 md:grid-cols-[1fr_auto] md:items-end lg:px-12 xl:px-16">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center">
                <img src="{{ asset('assets/Images/Logos/wispaconcept4_horizontal_color.svg') }}" alt="WISPA" class="h-9 w-auto brightness-0 invert">
            </a>
            <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">Automatic MikroTik backups, version history, and config comparison for network administrators.</p>
        </div>
        <div>
            <nav class="flex flex-wrap gap-x-7 gap-y-3 text-sm font-semibold text-slate-300" aria-label="Footer navigation">
                <a href="#how-it-works" class="transition hover:text-white">How it works</a>
                <a href="#diffs" class="transition hover:text-white">Config diffs</a>
                <a href="#pricing" class="transition hover:text-white">Pricing</a>
                <a href="#faq" class="transition hover:text-white">FAQ</a>
            </nav>
            <p class="mt-5 text-sm text-slate-500 md:text-right">© {{ now()->year }} WISPA. All rights reserved.</p>
        </div>
    </div>
</footer>
