@extends('layouts.admin')

@section('title', 'Payment Confirmed')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Plan & usage', 'href' => route('billing.subscription')],
        ['label' => 'Payment confirmed', 'current' => true],
    ]" />
@endpush

@section('content')
<div class="mx-auto max-w-xl py-8 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
        <svg class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" /></svg>
    </div>
    <h1 class="mt-6 text-3xl font-bold tracking-tight">Payment confirmed</h1>
    <p class="mt-3 text-slate-600">Your subscription capacity has been updated successfully.</p>

    <dl class="mt-8 border border-slate-200 bg-white text-left">
        <div class="flex justify-between gap-4 border-b border-slate-100 px-6 py-4"><dt class="text-slate-500">Amount</dt><dd class="font-bold">€{{ number_format($payment->amount, 2) }}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 px-6 py-4"><dt class="text-slate-500">Transaction</dt><dd class="font-mono text-xs text-slate-700">{{ $payment->transaction_id }}</dd></div>
        <div class="flex justify-between gap-4 px-6 py-4"><dt class="text-slate-500">Paid at</dt><dd class="font-semibold">{{ $payment->paid_at?->format('M j, Y H:i') }}</dd></div>
    </dl>

    <a href="{{ route('billing.subscription') }}" class="mt-8 inline-flex min-h-12 items-center justify-center bg-blue-600 px-8 text-sm font-bold text-white transition hover:bg-blue-700">Return to Plan & usage</a>
</div>
@endsection
