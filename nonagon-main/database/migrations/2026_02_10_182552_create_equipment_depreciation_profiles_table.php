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
        Schema::create('equipment_depreciation_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained();
            $table->string('method')->default('straight_line'); // enum straight_line
            $table->decimal('acquisition_cost')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('useful_life_years')->nullable();
            $table->decimal('salvage_value')->nullable();
            $table->date('start_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_depreciation_profiles');
    }
};
