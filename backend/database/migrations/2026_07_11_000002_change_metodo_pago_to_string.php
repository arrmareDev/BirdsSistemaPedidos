<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders ALTER COLUMN metodo_pago TYPE VARCHAR(30) USING metodo_pago::text");
    }

    public function down(): void
    {
        // No revertimos al enum original para evitar perder datos con los nuevos valores.
    }
};
