# 🚌 Bus Booking Management System

A full-featured bus booking management admin panel built with **Laravel 13**, **Livewire 4**, and **Flux UI**. Manage buses, routes, schedules, bookings, passengers, and payments — all in one dashboard (Passenger side will be added later).

## About

Admin-side bus ticket booking management system for transport operators. Handles the full operational workflow — managing bus fleets and routes, scheduling trips, processing bookings with automatic passenger-type discounts (Student/Senior/VIP), tracking payments with partial/full/refund support, and maintaining an immutable audit trail. Currently a single-role admin dashboard; passenger-facing frontend planned in React.

##Live Demo: https://bus-booking-management-system-0vln.onrender.com
## Features

- **Dashboard** — Real-time stats: active buses, upcoming trips, revenue, outstanding balance
- **Schedule Management** — Create, edit, cancel schedules with bus & route assignment
- **Booking Management** — Create bookings, apply passenger-type discounts (Student/Senior/VIP), auto-calculate fares
- **Passenger Management** — CRUD with search, filter by type/status, loyalty tiers
- **Payment Handling** — Full/partial payments, refunds, method-wise collection reports
- **Audit Logging** — All critical actions logged with timestamps
- **Authentication** — Login, registration, password reset, email verification, 2FA via Laravel Fortify
- **Responsive UI** — Built with Flux UI + Tailwind CSS v4

## Tech Stack

| Layer | Tech |
|-------|------|
| Framework | Laravel 13 |
| Frontend | Livewire 4, Flux UI (free), Tailwind CSS v4 |
| Auth | Laravel Fortify (with 2FA) |
| Database | MySQL |
| Testing | PHPUnit 12 |
| Tooling | Laravel Pint, Laravel Boost |

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL

### Installation

```bash
# Clone the repo
git clone https://github.com/Shahria-Faysal/Bus_Booking_Management_System.git
cd bus-booking-management

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your .env database connection, then run:
php artisan migrate --seed

# Build frontend
npm run build

# Start the dev server
php artisan serve
```

### Development

```bash
# Start dev server with Vite hot-reload
composer run dev
```

### Testing

```bash
php artisan test
```

## Project Structure

```
app/
├── Actions/Fortify/       # Auth logic (CreateNewUser, ResetUserPassword)
├── Http/Controllers/      # Web controllers
├── Livewire/              # Livewire components
│   ├── Dashboard/
│   ├── Bookings/
│   ├── Schedules/
│   ├── Passengers/
│   ├── Payments/
│   └── Settings/
├── Models/                # Eloquent models
├── Providers/             # Service providers
└── Services/              # Business logic layer

resources/views/
├── layouts/               # App & auth layouts
├── livewire/              # Livewire component views
└── pages/                 # Page-level Blade views

routes/
├── web.php                # Main routes
└── settings.php           # Settings routes

database/
└── migrations/            # All table migrations
```

## Screenshots

![alt text](<public/screenshots/Screenshot 2026-05-19 122114.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120341.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120351.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120455.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120620.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120707.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120936.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 120957.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 121337.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 121401.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 122106.png>)
![alt text](<public/screenshots/Screenshot 2026-05-19 121910.png>)

## License

MIT
