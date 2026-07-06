<?php
// database/migrations/2026_07_09_000001_dedupe_shared_extras.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('extras')
            ->select('name', 'business_line')
            ->groupBy('name', 'business_line')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $rows = DB::table('extras')
                ->where('name', $dup->name)
                ->where('business_line', $dup->business_line)
                ->orderBy('id')
                ->get();

            $keeper = $rows->first(); // conserva el más antiguo
            $losers = $rows->slice(1);

            foreach ($losers as $loser) {
                $pivotRows = DB::table('extra_product')->where('extra_id', $loser->id)->get();

                foreach ($pivotRows as $pivot) {
                    $yaExiste = DB::table('extra_product')
                        ->where('extra_id', $keeper->id)
                        ->where('product_id', $pivot->product_id)
                        ->exists();

                    if ($yaExiste) {
                        // el producto ya está vinculado al que se conserva → descarta el duplicado
                        DB::table('extra_product')->where('id', $pivot->id)->delete();
                    } else {
                        // re-apunta el vínculo al extra que se conserva
                        DB::table('extra_product')->where('id', $pivot->id)->update(['extra_id' => $keeper->id]);
                    }
                }

                DB::table('extras')->where('id', $loser->id)->delete();
            }
        }
    }

    public function down(): void
    {
        // No reversible — los duplicados eliminados no se pueden reconstruir con certeza
    }
};
