<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Onboarding - SkyBase Cloud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Welcome to SkyBase Cloud</h1>
                <p class="mt-2 text-slate-600">Let's get your account set up in a few simple steps.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="space-y-6">
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">1</div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Choose Your Plan</h3>
                            <p class="text-sm text-slate-500">Select the plan that fits your needs</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">2</div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Complete Payment</h3>
                            <p class="text-sm text-slate-500">Set up your billing details</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">3</div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Add Your First Router</h3>
                            <p class="text-sm text-slate-500">Connect your MikroTik router</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">4</div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Configure Backups</h3>
                            <p class="text-sm text-slate-500">Set up automatic daily backups</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('onboarding.step', 1) }}" class="block w-full text-center bg-slate-900 text-white py-3 px-6 rounded-xl font-semibold hover:bg-slate-800 transition">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
