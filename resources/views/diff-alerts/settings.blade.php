@extends('layouts.admin')

@section('title', 'Diff Alert Settings')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Diff Alert Settings</h1>
    <form method="POST" action="{{ route('diff-alerts.settings.update') }}" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="space-y-5">
            <x-ui.input.checkbox label="Enable alerts" name="is_enabled" :checked="old('is_enabled', $setting->is_enabled)" />
            <x-ui.input.checkbox label="Ignore blank-line changes" name="ignore_blank_lines" :checked="old('ignore_blank_lines', $setting->ignore_blank_lines)" />
            <x-ui.input.textarea label="Ignored sections" name="ignored_sections" :value="old('ignored_sections', implode(PHP_EOL, $setting->ignored_sections ?? []))" hint="One section per line, for example /system clock." />
            <x-ui.input.textarea label="Ignored keywords" name="ignored_keywords" :value="old('ignored_keywords', implode(PHP_EOL, $setting->ignored_keywords ?? []))" hint="One keyword per line." />
        </div>
        <button class="mt-6 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Save Settings</button>
    </form>
</div>
@endsection
