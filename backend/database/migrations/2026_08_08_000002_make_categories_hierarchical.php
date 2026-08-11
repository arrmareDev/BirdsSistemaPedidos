<?php
// database/migrations/2026_08_08_000002_make_categories_hierarchical.php
//
// Quita el concepto de "línea de negocio" (business_line) y lo reemplaza
// por una jerarquía real de categorías (categoría → subcategoría).
//
// Cada valor de business_line que existía (floreria, cafeteria, menu, etc.)
// se convierte en una categoría principal de verdad, y las categorías que
// tenían ese business_line pasan a ser sus subcategorías.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->restrictOnDelete();
        });

        // ── Migrar datos: crear una categoría raíz por cada línea existente ──
        $labels = [
            'floreria'  => 'Florería',
            'cafeteria' => 'Cafetería',
            'menu'      => 'Menú',
        ];

        $lineas = DB::table('categories')
            ->select('business_line')
            ->distinct()
            ->pluck('business_line');

        foreach ($lineas as $linea) {
            if (!$linea) continue;

            $slug = Str::slug($linea);

            $rootId = DB::table('categories')->insertGetId([
                'name'       => $labels[$linea] ?? Str::title($linea),
                'slug'       => $slug,
                'parent_id'  => null,
                'sort_order' => 0,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('categories')
                ->where('business_line', $linea)
                ->update(['parent_id' => $rootId]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['business_line']);
            $table->dropColumn('business_line');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('business_line')->default('floreria')->after('name');
        });

        // Mejor esfuerzo: backfill de business_line desde el slug de la categoría raíz.
        // No es 100% reversible si se crearon categorías/subcategorías nuevas
        // después de aplicar esta migración.
        $roots = DB::table('categories')->whereNull('parent_id')->get();

        foreach ($roots as $root) {
            DB::table('categories')
                ->where('parent_id', $root->id)
                ->update(['business_line' => $root->slug]);

            DB::table('categories')->where('id', $root->id)->delete();
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->index('business_line');
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
