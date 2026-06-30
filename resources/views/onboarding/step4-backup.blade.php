<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configure Backups - RouterVault</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Configure Backups</h1>
                <p class="mt-2 text-slate-600">Set up automatic daily backups for your routers</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($routers->isEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">No routers yet</h3>
                    <p class="mt-2 text-slate-500">Add a router first to configure backups.</p>
                    <a href="{{ route('onboarding.step', 3) }}" class="mt-4 inline-block bg-slate-900 text-white py-2 px-6 rounded-xl font-semibold hover:bg-slate-800 transition">
                        Add Router
                    </a>
                </div>
            @else
                <form action="{{ route('onboarding.backup') }}" method="POST">
                    @csrf
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                        <h3 class="font-semibold text-slate-900 mb-4">Select Routers</h3>
                        <div class="space-y-3">
                            @foreach ($routers as $router)
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" name="router_ids[]" value="{{ $router->id }}" class="w-5 h-5 text-slate-900 rounded focus:ring-slate-900" {{ $routers->count() === 1 ? 'checked' : '' }}>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $router->name }}</p>
                                        <p class="text-sm text-slate-500">{{ $router->ip_address }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4">Backup Schedule</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Every</label>
                                <input type="number" name="interval_value" value="1" min="1" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Frequency</label>
                                <select name="interval_unit" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                    <option value="hours">Hours</option>
                                    <option value="days" selected>Days</option>
                                    <option value="weeks">Weeks</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('onboarding.complete') }}" class="flex-1 text-center py-3 px-6 border border-slate-200 rounded-xl font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Skip for now
                        </a>
                        <button type="submit" class="flex-1 bg-slate-900 text-white py-3 px-6 rounded-xl font-semibold hover:bg-slate-800 transition">
                            Configure Backups
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
