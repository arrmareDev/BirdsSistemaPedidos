<?php

namespace App\Http\Controllers\Api;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function hoy(): JsonResponse
    {
        // Si hay una caja abierta de un día anterior, esa es la que hay
        // que mostrar y poder cerrar — si buscáramos solo por la fecha
        // de hoy, quedaría invisible y sin forma de cerrarla nunca.
        $caja = Caja::where('estado', 'abierta')->first()
            ?? Caja::where('fecha', today())->first();

        $caja?->load([
            'movimientos' => fn($q) => $q->orderBy('created_at'),
            'abiertaPor:id,name',
            'cerradaPor:id,name',
        ]);

        if (!$caja) {
            $pedidosHoy = Order::where('status', 'entregado')
                ->whereDate('updated_at', today())
                ->count();

            return $this->success([
                'caja'        => null,
                'estado'      => 'sin_abrir',
                'movimientos' => [],
                'pedidos_hoy' => $pedidosHoy,
            ]);
        }

        return $this->success([
            'caja' => $this->formatCaja($caja),
            'movimientos' => $caja->movimientos->map(fn($m) => $this->formatMovimiento($m))->toArray(),
            'es_dia_anterior' => $caja->fecha->format('Y-m-d') !== today()->format('Y-m-d'),
        ]);
    }

    // GET /admin/caja/historial — cajas pasadas, paginado, para auditar
    public function historial(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 50);

        $query = Caja::with(['abiertaPor:id,name', 'cerradaPor:id,name'])
            ->orderByDesc('fecha');

        if ($request->filled('desde')) {
            $query->where('fecha', '>=', $request->query('desde'));
        }
        if ($request->filled('hasta')) {
            $query->where('fecha', '<=', $request->query('hasta'));
        }
        // Filtrar solo las que tuvieron diferencia al cerrar — para
        // encontrar rápido los días con algo raro, sin revisar todo.
        if ($request->boolean('solo_con_diferencia')) {
            $query->whereNotNull('diferencia')->where('diferencia', '!=', 0);
        }

        $paginated = $query->paginate($perPage);

        return $this->success([
            'data' => collect($paginated->items())->map(fn($c) => $this->formatCaja($c)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
        ]);
    }

    public function abrir(Request $request): JsonResponse
    {
        $data = $request->validate([
            'monto_apertura'     => 'required|numeric|min:0',
            'motivo_reapertura'  => 'nullable|string|max:255',
        ]);

        // No se puede abrir una caja nueva si hay una de un día anterior
        // que se quedó sin cerrar — hay que resolver esa primero.
        $cajaAnteriorSinCerrar = Caja::where('estado', 'abierta')
            ->where('fecha', '!=', today())
            ->orderBy('fecha')
            ->first();

        if ($cajaAnteriorSinCerrar) {
            return $this->error(
                "Hay una caja del " . $cajaAnteriorSinCerrar->fecha->format('d/m/Y')
                    . " que quedó sin cerrar. Ciérrala antes de abrir una nueva.",
                409
            );
        }

        $cajaExistente = Caja::where('fecha', today())->first();

        if ($cajaExistente) {
            if ($cajaExistente->estado === 'cerrada') {
                if (empty($data['motivo_reapertura'])) {
                    return $this->error('Indica el motivo para reabrir la caja de hoy', 422);
                }

                $cajaExistente->update([
                    'estado'             => 'abierta',
                    'abierta_at'         => now(),
                    'cerrada_at'         => null,
                    'monto_cierre'       => null,
                    'monto_contado'      => null,
                    'diferencia'         => null,
                    'motivo_diferencia'  => null,
                    'cerrado_por'        => null,
                    'motivo_reapertura'  => $data['motivo_reapertura'],
                ]);
                $this->importarPedidosPendientes($cajaExistente);

                return $this->success($this->formatCaja($cajaExistente->fresh()), 'Caja reabierta');
            }
            return $this->error('Ya existe una caja abierta hoy', 400);
        }

        $caja = Caja::create([
            'fecha'          => today(),
            'monto_apertura' => $data['monto_apertura'],
            'estado'         => 'abierta',
            'user_id'        => auth()->id(),
            'abierta_at'     => now(),
        ]);

        $this->importarPedidosPendientes($caja);

        return $this->created($this->formatCaja($caja->fresh()), 'Caja abierta');
    }

    public function movimiento(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:venta,gasto,ingreso',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $caja = Caja::where('estado', 'abierta')->first();

        if (!$caja) {
            return $this->error('No hay caja abierta', 400);
        }

        $movimiento = CajaMovimiento::create([
            'caja_id'     => $caja->id,
            'type'        => $data['type'],
            'amount'      => $data['amount'],
            'description' => $data['description'],
            'user_id'     => auth()->id(),
        ]);

        return $this->created([
            ...$this->formatMovimiento($movimiento),
            'saldo' => (float) $caja->fresh()->saldo,
        ], 'Movimiento registrado');
    }

    // POST /admin/caja/movimiento/{id}/anular — nunca se borra ni se
    // edita un movimiento, solo se anula con motivo (queda visible,
    // tachado, y deja de contar para los totales).
    public function anular(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'motivo' => 'required|string|max:255',
        ]);

        $movimiento = CajaMovimiento::with('caja')->find($id);
        if (!$movimiento) return $this->notFound('Movimiento no encontrado');

        if ($movimiento->caja->estado !== 'abierta') {
            return $this->error('Solo se pueden anular movimientos de una caja abierta', 422);
        }

        if ($movimiento->anulado) {
            return $this->error('Este movimiento ya estaba anulado', 422);
        }

        $movimiento->update([
            'anulado'          => true,
            'motivo_anulacion' => $data['motivo'],
            'anulado_at'       => now(),
            'anulado_por'      => auth()->id(),
        ]);

        return $this->success([
            ...$this->formatMovimiento($movimiento),
            'saldo' => (float) $movimiento->caja->fresh()->saldo,
        ], 'Movimiento anulado');
    }

    public function cerrar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'monto_contado' => 'required|numeric|min:0',
            'motivo_diferencia' => 'nullable|string|max:255',
        ]);

        $caja = Caja::where('estado', 'abierta')->first();

        if (!$caja) {
            return $this->error('No hay caja abierta', 400);
        }

        $saldoEsperado = $caja->saldo;
        $diferencia    = round($data['monto_contado'] - $saldoEsperado, 2);

        // Si el conteo físico no coincide con lo esperado, hay que
        // explicar por qué — no se puede cerrar una diferencia en
        // silencio.
        if ($diferencia !== 0.0 && empty($data['motivo_diferencia'])) {
            return $this->error(
                "Hay una diferencia de S/ " . number_format(abs($diferencia), 2)
                    . ($diferencia > 0 ? ' de sobrante' : ' de faltante')
                    . " — indica el motivo antes de cerrar.",
                422
            );
        }

        $caja->update([
            'estado'            => 'cerrada',
            'monto_cierre'      => $saldoEsperado,
            'monto_contado'     => $data['monto_contado'],
            'diferencia'        => $diferencia,
            'motivo_diferencia' => $data['motivo_diferencia'] ?? null,
            'cerrada_at'        => now(),
            'cerrado_por'       => auth()->id(),
        ]);

        return $this->success($this->formatCaja($caja->fresh()), 'Caja cerrada correctamente');
    }

    // ── Importar pedidos entregados hoy ───────────────────
    // Solo los pagados en EFECTIVO — un pedido pagado por Yape,
    // tarjeta o anticipado nunca tocó el cajón físico, meterlo aquí
    // rompería el cuadre de raíz (se estaría comparando efectivo real
    // contra un total que incluye dinero que nunca estuvo en la caja).
    private function importarPedidosPendientes(Caja $caja): void
    {
        $pedidos = Order::where('status', 'entregado')
            ->where('metodo_pago', 'efectivo')
            ->whereDate('updated_at', today())
            ->get();

        foreach ($pedidos as $pedido) {
            $yaExiste = CajaMovimiento::where('order_id', $pedido->id)
                ->whereHas('caja', fn($q) => $q->where('fecha', today()))
                ->exists();

            if ($yaExiste) continue;

            CajaMovimiento::create([
                'caja_id'     => $caja->id,
                'order_id'    => $pedido->id,
                'type'        => 'venta',
                'amount'      => $pedido->total,
                'description' => "Pedido #{$pedido->codigo} — {$pedido->client_name}",
                'user_id'     => auth()->id(),
            ]);
        }
    }

    private function formatCaja(Caja $caja): array
    {
        return [
            'id'                 => $caja->id,
            'fecha'              => $caja->fecha->format('Y-m-d'),
            'estado'             => $caja->estado,
            'monto_apertura'     => (float) $caja->monto_apertura,
            'monto_cierre'       => $caja->monto_cierre !== null ? (float) $caja->monto_cierre : null,
            'monto_contado'      => $caja->monto_contado !== null ? (float) $caja->monto_contado : null,
            'diferencia'         => $caja->diferencia !== null ? (float) $caja->diferencia : null,
            'motivo_diferencia'  => $caja->motivo_diferencia,
            'motivo_reapertura'  => $caja->motivo_reapertura,
            'total_ventas'       => (float) $caja->total_ventas,
            'total_gastos'       => (float) $caja->total_gastos,
            'total_ingresos'     => (float) $caja->total_ingresos,
            'saldo'              => (float) $caja->saldo,
            'abierta_por'        => $caja->relationLoaded('abiertaPor') ? $caja->abiertaPor?->name : null,
            'cerrada_por'        => $caja->relationLoaded('cerradaPor') ? $caja->cerradaPor?->name : null,
        ];
    }

    private function formatMovimiento(CajaMovimiento $m): array
    {
        return [
            'id'                => $m->id,
            'type'              => $m->type,
            'amount'            => (float) $m->amount,
            'description'       => $m->description,
            'order_id'          => $m->order_id,
            'created_at'        => $m->created_at
                ->timezone('America/Lima')
                ->format('H:i'),
            'anulado'           => $m->anulado,
            'motivo_anulacion'  => $m->motivo_anulacion,
        ];
    }
}
