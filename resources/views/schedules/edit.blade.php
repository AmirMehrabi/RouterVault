@extends('layouts.admin')

@section('title', 'Edit Backup Schedule')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Edit Backup Schedule</h1>
    <form method="POST" action="{{ route('schedules.update', $schedule) }}">
        @method('PUT')
        @include('schedules._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('schedules.show', $schedule) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</a>
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Save Changes</button>
        </div>
    </form>
</div>
@endsection
