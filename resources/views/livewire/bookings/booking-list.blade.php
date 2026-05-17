<div class="space-y-4">

    {{-- Flash --}}
    @if($flashMessage)
        <flux:callout
            :variant="$flashType === 'success' ? 'success' : 'danger'"
            icon="{{ $flashType === 'success' ? 'check-circle' : 'x-circle' }}"
            :heading="$flashMessage"
        />
    @endif

    {{-- Filters --}}
    <flux:card class="flex flex-wrap gap-4 items-end">
        <flux:select wire:model.live="status" label="Status" class="w-44">
            <flux:select.option value="">All Statuses</flux:select.option>
            <flux:select.option value="Confirmed">Confirmed</flux:select.option>
            <flux:select.option value="Cancelled">Cancelled</flux:select.option>
            <flux:select.option value="Completed">Completed</flux:select.option>
            <flux:select.option value="No-Show">No-Show</flux:select.option>
        </flux:select>

        <flux:button wire:click="bulkNoShow" variant="ghost" size="sm" class="ml-auto"
            x-on:click="$confirm('Mark all past unconfirmed bookings as No-Show?') || $wire.$cancel()">
            Mark No-Shows
        </flux:button>
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Passenger</flux:table.column>
                <flux:table.column>Route</flux:table.column>
                <flux:table.column>Journey Date</flux:table.column>
                <flux:table.column>Seat</flux:table.column>
                <flux:table.column class="text-right">Fare</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Payment</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($bookings as $booking)
                    <flux:table.row>
                        <flux:table.cell class="text-zinc-400">{{ $booking->booking_id }}</flux:table.cell>
                        <flux:table.cell>{{ $booking->passenger->full_name }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $booking->schedule->route->origin }}
                            → {{ $booking->schedule->route->destination }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $booking->journey_date->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $booking->seat_number }}</flux:table.cell>
                        <flux:table.cell class="text-right">৳{{ number_format($booking->fare_paid, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $variant = match($booking->booking_status) {
                                    'Confirmed' => 'blue',
                                    'Completed' => 'green',
                                    'Cancelled' => 'red',
                                    default     => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$variant" size="sm">{{ $booking->booking_status }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                :color="$booking->payment?->payment_status === 'Paid' ? 'green' : 'yellow'"
                                size="sm">
                                {{ $booking->payment?->payment_status ?? '—' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($booking->booking_status === 'Confirmed')
                                <div class="flex gap-2">
                                    <flux:button wire:click="completeBooking({{ $booking->booking_id }})"
                                        size="xs" variant="ghost">Complete</flux:button>
                                    <flux:button wire:click="cancelBooking({{ $booking->booking_id }})"
                                        size="xs" variant="ghost" class="text-red-500">Cancel</flux:button>
                                </div>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center text-zinc-400 py-8">
                            No bookings found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="px-4 py-3 border-t dark:border-zinc-700">
            {{ $bookings->links() }}
        </div>
    </flux:card>

</div>