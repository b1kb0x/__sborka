<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_attribute_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_attribute_option_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('value_string')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 12, 3)->nullable();
            $table->boolean('value_boolean')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'product_attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
