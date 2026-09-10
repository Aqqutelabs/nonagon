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
        Schema::create('subscription_template_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_template_id')->constrained();
            //$table->foreignUuid('feature_registry_id')->constrained(); // Feature key
            $table->string('feature_key');
            $table->boolean('is_enabled');
            $table->integer('limit_value')->nullable();
            $table->unique(['subscription_template_id', 'feature_key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_template_features');
    }
};
