<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/settings.php';

/*
|--------------------------------------------------------------------------
| Bus Ticket Booking — Web Routes
| All routes are protected by auth middleware (provided by Breeze).
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // ── Dashboard ────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Routes ─────────────────────────────────────────────────
    Route::prefix('routes')->name('routes.')->group(function () {
        Route::get('/', [RouteController::class, 'index'])->name('index');
    });

    // ── Buses ───────────────────────────────────────────────────
    Route::prefix('buses')->name('buses.')->group(function () {
        Route::get('/', [BusController::class, 'index'])->name('index');
    });

    // ── Schedules ────────────────────────────────────────────────
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        // Route::get('/{id}', [ScheduleController::class, 'show'])->name('show');
        Route::get('/seat-summary', [ScheduleController::class, 'seatSummary'])->name('seat-summary');
    });

    // ── Bookings ─────────────────────────────────────────────────
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/create', [BookingController::class, 'create'])->name('create');
        Route::get('/{id}', [BookingController::class, 'show'])->name('show');
    });

    // ── Passengers ───────────────────────────────────────────────
    Route::prefix('passengers')->name('passengers.')->group(function () {
        Route::get('/', [PassengerController::class, 'index'])->name('index');
        Route::get('/{id}', [PassengerController::class, 'show'])->name('show');
    });

    // ── Payments ─────────────────────────────────────────────────
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
    });

    // ── Audit Logs ──────────────────────────────────────────────
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
    });

});

