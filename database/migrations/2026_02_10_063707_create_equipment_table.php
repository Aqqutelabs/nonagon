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
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('aid')->nullable(); // Asset ID
            $table->string('type')->nullable();
            $table->string('status')->default('Operational');
            $table->string('reg_no')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('emd')->nullable(); // Serial/ VIN
            $table->integer('manufacture_year')->nullable();
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('city')->nullable();
            $table->string('last_maintenance')->nullable();
            $table->foreignUuid('organization_id')->constrained();
            $table->foreignUuid('base_id')->constrained();
            $table->foreignUuid('site_id')->constrained();
            $table->foreignUuid('plant_id')->constrained();
            $table->foreignUuid('unit_id')->constrained();
            $table->foreignUuid('equipment_category_id')->constrained();
            $table->foreignUuid('equipment_subcategory_id')->constrained();
            $table->foreignUuid('equipment_type_id')->constrained();
            $table->foreignUuid('equipment_status_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
