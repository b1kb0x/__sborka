<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
            $table->string('email')->nullable()->after('phone');
            $table->string('region')->nullable()->after('email');
            $table->string('city')->nullable()->after('region');
            $table->string('address')->nullable()->after('city');
            $table->text('comment')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'email',
                'region',
                'city',
                'address',
                'comment',
            ]);
        });
    }
};
