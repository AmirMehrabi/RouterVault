@props([
    'current' => 'home',
])

@php
    $navItems = [
        ['key' => 'overview', 'label' => 'معرفی', 'href' => $current === 'home' ? '#overview' : route('home') . '#overview'],
        ['key' => 'capabilities', 'label' => 'قابلیت‌ها', 'href' => $current === 'home' ? '#capabilities' : route('home') . '#capabilities'],
        ['key' => 'pricing', 'label' => 'تعرفه‌ها', 'href' => route('pricing')],
        ['key' => 'about', 'label' => 'درباره ما', 'href' => route('about-us')],
        ['key' => 'contact', 'label' => 'تماس با ما', 'href' => route('contact-us')],
    ];
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-900 text-lg font-black text-white">
                    WI
                </div>
                <div>
                    <p class="text-lg font-black text-slate-950">ویسپا</p>
                    <p class="text-xs font-semibold tracking-[0.22em] text-slate-500">WISPA ISP OPERATIONS</p>
                </div>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 lg:flex">
                @foreach($navItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'transition hover:text-slate-950',
                            'text-slate-950' => $current === $item['key'],
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('auth.login') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                >
                    ورود
                </a>
                <a
                    href="{{ route('auth.register') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800"
                >
                    شروع استفاده
                </a>
            </div>
        </div>

        <nav class="mt-4 flex flex-wrap gap-2 lg:hidden">
            @foreach($navItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    @class([
                        'rounded-full border px-3 py-1.5 text-sm font-bold transition',
                        'border-slate-900 bg-slate-900 text-white' => $current === $item['key'],
                        'border-slate-200 bg-slate-50 text-slate-700 hover:bg-white' => $current !== $item['key'],
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
