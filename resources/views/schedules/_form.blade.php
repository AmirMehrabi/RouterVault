@csrf
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-ui.input.text label="Schedule Name" name="name" :value="old('name', $schedule->name)" :required="true" :error="$errors->first('name')" />
            <x-ui.input.text type="number" label="Retention Count" name="retention_count" :value="old('retention_count', $schedule->retention_count ?? 30)" :required="true" :error="$errors->first('retention_count')" />
            <x-ui.input.text type="number" label="Interval Value" name="interval_value" :value="old('interval_value', $schedule->interval_value ?? 1)" :required="true" :error="$errors->first('interval_value')" />
            <x-ui.input.select label="Interval Unit" name="interval_unit" :options="['minutes' => 'Minutes', 'hours' => 'Hours', 'days' => 'Days', 'weeks' => 'Weeks']" :value="old('interval_unit', $schedule->interval_unit ?? 'days')" :required="true" :error="$errors->first('interval_unit')" />
            <x-ui.input.text label="Timezone" name="timezone" :value="old('timezone', $schedule->timezone ?? config('app.timezone'))" :required="true" :error="$errors->first('timezone')" />
            <x-ui.input.text type="datetime-local" label="Next Run" name="next_run_at" :value="old('next_run_at', optional($schedule->next_run_at)->format('Y-m-d\\TH:i'))" :error="$errors->first('next_run_at')" />
            <div class="flex items-center pt-6">
                <x-ui.input.checkbox label="Enabled" name="is_enabled" :checked="old('is_enabled', $schedule->is_enabled ?? true)" :error="$errors->first('is_enabled')" />
            </div>
        </div>
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Full RouterOS exports may include sensitive credentials. Files are stored on the private local disk and downloaded only through authenticated routes.
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Routers</h3>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach($routers as $router)
                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2">
                    <input type="checkbox" name="router_ids[]" value="{{ $router->id }}" class="rounded border-gray-300 text-blue-600" @checked(in_array($router->id, old('router_ids', $schedule->routers?->pluck('id')->all() ?? [])))>
                    <span>
                        <span class="block text-sm font-medium text-gray-900">{{ $router->name }}</span>
                        <span class="block text-xs text-gray-500">{{ $router->ip_address }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('router_ids') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
