@extends('layouts.app')
@section('title', 'Bookings')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold">All Bookings</h2>
        <a href="{{ route('bookings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
            + New Booking
        </a>
    </div>

    <livewire:bookings.booking-list />
@endsection
