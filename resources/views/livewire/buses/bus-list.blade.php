<div class="space-y-4">

    {{-- Flash --}}
    @if($flashMessage)
        <flux:callout :variant="$flashType === 'success' ? 'success' : 'danger'"
            :icon="$flashType === 'success' ? 'check-circle' : 'x-circle'" :heading="$flashMessage" />
    @endif

    {{-- Filters + Add button --}}
    <flux:card class="flex flex-wrap gap-4 items-end">
        <flux:input wire:model.live.debounce.300ms="search" label="Search" placeholder="Name, reg no, operator..."
            icon="magnifying-glass" class="w-56" />

        <flux:select wire:model.live="type" label="Type" class="w-36">
            <flux:select.option value="">All Types</flux:select.option>
            <flux:select.option value="AC">AC</flux:select.option>
            <flux:select.option value="Non-AC">Non-AC</flux:select.option>
            <flux:select.option value="Sleeper">Sleeper</flux:select.option>
            <flux:select.option value="Mini">Mini</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="status" label="Status" class="w-36">
            <flux:select.option value="">All</flux:select.option>
            <flux:select.option value="Active">Active</flux:select.option>
            <flux:select.option value="Maintenance">Maintenance</flux:select.option>
            <flux:select.option value="Retired">Retired</flux:select.option>
        </flux:select>

        @if($search || $type || $status)
            <flux:button wire:click="$set('search', ''); $set('type', ''); $set('status', '')" variant="ghost" size="sm"
                class="self-end">
                Clear Filters
            </flux:button>
        @endif

        <flux:button wire:click="openCreate" variant="primary" icon="plus" class="ml-auto self-end">
            Add Bus
        </flux:button>
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">

        <flux:table>

            <flux:table.columns>
                <flux:table.column>Bus Name</flux:table.column>
                <flux:table.column>Reg No</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column class="text-right">Seats</flux:table.column>
                <flux:table.column>Operator</flux:table.column>
                <flux:table.column class="text-right">Schedules</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse($buses as $bus)

                    <flux:table.row>

                        <flux:table.cell class="font-medium">
                            {{ $bus->bus_name }}
                        </flux:table.cell>

                        <flux:table.cell class="font-mono text-sm text-zinc-500">
                            {{ $bus->registration_no }}
                        </flux:table.cell>

                        <flux:table.cell>

                            @php
                                $typeColor = match ($bus->bus_type) {
                                    'Sleeper' => 'purple',
                                    'AC' => 'blue',
                                    'Non-AC' => 'zinc',
                                    'Mini' => 'yellow',
                                    default => 'zinc',
                                };
                            @endphp

                            <flux:badge :color="$typeColor" size="sm">
                                {{ $bus->bus_type }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            {{ $bus->total_seats }}
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500">
                            {{ $bus->operator_name ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            {{ $bus->schedules_count }}
                        </flux:table.cell>

                        <flux:table.cell>

                            @php
                                $statusColor = match ($bus->status) {
                                    'Active' => 'green',
                                    'Maintenance' => 'yellow',
                                    'Retired' => 'red',
                                    default => 'zinc',
                                };
                            @endphp

                            <flux:badge :color="$statusColor" size="sm">
                                {{ $bus->status }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            <div class="flex gap-1 flex-wrap">

                                <flux:button wire:click="openEdit({{ $bus->bus_id }})" size="xs" variant="ghost"
                                    icon="pencil">
                                    Edit
                                </flux:button>

                                @if($bus->status !== 'Retired')

                                    <flux:button wire:click="retire({{ $bus->bus_id }})"
                                        wire:confirm="Mark this bus as retired?" size="xs" variant="ghost"
                                        class="text-orange-500">
                                        Retire
                                    </flux:button>

                                @endif

                                <flux:button wire:click="delete({{ $bus->bus_id }})"
                                    wire:confirm="Delete this bus? This cannot be undone." size="xs" variant="ghost"
                                    icon="trash" class="text-red-500">
                                </flux:button>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="8" class="text-center text-zinc-400 py-10">
                            No buses found. Click "Add Bus" to add one.
                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

        <div class="px-4 py-3 border-t dark:border-zinc-700">
            {{ $buses->links() }}
        </div>

    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal wire:model="showModal" name="bus-form" class="w-full max-w-md">

        <div class="px-6 pt-6">
            <flux:heading size="lg">
                {{ $editingId ? 'Edit Bus' : 'Add New Bus' }}
            </flux:heading>
        </div>

        <div class="space-y-4 py-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="bus_name" label="Bus Name" placeholder="e.g. Green Line Express" />
                    @error('bus_name') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:input wire:model="registration_no" label="Registration No" placeholder="e.g. DHA-12345" />
                    @error('registration_no') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:select wire:model="bus_type" label="Bus Type">
                        <flux:select.option value="Non-AC">Non-AC</flux:select.option>
                        <flux:select.option value="AC">AC</flux:select.option>
                        <flux:select.option value="Sleeper">Sleeper</flux:select.option>
                        <flux:select.option value="Mini">Mini</flux:select.option>
                    </flux:select>
                    @error('bus_type') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:input wire:model="total_seats" label="Total Seats" type="number" min="1" max="100" />
                    @error('total_seats') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>

            <div>
                <flux:input wire:model="operator_name" label="Operator Name (optional)"
                    placeholder="e.g. Green Line Paribahan" />
                @error('operator_name') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            {{-- Only show status when editing --}}
            @if($editingId)
                <div>
                    <flux:select wire:model="bus_status" label="Status">
                        <flux:select.option value="Active">Active</flux:select.option>
                        <flux:select.option value="Maintenance">Maintenance</flux:select.option>
                        <flux:select.option value="Retired">Retired</flux:select.option>
                    </flux:select>
                    @error('bus_status') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            @endif

        </div>

        <div class="flex gap-3 px-6 pb-6">
            <flux:button wire:click="save" variant="primary">
                {{ $editingId ? 'Update Bus' : 'Add Bus' }}
            </flux:button>
            <flux:button wire:click="closeModal" variant="ghost">Cancel</flux:button>
        </div>

    </flux:modal>

</div>