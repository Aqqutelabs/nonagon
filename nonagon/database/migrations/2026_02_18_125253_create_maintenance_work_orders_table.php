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
        Schema::create('maintenance_work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained();
            $table->foreignUuid('equipment_id')->constrained();
            $table->string('title')->nullable();
            $table->string('type')->nullable(); // enum
            $table->string('status')->nullable(); // enum
            $table->string('priority')->nullable(); // enum
            $table->text('description')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('target_start_at')->nullable();
            $table->timestamp('target_due_at')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('maintenance_schedule_id')->constrained();
            $table->foreignUuid('breakdown_incident_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_work_orders');
    }
};
