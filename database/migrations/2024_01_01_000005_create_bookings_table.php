<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('passenger_id');
            $table->string('seat_number', 10);
            $table->dateTime('booking_date')->useCurrent();
            $table->date('journey_date');
            $table->decimal('fare_paid', 8, 2);
            $table->enum('booking_status', ['Confirmed', 'Cancelled', 'Completed', 'No-Show'])->default('Confirmed');
            $table->decimal('discount_pct', 5, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('schedule_id')->on('schedules')->restrictOnDelete();
            $table->foreign('passenger_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
