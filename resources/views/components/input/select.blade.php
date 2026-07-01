@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'searchable' => false,
    'xModel' => null,
])

<div class="mb-4">
    @if($label)
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>
    @endif

    @if($searchable)
        @php
            $selectedValue = old($name, $value);
        @endphp

        <div
            class="relative mt-1"
            x-data="{
                open: false,
                search: '',
                selectedLabel: '',
                options: {{ Js::from(collect($options)->map(fn ($optionLabel, $optionValue) => ['value' => (string) $optionValue, 'label' => $optionLabel])->values()) }},
                get filteredOptions() {
                    const query = this.search.toLowerCase().trim();

                    return query === ''
                        ? this.options
                        : this.options.filter(option => option.label.toLowerCase().includes(query));
                },
                syncSelection() {
                    const selectedOption = this.options.find(option => option.value === this.$refs.select.value);
                    this.selectedLabel = selectedOption?.label ?? '';
                    this.search = this.selectedLabel;
                },
                selectOption(option) {
                    this.$refs.select.value = option.value;
                    this.$refs.select.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.select.dispatchEvent(new Event('change', { bubbles: true }));
                    this.selectedLabel = option.label;
                    this.search = option.label;
                    this.open = false;
                },
                close() {
                    this.open = false;
                    this.search = this.selectedLabel;
                }
            }"
            x-init="$nextTick(() => syncSelection())"
            @click.outside="close()"
            @keydown.escape.prevent="close()"
        >
            <input
                type="search"
                id="{{ $id }}"
                x-model="search"
                @focus="open = true; search = ''"
                @input="open = true"
                @keydown.down.prevent="open = true"
                autocomplete="off"
                @if($required) required @endif
                placeholder="{{ $placeholder ?? 'Search options' }}"
                @error($name)
                    class="w-full rounded-lg border border-red-500 bg-white px-4 py-3 pr-11 text-gray-900 placeholder-gray-500 shadow-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
                @else
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-11 text-gray-900 placeholder-gray-500 shadow-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
                @enderror
                role="combobox"
                aria-autocomplete="list"
                :aria-expanded="open.toString()"
                aria-controls="{{ $id }}_options"
            >

            <button
                type="button"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-500"
                @click="open = ! open; if (open) { search = ''; $nextTick(() => $el.previousElementSibling.focus()); }"
                tabindex="-1"
                aria-label="Toggle {{ strtolower($label ?? 'select') }} options"
            >
                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>

            <select
                x-ref="select"
                id="{{ $id }}_select"
                name="{{ $name }}"
                @if($xModel) x-model="{{ $xModel }}" @endif
                class="sr-only"
                tabindex="-1"
                aria-hidden="true"
            >
                @if($placeholder)
                    <option value="">{{ $placeholder }}</option>
                @endif

                @foreach($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" {{ (string) $selectedValue === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                @endforeach
            </select>

            <div
                id="{{ $id }}_options"
                x-show="open"
                x-cloak
                x-transition.opacity
                class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
                role="listbox"
            >
                <template x-for="option in filteredOptions" :key="option.value">
                    <button
                        type="button"
                        class="flex w-full px-4 py-2.5 text-left text-sm text-gray-900 transition hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
                        @click="selectOption(option)"
                        role="option"
                        x-text="option.label"
                    ></button>
                </template>

                <p x-show="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500">No matching options.</p>
            </div>
        </div>
    @else
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($xModel) x-model="{{ $xModel }}" @endif
        @error($name)
            class="mt-1 block w-full rounded-lg border border-red-500 bg-white px-4 py-3 text-gray-900 shadow-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
        @else
            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
        @enderror
    >
        @if($placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" {{ old($name, $value) === $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
