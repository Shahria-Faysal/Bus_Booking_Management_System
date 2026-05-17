<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->string('action_type', 50);
            $table->string('table_name', 80);
            $table->integer('record_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            // No updated_at — audit logs are immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
