@extends('layouts.onboarding')

@section('title', 'Connect your first router')

@section('content')
<div class="mx-auto max-w-2xl">
    <header class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Connect your first router</h1>
        <p class="mt-3 text-slate-600">Add a MikroTik router now, or finish setup and do this later.</p>
    </header>

    <section class="border border-slate-200 bg-white p-6 sm:p-8">
        <form id="router-form" method="POST" action="{{ route('onboarding.router') }}">
            @csrf
            <x-input.text id="name" name="name" label="Router name" placeholder="Branch Office Router" :required="true" autofocus />
            <x-input.text id="ip_address" name="ip_address" label="IP address" placeholder="192.168.88.1" :required="true" />
            <x-input.text id="api_username" name="api_username" label="Username" value="admin" :required="true" />
            <x-input.password id="api_password" name="api_password" label="Password" :required="true" :show-toggle="true" />
            <x-input.select
                id="ssh_auth_method"
                name="ssh_auth_method"
                label="Authentication method"
                value="password"
                :options="['password' => 'Password', 'private_key' => 'Private key']"
            />
            <x-input.number id="ssh_port" name="ssh_port" label="SSH port" value="22" min="1" max="65535" help="The default SSH port is 22." />
        </form>

        <div class="mt-3 grid gap-3">
            <button form="router-form" type="submit" class="flex min-h-12 items-center justify-center bg-blue-600 px-6 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                Save and continue
            </button>
            <form method="POST" action="{{ route('onboarding.router.skip') }}">
                @csrf
                <button type="submit" class="flex min-h-12 w-full items-center justify-center border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                    Finish setup without a router
                </button>
            </form>
            <a href="{{ route('onboarding.step', 1) }}" class="mt-2 text-sm font-semibold text-blue-700 hover:text-blue-900">← Back to plans</a>
        </div>
    </section>
</div>
@endsection
