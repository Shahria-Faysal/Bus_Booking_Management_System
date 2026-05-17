<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id('bus_id');
            $table->string('registration_no', 30)->unique();
            $table->string('bus_name', 100)->nullable();
            $table->enum('bus_type', ['AC', 'Non-AC', 'Sleeper', 'Mini'])->default('Non-AC');
            $table->integer('total_seats')->default(40);
            $table->string('operator_name', 150)->nullable();
            $table->enum('status', ['Active', 'Maintenance', 'Retired'])->default('Active');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
