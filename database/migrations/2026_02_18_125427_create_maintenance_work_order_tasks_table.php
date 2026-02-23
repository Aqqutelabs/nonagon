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
        Schema::create('maintenance_work_order_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_work_order_id')->constrained();
            $table->text('description')->nullable();
            $table->integer('sequence')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('completed_by')->constrained()->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_work_order_tasks');
    }
};
