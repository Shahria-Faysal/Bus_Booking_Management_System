{{-- resources/views/pages/schedules/seat-summary.blade.php --}}
@extends('layouts.app')
@section('title', 'Seat Summary')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Seat Summary</flux:heading>
            <flux:text class="text-zinc-400 mt-1">Live occupancy across all schedules</flux:text>
        </div>
        <flux:button href="{{ route('schedules.index') }}" variant="ghost" icon="arrow-left">
            Back to Schedules
        </flux:button>
    </div>

    <livewire:schedules.seat-summary />
@endsection