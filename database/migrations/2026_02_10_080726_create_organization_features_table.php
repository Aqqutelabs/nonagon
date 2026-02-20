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
        Schema::create('organization_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained();
            //$table->foreignUuid('feature_registry_id')->constrained(); // Feature key
            $table->string('feature_key');
            $table->boolean('is_enabled');
            $table->integer('limit_value')->nullable();
            $table->string('source'); // enum - template, custom, addOn
            $table->unique(['organization_id', 'feature_key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_features');
    }
};
