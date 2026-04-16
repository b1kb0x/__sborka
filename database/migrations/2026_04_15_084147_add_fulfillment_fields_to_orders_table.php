<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'fulfillment_status')) {
                $table->string('fulfillment_status', 50)
                    ->default('accepted')
                    ->after('status');
            }

            if (! Schema::hasColumn('orders', 'carrier_name')) {
                $table->string('carrier_name')->nullable()->after('fulfillment_status');
            }

            if (! Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('carrier_name');
            }

            if (! Schema::hasColumn('orders', 'handed_to_carrier_at')) {
                $table->timestamp('handed_to_carrier_at')->nullable()->after('tracking_number');
            }

            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('handed_to_carrier_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'fulfillment_status',
                'carrier_name',
                'tracking_number',
                'handed_to_carrier_at',
                'delivered_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
