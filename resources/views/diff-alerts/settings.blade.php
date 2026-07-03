@extends('layouts.admin')

@section('title', 'Diff Alert Settings')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'href' => route('dashboard')],
        ['label' => 'Diff Alerts', 'href' => route('diff-alerts.index')],
        ['label' => 'Settings', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="mx-auto max-w-5xl space-y-5 pb-10">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950">Alert settings</h1>
            <p class="mt-1 text-sm text-slate-500">Control which RouterOS configuration changes create alerts.</p>
        </div>
        <a href="{{ route('diff-alerts.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
            Back to alerts
        </a>
    </header>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('diff-alerts.settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-base font-bold text-slate-900">General behavior</h2>
                <p class="mt-1 text-sm text-slate-500">The master switch and normalization rules apply before an alert is created.</p>
            </div>
            <div class="divide-y divide-slate-100">
                <label class="flex cursor-pointer items-start justify-between gap-6 px-6 py-5">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Enable configuration change alerts</span>
                        <span class="mt-1 block text-sm text-slate-500">When disabled, backups and diffs continue, but no new alerts are generated.</span>
                    </div>
                    <input type="hidden" name="is_enabled" value="0">
                    <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $setting->is_enabled)) class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </label>
                <label class="flex cursor-pointer items-start justify-between gap-6 px-6 py-5">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Ignore blank-line changes</span>
                        <span class="mt-1 block text-sm text-slate-500">Do not alert when the only difference is added or removed empty lines.</span>
                    </div>
                    <input type="hidden" name="ignore_blank_lines" value="0">
                    <input type="checkbox" name="ignore_blank_lines" value="1" @checked(old('ignore_blank_lines', $setting->ignore_blank_lines)) class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </label>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M4 12h10M4 18h7"/></svg>
                    </div>
                    <div><h2 class="text-sm font-bold text-slate-900">Ignored sections</h2><p class="mt-1 text-xs leading-5 text-slate-500">RouterOS sections that should not produce alerts. Enter one path per line.</p></div>
                </div>
                <div class="mt-5">
                    <x-ui.input.textarea
                        label="Section paths"
                        name="ignored_sections"
                        :value="old('ignored_sections', implode(PHP_EOL, $setting->ignored_sections ?? []))"
                        :error="$errors->first('ignored_sections')"
                        hint="Example: /system clock or /interface"
                        rows="9"
                        class="font-mono text-xs"
                    />
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="m15 5 4 4L9 19H5v-4L15 5zM13 7l4 4"/></svg>
                    </div>
                    <div><h2 class="text-sm font-bold text-slate-900">Ignored keywords</h2><p class="mt-1 text-xs leading-5 text-slate-500">Suppress alerts when a changed line contains one of these values.</p></div>
                </div>
                <div class="mt-5">
                    <x-ui.input.textarea
                        label="Keywords"
                        name="ignored_keywords"
                        :value="old('ignored_keywords', implode(PHP_EOL, $setting->ignored_keywords ?? []))"
                        :error="$errors->first('ignored_keywords')"
                        hint="One case-insensitive keyword per line."
                        rows="9"
                        class="font-mono text-xs"
                    />
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-base font-bold text-slate-900">Notification delivery</h2>
                <p class="mt-1 text-sm text-slate-500">Channels are used when they are included in the tenant’s active plan.</p>
            </div>
            <div class="grid gap-6 p-6 lg:grid-cols-2">
                <x-ui.input.textarea
                    label="Email recipients"
                    name="email_recipients"
                    :value="old('email_recipients', implode(PHP_EOL, $setting->email_recipients ?? []))"
                    :error="$errors->first('email_recipients')"
                    hint="One address per line. The tenant account email is used when this is empty."
                    rows="5"
                />
                <div class="space-y-5">
                    <x-ui.input.text
                        label="Telegram chat ID"
                        name="telegram_chat_id"
                        :value="old('telegram_chat_id', $setting->telegram_chat_id)"
                        :error="$errors->first('telegram_chat_id')"
                    />
                    <x-ui.input.password
                        label="Telegram bot token"
                        name="telegram_bot_token"
                        :error="$errors->first('telegram_bot_token')"
                        placeholder="Leave blank to keep the current token"
                    />
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('diff-alerts.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
            <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
                Save alert settings
            </button>
        </div>
    </form>
</div>
@endsection
