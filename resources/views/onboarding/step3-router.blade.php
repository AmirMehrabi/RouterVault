<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Your First Router - RouterVault</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Add Your First Router</h1>
                <p class="mt-2 text-slate-600">Connect your MikroTik router to start backups</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <form action="{{ route('onboarding.router') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Router Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g., Core-Router-1" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Router IP Address</label>
                            <input type="text" name="ip_address" value="{{ old('ip_address') }}" required placeholder="e.g., 192.168.1.1" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                        </div>

                        <div class="border-t border-slate-200 pt-4 mt-4">
                            <h3 class="font-semibold text-slate-900 mb-3">SSH Connection (for backups)</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">SSH Username</label>
                            <input type="text" name="api_username" value="{{ old('api_username', 'admin') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">SSH Password</label>
                            <input type="password" name="api_password" value="{{ old('api_password') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Auth Method</label>
                            <select name="ssh_auth_method" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                <option value="password" {{ old('ssh_auth_method') === 'password' ? 'selected' : '' }}>Password</option>
                                <option value="private_key" {{ old('ssh_auth_method', 'private_key') === 'private_key' ? 'selected' : '' }}>Private Key</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">SSH Port</label>
                            <input type="number" name="ssh_port" value="{{ old('ssh_port', 22) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('onboarding.step', 4) }}" class="flex-1 text-center py-3 px-6 border border-slate-200 rounded-xl font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Skip for now
                        </a>
                        <button type="submit" class="flex-1 bg-slate-900 text-white py-3 px-6 rounded-xl font-semibold hover:bg-slate-800 transition">
                            Add Router
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
