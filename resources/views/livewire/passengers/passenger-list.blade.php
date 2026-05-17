<div class="space-y-4">

    {{-- Flash --}}
    @if($flashMessage)
        <flux:callout
            :variant="$flashType === 'success' ? 'success' : 'danger'"
            :icon="$flashType === 'success' ? 'check-circle' : 'x-circle'"
            :heading="$flashMessage"
        />
    @endif

    {{-- Filters + Add button --}}
    <flux:card class="flex flex-wrap gap-4 items-end">
        <flux:input wire:model.live.debounce.300ms="search"
            label="Search" placeholder="Name, email, phone..."
            icon="magnifying-glass" class="w-56" />

        <flux:select wire:model.live="type" label="Type" class="w-36">
            <flux:select.option value="">All Types</flux:select.option>
            <flux:select.option>Regular</flux:select.option>
            <flux:select.option>Student</flux:select.option>
            <flux:select.option>Senior</flux:select.option>
            <flux:select.option>VIP</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="status" label="Status" class="w-36">
            <flux:select.option value="">All</flux:select.option>
            <flux:select.option>Active</flux:select.option>
            <flux:select.option>Suspended</flux:select.option>
            <flux:select.option>Blacklisted</flux:select.option>
        </flux:select>

        <flux:button wire:click="openCreate" variant="primary" icon="plus" class="ml-auto">
            Add Passenger
        </flux:button>
    </flux:card>

    {{-- Table --}}
    <flux:card class="p-0 overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column class="text-right">Trips</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($passengers as $passenger)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $passenger->full_name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $passenger->email }}</flux:table.cell>
                        <flux:table.cell>{{ $passenger->phone ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $passenger->passenger_type }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $color = match($passenger->account_status) {
                                    'Active'      => 'green',
                                    'Suspended'   => 'yellow',
                                    'Blacklisted' => 'red',
                                    default       => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$color" size="sm">{{ $passenger->account_status }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">{{ $passenger->total_trips }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button wire:click="openEdit({{ $passenger->id }})"
                                    size="xs" variant="ghost" icon="pencil">Edit</flux:button>

                                @if($passenger->account_status !== 'Active')
                                    <flux:button wire:click="reactivate({{ $passenger->id }})"
                                        size="xs" variant="ghost" class="text-green-600">Reactivate</flux:button>
                                @endif

                                <flux:button wire:click="delete({{ $passenger->id }})"
                                    size="xs" variant="ghost" class="text-red-500" icon="trash">
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-400 py-8">
                            No passengers found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="px-4 py-3 border-t dark:border-zinc-700">
            {{ $passengers->links() }}
        </div>
    </flux:card>

    {{-- Create / Edit Modal --}}
    <flux:modal wire:model="showModal" name="passenger-form" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-4">{{ $editingId ? 'Edit Passenger' : 'New Passenger' }}</flux:heading>

        <div class="space-y-4">
            <flux:input wire:model="form.full_name" label="Full Name" placeholder="Full name" required />
            @error('form.full_name') <flux:error>{{ $message }}</flux:error> @enderror

            <flux:input wire:model="form.email" label="Email" type="email" placeholder="email@example.com" required />
            @error('form.email') <flux:error>{{ $message }}</flux:error> @enderror

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="form.phone" label="Phone" placeholder="017xxxxxxxx" />
                </div>
                <div>
                    <flux:input wire:model="form.nid_number" label="NID Number" />
                </div>
            </div>

            <flux:textarea wire:model="form.address" label="Address" rows="2" />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="form.passenger_type" label="Type">
                    <flux:select.option>Regular</flux:select.option>
                    <flux:select.option>Student</flux:select.option>
                    <flux:select.option>Senior</flux:select.option>
                    <flux:select.option>VIP</flux:select.option>
                </flux:select>

                <flux:select wire:model="form.account_status" label="Status">
                    <flux:select.option>Active</flux:select.option>
                    <flux:select.option>Suspended</flux:select.option>
                    <flux:select.option>Blacklisted</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <flux:button wire:click="save" variant="primary">Save</flux:button>
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

</div>