<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('passenger_id');
            $table->decimal('amount_due', 8, 2);
            $table->decimal('amount_paid', 8, 2)->default(0.00);
            $table->enum('payment_method', ['Cash', 'Card', 'Mobile Banking', 'Online'])->default('Cash');
            $table->enum('payment_status', ['Pending', 'Paid', 'Refunded', 'Partial'])->default('Pending');
            $table->dateTime('payment_date')->nullable();
            $table->decimal('refund_amount', 8, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            $table->foreign('passenger_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
