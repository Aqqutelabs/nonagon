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
        Schema::create('breakdown_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained();
            $table->foreignUuid('equipment_id')->constrained();
            $table->timestamp('occured_at')->nullable();
            $table->string('location_snapshot')->nullable();
            $table->foreignUuid('reported_by')->nullable()->constrained()->on('users');
            $table->string('summary')->nullable();
            $table->string('probable_cause')->nullable();
            $table->string('severity')->nullable(); // enum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breakdown_incidents');
    }
};
