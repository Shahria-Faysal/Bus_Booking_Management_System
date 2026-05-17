@extends('layouts.app')
@section('title', 'New Booking')

@section('content')
    <div class="mb-6">
        <a href="{{ route('bookings.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Bookings</a>
    </div>

    <livewire:bookings.create-booking />
@endsection
