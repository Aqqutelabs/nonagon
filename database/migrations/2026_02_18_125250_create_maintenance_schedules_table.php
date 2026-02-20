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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained();
            $table->foreignUuid('equipment_id')->constrained();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('trigger_type')->nullable(); // enum
            $table->integer('time_frequency_value')->nullable();
            $table->string('time_frequency_unit')->nullable(); // enum
            $table->foreignUuid('equipment_meters_id')->constrained(); // equipment meters table
            $table->decimal('usage_threshold')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('next_due_date')->nullable();
            $table->foreignUuid('completed_by')->constrained()->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
