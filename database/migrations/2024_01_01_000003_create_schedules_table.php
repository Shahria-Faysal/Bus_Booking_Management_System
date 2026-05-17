<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('route_id');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->integer('available_seats');
            $table->decimal('fare_override', 8, 2)->nullable();
            $table->enum('schedule_status', ['Scheduled', 'Departed', 'Arrived', 'Cancelled'])->default('Scheduled');
            $table->timestamps();

            $table->foreign('bus_id')->references('bus_id')->on('buses')->restrictOnDelete();
            $table->foreign('route_id')->references('route_id')->on('routes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
