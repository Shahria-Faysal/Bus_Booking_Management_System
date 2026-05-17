<div class="max-w-2xl space-y-4">

    {{-- Flash --}}
    @if($flashMessage)
        <flux:callout
            :variant="$flashType === 'success' ? 'success' : 'danger'"
            :icon="$flashType === 'success' ? 'check-circle' : 'x-circle'"
            :heading="$flashMessage"
        />
    @endif

    <flux:card class="space-y-5">
        <flux:heading size="lg">New Booking</flux:heading>

        {{-- Schedule --}}
        <flux:select wire:model.live="schedule_id" label="Schedule" placeholder="Select a schedule...">
            @foreach($schedules as $schedule)
                <flux:select.option value="{{ $schedule->schedule_id }}">
                    {{ $schedule->route->origin }} → {{ $schedule->route->destination }}
                    | {{ $schedule->departure_time->format('d M Y, H:i') }}
                    | {{ $schedule->available_seats }} seats left
                    | ৳{{ $schedule->effective_fare }}
                </flux:select.option>
            @endforeach
        </flux:select>
        @error('schedule_id') <flux:error>{{ $message }}</flux:error> @enderror

        {{-- Passenger --}}
        <flux:select wire:model.live="passenger_id" label="Passenger" placeholder="Select a passenger...">
            @foreach($passengers as $passenger)
                <flux:select.option value="{{ $passenger->id }}">
                    {{ $passenger->full_name }} ({{ $passenger->passenger_type }})
                </flux:select.option>
            @endforeach
        </flux:select>
        @error('passenger_id') <flux:error>{{ $message }}</flux:error> @enderror

        {{-- Fare Preview --}}
        @if($previewFare !== null)
            <flux:callout variant="info" icon="information-circle">
                <flux:callout.heading>Fare: ৳{{ number_format($previewFare, 2) }}</flux:callout.heading>
                @if($previewDiscount)
                    <flux:callout.text>{{ $previewDiscount }}</flux:callout.text>
                @endif
            </flux:callout>
        @endif

        {{-- Seat Number --}}
        <flux:input wire:model="seat_number" label="Seat Number" placeholder="e.g. A1" />
        @error('seat_number') <flux:error>{{ $message }}</flux:error> @enderror

        {{-- Journey Date --}}
        <flux:input wire:model="journey_date" label="Journey Date" type="date"
            min="{{ now()->toDateString() }}" />
        @error('journey_date') <flux:error>{{ $message }}</flux:error> @enderror

        {{-- Notes --}}
        <flux:textarea wire:model="notes" label="Notes (optional)" rows="2"
            placeholder="Any special requests..." />

        <div class="flex gap-3 pt-2">
            <flux:button wire:click="save" variant="primary">Confirm Booking</flux:button>
            <flux:button href="{{ route('bookings.index') }}" variant="ghost">Cancel</flux:button>
        </div>
    </flux:card>

</div>