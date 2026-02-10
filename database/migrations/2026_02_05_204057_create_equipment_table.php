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
            $table->foreignUuid('base_id')->constrained()->cascadeOnDelete();
            $table->string('eid')->nullable(); // Equipment Identification No
            $table->string('reg_no')->nullable();
            $table->string('model')->nullable();
            $table->integer('year_of_prod')->nullable();
            $table->string('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('city')->nullable();
            $table->string('last_maintenance')->nullable();
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
