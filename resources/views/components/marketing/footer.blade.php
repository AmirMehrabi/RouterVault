<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)_minmax(0,0.8fr)]">
            <div>
                <div class="flex items-center gap-4">
                    <x-brand-logo class="h-12" />
                </div>
                <p class="mt-5 max-w-xl text-sm leading-8 text-slate-600">
                    RouterVault پلتفرمی برای مدیریت عملیات ISP و شبکه‌های MikroTik است؛ جایی که مانیتورینگ، مدیریت مشترک و اقدام‌های روزمره
                    در یک تجربه منسجم و فارسی کنار هم قرار می‌گیرند.
                </p>
            </div>

            <div>
                <p class="text-sm font-black text-slate-950">دسترسی سریع</p>
                <div class="mt-4 space-y-3 text-sm font-semibold text-slate-600">
                    <a href="{{ route('home') }}" class="block transition hover:text-slate-950">خانه</a>
                    <a href="{{ route('pricing') }}" class="block transition hover:text-slate-950">تعرفه‌ها</a>
                    <a href="{{ route('about-us') }}" class="block transition hover:text-slate-950">درباره ما</a>
                    <a href="{{ route('contact-us') }}" class="block transition hover:text-slate-950">تماس با ما</a>
                </div>
            </div>

            <div>
                <p class="text-sm font-black text-slate-950">اقدام بعدی</p>
                <div class="mt-4 space-y-3 text-sm leading-7 text-slate-600">
                    <p>برای دریافت دمو، بررسی پلن مناسب یا قیمت سازمانی، از صفحه تماس با ما شروع کنید.</p>
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                        <a
                            href="{{ route('contact-us') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800"
                        >
                            درخواست مشاوره
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-slate-200 pt-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} RouterVault. همه حقوق محفوظ است.</p>
            <p>طراحی‌شده برای تیم‌های عملیات، پشتیبانی و مدیریت ISP</p>
        </div>
    </div>
</footer>
