<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'radio_km']);
            $table->json('poligono')->nullable()->after('precio');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn('poligono');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('radio_km', 6, 2)->nullable();
        });
    }
};
