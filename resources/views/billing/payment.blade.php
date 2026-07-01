@extends('layouts.admin')

@section('title', 'Complete Payment')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Plan & usage', 'href' => route('billing.subscription')],
        ['label' => 'Checkout', 'current' => true],
    ]" />
@endpush

@section('content')
@php
    $isExtraRouter = data_get($payment->metadata, 'type') === 'extra_router';
    $quantity = (int) data_get($payment->metadata, 'quantity', 1);
@endphp
<div class="mx-auto max-w-xl pb-10">
    <header class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-950">Complete payment</h1>
        <p class="mt-2 text-sm text-slate-600">Confirm this purchase through the configured dummy gateway.</p>
    </header>

    <section class="border border-slate-200 bg-white p-6 sm:p-8">
        <div class="flex items-start justify-between gap-6 border-b border-slate-200 pb-6">
            <div>
                <p class="text-sm font-semibold text-slate-500">{{ $isExtraRouter ? 'Capacity purchase' : 'Plan upgrade' }}</p>
                <h2 class="mt-1 text-xl font-bold">{{ $isExtraRouter ? $quantity.' extra router'.($quantity === 1 ? '' : 's') : $payment->subscription->plan->name.' plan' }}</h2>
            </div>
            <p class="text-2xl font-bold">€{{ number_format($payment->amount, 2) }}</p>
        </div>

        <dl class="grid gap-4 py-6 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Payment reference</dt><dd class="mt-1 font-mono text-xs font-semibold">PAY-{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</dd></div>
            <div><dt class="text-slate-500">Status</dt><dd class="mt-1 font-semibold">{{ ucfirst($payment->status) }}</dd></div>
        </dl>

        <div class="border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
            This checkout uses the dummy payment gateway. No external charge is made; successful confirmation still updates the real subscription and payment records.
        </div>

        @if($payment->isPending())
            <form action="{{ route('billing.payment.process', $payment) }}" method="POST" class="mt-6">
                @csrf
                @method('PATCH')
                <button type="submit" class="flex min-h-12 w-full items-center justify-center bg-blue-600 px-6 text-sm font-bold text-white transition hover:bg-blue-700">
                    Confirm payment · €{{ number_format($payment->amount, 2) }}
                </button>
            </form>
        @else
            <a href="{{ route('billing.payment.confirmation', $payment) }}" class="mt-6 flex min-h-12 w-full items-center justify-center bg-slate-950 px-6 text-sm font-bold text-white">View confirmation</a>
        @endif

        <a href="{{ route('billing.subscription') }}" class="mt-5 block text-center text-sm font-semibold text-slate-600 hover:text-slate-950">Cancel and return to Plan & usage</a>
    </section>
</div>
@endsection
