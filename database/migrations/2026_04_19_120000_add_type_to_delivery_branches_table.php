<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_branches', function (Blueprint $table) {
            $table->string('type')->default('branch')->after('postal_code');
            $table->index(['delivery_city_id', 'type']);
        });

        DB::table('delivery_branches')->update(['type' => 'branch']);
    }

    public function down(): void
    {
        Schema::table('delivery_branches', function (Blueprint $table) {
            $table->dropIndex(['delivery_city_id', 'type']);
            $table->dropColumn('type');
        });
    }
};
