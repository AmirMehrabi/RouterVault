@extends('layouts.admin')

@section('title', 'Plan & Usage')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Plan & usage', 'current' => true]]" />
@endpush

@section('content')
@php
    $routerPercent = $limits['total_allowed'] > 0 ? min(100, round(($usage['current'] / $limits['total_allowed']) * 100)) : 100;
    $teamPercent = $teamUsage['limit'] > 0 ? min(100, round(($teamUsage['current'] / $teamUsage['limit']) * 100)) : 100;
@endphp

<div class="mx-auto max-w-6xl space-y-6 pb-10">
    <header>
        <h1 class="text-3xl font-bold tracking-tight text-slate-950">Plan & usage</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Review your subscription, capacity, and payment history.</p>
    </header>

    @if(session('success'))
        <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ $errors->first() }}</div>
    @endif

    <section class="border border-slate-200 bg-white p-6" aria-labelledby="current-plan-heading">
        <div class="flex flex-col gap-5 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Current plan</p>
                <h2 id="current-plan-heading" class="mt-2 text-3xl font-bold text-slate-950">{{ $currentPlan?->name ?? 'No active plan' }}</h2>
                @if($currentPlan)
                    <p class="mt-1 text-lg font-semibold text-slate-700">€{{ number_format($currentPlan->price, 0) }} <span class="text-sm font-normal text-slate-500">/ month</span></p>
                @endif
            </div>
            @if($subscription)
                <div class="text-left sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Renews on</p>
                    <p class="mt-2 font-semibold text-slate-900">{{ $subscription->current_period_end->format('M j, Y') }}</p>
                </div>
            @endif
        </div>

        <div class="grid divide-y divide-slate-200 pt-2 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="py-5 sm:pr-6">
                <div class="flex items-end justify-between"><p class="text-sm font-semibold text-slate-700">Routers</p><p class="text-xl font-bold">{{ $usage['current'] }} of {{ $limits['total_allowed'] }}</p></div>
                <div class="mt-3 h-2 overflow-hidden bg-slate-100"><div class="h-full {{ $routerPercent >= 100 ? 'bg-amber-500' : 'bg-blue-600' }}" style="width: {{ $routerPercent }}%"></div></div>
                @if($limits['extra_routers'] > 0)<p class="mt-2 text-xs text-slate-500">Includes {{ $limits['extra_routers'] }} extra router{{ $limits['extra_routers'] === 1 ? '' : 's' }}</p>@endif
            </div>
            <div class="py-5 sm:px-6">
                <div class="flex items-end justify-between"><p class="text-sm font-semibold text-slate-700">Team members</p><p class="text-xl font-bold">{{ $teamUsage['current'] }} of {{ $teamUsage['limit'] }}</p></div>
                <div class="mt-3 h-2 overflow-hidden bg-slate-100"><div class="h-full {{ $teamPercent >= 100 ? 'bg-amber-500' : 'bg-blue-600' }}" style="width: {{ $teamPercent }}%"></div></div>
            </div>
            <div class="py-5 sm:pl-6">
                <p class="text-sm font-semibold text-slate-700">Backup history</p>
                <p class="mt-2 text-xl font-bold">{{ $limits['backup_retention_days'] }} days</p>
                <p class="mt-2 text-xs text-slate-500">Retention included with your plan</p>
            </div>
        </div>
    </section>

    <section aria-labelledby="plans-heading">
        <div class="mb-4">
            <h2 id="plans-heading" class="text-xl font-bold text-slate-950">Choose a plan</h2>
            <p class="mt-1 text-sm text-slate-600">Upgrade when you need more routers, history, or team access.</p>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $currentPlan?->id === $plan->id;
                    $isUpgrade = !$currentPlan || $plan->priority > $currentPlan->priority;
                @endphp
                <article @class([
                    'flex flex-col border bg-white p-5',
                    'border-blue-600 ring-2 ring-blue-100' => $isCurrent,
                    'border-slate-200' => !$isCurrent,
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-bold">{{ $plan->name }}</h3>
                        @if($isCurrent)<span class="text-xs font-bold text-blue-700">Current plan</span>@endif
                    </div>
                    <p class="mt-4 text-3xl font-bold">€{{ number_format($plan->price, 0) }}<span class="text-sm font-normal text-slate-500"> / month</span></p>
                    <ul class="mt-5 flex-1 space-y-2 border-t border-slate-200 pt-4 text-sm text-slate-700">
                        <li>{{ $plan->max_routers }} router{{ $plan->max_routers === 1 ? '' : 's' }}</li>
                        <li>{{ $plan->max_users }} team member{{ $plan->max_users === 1 ? '' : 's' }}</li>
                        <li>{{ $plan->backup_retention_days }}-day history</li>
                    </ul>
                    @if($canManageBilling && $isUpgrade && !$isCurrent)
                        <form method="POST" action="{{ route('billing.upgrade') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="flex min-h-11 w-full items-center justify-center bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Upgrade to {{ $plan->name }}</button>
                        </form>
                    @elseif($isCurrent)
                        <div class="mt-5 flex min-h-11 items-center justify-center border border-blue-200 bg-blue-50 text-sm font-bold text-blue-700">Current plan</div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    @if($extraRouterPlan && $currentPlan)
        <section id="extra-routers" class="border border-slate-200 bg-white p-6">
            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <h2 class="text-lg font-bold">Extra router capacity</h2>
                    <p class="mt-2 text-sm text-slate-600">Add capacity without changing plans. €{{ number_format($extraRouterPlan->price, 0) }} per router each month.</p>
                </div>
                @if($canManageBilling)
                    <form method="POST" action="{{ route('billing.extra-routers.store') }}" class="flex items-end gap-3">
                        @csrf
                        <div class="w-28">
                            <x-input.number id="quantity" name="quantity" label="Quantity" value="1" min="1" max="100" :required="true" />
                        </div>
                        <button type="submit" class="mb-4 flex min-h-12 items-center justify-center bg-blue-600 px-5 text-sm font-bold text-white transition hover:bg-blue-700">Add capacity</button>
                    </form>
                @endif
            </div>
        </section>
    @endif

    <section class="border border-slate-200 bg-white" aria-labelledby="payments-heading">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 id="payments-heading" class="text-lg font-bold">Payment history</h2>
        </div>
        @if($payments->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-slate-500">No payments yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr><th class="px-6 py-3">Date</th><th class="px-6 py-3">Description</th><th class="px-6 py-3">Amount</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Transaction</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($payments as $payment)
                            <tr>
                                <td class="px-6 py-4 text-slate-600">{{ $payment->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ data_get($payment->metadata, 'type') === 'extra_router' ? 'Extra router capacity' : 'Plan subscription' }}</td>
                                <td class="px-6 py-4 font-semibold">€{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4"><span @class(['font-semibold', 'text-emerald-700' => $payment->status === 'completed', 'text-amber-700' => $payment->status === 'pending', 'text-rose-700' => $payment->status === 'failed'])>{{ ucfirst($payment->status) }}</span></td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $payment->transaction_id ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
