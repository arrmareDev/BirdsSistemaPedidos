<?php

namespace App\Observers;

use App\Models\Comision;
use App\Models\ConfiguracionSistema;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Se dispara cada vez que un Order es actualizado.
     * Crea una Comision cuando el status cambia a "entregado".
     */
    public function updated(Order $order): void
    {
        // Solo actuar si el status cambió a "entregado"
        if (! $order->wasChanged('status')) {
            return;
        }

        if ($order->status !== 'entregado') {
            return;
        }

        // Evitar duplicados — si ya existe comisión para este pedido, salir
        if (Comision::where('order_id', $order->id)->exists()) {
            return;
        }

        $this->crearComision($order);
    }

    /**
     * Crea el registro de comisión para un pedido entregado.
     */
    private function crearComision(Order $order): void
    {
        try {
            $montoComision = (float) ConfiguracionSistema::get(
                'comision_por_pedido',
                '0.30'          // valor por defecto si no existe config
            );

            Comision::create([
                'order_id'       => $order->id,
                'fecha'          => today(),
                'monto_pedido'   => $order->total,
                'monto_comision' => $montoComision,
                'cobrado'        => false,
            ]);

            Log::info('Comisión creada', [
                'order_id' => $order->id,
                'monto'    => $montoComision,
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo del pedido si falla la comisión
            Log::error('Error creando comisión', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
