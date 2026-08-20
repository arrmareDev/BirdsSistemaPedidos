<?php

namespace App\Http\Controllers\Api;

use App\Exports\InventarioExport;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class InventarioReporteController extends Controller
{
    // Mismos filtros que ya usa la tabla de InventarioView.vue — para
    // que "descargar" siempre traiga exactamente lo que se está viendo
    // en pantalla, no un listado distinto y confuso.
    private function consultarProductos(Request $request): Collection
    {
        return Product::with('category')
            ->where('controla_stock', true)
            ->when(
                $request->filled('q'),
                fn($q) => $q->where(function ($qq) use ($request) {
                    $termino = $request->query('q');
                    $qq->where('name', 'ilike', "%{$termino}%")
                        ->orWhere('description', 'ilike', "%{$termino}%");
                })
            )
            ->when(
                $request->filled('category_id'),
                fn($q) => $q->where('category_id', $request->query('category_id'))
            )
            ->when(
                $request->boolean('solo_problema'),
                fn($q) => $q->where(function ($qq) {
                    $qq->where('stock', '<=', 0)
                        ->orWhereColumn('stock', '<=', 'stock_minimo');
                })
            )
            ->orderBy('name')
            ->get();
    }

    // GET /admin/reportes/inventario/pdf
    public function pdf(Request $request)
    {
        $productos = $this->consultarProductos($request);
        $valorTotal = $productos->sum(fn($p) => $p->stock * (float) $p->price);

        $pdf = Pdf::loadView('reportes.inventario', [
            'productos'  => $productos,
            'valorTotal' => $valorTotal,
            'generado'   => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('inventario.pdf');
    }

    // GET /admin/reportes/inventario/excel
    public function excel(Request $request)
    {
        $productos = $this->consultarProductos($request);

        return Excel::download(new InventarioExport($productos), 'inventario.xlsx');
    }
}
