<?php
// database/migrations/2026_07_06_000001_add_business_line_to_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('business_line')
                ->default('floreria')
                ->after('name');

            $table->index('business_line');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['business_line']);
            $table->dropColumn('business_line');
        });
    }
};
