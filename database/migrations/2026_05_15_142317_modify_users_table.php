<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable();
            $table->string('nid_number', 30)->unique()->nullable();
            $table->text('address')->nullable();
            $table->enum('passenger_type', ['Regular', 'Student', 'Senior', 'VIP'])->default('Regular');
            $table->enum('account_status', ['Active', 'Suspended', 'Blacklisted'])->default('Active');
            $table->date('join_date')->nullable();
            $table->integer('total_trips')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

        // Drop unique index first
        $table->dropUnique(['nid_number']);

        // Then drop columns
        $table->dropColumn([
            'phone',
            'nid_number',
            'address',
            'passenger_type',
            'account_status',
            'join_date',
            'total_trips'
        ]);

        });
    }
};
