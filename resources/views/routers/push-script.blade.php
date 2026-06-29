<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Push Backup Script - {{ $router->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('routers.show', $router) }}" class="text-sm text-slate-500 hover:text-slate-700">← Back to {{ $router->name }}</a>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Router Backup Script</h1>
                <p class="mt-2 text-slate-600">Copy this script and run it on your RouterOS device</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-900">Security Notice</h2>
                        <p class="text-sm text-slate-500">Secrets are hidden by default. Backups are encrypted.</p>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-amber-700">
                        <strong>Important:</strong> This script uses <code>/export hide-sensitive</code> which automatically hides passwords, keys, and certificates in the backup.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-slate-700">Upload URL</h3>
                        <button onclick="navigator.clipboard.writeText('{{ $uploadUrl }}')" class="text-xs text-slate-500 hover:text-slate-700">Copy</button>
                    </div>
                    <code class="text-sm text-slate-600 break-all">{{ $uploadUrl }}</code>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">RouterOS Script</h3>
                    <button onclick="copyScript()" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                        Copy Script
                    </button>
                </div>
                <pre id="script-content" class="bg-slate-900 text-slate-100 p-4 rounded-xl text-sm overflow-x-auto whitespace-pre-wrap">{{ $script }}</pre>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-3">How to use</h3>
                <ol class="space-y-2 text-sm text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-slate-900">1.</span>
                        Open Winbox or SSH into your router
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-slate-900">2.</span>
                        Go to <code class="bg-slate-100 px-1 rounded">New Terminal</code>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-slate-900">3.</span>
                        Paste the entire script and press Enter
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-semibold text-slate-900">4.</span>
                        The script will create a daily backup schedule automatically
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        function copyScript() {
            const script = document.getElementById('script-content').textContent;
            navigator.clipboard.writeText(script).then(() => {
                alert('Script copied to clipboard!');
            });
        }
    </script>
</body>
</html>
