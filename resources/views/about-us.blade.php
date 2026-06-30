<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        name="description"
        content="درباره RouterVault؛ پلتفرمی فارسی برای مدیریت عملیات ISP، مانیتورینگ شبکه MikroTik و هماهنگی تیم‌های فروش، پشتیبانی و عملیات."
    >
    <title>درباره ما | RouterVault</title>
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
        <x-marketing.navbar current="about" />

        <main>
            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                    <div class="mx-auto max-w-4xl text-center">
                        <p class="text-sm font-black text-slate-600">درباره RouterVault</p>
                        <h1 class="mt-4 text-4xl font-black leading-tight text-slate-950 sm:text-5xl">
                            RouterVault برای این ساخته شده که اداره یک ISP، روشن‌تر، منظم‌تر و قابل‌اتکاتر شود
                        </h1>
                        <p class="mt-6 text-base leading-8 text-slate-600 sm:text-lg">
                            ما به این فکر کرده‌ایم که تیم‌های ISP برای انجام کار روزمره‌شان به چه چیزی نیاز دارند: دید سریع، داده قابل اقدام
                            و تجربه‌ای که بین فروش، پشتیبانی و عملیات شکاف ایجاد نکند.
                        </p>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <p class="text-sm font-black text-slate-950">نگاه ما</p>
                            <p class="mt-4 text-sm leading-8 text-slate-600">
                                ابزار خوب برای ISP فقط مجموعه‌ای از امکانات نیست؛ باید به زبان تیم عملیات فکر کند و مسیر تصمیم‌گیری را کوتاه‌تر کند.
                            </p>
                        </article>
                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <p class="text-sm font-black text-slate-950">مسئله‌ای که حل می‌کنیم</p>
                            <p class="mt-4 text-sm leading-8 text-slate-600">
                                پراکندگی بین مانیتورینگ شبکه، مدیریت مشترک و عملیات تکراری باعث کندی، خطا و فرسایش تیم می‌شود. RouterVault این فاصله را کم می‌کند.
                            </p>
                        </article>
                        <article class="rounded-[1.75rem] border border-slate-900 bg-slate-900 p-7 text-white shadow-sm">
                            <p class="text-sm font-black">چرا این مهم است؟</p>
                            <p class="mt-4 text-sm leading-8 text-slate-300">
                                چون کیفیت تجربه مشتری نهایی، مستقیم به کیفیت ابزار و سرعت عمل تیم داخلی شما وابسته است.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-7">
                            <p class="text-sm font-black text-slate-950">RouterVault چه نوع محصولی است؟</p>
                            <div class="mt-6 space-y-4 text-sm leading-8 text-slate-600">
                                <p>RouterVault یک پلتفرم عملیاتی برای مدیریت شبکه‌های MikroTik و جریان‌های روزمره ISP است.</p>
                                <p>هدف ما این است که تیم شما برای دیدن وضعیت، فهمیدن مسئله و انجام عملیات، مسیر ساده‌تری داشته باشد.</p>
                                <p>به همین دلیل طراحی محصول، لحن محتوا و ساختار صفحه‌ها به‌جای نمایش صرف داده، روی فهم و اقدام تمرکز دارد.</p>
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-7">
                            <p class="text-sm font-black text-slate-950">برای آینده چه می‌خواهیم؟</p>
                            <div class="mt-6 space-y-4 text-sm leading-8 text-slate-600">
                                <p>می‌خواهیم RouterVault به تجربه‌ای تبدیل شود که تیم‌های ISP هر روز با اطمینان به آن رجوع کنند.</p>
                                <p>هر صفحه، هر سناریو و هر قابلیت باید به کاهش اصطکاک عملیاتی و افزایش وضوح تصمیم‌گیری کمک کند.</p>
                                <p>اگر شما هم چنین نگاهی دارید، خوشحال می‌شویم درباره مسیر همکاری صحبت کنیم.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <x-marketing.footer />
    </div>
</body>
</html>
