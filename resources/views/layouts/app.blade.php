<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Bus Ticket Booking') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- @fluxStyles --}}
</head>

<body class="flex min-h-screen bg-zinc-100 dark:bg-zinc-900">

    <flux:sidebar sticky stashable class="bg-zinc-900 border-r border-zinc-800">

        <flux:sidebar.toggle class="lg:hidden text-zinc-400" icon="x-mark" />

        {{-- Logo --}}
        <div class="flex items-center gap-2 px-4 py-5">
            <span class="text-2xl">🚌</span>
            <flux:heading size="lg" class="!text-white">BusTicket</flux:heading>
        </div>

        {{-- Nav --}}
        <div class="flex-1 px-3 py-2">
            <flux:navlist>
                <flux:navlist.item icon="squares-2x2" href="{{ route('dashboard') }}"
                    :current="request()->routeIs('dashboard')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Dashboard
                </flux:navlist.item>

                <flux:navlist.item icon="map" href="{{ route('routes.index') }}"
                    :current="request()->routeIs('routes.*')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Routes
                </flux:navlist.item>

                <flux:navlist.item icon="truck" href="{{ route('buses.index') }}"
                    :current="request()->routeIs('buses.*')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Buses
                </flux:navlist.item>

                <flux:navlist.item icon="calendar-days" href="{{ route('schedules.index') }}"
                    :current="request()->routeIs('schedules.*')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Schedules
                </flux:navlist.item>

                <flux:navlist.item icon="ticket" href="{{ route('bookings.index') }}"
                    :current="request()->routeIs('bookings.*')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Bookings
                </flux:navlist.item>

                <flux:navlist.item icon="users" href="{{ route('passengers.index') }}"
                    :current="request()->routeIs('passengers.*')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Passengers
                </flux:navlist.item>

                <flux:navlist.item icon="credit-card" href="{{ route('payments.index') }}"
                    :current="request()->routeIs('payments.*')"
                    class="!text-zinc-300 hover:!text-white hover:!bg-zinc-700">
                    Payments
                </flux:navlist.item>
            </flux:navlist>
        </div>

        <flux:spacer />

        {{-- Bottom: logout --}}
        <div class="px-3 py-3 border-t border-zinc-700">
            <flux:navlist>
                <flux:navlist.item icon="arrow-left-start-on-rectangle"
                    class="!text-zinc-400 hover:!text-white hover:!bg-zinc-700" x-data
                    x-on:click.prevent="document.getElementById('logout-form').submit()" href="#">
                    Logout
                </flux:navlist.item>
            </flux:navlist>
        </div>

        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>

    </flux:sidebar>

    {{-- Main area — flex-1 fills remaining width; Flux sidebar handles its own offset --}}
    <div class="flex flex-col flex-1 min-w-0">

        {{-- Top bar --}}
        <header
            class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-white border-b dark:bg-zinc-800 dark:border-zinc-700">
            <div class="flex items-center gap-3">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
                <flux:heading>@yield('title', 'Dashboard')</flux:heading>
            </div>
            <flux:text size="sm" class="text-zinc-400">{{ now()->format('D, d M Y') }}</flux:text>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-6">
            @yield('content')
            @isset($slot) {{ $slot }} @endisset
        </main>

    </div>

    @fluxScripts
</body>

</html>