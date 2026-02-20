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
        Schema::create('equipment_assembly_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_assembly_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('part_master_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('assembly_template_id')->constrained()->cascadeOnDelete();
            $table->string('custom_name');
            $table->decimal('quantity')->nullable();
            $table->integer('sequence')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_assembly_items');
    }
};
