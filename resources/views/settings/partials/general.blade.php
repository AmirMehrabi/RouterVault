<form action="{{ route('settings.update.general') }}" method="POST" class="space-y-5">
    @csrf
    @method('PUT')

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-base font-bold text-slate-900">Organization</h2>
            <p class="mt-1 text-sm text-slate-500">Information actively used to identify this tenant and contact its operators.</p>
        </div>
        <div class="grid gap-6 p-6 md:grid-cols-2">
            <x-ui.input.text
                label="Company name"
                name="company_name"
                :value="old('company_name', $tenant->company_name)"
                :required="true"
                :error="$errors->first('company_name')"
            />
            <x-ui.input.text
                label="Account email"
                name="account_email"
                type="email"
                :value="$tenant->email"
                :readonly="true"
                :disabled="true"
                hint="The tenant account email cannot be changed here."
                class="cursor-not-allowed bg-slate-100 text-slate-500"
            />
            <x-ui.input.text
                label="Phone number"
                name="phone"
                :value="old('phone', $tenant->phone)"
                :error="$errors->first('phone')"
                placeholder="+1 555 000 0000"
            />
            <x-ui.input.text
                label="Country"
                name="country"
                :value="old('country', $tenant->country)"
                :error="$errors->first('country')"
            />
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Application timezone</h2>
                    <p class="mt-1 text-sm text-slate-500">Used throughout the tenant UI, timestamps, scheduled jobs, and all backup schedules.</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="max-w-xl">
                <x-ui.input.select
                    label="Timezone"
                    name="timezone"
                    :options="array_combine($timezones, $timezones)"
                    :value="old('timezone', $tenant->timezone)"
                    placeholder="Select timezone"
                    :required="true"
                    :error="$errors->first('timezone')"
                    hint="Changing this also updates existing backup schedules to use the selected timezone."
                />
            </div>
        </div>
    </section>

    <div class="flex justify-end">
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
            Save settings
        </button>
    </div>
</form>
