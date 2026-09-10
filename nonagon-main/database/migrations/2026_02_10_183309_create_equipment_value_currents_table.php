<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpParser\Node\Scalar\String_;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_value_currents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained();
            $table->decimal('current_book_value')->nullable();
            $table->string('current_book_value_source_type')->nullable(); // enum
            $table->decimal('current_market_value')->nullable();
            $table->string('current_market_value_source_type')->nullable(); // enum
            $table->string('current_market_value_source_detail')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_value_currents');
    }
};
