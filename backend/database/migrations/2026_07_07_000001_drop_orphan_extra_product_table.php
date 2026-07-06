<?php
// database/migrations/2026_07_07_000001_drop_orphan_extra_product_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla creada sin columnas útiles, nunca usada en código.
        // Se reemplaza por el modelo correcto en la siguiente migración.
        Schema::dropIfExists('extra_product');
    }

    public function down(): void
    {
        Schema::create('extra_product', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }
};
