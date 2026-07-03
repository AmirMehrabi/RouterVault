<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        name="description"
        content="تماس با تیم RouterVault برای دریافت مشاوره، دمو، بررسی پلن مناسب یا قیمت‌گذاری سازمانی."
    >
    <title>تماس با ما | RouterVault</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Images/Logos/routervault_symbol_color.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/fonts.css') }}">
    <style>
        body {
            font-family: Pelak, "Segoe UI", sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen">
        <x-marketing.navbar current="contact" />

        <main>
            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-center">
                        <div>
                            <p class="text-sm font-black text-slate-600">تماس با ما</p>
                            <h1 class="mt-4 text-4xl font-black leading-tight text-slate-950 sm:text-5xl">
                                اگر می‌خواهید RouterVault را برای کسب‌وکار خود ارزیابی کنید، اینجا شروع کنید
                            </h1>
                            <p class="mt-6 text-base leading-8 text-slate-600 sm:text-lg">
                                چه برای انتخاب پلن مناسب، چه برای درخواست دمو یا بررسی قیمت‌گذاری سازمانی، تیم ما آماده است درباره ساختار
                                عملیات، اندازه شبکه و نیازهای شما صحبت کند.
                            </p>
                        </div>

                        <div class="rounded-[1.75rem] border border-slate-900 bg-slate-900 p-7 text-white">
                            <p class="text-sm font-black text-slate-300">در چه مواردی می‌توانیم کمک کنیم؟</p>
                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl border border-slate-700 bg-slate-800 p-4">
                                    <p class="text-sm font-black">انتخاب پلن مناسب</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">بر اساس تعداد کاربران، ساختار تیم و مرحله رشد کسب‌وکار شما</p>
                                </div>
                                <div class="rounded-2xl border border-slate-700 bg-slate-800 p-4">
                                    <p class="text-sm font-black">بررسی سناریوی سازمانی</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">برای مجموعه‌های بزرگ‌تر یا نیازهای خاص عملیاتی</p>
                                </div>
                                <div class="rounded-2xl border border-slate-700 bg-slate-800 p-4">
                                    <p class="text-sm font-black">آشنایی با تجربه محصول</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">برای درک بهتر اینکه RouterVault در عمل چه مسئله‌ای را حل می‌کند</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-6 lg:grid-cols-1">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm w-2xl max-w-4xl mx-auto">
                            <p class="text-sm font-black text-slate-950">راه‌های ارتباط</p>
                            <div class="mt-6 space-y-5 text-sm leading-8 text-slate-600">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-black text-slate-950">ایمیل</p>
                                    <p class="mt-2">info@routervault.app</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-black text-slate-950">تلفن</p>
                                    <p class="mt-2">09336337953</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-black text-slate-950">ساعت پاسخ‌گویی</p>
                                    <p class="mt-2">شنبه تا چهارشنبه، ۹ تا ۱۷</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </main>

        <x-marketing.footer />
    </div>
    <x-global-waiting-state />
</body>
</html>
