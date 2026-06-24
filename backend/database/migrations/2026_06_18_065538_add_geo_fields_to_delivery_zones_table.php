<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('precio');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->decimal('radio_km', 6, 2)->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'radio_km']);
        });
    }
};
