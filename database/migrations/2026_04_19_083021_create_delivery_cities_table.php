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
        Schema::create('delivery_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_region_id')
                ->constrained('delivery_regions')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('external_id')->nullable();
            $table->string('district_name')->nullable();
            $table->string('postal_code')->nullable();

            $table->timestamps();

            $table->unique(['delivery_region_id', 'name']);
            $table->index(['delivery_region_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_cities');
    }
};
