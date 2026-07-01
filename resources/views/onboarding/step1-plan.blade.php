@extends('layouts.onboarding')

@section('title', 'Choose your plan')

@section('content')
<div class="mx-auto max-w-5xl">
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Choose the plan that fits your network</h1>
        <p class="mt-3 text-base text-slate-600">Start free or choose more capacity. You can upgrade later.</p>
    </header>

    <form method="POST" action="{{ route('onboarding.plan') }}">
        @csrf
        <div class="grid gap-5 md:grid-cols-3">
            @foreach($plans as $plan)
                <label class="relative cursor-pointer">
                    <input
                        type="radio"
                        name="plan_id"
                        value="{{ $plan->id }}"
                        class="peer sr-only"
                        required
                        {{ (string) old('plan_id', $tenant->saas_plan_id ?? $plans->firstWhere('price', 0)?->id) === (string) $plan->id ? 'checked' : '' }}
                    >
                    <span class="flex h-full flex-col border border-slate-200 bg-white p-6 transition hover:border-blue-300 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-100">
                        <span class="flex items-start justify-between gap-3">
                            <span class="text-xl font-bold text-slate-950">{{ $plan->name }}</span>
                            <span class="flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 peer-checked:border-blue-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-600 opacity-0 peer-checked:opacity-100"></span>
                            </span>
                        </span>
                        <span class="mt-5 text-4xl font-bold tracking-tight text-slate-950">€{{ number_format($plan->price, 0) }}<span class="text-sm font-normal text-slate-500"> / month</span></span>
                        <span class="mt-4 min-h-12 text-sm leading-6 text-slate-600">{{ $plan->description }}</span>
                        <ul class="mt-6 space-y-3 border-t border-slate-200 pt-5 text-sm text-slate-700">
                            <li>{{ $plan->max_routers }} router{{ $plan->max_routers === 1 ? '' : 's' }}</li>
                            <li>{{ $plan->backup_retention_days }}-day backup history</li>
                            <li>{{ $plan->max_users }} team member{{ $plan->max_users === 1 ? '' : 's' }}</li>
                        </ul>
                    </span>
                </label>
            @endforeach
        </div>
        @error('plan_id')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror

        <div class="mt-8 flex justify-center">
            <button type="submit" class="inline-flex min-h-12 items-center justify-center bg-blue-600 px-8 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                Continue
            </button>
        </div>
    </form>
</div>
@endsection
