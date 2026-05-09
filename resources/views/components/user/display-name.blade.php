@props([
    'user',
    'class' => '',
    'textClass' => '',
    'iconClass' => 'w-4 h-4 text-sky-500',
])

<span {{ $attributes->class("inline-flex items-center gap-1.5 {$class}") }}>
    <span class="{{ $textClass }}">{{ $user->name }}</span>
    @if($user->hasBadge('verified'))
        <svg class="{{ $iconClass }}" viewBox="0 0 20 20" fill="currentColor" aria-label="Verified badge" role="img">
            <path fill-rule="evenodd" d="M10 1.5a2 2 0 011.734 1.002l.47.806.917.17a2 2 0 011.575 1.575l.17.917.806.47a2 2 0 011.002 1.734 2 2 0 01-1.002 1.734l-.806.47-.17.917a2 2 0 01-1.575 1.575l-.917.17-.47.806A2 2 0 0110 18.5a2 2 0 01-1.734-1.002l-.47-.806-.917-.17a2 2 0 01-1.575-1.575l-.17-.917-.806-.47A2 2 0 012.5 10a2 2 0 011.002-1.734l.806-.47.17-.917a2 2 0 011.575-1.575l.917-.17.47-.806A2 2 0 0110 1.5zm2.354 6.146a.5.5 0 00-.708-.708L9 9.586 8.354 8.94a.5.5 0 10-.708.708l1 1a.5.5 0 00.708 0l3-3z" clip-rule="evenodd" />
        </svg>
    @endif
</span>
