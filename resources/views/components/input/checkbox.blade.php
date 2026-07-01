@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'value' => null,
    'checked' => false,
    'help' => null,
    'required' => false,
    'xModel' => null,
])

<div>
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input
                type="checkbox"
                id="{{ $id }}"
                name="{{ $name }}"
                value="{{ $value ?? 1 }}"
                {{ $checked ? 'checked' : '' }}
                @if($required) required @endif
                @if($xModel) x-model="{{ $xModel }}" @endif
                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
            >
        </div>
        <div class="ml-3 text-sm">
            @if($slot->isNotEmpty())
            <label for="{{ $id }}" class="font-medium text-gray-700">{{ $slot }}</label>
            @elseif($label)
            <label for="{{ $id }}" class="font-medium text-gray-700">{{ $label }}</label>
            @endif
            @if($help)
            <p class="text-gray-500">{{ $help }}</p>
            @endif
        </div>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
