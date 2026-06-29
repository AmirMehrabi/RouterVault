<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Payment - SkyBase Cloud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-md mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Complete Payment</h1>
                <p class="mt-2 text-slate-600">Process your payment for the {{ $payment->subscription->plan->name }} plan</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl mb-6">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $payment->subscription->plan->name }} Plan</p>
                        <p class="text-sm text-slate-500">Monthly subscription</p>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">${{ number_format($payment->amount, 2) }}</p>
                </div>

                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-700 text-sm">
                    This is a demo payment page. No real payment will be processed.
                </div>

                <form action="{{ route('billing.payment.process', $payment) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Card Number</label>
                            <input type="text" value="4242 4242 4242 4242" readonly class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Expiry</label>
                                <input type="text" value="12/28" readonly class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">CVC</label>
                                <input type="text" value="123" readonly class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-500">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full bg-slate-900 text-white py-3 px-6 rounded-xl font-semibold hover:bg-slate-800 transition">
                        Pay ${{ number_format($payment->amount, 2) }}
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <a href="{{ route('dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back to dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
