<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        name="description"
        content="ویسپا یک پلتفرم فارسی برای مدیریت عملیات ISP و شبکه‌های MikroTik است؛ از مانیتورینگ روتر و اکسس‌پوینت تا مدیریت مشترک، سرویس و اجرای عملیات روزمره."
    >
    <title>ویسپا | پلتفرم عملیات ISP و مدیریت شبکه MikroTik</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/fonts.css') }}">
    <style>
        body {
            font-family: Pelak, "Segoe UI", sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen" x-data="{ activeStory: 'noc', activeFaq: 0 }">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-900 text-lg font-black text-white">
                        WI
                    </div>
                    <div>
                        <p class="text-lg font-black text-slate-950">ویسپا</p>
                        <p class="text-xs font-semibold tracking-[0.22em] text-slate-500">WISPA ISP OPERATIONS</p>
                    </div>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 xl:flex">
                    <a href="#overview" class="transition hover:text-slate-950">معرفی</a>
                    <a href="#capabilities" class="transition hover:text-slate-950">قابلیت‌ها</a>
                    <a href="#experience" class="transition hover:text-slate-950">تجربه کار</a>
                    <a href="#audience" class="transition hover:text-slate-950">مخاطب</a>
                    <a href="#faq" class="transition hover:text-slate-950">سوالات متداول</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('auth.login') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                    >
                        ورود
                    </a>
                    <a
                        href="{{ route('auth.register') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800"
                    >
                        شروع استفاده
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section id="overview" class="border-b border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:px-8 lg:py-20">
                    <div class="space-y-8">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-bold text-slate-600">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            پنل فارسی مدیریت عملیات شبکه و مشترکین برای ISPها
                        </div>

                        <div class="space-y-5">
                            <h1 class="max-w-3xl text-2xl font-black leading-tight text-slate-950  xl:text-3xl">
                                همه چیزهایی که برای اداره یک شبکه MikroTik لازم دارید، در یک تجربه واحد و قابل‌فهم
                            </h1>
                            <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                                ویسپا برای تیم‌هایی ساخته شده که می‌خواهند از وضعیت شبکه، عملکرد اکسس‌پوینت‌ها، کیفیت مشترکین،
                                فعال‌سازی سرویس‌ها و عملیات روزانه، یک تصویر روشن و قابل اقدام داشته باشند؛ بدون پراکندگی ابزار،
                                بدون حدس، و بدون اتکای دائمی به Winbox.
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <p class="text-xs font-bold tracking-[0.22em] text-slate-500">NOC</p>
                                <p class="mt-3 text-2xl font-black text-slate-950">مانیتورینگ زنده</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">روتر، سایت، AP و کیفیت سرویس مشترک در یک نمای واحد.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <p class="text-xs font-bold tracking-[0.22em] text-slate-500">OPS</p>
                                <p class="mt-3 text-2xl font-black text-slate-950">اقدام سریع</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">تمدید، قطع، وصل، تغییر پلن و مدیریت دسترسی بدون کار دستی تکراری.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-900 bg-slate-900 p-5 text-white">
                                <p class="text-xs font-bold tracking-[0.22em] text-slate-300">CX</p>
                                <p class="mt-3 text-2xl font-black">تجربه حرفه‌ای</p>
                                <p class="mt-2 text-sm leading-7 text-slate-300">طراحی شده برای تیم فروش، پشتیبانی و عملیات در کنار هم.</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('auth.register') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3.5 text-sm font-black text-white transition hover:bg-slate-800"
                            >
                                ساخت حساب و شروع
                            </a>
                            <a
                                href="{{ route('auth.login') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                ورود به پنل
                            </a>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-bold text-slate-900">برای چه کسی؟</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">ISPهای محلی، تیم‌های NOC، پشتیبانی فنی و اپراتورهای چندسایته.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-bold text-slate-900">مسئله اصلی</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">پراکنده بودن داده‌های شبکه، مشترک و عملیات در چند ابزار متفاوت.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-bold text-slate-900">نتیجه</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">تصمیم سریع‌تر، اجرای منظم‌تر و زمان کمتر برای پیگیری‌های تکراری.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-x-10 top-8 h-24 rounded-full bg-slate-200 blur-3xl"></div>
                        <div class="relative rounded-[2rem] border border-slate-300 bg-slate-200 p-4 shadow-2xl shadow-slate-300/60">
                            <div class="rounded-[1.6rem] border border-slate-800 bg-slate-950 p-5 text-white">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                                    <div>
                                        <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">WISPA MONITOR</p>
                                        <p class="mt-2 text-xl font-black">نمای عملیاتی شبکه</p>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        ۱۲ سایت پایدار
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-4 xl:grid-cols-1">
                                    <div class="space-y-4">
                                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">Dashboard Snapshot</p>
                                                    <p class="mt-2 text-lg font-black">Device Operations Dashboard</p>
                                                    <p class="mt-2 text-sm text-slate-400">وضعیت لحظه‌ای بار سایت‌ها، سلامت APها و رویدادهای اخیر شبکه</p>
                                                </div>
                                                <div class="grid gap-2 text-left text-xs">
                                                    <span class="rounded-full border border-cyan-500/30 bg-cyan-500/10 px-2.5 py-1 text-cyan-300">۱۸۴۳ کاربر فعال</span>
                                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-amber-300">۳ ناحیه پرمصرف</span>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                                                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                    <p class="text-xs text-slate-500">روتر آنلاین</p>
                                                    <p class="mt-2 text-2xl font-black">24</p>
                                                </div>
                                                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                    <p class="text-xs text-slate-500">AP پایدار</p>
                                                    <p class="mt-2 text-2xl font-black">57</p>
                                                </div>
                                                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                    <p class="text-xs text-slate-500">هشدار باز</p>
                                                    <p class="mt-2 text-2xl font-black">7</p>
                                                </div>
                                                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                    <p class="text-xs text-slate-500">دستور امروز</p>
                                                    <p class="mt-2 text-2xl font-black">96</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]">
                                            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                                <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">Access Point View</p>
                                                <p class="mt-2 text-lg font-black">Tower-AP-03</p>
                                                <div class="mt-4 space-y-3 text-sm">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-slate-400">Connected Clients</span>
                                                        <span class="font-bold text-white">43</span>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-slate-400">Signal Quality</span>
                                                        <span class="font-bold text-white">89%</span>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-slate-400">CPU / Memory</span>
                                                        <span class="font-bold text-white">31% / 58%</span>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-slate-400">Channel Utilization</span>
                                                        <span class="font-bold text-white">67%</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">Operations Queue</p>
                                                        <p class="mt-2 text-lg font-black">آنچه باید همین حالا انجام شود</p>
                                                    </div>
                                                    <span class="rounded-full border border-slate-700 px-2.5 py-1 text-xs font-bold text-slate-300">Real-time</span>
                                                </div>
                                                <div class="mt-4 space-y-3">
                                                    <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                        <p class="text-sm font-bold text-white">دو AP با فشار بالا</p>
                                                        <p class="mt-1 text-xs leading-6 text-slate-400">جابجایی بار یا بازبینی فرکانس برای حفظ کیفیت سرویس</p>
                                                    </div>
                                                    <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                        <p class="text-sm font-bold text-white">سه مشترک با سیگنال ضعیف</p>
                                                        <p class="mt-1 text-xs leading-6 text-slate-400">بررسی سریع از روی سایت، AP و کیفیت لینک قبل از تماس پشتیبانی</p>
                                                    </div>
                                                    <div class="rounded-2xl border border-slate-800 bg-slate-950 p-3">
                                                        <p class="text-sm font-bold text-white">یک تمدید گروهی امروز</p>
                                                        <p class="mt-1 text-xs leading-6 text-slate-400">اعمال پلن و تمدید اشتراک از همان پنل مدیریتی</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                                        <p class="text-xs font-semibold tracking-[0.24em] text-slate-500">Why It Matters</p>
                                        <div class="mt-4 space-y-4">
                                            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
                                                <p class="text-sm font-bold text-white">هر داده، قابل اقدام است</p>
                                                <p class="mt-2 text-xs leading-6 text-slate-400">از دیدن وضعیت تا اجرای عملیات، مسیر تصمیم‌گیری قطع نمی‌شود.</p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
                                                <p class="text-sm font-bold text-white">برای چند نقش، یک تجربه مشترک</p>
                                                <p class="mt-2 text-xs leading-6 text-slate-400">مدیر، پشتیبان و اپراتور همه از یک زبان و یک صفحه استفاده می‌کنند.</p>
                                            </div>
                                            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
                                                <p class="text-sm font-bold text-white">تمرکز روی شبکه‌های واقعی</p>
                                                <p class="mt-2 text-xs leading-6 text-slate-400">طراحی‌شده بر اساس سناریوهای واقعی ISP و صفحات مدیریتی خود سامانه.</p>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>

                            <div class="mx-auto mt-4 h-4 w-40 rounded-full bg-slate-400"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <div class="grid gap-px overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-200 md:grid-cols-4">
                        <div class="bg-white p-6">
                            <p class="text-xs font-bold tracking-[0.22em] text-slate-500">ONE PLACE</p>
                            <p class="mt-3 text-2xl font-black text-slate-950">روتر، سایت، AP، مشترک و سرویس</p>
                        </div>
                        <div class="bg-white p-6">
                            <p class="text-xs font-bold tracking-[0.22em] text-slate-500">ONE FLOW</p>
                            <p class="mt-3 text-2xl font-black text-slate-950">از مشاهده تا اجرا، بدون جابجایی ابزار</p>
                        </div>
                        <div class="bg-white p-6">
                            <p class="text-xs font-bold tracking-[0.22em] text-slate-500">ONE LANGUAGE</p>
                            <p class="mt-3 text-2xl font-black text-slate-950">رابط فارسی و ساختار مناسب تیم‌های عملیاتی</p>
                        </div>
                        <div class="bg-slate-900 p-6 text-white">
                            <p class="text-xs font-bold tracking-[0.22em] text-slate-400">OUTCOME</p>
                            <p class="mt-3 text-2xl font-black">زمان کمتر برای پیگیری، تصویر واضح‌تر برای تصمیم‌گیری</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="capabilities" class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <p class="text-sm font-black text-slate-600">آنچه ویسپا را به ابزار روزمره تیم شما تبدیل می‌کند</p>
                        {{-- <h2 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-4xl">
                            این صفحه فقط درباره امکانات نیست؛ درباره سرعت فهم، سرعت اقدام و اطمینان در اداره شبکه است
                        </h2> --}}
                    </div>

                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-7 shadow-sm">
                            <p class="text-xs font-black tracking-[0.22em] text-slate-500">01 / مانیتورینگ شبکه</p>
                            <h3 class="mt-4 text-2xl font-black text-slate-950">تصویر زنده از وضعیت واقعی</h3>
                            <p class="mt-4 text-sm leading-8 text-slate-600">
                                تیم شما می‌تواند به جای جست‌وجوی پراکنده در چند محیط، وضعیت روترها، سایت‌ها، اکسس‌پوینت‌ها و شاخص‌های سلامت را
                                در یک صفحه منسجم ببیند.
                            </p>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-700">
                                <li>وضعیت آنلاین/آفلاین و شاخص‌های مصرف منابع</li>
                                <li>فشار بار روی APها و کیفیت سرویس مشترکین</li>
                                <li>نمای خلاصه برای تشخیص سریع اولویت‌ها</li>
                            </ul>
                        </article>

                        <article class="rounded-[1.75rem] border border-slate-900 bg-slate-900 p-7 text-white shadow-sm">
                            <p class="text-xs font-black tracking-[0.22em] text-slate-400">02 / مدیریت مشترک و سرویس</p>
                            <h3 class="mt-4 text-2xl font-black">فروش، تمدید و تغییر سرویس بدون اصطکاک</h3>
                            <p class="mt-4 text-sm leading-8 text-slate-300">
                                وقتی اطلاعات مشتری، وضعیت سرویس و داده‌های شبکه در کنار هم قرار بگیرند، تیم فروش و پشتیبانی تصمیم‌های دقیق‌تری
                                می‌گیرند و پاسخ سریع‌تری به مشتری می‌دهند.
                            </p>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-300">
                                <li>جریان روشن برای ایجاد، تمدید، تعلیق و فعال‌سازی</li>
                                <li>هماهنگی بین سرویس، دسترسی و وضعیت تجهیز</li>
                                <li>کاهش خطاهای دستی در عملیات تکراری روزانه</li>
                            </ul>
                        </article>

                        <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-7 shadow-sm">
                            <p class="text-xs font-black tracking-[0.22em] text-slate-500">03 / عملیات فنی</p>
                            <h3 class="mt-4 text-2xl font-black text-slate-950">اقدام مستقیم از داخل پنل</h3>
                            <p class="mt-4 text-sm leading-8 text-slate-600">
                                ویسپا فقط یک داشبورد برای تماشا نیست؛ محیطی است که تیم شما از داخل آن، کارهای روزمره مدیریت شبکه را به شکل کنترل‌شده
                                و قابل پیگیری انجام می‌دهد.
                            </p>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-700">
                                <li>مدیریت سناریوهای تکرارشونده برای تیم عملیات</li>
                                <li>ثبت و پیگیری بهتر رویدادها و اقدامات</li>
                                <li>کاهش وابستگی به ابزارهای پراکنده برای کارهای ساده</li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section id="experience" class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-8 xl:grid-cols-[minmax(0,0.78fr)_minmax(0,1.22fr)]">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <p class="text-sm font-black text-slate-600">تجربه‌ای که کاربر را نگه می‌دارد</p>
                            <h2 class="mt-4 text-3xl font-black text-slate-950">هر بخش طوری چیده شده که بازدیدکننده سریع‌تر بفهمد و بیشتر بماند</h2>
                            <p class="mt-4 text-sm leading-8 text-slate-600">
                                به جای شلوغی بصری، ویسپا با بلوک‌های روشن، داده‌های آشنا و داستان‌های کاربردی نشان می‌دهد در هر نقش چه ارزشی ایجاد می‌کند.
                            </p>

                            <div class="mt-6 space-y-3">
                                <button
                                    type="button"
                                    @click="activeStory = 'noc'"
                                    :class="activeStory === 'noc' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700'"
                                    class="flex w-full items-start justify-between rounded-2xl border p-4 text-right transition"
                                >
                                    <span>
                                        <span class="block text-sm font-black">برای تیم NOC</span>
                                        <span class="mt-1 block text-sm leading-7">در چند ثانیه می‌فهمد کدام سایت ناپایدار است و کجا باید اقدام شود.</span>
                                    </span>
                                    <span class="text-xs font-bold">01</span>
                                </button>

                                <button
                                    type="button"
                                    @click="activeStory = 'support'"
                                    :class="activeStory === 'support' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700'"
                                    class="flex w-full items-start justify-between rounded-2xl border p-4 text-right transition"
                                >
                                    <span>
                                        <span class="block text-sm font-black">برای پشتیبانی</span>
                                        <span class="mt-1 block text-sm leading-7">از روی وضعیت سرویس و AP، سریع‌تر ریشه مشکل مشتری را پیدا می‌کند.</span>
                                    </span>
                                    <span class="text-xs font-bold">02</span>
                                </button>

                                <button
                                    type="button"
                                    @click="activeStory = 'sales'"
                                    :class="activeStory === 'sales' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700'"
                                    class="flex w-full items-start justify-between rounded-2xl border p-4 text-right transition"
                                >
                                    <span>
                                        <span class="block text-sm font-black">برای فروش و مدیریت</span>
                                        <span class="mt-1 block text-sm leading-7">درک می‌کند چه سرویس‌هایی فعال هستند و کجا فرصت توسعه وجود دارد.</span>
                                    </span>
                                    <span class="text-xs font-bold">03</span>
                                </button>
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <div x-show="activeStory === 'noc'" x-transition.opacity.duration.200ms>
                                <p class="text-xs font-black tracking-[0.22em] text-slate-500">SCENARIO / NOC</p>
                                <h3 class="mt-3 text-2xl font-black text-slate-950">وقتی فشار شبکه بالا می‌رود، اولویت‌ها فوراً معلوم‌اند</h3>
                                <p class="mt-4 text-sm leading-8 text-slate-600">
                                    تیم NOC از روی داشبورد می‌بیند کدام سایت‌ها شلوغ‌تر شده‌اند، کدام APها به آستانه فشار رسیده‌اند و چه هشدارهایی نیاز به اقدام دارند.
                                </p>
                                <div class="mt-6 grid gap-4 md:grid-cols-3">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-xs text-slate-500">سایت‌های پایدار</p>
                                        <p class="mt-2 text-3xl font-black text-slate-950">12</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-xs text-slate-500">AP پرریسک</p>
                                        <p class="mt-2 text-3xl font-black text-slate-950">3</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-xs text-slate-500">اقدام فوری</p>
                                        <p class="mt-2 text-3xl font-black text-slate-950">7</p>
                                    </div>
                                </div>
                            </div>

                            <div x-show="activeStory === 'support'" x-transition.opacity.duration.200ms>
                                <p class="text-xs font-black tracking-[0.22em] text-slate-500">SCENARIO / SUPPORT</p>
                                <h3 class="mt-3 text-2xl font-black text-slate-950">پشتیبان فقط یک تیکت نمی‌بیند؛ زمینه واقعی مشکل را می‌فهمد</h3>
                                <p class="mt-4 text-sm leading-8 text-slate-600">
                                    وقتی کیفیت سیگنال، AP متصل، وضعیت سرویس و آخرین وضعیت ارتباط در یک دید قرار بگیرد، زمان تشخیص و زمان پاسخ‌گویی کمتر می‌شود.
                                </p>
                                <div class="mt-6 space-y-3">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-sm font-black text-slate-950">مشترک: 912xxxx</p>
                                        <p class="mt-2 text-sm leading-7 text-slate-600">سیگنال ضعیف، AP مقصد شلوغ، سرویس فعال و قابل تمدید</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-sm font-black text-slate-950">اقدام پیشنهادی</p>
                                        <p class="mt-2 text-sm leading-7 text-slate-600">بررسی فشار AP، بازبینی تنظیم فرکانس و اطلاع‌رسانی دقیق‌تر به مشتری</p>
                                    </div>
                                </div>
                            </div>

                            <div x-show="activeStory === 'sales'" x-transition.opacity.duration.200ms>
                                <p class="text-xs font-black tracking-[0.22em] text-slate-500">SCENARIO / MANAGEMENT</p>
                                <h3 class="mt-3 text-2xl font-black text-slate-950">مدیریت، فقط گزارش نمی‌خواهد؛ دید روشن برای رشد می‌خواهد</h3>
                                <p class="mt-4 text-sm leading-8 text-slate-600">
                                    از روی تمرکز مشترکین، توزیع فشار شبکه و روند عملیات، تصمیم‌های توسعه، فروش و ظرفیت‌گذاری با اطمینان بیشتری گرفته می‌شود.
                                </p>
                                <div class="mt-6 grid gap-4 md:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-xs text-slate-500">تمدیدهای امروز</p>
                                        <p class="mt-2 text-3xl font-black text-slate-950">28</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="text-xs text-slate-500">فرصت توسعه سایت</p>
                                        <p class="mt-2 text-3xl font-black text-slate-950">2</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="audience" class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-5 lg:grid-cols-4">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6">
                            <p class="text-sm font-black text-slate-950">ISPهای در حال رشد</p>
                            <p class="mt-3 text-sm leading-8 text-slate-600">برای تیم‌هایی که می‌خواهند رشد تعداد مشترکین باعث آشفتگی عملیاتی نشود.</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6">
                            <p class="text-sm font-black text-slate-950">تیم‌های چندنقشی</p>
                            <p class="mt-3 text-sm leading-8 text-slate-600">وقتی پشتیبانی، عملیات و فروش باید روی یک داده مشترک تصمیم بگیرند.</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6">
                            <p class="text-sm font-black text-slate-950">اپراتورهای چندسایته</p>
                            <p class="mt-3 text-sm leading-8 text-slate-600">برای شبکه‌هایی که دیدن سلامت هر سایت و AP باید سریع و قابل اتکا باشد.</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-900 bg-slate-900 p-6 text-white">
                            <p class="text-sm font-black">کسانی که زمان مهم است</p>
                            <p class="mt-3 text-sm leading-8 text-slate-300">اگر هر دقیقه تأخیر در تشخیص یا اجرای عملیات هزینه دارد، ویسپا برای شما ساخته شده است.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="faq" class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <p class="text-sm font-black text-slate-600">سوالات متداول</p>
                        <h2 class="mt-4 text-3xl font-black text-slate-950 sm:text-4xl">سوال‌هایی که یک مشتری بالقوه پیش از ثبت‌نام می‌پرسد</h2>
                    </div>

                    <div class="mt-10 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <button type="button" @click="activeFaq = activeFaq === 0 ? -1 : 0" class="flex w-full items-center justify-between gap-4 px-5 py-5 text-right">
                                <span class="text-base font-black text-slate-950">ویسپا دقیقاً چه مسئله‌ای را حل می‌کند؟</span>
                                <span class="text-slate-400" x-text="activeFaq === 0 ? '−' : '+'"></span>
                            </button>
                            <div x-show="activeFaq === 0" x-transition.opacity.duration.200ms class="px-5 pb-5 text-sm leading-8 text-slate-600">
                                پراکندگی بین دیدن وضعیت شبکه، مدیریت مشترک و اجرای عملیات روزانه را از بین می‌برد و همه این‌ها را در یک تجربه منظم و فارسی کنار هم می‌آورد.
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <button type="button" @click="activeFaq = activeFaq === 1 ? -1 : 1" class="flex w-full items-center justify-between gap-4 px-5 py-5 text-right">
                                <span class="text-base font-black text-slate-950">این سیستم فقط برای مانیتورینگ است؟</span>
                                <span class="text-slate-400" x-text="activeFaq === 1 ? '−' : '+'"></span>
                            </button>
                            <div x-show="activeFaq === 1" x-transition.opacity.duration.200ms class="px-5 pb-5 text-sm leading-8 text-slate-600">
                                خیر. ویسپا علاوه بر مانیتورینگ، برای مدیریت سرویس، پیگیری وضعیت مشترکین و اجرای جریان‌های عملیاتی روزمره هم طراحی شده است.
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <button type="button" @click="activeFaq = activeFaq === 2 ? -1 : 2" class="flex w-full items-center justify-between gap-4 px-5 py-5 text-right">
                                <span class="text-base font-black text-slate-950">برای چه نوع تیمی مناسب‌تر است؟</span>
                                <span class="text-slate-400" x-text="activeFaq === 2 ? '−' : '+'"></span>
                            </button>
                            <div x-show="activeFaq === 2" x-transition.opacity.duration.200ms class="px-5 pb-5 text-sm leading-8 text-slate-600">
                                برای ISPها و تیم‌هایی که فروش، پشتیبانی و عملیات‌شان باید به یک تصویر مشترک از وضعیت سرویس و شبکه دسترسی داشته باشند.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white">
                <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="rounded-[2rem] border border-slate-200 bg-slate-900 px-6 py-10 text-white shadow-xl shadow-slate-300/50 sm:px-10">
                        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center">
                            <div>
                                <p class="text-sm font-black text-slate-300">اگر قرار است مخاطب فقط یک چیز از ویسپا به خاطر بسپارد</p>
                                <h2 class="mt-4 text-3xl font-black leading-tight sm:text-4xl">
                                    ویسپا جایی است که عملیات شبکه، مدیریت مشترک و تصمیم‌های روزمره ISP به یک صفحه قابل اتکا تبدیل می‌شوند
                                </h2>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                                <a
                                    href="{{ route('auth.register') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3.5 text-sm font-black text-slate-950 transition hover:bg-slate-100"
                                >
                                    شروع استفاده از ویسپا
                                </a>
                                <a
                                    href="{{ route('auth.login') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-700 px-6 py-3.5 text-sm font-black text-white transition hover:bg-slate-800"
                                >
                                    ورود به حساب
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
