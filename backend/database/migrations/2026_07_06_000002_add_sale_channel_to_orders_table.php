<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Reemplazar el CHECK constraint del enum 'type' ──────
        // Laravel en PostgreSQL no crea un enum nativo, crea un
        // CHECK CONSTRAINT. Por eso no usamos ALTER TYPE ... ADD VALUE,
        // sino que soltamos el constraint viejo y creamos uno nuevo.
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_type_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_type_check CHECK (type IN ('recoger', 'delivery', 'local'))");

        // ── 2. Agregar columna 'mesa' si no existe ─────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'mesa')) {
                $table->string('mesa')->nullable()->after('district');
            }
        });
    }

    public function down(): void
    {
        // Revertir constraint a solo los 2 valores originales
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_type_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_type_check CHECK (type IN ('recoger', 'delivery'))");

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'mesa')) {
                $table->dropColumn('mesa');
            }
        });
    }
};
