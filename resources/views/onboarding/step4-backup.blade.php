@extends('layouts.onboarding')

@section('title', 'Configure backups')

@section('content')
<div class="mx-auto max-w-2xl">
    <header class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Schedule automatic backups</h1>
        <p class="mt-3 text-slate-600">Choose which routers to protect and how often RouterVault should back them up.</p>
    </header>

    <section class="border border-slate-200 bg-white p-6 sm:p-8">
        <form id="backup-form" method="POST" action="{{ route('onboarding.backup') }}">
            @csrf
            <fieldset>
                <legend class="text-sm font-bold text-slate-900">Routers</legend>
                <div class="mt-3 divide-y divide-slate-100 border-y border-slate-200">
                    @foreach($routers as $router)
                        <div class="py-4">
                            <x-input.checkbox id="router_{{ $router->id }}" name="router_ids[]" :value="$router->id" :checked="$routers->count() === 1">
                                <span class="font-semibold text-slate-900">{{ $router->name }}</span>
                                <span class="ml-2 font-normal text-slate-500">{{ $router->ip_address }}</span>
                            </x-input.checkbox>
                        </div>
                    @endforeach
                </div>
                @error('router_ids')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                @error('router_ids.*')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </fieldset>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <x-input.number id="interval_value" name="interval_value" label="Every" value="1" min="1" max="365" :required="true" />
                <x-input.select
                    id="interval_unit"
                    name="interval_unit"
                    label="Frequency"
                    value="days"
                    :options="['hours' => 'Hours', 'days' => 'Days', 'weeks' => 'Weeks']"
                    :required="true"
                />
            </div>
        </form>

        <div class="mt-3 grid gap-3">
            <button form="backup-form" type="submit" class="flex min-h-12 items-center justify-center bg-blue-600 px-6 text-sm font-bold text-white transition hover:bg-blue-700">
                Configure backups
            </button>
            <form method="POST" action="{{ route('onboarding.backup.skip') }}">
                @csrf
                <button type="submit" class="flex min-h-12 w-full items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                    Skip backup setup
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
