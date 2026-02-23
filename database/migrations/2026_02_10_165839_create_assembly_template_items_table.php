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
        Schema::create('assembly_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assembly_template_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('part_master_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity')->nullable();
            $table->integer('sequence')->nullable();
            $table->boolean('is_required')->nullable();
            $table->boolean('custom_slot')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_template_items');
    }
};
