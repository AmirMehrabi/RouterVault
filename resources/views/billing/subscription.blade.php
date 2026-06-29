<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subscription & Billing - SkyBase Cloud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Subscription & Billing</h1>
                <p class="mt-2 text-slate-600">Manage your plan, usage, and payments</p>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Current Plan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Current Plan</h2>
                    @if ($currentPlan)
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 text-sm font-semibold rounded-full">{{ $currentPlan->name }}</span>
                    @endif
                </div>

                @if ($currentPlan)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-slate-50 rounded-xl">
                            <p class="text-sm text-slate-500">Monthly Price</p>
                            <p class="text-2xl font-bold text-slate-900">${{ number_format($currentPlan->price, 2) }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl">
                            <p class="text-sm text-slate-500">Routers</p>
                            <p class="text-2xl font-bold text-slate-900">{{ $usage['current'] }} / {{ $limits['total_allowed'] }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl">
                            <p class="text-sm text-slate-500">Backup Retention</p>
                            <p class="text-2xl font-bold text-slate-900">{{ $limits['backup_retention_days'] }} days</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl">
                            <p class="text-sm text-slate-500">Team Members</p>
                            <p class="text-2xl font-bold text-slate-900">{{ $tenant->users()->count() }} / {{ $limits['max_users'] }}</p>
                        </div>
                    </div>

                    @if ($usage['overage'] > 0)
                        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                            <p class="text-amber-700">
                                <strong>Overage:</strong> You have {{ $usage['overage'] }} extra router(s) beyond your plan limit.
                                Additional cost: ${{ number_format($usage['overage'] * 1, 2) }}/month
                            </p>
                        </div>
                    @endif
                @else
                    <p class="text-slate-500">No active plan. Choose a plan below to get started.</p>
                @endif
            </div>

            <!-- Available Plans -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Available Plans</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div class="border border-slate-200 rounded-xl p-4 {{ $currentPlan && $currentPlan->id === $plan->id ? 'bg-slate-50 border-slate-400' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-slate-900">{{ $plan->name }}</h3>
                                @if ($currentPlan && $currentPlan->id === $plan->id)
                                    <span class="text-xs text-emerald-600 font-semibold">Current</span>
                                @endif
                            </div>
                            <p class="text-2xl font-bold text-slate-900">${{ number_format($plan->price, 0) }}<span class="text-sm font-normal text-slate-500">/mo</span></p>
                            <ul class="mt-3 space-y-1 text-sm text-slate-600">
                                <li>{{ $plan->max_routers }} router(s)</li>
                                <li>{{ $plan->backup_retention_days }}-day history</li>
                                <li>{{ $plan->max_users }} team member(s)</li>
                            </ul>
                            @if (!$currentPlan || $currentPlan->id !== $plan->id)
                                <form action="{{ route('billing.upgrade') }}" method="POST" class="mt-4">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="w-full bg-slate-900 text-white py-2 px-4 rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                                        {{ $currentPlan ? 'Switch Plan' : 'Subscribe' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Payment History</h2>
                @if ($payments->isEmpty())
                    <p class="text-slate-500">No payments yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Amount</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-3 px-4 text-slate-600">{{ $payment->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 font-semibold text-slate-900">${{ number_format($payment->amount, 2) }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $payment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 text-xs">{{ $payment->transaction_id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
