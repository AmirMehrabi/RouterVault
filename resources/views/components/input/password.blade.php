@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'placeholder' => null,
    'required' => false,
    'minlength' => null,
    'xModel' => null,
    'showToggle' => false,
])

<div class="space-y-2 mb-4">
    @if($label)
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)<span class="text-red-600">*</span>@endif
    </label>
    @endif

    <div class="relative">
        <input
            type="password"
            id="{{ $id }}"
            name="{{ $name }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($minlength) minlength="{{ $minlength }}" @endif
            @if($xModel) x-model="{{ $xModel }}" @endif
            @error($name)
                class="w-full rounded-lg border border-red-500 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
            @else
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
            @enderror
        >

        @if($showToggle)
        <button
            type="button"
            @click="$el.previousElementSibling.type = $el.previousElementSibling.type === 'password' ? 'text' : 'password'"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 transition"
        >
            <i class="fas fa-eye"></i>
        </button>
        @endif
    </div>

    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
