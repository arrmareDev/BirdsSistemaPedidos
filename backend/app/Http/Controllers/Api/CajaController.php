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
        $caja = Caja::where('fecha', today())
            ->with(['movimientos' => fn($q) => $q->orderBy('created_at')])
            ->first();

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
            'caja' => [
                'id'             => $caja->id,
                'fecha'          => $caja->fecha->format('Y-m-d'),
                'estado'         => $caja->estado,
                'monto_apertura' => (float) $caja->monto_apertura,
                'monto_cierre'   => $caja->monto_cierre
                    ? (float) $caja->monto_cierre : null,
                'total_ventas'   => (float) $caja->total_ventas,
                'total_gastos'   => (float) $caja->total_gastos,
                'total_ingresos' => (float) $caja->total_ingresos,
                'saldo'          => (float) $caja->saldo,
            ],
            'movimientos' => $caja->movimientos->map(fn($m) => [
                'id'          => $m->id,
                'type'        => $m->type,
                'amount'      => (float) $m->amount,
                'description' => $m->description,
                'order_id'    => $m->order_id,
                'created_at'  => $m->created_at->format('H:i'),
            ])->toArray(),
        ]);
    }

    public function abrir(Request $request): JsonResponse
    {
        $data = $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
        ]);

        $cajaExistente = Caja::where('fecha', today())->first();

        if ($cajaExistente) {
            // Si existe pero está cerrada → reabrir
            if ($cajaExistente->estado === 'cerrada') {
                $cajaExistente->update([
                    'estado'      => 'abierta',
                    'abierta_at'  => now(),
                    'cerrada_at'  => null,
                    'monto_cierre' => null,
                ]);
                $this->importarPedidosPendientes($cajaExistente);
                return $this->success([
                    'id'             => $cajaExistente->id,
                    'monto_apertura' => (float) $cajaExistente->monto_apertura,
                    'estado'         => $cajaExistente->estado,
                ], 'Caja reabierta');
            }
            // Si ya está abierta
            return $this->error('Ya existe una caja abierta hoy', 400);
        }

        // Crear nueva caja
        $caja = Caja::create([
            'fecha'          => today(),
            'monto_apertura' => $data['monto_apertura'],
            'estado'         => 'abierta',
            'user_id'        => auth()->id(),
            'abierta_at'     => now(),
        ]);

        $this->importarPedidosPendientes($caja);

        return $this->created([
            'id'             => $caja->id,
            'monto_apertura' => (float) $caja->monto_apertura,
            'estado'         => $caja->estado,
        ], 'Caja abierta');
    }

    public function movimiento(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:venta,gasto,ingreso',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $caja = Caja::where('fecha', today())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return $this->error('No hay caja abierta hoy', 400);
        }

        $movimiento = CajaMovimiento::create([
            'caja_id'     => $caja->id,
            'type'        => $data['type'],
            'amount'      => $data['amount'],
            'description' => $data['description'],
            'user_id'     => auth()->id(),
        ]);

        return $this->created([
            'id'          => $movimiento->id,
            'type'        => $movimiento->type,
            'amount'      => (float) $movimiento->amount,
            'description' => $movimiento->description,
            'saldo'       => (float) $caja->fresh()->saldo,
        ], 'Movimiento registrado');
    }

    public function cerrar(): JsonResponse
    {
        $caja = Caja::where('fecha', today())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return $this->error('No hay caja abierta hoy', 400);
        }

        $caja->update([
            'estado'       => 'cerrada',
            'monto_cierre' => $caja->saldo,
            'cerrada_at'   => now(),
        ]);

        return $this->success([
            'saldo_final'    => (float) $caja->saldo,
            'total_ventas'   => (float) $caja->total_ventas,
            'total_gastos'   => (float) $caja->total_gastos,
            'total_ingresos' => (float) $caja->total_ingresos,
        ], 'Caja cerrada correctamente');
    }

    // ── Importar pedidos entregados hoy ───────────────────
    private function importarPedidosPendientes(Caja $caja): void
    {
        $pedidos = Order::where('status', 'entregado')
            ->whereDate('updated_at', today())
            ->get();

        foreach ($pedidos as $pedido) {
            // Verificar globalmente que no esté en ninguna caja de hoy
            $yaExiste = CajaMovimiento::where('order_id', $pedido->id)
                ->whereHas('caja', fn($q) => $q->where('fecha', today()))
                ->exists();

            if ($yaExiste) continue;

            CajaMovimiento::create([
                'caja_id'     => $caja->id,
                'order_id'    => $pedido->id,
                'type'        => 'venta',
                'amount'      => $pedido->total,
                'description' => "Pedido #{$pedido->id} — {$pedido->client_name}",
                'user_id'     => auth()->id(),
            ]);
        }
    }
}
