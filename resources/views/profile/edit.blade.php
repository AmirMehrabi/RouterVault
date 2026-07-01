@extends('layouts.admin')

@section('title', 'Profile')

@push('navbar-breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Profile', 'current' => true]]" />
@endpush

@section('content')
<div class="mx-auto max-w-3xl space-y-6 pb-10">
    <header>
        <h1 class="text-3xl font-bold tracking-tight text-slate-950">Profile</h1>
        <p class="mt-2 text-sm text-slate-600">Manage your personal details and account password.</p>
    </header>

    @if(session('success'))
        <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <section class="border border-slate-200 bg-white p-6 sm:p-8">
        <h2 class="text-lg font-bold">Personal information</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="mt-6">
            @csrf
            @method('PATCH')
            <x-input.text id="name" name="name" label="Full name" :value="$user->name" :required="true" />
            <x-input.email id="email" name="email" label="Email address" :value="$user->email" :required="true" />
            <x-input.tel id="phone" name="phone" label="Phone number" :value="$user->phone" />
            <button type="submit" class="mt-2 inline-flex min-h-11 items-center justify-center bg-blue-600 px-6 text-sm font-bold text-white transition hover:bg-blue-700">Save profile</button>
        </form>
    </section>

    <section class="border border-slate-200 bg-white p-6 sm:p-8">
        <h2 class="text-lg font-bold">Change password</h2>
        <form method="POST" action="{{ route('profile.password.update') }}" class="mt-6">
            @csrf
            @method('PATCH')
            <x-input.password id="current_password" name="current_password" label="Current password" :required="true" />
            <x-input.password id="password" name="password" label="New password" :required="true" minlength="8" />
            <x-input.password id="password_confirmation" name="password_confirmation" label="Confirm new password" :required="true" minlength="8" />
            <button type="submit" class="mt-2 inline-flex min-h-11 items-center justify-center bg-slate-950 px-6 text-sm font-bold text-white transition hover:bg-slate-800">Update password</button>
        </form>
    </section>
</div>
@endsection
