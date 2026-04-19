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
        Schema::create('delivery_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_city_id')
                ->constrained('delivery_cities')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('external_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['delivery_city_id', 'name', 'postal_code']);
            $table->index(['delivery_city_id', 'external_id']);
            $table->index(['delivery_city_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_branches');
    }
};
