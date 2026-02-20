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
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->default('Company');
            $table->text('description')->nullable();
            $table->string('department_code')->nullable();
            $table->foreignUuid('plant_id')->constrained();
            $table->foreignUuid('site_id')->constrained();
            $table->foreignUuid('base_id')->constrained();
            $table->foreignUuid('organization_id')->constrained();
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
