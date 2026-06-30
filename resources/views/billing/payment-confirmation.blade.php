<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Confirmed - RouterVault</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>

            <h1 class="text-3xl font-bold text-slate-900">Payment Confirmed!</h1>
            <p class="mt-3 text-slate-600">Your payment has been processed successfully.</p>

            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-left">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-slate-600">Amount Paid</span>
                    <span class="font-bold text-slate-900">${{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-slate-600">Plan</span>
                    <span class="font-semibold text-slate-900">{{ $payment->subscription->plan->name }}</span>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-slate-600">Transaction ID</span>
                    <span class="text-sm text-slate-500">{{ $payment->transaction_id }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Paid At</span>
                    <span class="text-sm text-slate-500">{{ $payment->paid_at?->format('M d, Y H:i') }}</span>
                </div>
            </div>

            <a href="{{ route('dashboard') }}" class="mt-8 inline-block bg-slate-900 text-white py-3 px-8 rounded-xl font-semibold hover:bg-slate-800 transition">
                Go to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
