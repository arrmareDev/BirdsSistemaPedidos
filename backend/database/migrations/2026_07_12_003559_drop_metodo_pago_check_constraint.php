<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_metodo_pago_check");
    }

    public function down(): void
    {
        // No revertimos — el constraint viejo no debería restaurarse.
    }
};
