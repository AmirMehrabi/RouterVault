@props([
    'items' => [],
])

@php
    $items = collect($items)
        ->filter(fn ($item) => filled($item['label'] ?? null))
        ->values();
@endphp

@if($items->isNotEmpty())
    <nav aria-label="Breadcrumb">
        <ol class="flex min-w-0 items-center gap-1 overflow-x-auto rounded-2xl border border-slate-200/80 bg-slate-50/90 px-2 py-1.5 shadow-sm shadow-slate-200/70 backdrop-blur">
            @foreach($items as $item)
                <li class="flex min-w-0 items-center gap-1">
                    @unless($loop->first)
                        <span aria-hidden="true" class="flex h-7 w-7 items-center justify-center text-slate-300">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 6l6 6-6 6" />
                            </svg>
                        </span>
                    @endunless

                    @if(filled($item['href'] ?? null) && !($item['current'] ?? false))
                        <a
                            href="{{ $item['href'] }}"
                            class="truncate rounded-xl border border-transparent px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:border-slate-200 hover:bg-white hover:text-slate-900"
                        >
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span
                            @if($item['current'] ?? false) aria-current="page" @endif
                            @if(filled($item['xText'] ?? null)) x-text="{{ $item['xText'] }}" @endif
                            class="truncate rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-900 shadow-sm shadow-slate-200/60"
                        >
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
