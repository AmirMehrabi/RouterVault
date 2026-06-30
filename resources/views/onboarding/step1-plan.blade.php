<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Choose Your Plan - RouterVault</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Choose Your Plan</h1>
                <p class="mt-2 text-slate-600">Select the plan that best fits your needs</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('onboarding.plan') }}" method="POST">
                @csrf
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div class="relative">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" id="plan-{{ $plan->id }}" class="peer sr-only" {{ $plan->price == 0 ? 'checked' : '' }}>
                            <label for="plan-{{ $plan->id }}" class="block cursor-pointer rounded-2xl border-2 border-slate-200 bg-white p-6 transition hover:border-slate-400 peer-checked:border-slate-900 peer-checked:bg-slate-50">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h3>
                                    @if ($plan->price == 0)
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">Free</span>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <span class="text-4xl font-bold text-slate-900">${{ number_format($plan->price, 0) }}</span>
                                    <span class="text-slate-500">/month</span>
                                </div>
                                <p class="mt-3 text-sm text-slate-600">{{ $plan->description }}</p>
                                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        {{ $plan->max_routers }} router{{ $plan->max_routers > 1 ? 's' : '' }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        {{ $plan->backup_retention_days }}-day backup history
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        {{ $plan->max_users }} team member{{ $plan->max_users > 1 ? 's' : '' }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        {{ ucfirst(implode(', ', $plan->alert_channels ?? ['in_app'])) }} alerts
                                    </li>
                                </ul>
                                <div class="mt-4 w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-slate-900 peer-checked:bg-slate-900 mx-auto"></div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-center">
                    <button type="submit" class="bg-slate-900 text-white py-3 px-8 rounded-xl font-semibold hover:bg-slate-800 transition">
                        Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
