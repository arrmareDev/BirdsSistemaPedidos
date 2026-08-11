<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_zone_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('delivery_zone_id')
                ->references('id')->on('delivery_tariffs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_zone_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('delivery_zone_id')
                ->references('id')->on('delivery_zones')
                ->nullOnDelete();
        });
    }
};
