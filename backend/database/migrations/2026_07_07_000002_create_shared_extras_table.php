<?php
// database/migrations/2026_07_07_000002_create_shared_extras_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tabla de extras independientes ──────────────────
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('business_line')->default('cafeteria'); // floreria | cafeteria | menu
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ── 2. Pivote real: un extra puede aplicar a varios productos ──
        Schema::create('extra_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extra_id')->constrained('extras')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['extra_id', 'product_id']); // evita duplicados
        });

        // ── 3. Migrar datos existentes de product_extras → extras + pivote ──
        $existentes = DB::table('product_extras')->get();

        foreach ($existentes as $viejo) {
            $extraId = DB::table('extras')->insertGetId([
                'name'          => $viejo->name,
                'price'         => $viejo->price,
                'business_line' => 'floreria', // los actuales son de florería
                'sort_order'    => $viejo->sort_order,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::table('extra_product')->insert([
                'extra_id'   => $extraId,
                'product_id' => $viejo->product_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Nota: NO se borra product_extras todavía — se deja como respaldo
        // hasta confirmar que el nuevo modelo funciona bien en producción.
        // Cuando lo confirmes, corremos una migración aparte para dropearla.
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_product');
        Schema::dropIfExists('extras');
    }
};
