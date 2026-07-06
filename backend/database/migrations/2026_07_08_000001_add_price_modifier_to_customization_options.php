<?php
// database/migrations/2026_07_08_000001_add_price_modifier_to_customization_options.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_customization_options', function (Blueprint $table) {
            $table->decimal('price_modifier', 10, 2)->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('product_customization_options', function (Blueprint $table) {
            $table->dropColumn('price_modifier');
        });
    }
};
