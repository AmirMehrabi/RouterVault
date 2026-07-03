<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="RouterVault یک پنل فارسی برای مانیتورینگ، پروویژن و مدیریت شبکه های MikroTik است؛ از روتر و اکسس پوینت تا مشترک و سرویس.">
    <title>RouterVault | مانیتورینگ و پروویژن شبکه های MikroTik</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Images/Logos/routervault_symbol_color.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset("assets/fonts.css") }}">
    <style>
        body {
            font-family: Pelak, "Segoe UI", sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center">
                    <x-brand-logo class="w-12" />
                </div>

                <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 lg:flex">
                    <a href="#capabilities" class="transition hover:text-blue-700">قابلیت ها</a>
                    <a href="#workflow" class="transition hover:text-blue-700">چرخه کار</a>
                    <a href="#operations" class="transition hover:text-blue-700">برای تیم عملیات</a>
                    <a href="#audience" class="transition hover:text-blue-700">مناسب چه کسانی است</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50">
                        ورود
                    </a>
                    <a href="{{ route('auth.register') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        شروع استفاده
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-1 lg:px-8 lg:py-16">
                    <div class="order-2 lg:order-1">
                        <div class="border-r-4 border-blue-600 pr-4 sm:pr-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.32em] text-blue-700">پلتفرم عملیات شبکه</p>
                            <h1 class="mt-5 max-w-4xl text-4xl font-black leading-tight text-slate-950 sm:text-5xl xl:text-6xl">
                                مانیتورینگ، پروویژن و مدیریت شبکه های MikroTik در یک صفحه قابل فهم
                            </h1>
                        </div>

                        <div class="mt-8 grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                                <p class="text-sm leading-7 text-slate-700">
                                    مشتری باید در چند ثانیه بفهمد این سیستم چه کاری می کند: وضعیت روترها را می بیند، مشترک را فعال یا معلق می کند، رادیو را از راه دور تنظیم می کند و بدون ورود به Winbox عملیات روزانه را جلو می برد.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-blue-200 bg-blue-600 p-5 text-white shadow-sm">
                                <p class="text-sm font-semibold uppercase text-blue-100">تمرکز اصلی</p>
                                <ul class="mt-4 space-y-3 text-sm leading-7 text-blue-50">
                                    <li>مانیتورینگ زنده روتر، اکسس پوینت و وایرلس کلاینت</li>
                                    <li>پروویژن خودکار سرویس، اعتبار و دسترسی مشترک</li>
                                    <li>اجرای دستورات مدیریتی MikroTik از داخل پنل</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('auth.register') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                                ایجاد حساب و شروع
                            </a>
                            <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-100">
                                ورود به داشبورد
                            </a>
                        </div>
                    </div>

                    <div class="order-1 lg:order-2">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-950 p-4 text-white shadow-lg shadow-slate-300/40">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">NOC View</p>
                                    <p class="mt-1 text-lg font-bold">کنترل زنده شبکه</p>
                                </div>
                                <span class="border border-emerald-500/40 bg-emerald-500/10 px-2 py-1 text-xs font-semibold text-emerald-300">23 تجهیز آنلاین</span>
                            </div>

                            <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                                <div class="space-y-4">
                                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Router Status</p>
                                                <p class="mt-2 text-xl font-bold">Core-MK-01</p>
                                                <p class="mt-1 text-sm text-slate-400">CPU 41% / Memory 63% / Uptime 18d</p>
                                            </div>
                                            <div class="grid gap-2 text-left text-xs">
                                                <span class="border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-emerald-300">Online</span>
                                                <span class="border border-slate-700 px-2 py-1 text-slate-300">Radius OK</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Wireless Client</p>
                                            <p class="mt-2 text-lg font-bold">Tower1-AP3-912xxxx</p>
                                            <p class="mt-2 text-sm text-slate-400">Signal -58 / Lease 10.0.0.25</p>
                                            <p class="mt-1 text-sm text-slate-400">Discovery / Reboot / DNS / SNMP</p>
                                        </div>
                                        <div class="rounded-2xl border border-blue-500/30 bg-blue-600/10 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-200">Provisioning</p>
                                            <p class="mt-2 text-lg font-bold text-white">PPPoE + Radius + Plan</p>
                                            <p class="mt-2 text-sm text-blue-100">ایجاد، تمدید، قطع، وصل و اعمال پروفایل از یک مسیر واحد</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Operational Pulse</p>
                                    <div class="mt-4 space-y-4">
                                        <div class="border-r-2 border-blue-500 pr-3">
                                            <p class="text-sm font-semibold text-white">7 هشدار نیازمند اقدام</p>
                                            <p class="mt-1 text-xs leading-6 text-slate-400">دو اکسس پوینت ناپایدار، سه مشترک با سیگنال ضعیف، دو روتر با مصرف CPU بالا</p>
                                        </div>
                                        <div class="border-r-2 border-slate-700 pr-3">
                                            <p class="text-sm font-semibold text-white">در صف عملیات امروز</p>
                                            <p class="mt-1 text-xs leading-6 text-slate-400">کشف خودکار رادیوها، اصلاح DNS، همگام سازی NTP و بازبینی لاگ های مدیریتی</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 pt-2">
                                            <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                                                <p class="text-xs text-slate-500">مشترک فعال</p>
                                                <p class="mt-2 text-2xl font-black text-white">1,284</p>
                                            </div>
                                            <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                                                <p class="text-xs text-slate-500">دستورات امروز</p>
                                                <p class="mt-2 text-2xl font-black text-white">96</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-200 shadow-sm md:grid md:grid-cols-4 md:gap-px">
                        <div class="bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Monitor</p>
                            <p class="mt-3 text-2xl font-black text-slate-950">Router + AP + Client</p>
                        </div>
                        <div class="bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Provision</p>
                            <p class="mt-3 text-2xl font-black text-slate-950">Radius + PPPoE + Plans</p>
                        </div>
                        <div class="bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Control</p>
                            <p class="mt-3 text-2xl font-black text-slate-950">Discovery + Config + Reboot</p>
                        </div>
                        <div class="bg-blue-600 p-5 text-white">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-100">Outcome</p>
                            <p class="mt-3 text-2xl font-black">کاهش کار دستی و حذف Winbox برای عملیات روزمره</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="capabilities" class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-8 xl:grid-cols-[minmax(320px,0.85fr)_minmax(0,1.15fr)]">
                        <div class="border-l-4 border-blue-600 pl-5">
                            <p class="text-sm font-semibold uppercase text-blue-700">سه محور اصلی</p>
                            <h2 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-4xl">
                                این سیستم فقط داشبورد نیست؛ یک اتاق عملیات برای شبکه MikroTik است
                            </h2>
                            <p class="mt-5 text-sm leading-7 text-slate-600">
                                ساختار صفحه به شکلی طراحی شده که مخاطب حتی بدون خواندن متن های طولانی، بفهمد این محصول دقیقاً برای چه کاری ساخته شده است: دیدن، تصمیم گرفتن و اجرا کردن.
                            </p>
                        </div>

                        <div class="grid gap-5 lg:grid-cols-3">
                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-6 shadow-sm lg:mt-10">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">01 / مانیتورینگ</p>
                                <h3 class="mt-4 text-2xl font-bold text-slate-900">دید لحظه ای از وضعیت شبکه</h3>
                                <p class="mt-4 text-sm leading-7 text-slate-600">
                                    وضعیت روترها، اکسس پوینت ها و وایرلس کلاینت ها در یک مسیر مشخص دیده می شود؛ از آنلاین/آفلاین بودن تا سیگنال، نسخه، uptime و حرکت بین APها.
                                </p>
                                <ul class="mt-5 space-y-3 text-sm leading-7 text-slate-700">
                                    <li>وضعیت تجهیزات و آلارم های فوری</li>
                                    <li>ردیابی وایرلس کلاینت و جابجایی بین APها</li>
                                    <li>جمع آوری Discovery و Snapshot از رادیو</li>
                                </ul>
                            </article>

                            <article class="rounded-[1.5rem] border border-blue-200 bg-blue-600 p-6 text-white shadow-md">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-100">02 / پروویژن</p>
                                <h3 class="mt-4 text-2xl font-bold">اجرای سریع سرویس و مشترک</h3>
                                <p class="mt-4 text-sm leading-7 text-blue-50">
                                    از تعریف پلن و اعتبار تا ایجاد دسترسی Radius و PPPoE، عملیات فروش و راه اندازی مشتری در همان پلتفرمی انجام می شود که تیم فنی شبکه را می بیند.
                                </p>
                                <ul class="mt-5 space-y-3 text-sm leading-7 text-blue-50">
                                    <li>پلن، اشتراک و حساب Radius در یک جریان</li>
                                    <li>فعال سازی، تعلیق و تغییر سرویس بدون کار دستی</li>
                                    <li>هماهنگی بین اطلاعات مشتری و تجهیزات شبکه</li>
                                </ul>
                            </article>

                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-md lg:mt-16">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">03 / مدیریت</p>
                                <h3 class="mt-4 text-2xl font-bold">اجرای دستورات مدیریتی بدون Winbox</h3>
                                <p class="mt-4 text-sm leading-7 text-slate-300">
                                    تنظیم Identity، DNS، NTP، Timezone، SNMP، Password و Reboot مستقیماً از پنل انجام می شود؛ با لاگ، اعتبارسنجی و تاریخچه اجرای دستور.
                                </p>
                                <ul class="mt-5 space-y-3 text-sm leading-7 text-slate-200">
                                    <li>Command Registry برای عملیات های تکرارشونده</li>
                                    <li>Log و Snapshot برای هر اقدام مدیریتی</li>
                                    <li>کنترل مبتنی بر Tenant و Credential</li>
                                </ul>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="workflow" class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-8 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold uppercase text-blue-700">چرخه عملیاتی</p>
                            <h2 class="mt-4 text-3xl font-black text-slate-950">از کشف تجهیز تا اقدام اصلاحی، مسیر گم نمی شود</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-600">
                                صفحه اصلی باید نشان بدهد که محصول فقط برای نمایش نیست. این پلتفرم از داده خام MikroTik تا تصمیم و اجرای عملیات را به یک فرآیند قابل پیگیری تبدیل می کند.
                            </p>
                        </div>

                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-200 shadow-sm">
                            <div class="grid gap-5 bg-white p-6 md:grid-cols-[96px_minmax(0,1fr)]">
                                <div class="rounded-2xl bg-slate-950 px-4 py-5 text-center text-white">
                                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Step</span>
                                    <p class="mt-2 text-3xl font-black">01</p>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900">کشف و همگام سازی</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">اطلاعات رادیو، روتر و وایرلس کلاینت از API گرفته می شود و همزمان در دیتابیس ذخیره و به روز می شود.</p>
                                </div>
                            </div>
                            <div class="grid gap-5 bg-white p-6 md:grid-cols-[96px_minmax(0,1fr)]">
                                <div class="rounded-2xl bg-blue-600 px-4 py-5 text-center text-white">
                                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-100">Step</span>
                                    <p class="mt-2 text-3xl font-black">02</p>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900">تشخیص وضعیت و اولویت بندی</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">تیم عملیات بلافاصله می بیند کدام تجهیز آفلاین است، کدام مشترک سیگنال ضعیف دارد و کجا باید وارد عمل شد.</p>
                                </div>
                            </div>
                            <div class="grid gap-5 bg-white p-6 md:grid-cols-[96px_minmax(0,1fr)]">
                                <div class="rounded-2xl bg-slate-200 px-4 py-5 text-center text-slate-900">
                                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Step</span>
                                    <p class="mt-2 text-3xl font-black">03</p>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900">اجرای مستقیم اقدام</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">همان جا Discovery، DNS، NTP، SNMP، Password یا Reboot اجرا می شود و نتیجه با لاگ کامل ثبت می گردد.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="operations" class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                        <div class="grid gap-6">
                            <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-6 shadow-sm">
                                <p class="text-sm font-semibold uppercase text-blue-700">برای تیم NOC و فنی</p>
                                <h2 class="mt-4 text-3xl font-black text-slate-950">وقتی اپراتور صفحه را باز می کند، باید بلافاصله مسیر اقدام را ببیند</h2>
                            </div>
                            <div class="grid gap-5 md:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">نمای واضح</p>
                                    <p class="mt-3 text-lg font-bold text-slate-900">از روتر مرکزی تا وایرلس کلاینت</p>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">داده های شبکه در چند ماژول جدا ولی مرتبط دیده می شود، نه در یک لیست شلوغ و مبهم.</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">اقدام کنترل شده</p>
                                    <p class="mt-3 text-lg font-bold text-slate-900">اعتبارسنجی، لاگ، اسنپ شات</p>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">هر دستور مدیریتی فقط وقتی اجرا می شود که شرایط دستگاه، tenant و credential درست باشد.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-md">
                            <p class="text-sm font-semibold uppercase text-slate-400">نمونه نتیجه</p>
                            <div class="mt-5 space-y-5">
                                <div class="rounded-2xl border border-slate-800 p-4">
                                    <p class="text-sm font-bold">بدون جابه جایی بین ابزارها</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">دیگر لازم نیست برای مانیتورینگ یک ابزار، برای Radius ابزار دیگر و برای تنظیم رادیو Winbox باز شود.</p>
                                </div>
                                <div class="rounded-2xl border border-slate-800 p-4">
                                    <p class="text-sm font-bold">زبان مشترک بین بخش فروش و فنی</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">پلن، مشتری، روتر، AP و رادیو در یک مدل عملیاتی دیده می شوند و انتقال کار بین تیم ها سریع تر می شود.</p>
                                </div>
                                <div class="rounded-2xl border border-blue-500/30 bg-blue-600/10 p-4">
                                    <p class="text-sm font-bold text-white">این دقیقاً برای ISP/WISP ساخته شده است</p>
                                    <p class="mt-2 text-sm leading-7 text-blue-100">نه یک CRM عمومی، نه یک NMS عمومی؛ بلکه یک پنل متمرکز برای عملیات شبکه MikroTik.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="audience" class="bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-200 shadow-sm md:grid-cols-3">
                            <div class="bg-white p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">ISP Owners</p>
                                <p class="mt-4 text-xl font-black text-slate-950">برای مدیرانی که دید یکپارچه می خواهند</p>
                            </div>
                            <div class="bg-white p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">NOC Teams</p>
                                <p class="mt-4 text-xl font-black text-slate-950">برای اپراتورهایی که باید سریع ببینند و سریع اقدام کنند</p>
                            </div>
                            <div class="bg-white p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Provisioning Ops</p>
                                <p class="mt-4 text-xl font-black text-slate-950">برای تیمی که راه اندازی مشتری را استاندارد و سریع می خواهد</p>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-blue-700 bg-blue-600 p-6 text-white shadow-md">
                            <p class="text-sm font-semibold uppercase text-blue-100">آماده شروع؟</p>
                            <h2 class="mt-4 text-2xl font-black leading-tight">اگر شبکه شما بر پایه MikroTik است، صفحه اصلی باید همین حالا تکلیف محصول را روشن کند.</h2>
                            <div class="mt-6 grid gap-3">
                                <a href="{{ route('auth.register') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-bold text-blue-700 shadow-sm transition hover:bg-blue-50">حساب جدید بسازید</a>
                                <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-300 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700">ورود برای کاربران فعلی</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <x-global-waiting-state />
</body>
</html>
