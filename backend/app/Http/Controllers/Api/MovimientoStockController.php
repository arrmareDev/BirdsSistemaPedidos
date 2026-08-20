<?php

namespace App\Http\Controllers\Api;

use App\Models\MovimientoStock;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovimientoStockController extends Controller
{
    // GET /admin/products/{id}/movimientos-stock — historial paginado
    public function index(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) return $this->notFound('Producto no encontrado');

        $perPage = min((int) $request->query('per_page', 20), 50);

        $paginated = $product->movimientosStock()
            ->with(['order:id,codigo', 'user:id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->success([
            'data' => collect($paginated->items())->map(fn($m) => $this->format($m)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // POST /admin/products/{id}/reponer-stock — suma stock (llegó
    // mercadería nueva). El motivo es opcional, a diferencia del ajuste.
    public function reponer(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) return $this->notFound('Producto no encontrado');

        if (!$product->controla_stock) {
            return $this->error('Este producto no tiene control de stock activado', 422);
        }

        $data = $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo'   => 'nullable|string|max:255',
        ]);

        $product->restaurarStock($data['cantidad'], 'reposicion', null, $data['motivo'] ?? null);

        return $this->success([
            'stock' => $product->fresh()->stock,
        ], 'Stock repuesto');
    }

    // POST /admin/products/{id}/ajustar-stock — el staff escribe el
    // total real (después de un conteo físico, por ejemplo), no una
    // diferencia — es más fácil de usar que pedirle que calcule el
    // delta él mismo. El motivo es obligatorio, a diferencia de reponer.
    public function ajustar(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) return $this->notFound('Producto no encontrado');

        if (!$product->controla_stock) {
            return $this->error('Este producto no tiene control de stock activado', 422);
        }

        $data = $request->validate([
            'stock_nuevo' => 'required|integer|min:0',
            'motivo'      => 'required|string|max:255',
        ]);

        $delta = $data['stock_nuevo'] - $product->stock;

        if ($delta > 0) {
            $product->restaurarStock($delta, 'ajuste', null, $data['motivo']);
        } elseif ($delta < 0) {
            $product->reducirStock(abs($delta), 'ajuste', null, $data['motivo']);
        }
        // Si delta es 0, no hay nada que registrar — el conteo confirmó
        // que el número ya estaba correcto.

        return $this->success([
            'stock' => $product->fresh()->stock,
        ], 'Stock ajustado');
    }

    private function format(MovimientoStock $m): array
    {
        return [
            'id'               => $m->id,
            'tipo'             => $m->tipo,
            'cantidad'         => $m->cantidad,
            'stock_resultante' => $m->stock_resultante,
            'motivo'           => $m->motivo,
            'order_codigo'     => $m->order?->codigo,
            'usuario'          => $m->user?->name,
            'created_at'       => $m->created_at?->toISOString(),
        ];
    }
}
