<div class="space-y-4">

    {{-- Flash --}}
    @if($flashMessage)
        <flux:callout :variant="$flashType === 'success' ? 'success' : 'danger'"
            :icon="$flashType === 'success' ? 'check-circle' : 'x-circle'" :heading="$flashMessage" />
    @endif

    {{-- Filters + Add button --}}
    <flux:card class="flex flex-wrap gap-4 items-end">
        <flux:input wire:model.live.debounce.300ms="search" label="Search" placeholder="Origin or destination..."
            icon="magnifying-glass" class="w-64" />

        @if($search)
            <flux:button wire:click="$set('search', '')" variant="ghost" size="sm" class="self-end">
                Clear
            </flux:button>
        @endif

        <flux:button wire:click="openCreate" variant="primary" icon="plus" class="ml-auto self-end">
            Add Route
        </flux:button>
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">

        <flux:table>

            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Origin</flux:table.column>
                <flux:table.column>Destination</flux:table.column>
                <flux:table.column class="text-right">Distance (km)</flux:table.column>
                <flux:table.column class="text-right">Base Fare (৳)</flux:table.column>
                <flux:table.column class="text-right">Duration (hrs)</flux:table.column>
                <flux:table.column class="text-right">Schedules</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse($routes as $route)

                            <flux:table.row>

                                <flux:table.cell class="text-zinc-400">
                                    {{ $route->route_id }}
                                </flux:table.cell>

                                <flux:table.cell class="font-medium">
                                    {{ $route->origin }}
                                </flux:table.cell>

                                <flux:table.cell class="font-medium">
                                    {{ $route->destination }}
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    {{ $route->distance_km ? number_format($route->distance_km, 1) : '—' }}
                                </flux:table.cell>

                                <flux:table.cell class="text-right font-semibold">
                                    ৳{{ number_format($route->base_fare, 2) }}
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    {{ $route->duration_hours
                    ? number_format($route->duration_hours, 1) . ' hrs'
                    : '—' }}
                                </flux:table.cell>

                                <flux:table.cell class="text-right">

                                    <flux:badge :color="$route->schedules_count > 0 ? 'blue' : 'zinc'" size="sm">
                                        {{ $route->schedules_count }}
                                    </flux:badge>

                                </flux:table.cell>

                                <flux:table.cell>

                                    <div class="flex gap-1">

                                        <flux:button wire:click="openEdit({{ $route->route_id }})" size="xs" variant="ghost"
                                            icon="pencil">
                                            Edit
                                        </flux:button>

                                        <flux:button wire:click="delete({{ $route->route_id }})"
                                            wire:confirm="Delete this route? This cannot be undone." size="xs" variant="ghost"
                                            icon="trash" class="text-red-500">
                                        </flux:button>

                                    </div>

                                </flux:table.cell>

                            </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="8" class="text-center text-zinc-400 py-10">
                            No routes found. Click "Add Route" to create one.
                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

        <div class="px-4 py-3 border-t dark:border-zinc-700">
            {{ $routes->links() }}
        </div>

    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal wire:model="showModal" name="route-form" class="w-full max-w-md">

        <div class="px-6 pt-6">
            <flux:heading size="lg">
                {{ $editingId ? 'Edit Route' : 'Add New Route' }}
            </flux:heading>
        </div>

        <div class="space-y-4 py-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="origin" label="Origin" placeholder="e.g. Dhaka" />
                    @error('origin') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:input wire:model="destination" label="Destination" placeholder="e.g. Chittagong" />
                    @error('destination') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>

            <div>
                <flux:input wire:model="base_fare" label="Base Fare (৳)" type="number" step="0.01" min="0"
                    placeholder="0.00" />
                @error('base_fare') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="distance_km" label="Distance (km) — optional" type="number" step="0.1"
                        min="0" placeholder="e.g. 250" />
                    @error('distance_km') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:input wire:model="duration_hours" label="Duration (hrs) — optional" type="number" step="0.5"
                        min="0" placeholder="e.g. 4.5" />
                    @error('duration_hours') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>

        </div>

        <div class="flex gap-3 px-6 pb-6">
            <flux:button wire:click="save" variant="primary">
                {{ $editingId ? 'Update Route' : 'Add Route' }}
            </flux:button>
            <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
        </div>

    </flux:modal>

</div>