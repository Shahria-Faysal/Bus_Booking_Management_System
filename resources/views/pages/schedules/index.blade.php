@extends('layouts.app')
@section('title', 'Schedules')

<div class="">
    @section('content')
        <livewire:schedules.schedule-list />
        <flux:button href="{{ route('schedules.seat-summary') }}" variant="ghost" icon="chart-bar">
            Seat Summary
        </flux:button>
    @endsection
</div>