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
        Schema::create('delivery_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_service_id')
                ->constrained('delivery_services')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('external_id')->nullable();

            $table->timestamps();

            $table->unique(['delivery_service_id', 'name']);
            $table->unique(['delivery_service_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_regions');
    }
};
