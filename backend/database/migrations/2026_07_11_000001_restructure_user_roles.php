<?php
// database/migrations/2026_07_11_000001_restructure_user_roles.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quitar el constraint por completo — sin ningún constraint activo,
        //    la columna acepta temporalmente cualquier valor mientras migramos los datos.
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        // 2. Migrar los datos existentes a los nuevos nombres de rol
        //    (ahora sí funciona, no hay ningún constraint bloqueando)
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'cajero')->update(['role' => 'contador']);

        // 3. Recién ahora, con todas las filas ya usando los nombres nuevos,
        //    agregar el constraint definitivo — no va a fallar porque
        //    ninguna fila tiene ya un valor fuera de esta lista.
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'sistema', 'contador', 'atencion', 'salon'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin', 'admin', 'cajero', 'sistema'))");
    }
};
