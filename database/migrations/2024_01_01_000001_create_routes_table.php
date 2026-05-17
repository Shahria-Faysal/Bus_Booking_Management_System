<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id('route_id');
            $table->string('origin', 100);
            $table->string('destination', 100);
            $table->decimal('distance_km', 7, 2)->nullable();
            $table->decimal('base_fare', 8, 2);
            $table->decimal('duration_hours', 4, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
