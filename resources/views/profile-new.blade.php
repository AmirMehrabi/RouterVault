@php
    $profileBadges = $user->profileBadges();
@endphp

@if($profileBadges->isNotEmpty())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Badges</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($profileBadges as $badge)
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        @if($badge->icon_path)
                            <img src="{{ $badge->icon_path }}" alt="{{ $badge->name }}" class="h-6 w-6">
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 2a1 1 0 01.894.553l1.382 2.8 3.09.449a1 1 0 01.554 1.706l-2.236 2.18.528 3.078a1 1 0 01-1.451 1.054L10 12.347 7.239 13.82a1 1 0 01-1.45-1.054l.527-3.078L4.08 7.508a1 1 0 01.554-1.706l3.09-.449 1.382-2.8A1 1 0 0110 2z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $badge->name }}</p>
                        <p class="text-sm text-gray-600">{{ $badge->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
