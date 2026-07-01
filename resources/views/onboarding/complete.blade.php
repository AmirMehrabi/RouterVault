@extends('layouts.onboarding')

@section('title', 'Setup complete')

@section('content')
<div class="mx-auto max-w-lg py-8 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
        <svg class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" /></svg>
    </div>
    <h1 class="mt-6 text-3xl font-bold tracking-tight">Your workspace is ready</h1>
    <p class="mt-3 leading-7 text-slate-600">Your plan is active. You can add routers, configure schedules, and manage billing at any time.</p>
    <a href="{{ route('dashboard') }}" class="mt-8 inline-flex min-h-12 items-center justify-center bg-blue-600 px-8 text-sm font-bold text-white transition hover:bg-blue-700">
        Go to dashboard
    </a>
</div>
@endsection
