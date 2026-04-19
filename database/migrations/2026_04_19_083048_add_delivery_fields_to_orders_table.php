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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_service_id')
                ->nullable()
                ->after('address')
                ->constrained('delivery_services')
                ->nullOnDelete();

            $table->foreignId('delivery_region_id')
                ->nullable()
                ->after('delivery_service_id')
                ->constrained('delivery_regions')
                ->nullOnDelete();

            $table->foreignId('delivery_city_id')
                ->nullable()
                ->after('delivery_region_id')
                ->constrained('delivery_cities')
                ->nullOnDelete();

            $table->foreignId('delivery_branch_id')
                ->nullable()
                ->after('delivery_city_id')
                ->constrained('delivery_branches')
                ->nullOnDelete();

            $table->string('delivery_service_name')->nullable()->after('delivery_branch_id');
            $table->string('delivery_region_name')->nullable()->after('delivery_service_name');
            $table->string('delivery_city_name')->nullable()->after('delivery_region_name');
            $table->string('delivery_branch_name')->nullable()->after('delivery_city_name');
            $table->string('delivery_branch_address')->nullable()->after('delivery_branch_name');
            $table->string('delivery_branch_postal_code')->nullable()->after('delivery_branch_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
