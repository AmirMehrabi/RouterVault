<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta
        name="description"
        content="تعرفه‌های ویسپا برای ISPها؛ از پلن پایه تا پلن سازمانی برای مدیریت عملیات شبکه و مشترکین."
    >
    <title>تعرفه‌های ویسپا | پلن‌های WISPA</title>
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
        <x-marketing.navbar current="pricing" />

        <main>
            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                    <div class="mx-auto max-w-3xl text-center">
                        <p class="text-sm font-black text-slate-600">تعرفه‌های ویسپا</p>
                        <h1 class="mt-4 text-4xl font-black leading-tight text-slate-950 sm:text-5xl">
                            پلنی را انتخاب کنید که با اندازه تیم و تعداد مشترکین شما هماهنگ است
                        </h1>
                        <p class="mt-6 text-base leading-8 text-slate-600 sm:text-lg">
                            قیمت‌گذاری ویسپا ساده است: تعداد کاربر مشخص، دسترسی روشن و مسیر ارتقا بدون ابهام. اگر نیاز شما از محدوده‌های
                            استاندارد بالاتر است، پلن سازمانی برایتان آماده است.
                        </p>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-100">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-6 xl:grid-cols-4">
                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <p class="text-sm font-black text-slate-950">Basic</p>
                            <p class="mt-3 text-sm leading-7 text-slate-600">برای تیم‌های کوچک که می‌خواهند عملیات را ساختارمند و حرفه‌ای شروع کنند.</p>
                            <div class="mt-6">
                                <p class="text-4xl font-black text-slate-950">۱۸ میلیون</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">سالانه</p>
                            </div>
                            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-black tracking-[0.22em] text-slate-500">ظرفیت</p>
                                <p class="mt-2 text-2xl font-black text-slate-950">تا ۵۰ کاربر</p>
                            </div>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-700">
                                <li>داشبورد عملیات شبکه و مشترکین</li>
                                <li>مدیریت پایه سرویس و وضعیت کاربران</li>
                                <li>مناسب برای شروع و تیم‌های جمع‌وجور</li>
                            </ul>
                        </article>

                        <article class="rounded-[1.75rem] border border-slate-900 bg-slate-900 p-7 text-white shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-black">Professional</p>
                                <span class="rounded-full border border-slate-700 px-3 py-1 text-xs font-black text-slate-300">پیشنهادی</span>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-300">برای ISPهای در حال رشد که نیاز به ظرفیت بیشتر و جریان عملیاتی روان‌تر دارند.</p>
                            <div class="mt-6">
                                <p class="text-4xl font-black">۲۸ میلیون</p>
                                <p class="mt-2 text-sm font-semibold text-slate-400">سالانه</p>
                            </div>
                            <div class="mt-6 rounded-2xl border border-slate-700 bg-slate-800 p-4">
                                <p class="text-xs font-black tracking-[0.22em] text-slate-400">ظرفیت</p>
                                <p class="mt-2 text-2xl font-black">تا ۲۰۰ کاربر</p>
                            </div>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-300">
                                <li>مناسب برای تیم‌های چندنقشی فروش، پشتیبانی و عملیات</li>
                                <li>پوشش بهتر برای سناریوهای روزمره رشد شبکه</li>
                                <li>تعادل مناسب بین هزینه و ظرفیت</li>
                            </ul>
                        </article>

                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <p class="text-sm font-black text-slate-950">Business</p>
                            <p class="mt-3 text-sm leading-7 text-slate-600">برای اپراتورهایی که چند سایت و حجم بیشتری از عملیات روزانه را مدیریت می‌کنند.</p>
                            <div class="mt-6">
                                <p class="text-4xl font-black text-slate-950">۴۰ میلیون</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">سالانه</p>
                            </div>
                            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-black tracking-[0.22em] text-slate-500">ظرفیت</p>
                                <p class="mt-2 text-2xl font-black text-slate-950">تا ۵۰۰ کاربر</p>
                            </div>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-700">
                                <li>مناسب برای عملیات وسیع‌تر و دید متمرکزتر</li>
                                <li>پاسخ بهتر به رشد شبکه و مشترکین</li>
                                <li>انتخاب مناسب برای مرحله بلوغ کسب‌وکار</li>
                            </ul>
                        </article>

                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-7 shadow-sm">
                            <p class="text-sm font-black text-slate-950">Enterprise</p>
                            <p class="mt-3 text-sm leading-7 text-slate-600">برای سازمان‌ها و ISPهای بزرگ با نیازهای خاص، چند تیم و مقیاس عملیاتی بالا.</p>
                            <div class="mt-6">
                                <p class="text-3xl font-black text-slate-950">تماس بگیرید</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">قیمت‌گذاری سفارشی</p>
                            </div>
                            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-black tracking-[0.22em] text-slate-500">ظرفیت</p>
                                <p class="mt-2 text-2xl font-black text-slate-950">بیش از ۱۰۰۰ کاربر</p>
                            </div>
                            <ul class="mt-6 space-y-3 text-sm leading-7 text-slate-700">
                                <li>طراحی مسیر همکاری متناسب با مقیاس شما</li>
                                <li>مناسب برای ساختارهای پیچیده‌تر و سناریوهای خاص</li>
                                <li>هماهنگی مستقیم برای برآورد و مشاوره</li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-7">
                            <p class="text-sm font-black text-slate-950">چطور پلن مناسب را انتخاب کنیم؟</p>
                            <div class="mt-6 space-y-4 text-sm leading-8 text-slate-600">
                                <p>اگر در ابتدای مسیر هستید و می‌خواهید عملیات‌تان ساختار پیدا کند، پلن `Basic` نقطه شروع خوبی است.</p>
                                <p>اگر تیم شما همزمان با رشد مشترکین، نیاز به هماهنگی بیشتر بین فروش، پشتیبانی و عملیات دارد، پلن `Professional` انتخاب متعادل‌تری است.</p>
                                <p>برای مجموعه‌هایی که چند سایت، بار عملیاتی بالاتر و فرایندهای جدی‌تری دارند، `Business` و `Enterprise` مناسب‌تر هستند.</p>
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-slate-900 bg-slate-900 p-7 text-white">
                            <p class="text-sm font-black text-slate-300">نیاز به پلن سازمانی دارید؟</p>
                            <h2 class="mt-4 text-3xl font-black leading-tight">اگر بیش از ۱۰۰۰ کاربر دارید، با ما برای قیمت‌گذاری و مشاوره تماس بگیرید</h2>
                            <p class="mt-4 text-sm leading-8 text-slate-300">
                                ما برای تیم‌های بزرگ‌تر، مسیر همکاری متناسب با ظرفیت و نوع عملیات شما در نظر می‌گیریم.
                            </p>
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <a
                                    href="{{ route('contact-us') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3.5 text-sm font-black text-slate-950 transition hover:bg-slate-100"
                                >
                                    تماس با ما
                                </a>
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
