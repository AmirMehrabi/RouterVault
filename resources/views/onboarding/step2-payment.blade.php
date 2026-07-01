@extends('layouts.onboarding')

@section('title', 'Complete payment')

@section('content')
<div class="mx-auto max-w-xl">
    @if($payment)
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold tracking-tight">Confirm your subscription</h1>
            <p class="mt-3 text-slate-600">Review the plan and complete the simulated checkout.</p>
        </header>

        <section class="border border-slate-200 bg-white p-6 sm:p-8">
            <div class="flex items-start justify-between gap-6 border-b border-slate-200 pb-6">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Selected plan</p>
                    <h2 class="mt-1 text-2xl font-bold">{{ $payment->subscription->plan->name }}</h2>
                </div>
                <p class="text-2xl font-bold">€{{ number_format($payment->amount, 2) }}<span class="text-sm font-normal text-slate-500">/mo</span></p>
            </div>
            <dl class="grid gap-4 py-6 text-sm sm:grid-cols-3">
                <div><dt class="text-slate-500">Routers</dt><dd class="mt-1 font-bold">{{ $payment->subscription->plan->max_routers }}</dd></div>
                <div><dt class="text-slate-500">History</dt><dd class="mt-1 font-bold">{{ $payment->subscription->plan->backup_retention_days }} days</dd></div>
                <div><dt class="text-slate-500">Team</dt><dd class="mt-1 font-bold">{{ $payment->subscription->plan->max_users }}</dd></div>
            </dl>
            <div class="border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                Dummy gateway is active. No card will be charged, but a real payment record and subscription will be created.
            </div>
            <form method="POST" action="{{ route('onboarding.payment') }}" class="mt-6">
                @csrf
                <button type="submit" class="flex min-h-12 w-full items-center justify-center bg-blue-600 px-6 text-sm font-bold text-white transition hover:bg-blue-700">
                    Confirm payment · €{{ number_format($payment->amount, 2) }}
                </button>
            </form>
            <a href="{{ route('onboarding.step', 1) }}" class="mt-5 block text-center text-sm font-semibold text-slate-600 hover:text-slate-950">Back to plans</a>
        </section>
    @else
        <div class="border border-rose-200 bg-white p-8 text-center">
            <h1 class="text-2xl font-bold">No pending payment</h1>
            <p class="mt-2 text-slate-600">Choose a plan to continue.</p>
            <a href="{{ route('onboarding.step', 1) }}" class="mt-6 inline-flex bg-blue-600 px-6 py-3 text-sm font-bold text-white">Choose a plan</a>
        </div>
    @endif
</div>
@endsection
