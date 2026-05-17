<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $cards = [
                ['label' => 'Active Buses',          'value' => $stats['active_buses'],          'icon' => 'truck'],
                ['label' => 'Upcoming Trips',        'value' => $stats['upcoming_trips'],        'icon' => 'calendar-days'],
                ['label' => 'Active Passengers',     'value' => $stats['active_passengers'],     'icon' => 'users'],
                ['label' => 'Confirmed Bookings',    'value' => $stats['confirmed_bookings'],    'icon' => 'ticket'],
                ['label' => 'Cancelled Bookings',    'value' => $stats['cancelled_bookings'],    'icon' => 'x-circle'],
                ['label' => 'Revenue Collected',     'value' => '৳ ' . number_format($stats['total_revenue_collected'], 2), 'icon' => 'banknotes'],
                ['label' => 'Outstanding Balance',   'value' => '৳ ' . number_format($stats['outstanding_balance'], 2),    'icon' => 'exclamation-circle'],
            ];
        @endphp

        @foreach($cards as $card)
            <flux:card class="flex flex-col gap-1">
                <div class="flex items-center gap-2 text-zinc-500">
                    <flux:icon :name="$card['icon']" class="size-4" />
                    <flux:text size="sm">{{ $card['label'] }}</flux:text>
                </div>
                <flux:heading size="xl">{{ $card['value'] }}</flux:heading>
            </flux:card>
        @endforeach
    </div>

    {{-- Top Travelers --}}
    <flux:card>
        <flux:heading class="mb-4">🏆 Top Travelers</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column class="text-right">Bookings</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($topTravelers as $traveler)
                    <flux:table.row>
                        <flux:table.cell>{{ $traveler->full_name }}</flux:table.cell>
                        <flux:table.cell>{{ $traveler->passenger_type }}</flux:table.cell>
                        <flux:table.cell class="text-right font-semibold">{{ $traveler->booking_count }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

</div>