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
        Schema::create('part_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('manfacturer_part_number')->nullable();
            $table->string('oem_part_number')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->string('function')->nullable();
            $table->boolean('is_serviceable')->nullable();
            $table->boolean('is_consumable')->nullable();
            $table->boolean('is_global')->nullable();
            $table->boolean('is_verified')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_masters');
    }
};
