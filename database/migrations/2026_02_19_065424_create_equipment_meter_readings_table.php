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
        Schema::create('equipment_meter_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_meter_id')->constrained();
            $table->decimal('reading_value')->nullable();
            $table->timestamp('reading_at')->nullable();
            $table->string('source_type')->nullable(); //enum
            $table->foreignUuid('recorded_by')->constrained()->on('users');
            $table->timestamps();

            $table->index(['meter_id', 'reading_at']);
            $table->index(['meter_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_meter_readings');
    }
};
