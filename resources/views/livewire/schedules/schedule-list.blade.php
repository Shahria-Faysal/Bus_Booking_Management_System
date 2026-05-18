<div class="space-y-4">

    {{-- Flash --}}
    @if($flashMessage)
        <flux:callout :variant="$flashType === 'success' ? 'success' : 'danger'"
            :icon="$flashType === 'success' ? 'check-circle' : 'x-circle'" :heading="$flashMessage" />
    @endif

    {{-- Filters + Add button --}}
    <flux:card class="flex flex-wrap gap-4 items-end">
        <flux:select wire:model.live="status" label="Status" class="w-40">
            <flux:select.option value="">All Statuses</flux:select.option>
            <flux:select.option value="Scheduled">Scheduled</flux:select.option>
            <flux:select.option value="Departed">Departed</flux:select.option>
            <flux:select.option value="Arrived">Arrived</flux:select.option>
            <flux:select.option value="Cancelled">Cancelled</flux:select.option>
        </flux:select>

        <flux:input wire:model.live="dateFrom" label="From Date" type="date" class="w-40" />
        <flux:input wire:model.live="dateTo" label="To Date" type="date" class="w-40" />

        @if($dateFrom || $dateTo || $status)
            <flux:button wire:click="$set('status', ''); $set('dateFrom', ''); $set('dateTo', '')" variant="ghost" size="sm"
                class="self-end">
                Clear Filters
            </flux:button>
        @endif

        <flux:button wire:click="openCreate" variant="primary" icon="plus" class="ml-auto self-end">
            Add Schedule
        </flux:button>
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <flux:table>

            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Bus</flux:table.column>
                <flux:table.column>Route</flux:table.column>
                <flux:table.column>Departure</flux:table.column>
                <flux:table.column>Arrival</flux:table.column>
                <flux:table.column class="text-right">Seats</flux:table.column>
                <flux:table.column class="text-right">Fare</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse($schedules as $schedule)

                    <flux:table.row>

                        <flux:table.cell class="text-zinc-400">
                            {{ $schedule->schedule_id }}
                        </flux:table.cell>

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
                            <div>
                                {{ $schedule->arrival_time->format('d M Y') }}
                            </div>

                            <div class="text-xs text-zinc-400">
                                {{ $schedule->arrival_time->format('H:i') }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <flux:badge :color="$schedule->available_seats === 0
                                    ? 'red'
                                    : ($schedule->available_seats <= 5 ? 'yellow' : 'green')" size="sm">
                                {{ $schedule->available_seats }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            ৳{{ number_format($schedule->effective_fare, 2) }}

                            @if($schedule->fare_override)
                                <div class="text-xs text-zinc-400 line-through">
                                    ৳{{ number_format($schedule->route->base_fare, 2) }}
                                </div>
                            @endif

                        </flux:table.cell>

                        <flux:table.cell>

                            @php
                                $color = match ($schedule->schedule_status) {
                                    'Scheduled' => 'blue',
                                    'Departed' => 'yellow',
                                    'Arrived' => 'green',
                                    'Cancelled' => 'red',
                                    default => 'zinc',
                                };
                            @endphp

                            <flux:badge :color="$color" size="sm">
                                {{ $schedule->schedule_status }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            <div class="flex gap-1 flex-wrap">

                                <flux:button wire:click="openEdit({{ $schedule->schedule_id }})" size="xs" variant="ghost"
                                    icon="pencil">
                                    Edit
                                </flux:button>

                                @if($schedule->schedule_status === 'Scheduled')

                                    <flux:button wire:click="cancel({{ $schedule->schedule_id }})"
                                        wire:confirm="Cancel this schedule and all its confirmed bookings?" size="xs"
                                        variant="ghost" class="text-orange-500">
                                        Cancel
                                    </flux:button>

                                @endif

                                <flux:button wire:click="delete({{ $schedule->schedule_id }})"
                                    wire:confirm="Delete this schedule? This cannot be undone." size="xs" variant="ghost"
                                    class="text-red-500" icon="trash">
                                </flux:button>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="9" class="text-center text-zinc-400 py-10">
                            No schedules found. Click "Add Schedule" to create one.
                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>
    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal wire:model="showModal" name="schedule-form" class="w-full max-w-lg">

        <div class="px-6 pt-6">
            <flux:heading size="lg">
                {{ $editingId ? 'Edit Schedule' : 'New Schedule' }}
            </flux:heading>
        </div>

        <div class="space-y-4 py-4">

            {{-- Bus --}}
            <flux:select wire:model="bus_id" label="Bus" placeholder="Select a bus...">
                @foreach($buses as $bus)
                    <flux:select.option value="{{ $bus->bus_id }}">
                        {{ $bus->bus_name }} ({{ $bus->bus_type }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            @error('bus_id') <flux:error>{{ $message }}</flux:error> @enderror

            {{-- Route --}}
            <flux:select wire:model="route_id" label="Route" placeholder="Select a route...">
                @foreach($routes as $route)
                    <flux:select.option value="{{ $route->route_id }}">
                        {{ $route->origin }} → {{ $route->destination }}
                        (Base fare: ৳{{ number_format($route->base_fare, 2) }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            @error('route_id') <flux:error>{{ $message }}</flux:error> @enderror

            {{-- Departure / Arrival --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="departure_time" label="Departure" type="datetime-local" />
                    @error('departure_time') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:input wire:model="arrival_time" label="Arrival" type="datetime-local" />
                    @error('arrival_time') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>

            {{-- Seats / Fare override --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="available_seats" label="Available Seats" type="number" min="0" />
                    @error('available_seats') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:input wire:model="fare_override" label="Fare Override (optional)" type="number" step="0.01"
                        min="0" placeholder="Leave blank to use route base fare" />
                    @error('fare_override') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>

            {{-- Status (only shown when editing) --}}
            @if($editingId)
                <flux:select wire:model="schedule_status" label="Status">
                    <flux:select.option value="Scheduled">Scheduled</flux:select.option>
                    <flux:select.option value="Departed">Departed</flux:select.option>
                    <flux:select.option value="Arrived">Arrived</flux:select.option>
                    <flux:select.option value="Cancelled">Cancelled</flux:select.option>
                </flux:select>
                @error('schedule_status') <flux:error>{{ $message }}</flux:error> @enderror
            @endif

        </div>

        <div class="flex gap-3 px-6 pb-6">
            <flux:button wire:click="save" variant="primary">
                {{ $editingId ? 'Update Schedule' : 'Create Schedule' }}
            </flux:button>
            <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
        </div>

    </flux:modal>

</div>