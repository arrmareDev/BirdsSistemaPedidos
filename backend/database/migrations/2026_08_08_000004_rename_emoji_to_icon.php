<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('emoji', 'icon');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('emoji', 'icon');
        });

        // El campo ahora guarda nombres de ícono ("flower-2"), no emojis
        // sueltos — amplía el límite para nombres compuestos.
        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon', 50)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('icon', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon', 10)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('icon', 10)->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('icon', 'emoji');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('icon', 'emoji');
        });
    }
};
