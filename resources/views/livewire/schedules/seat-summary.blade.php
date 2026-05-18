<div class="space-y-4">

    {{-- Search --}}
    <flux:card class="flex items-end gap-4">
        <flux:input wire:model.live.debounce.300ms="search" label="Search"
            placeholder="Bus name, origin, destination..." icon="magnifying-glass" class="w-72" />

        @if($search)
            <flux:button wire:click="$set('search', '')" variant="ghost" size="sm" class="self-end">
                Clear
            </flux:button>
        @endif
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">

        <flux:table>

            <flux:table.columns>
                <flux:table.column>Bus</flux:table.column>
                <flux:table.column>Route</flux:table.column>
                <flux:table.column>Departure</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column class="text-right">Total Seats</flux:table.column>
                <flux:table.column class="text-right">Booked</flux:table.column>
                <flux:table.column class="text-right">Remaining</flux:table.column>
                <flux:table.column class="text-right">Fare (৳)</flux:table.column>
                <flux:table.column>Occupancy</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($schedules as $schedule)
                    @php
                        $total = $schedule->bus->total_seats;
                        $booked = $schedule->confirmed_bookings;
                        $remaining = $schedule->remaining_seats;
                        $occupancy = $total > 0
                            ? round(($booked / $total) * 100)
                            : 0;

                        $barColor = match (true) {
                            $occupancy >= 90 => 'bg-red-500',
                            $occupancy >= 60 => 'bg-yellow-400',
                            default => 'bg-green-500',
                        };
                    @endphp
                    <flux:table.row>
                        <flux:table.cell>

                            <div class="font-medium">
                                {{ $schedule->bus->bus_name }}
                            </div>

                            <div class="text-xs text-zinc-400">
                                {{ $schedule->bus->bus_type }}
                            </div>

                        </flux:table.cell>

                        <flux:table.cell>

                            {{ $schedule->route->origin }}

                            <span class="text-zinc-400 mx-1">→</span>

                            {{ $schedule->route->destination }}

                        </flux:table.cell>

                        <flux:table.cell>

                            <div>
                                {{ $schedule->departure_time->format('d M Y') }}
                            </div>

                            <div class="text-xs text-zinc-400">
                                {{ $schedule->departure_time->format('H:i') }}
                            </div>

                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $statusColor = match ($schedule->schedule_status) {
                                    'Scheduled' => 'blue',
                                    'Departed' => 'yellow',
                                    'Arrived' => 'green',
                                    'Cancelled' => 'red',
                                    default => 'zinc',
                                };
                            @endphp

                            <flux:badge :color="$statusColor" size="sm">
                                {{ $schedule->schedule_status }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            {{ $total }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right font-semibold">
                            {{ $booked }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <flux:badge :color="$remaining === 0
                                    ? 'red'
                                    : ($remaining <= 5 ? 'yellow' : 'green')" size="sm">
                                {{ $remaining }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            ৳{{ number_format($schedule->current_fare, 2) }}
                        </flux:table.cell>

                        {{-- Visual occupancy bar --}}
                        <flux:table.cell class="min-w-32">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-zinc-200 dark:bg-zinc-700 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all"
                                        style="width: {{ $occupancy }}%">
                                    </div>
                                </div>
                                <span class="text-xs text-zinc-500 w-10 text-right">
                                    {{ $occupancy }}%
                                </span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center text-zinc-400 py-10">
                            No schedules found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

</div>