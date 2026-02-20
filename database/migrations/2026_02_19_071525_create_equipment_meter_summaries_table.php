<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_meter_summaries', function (Blueprint $table) { // cron job
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_meter_id')->constrained();
            $table->decimal('current_reading_value')->nullable();
            $table->timestamp('current_reading_at')->nullable();
            $table->decimal('last_delta_value')->nullable();
            $table->integer('last_delta_period_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_meter_summaries');
    }
};
