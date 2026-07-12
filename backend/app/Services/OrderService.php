<?php

namespace App\Services;

use App\Enums\SaleChannel;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Client;
use App\Models\Comision;
use App\Models\ConfiguracionSistema;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
    ) {}

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {

            $channel = SaleChannel::from($data['type']);

            // ── 0. Validar requisitos según canal ─────────────────
            if ($channel->requiresMesa() && empty($data['mesa'])) {
                throw new \Exception('Debes indicar el número de mesa para pedidos en local.');
            }

            if ($channel->requiresAddress() && empty($data['address'])) {
                throw new \Exception('Debes indicar la dirección para pedidos delivery.');
            }

            // ── 1. Calcular subtotal ──────────────────────────────
            $items    = $data['items'] ?? [];
            $subtotal = collect($items)->sum(
                fn($item) => ($item['unit_price'] ?? 0) * ($item['qty'] ?? 1)
            );

            // ── 2. Validar stock antes de crear ──────────────────
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (int) ($item['qty'] ?? 1);

                if ($product->controla_stock && !$product->tieneStock($qty)) {
                    throw new \Exception(
                        "Stock insuficiente para: {$product->name}. " .
                            "Disponible: {$product->stock}, solicitado: {$qty}"
                    );
                }
            }

            // ── 3. Resolver delivery fee (solo si el canal lo requiere) ──
            $deliveryFee = $channel->requiresDeliveryFee()
                ? $this->resolveDeliveryFee(
                    zoneId: $data['delivery_zone_id'] ?? null,
                    deliveryFee: $data['delivery_fee'] ?? null,
                    district: $data['district'] ?? null,
                )
                : 0.0;

            // ── 4. Resolver cliente (opcional si no hay teléfono, ej. pedidos Local) ──
            $client = null;
            if (!empty($data['client_phone'])) {
                $phone  = preg_replace('/\D/', '', $data['client_phone']);
                $client = Client::firstOrCreate(
                    ['phone' => $phone],
                    [
                        'name'     => $data['client_name'],
                        'address'  => $data['address']  ?? null,
                        'district' => $data['district'] ?? null,
                    ]
                );

                $client->update([
                    'name'     => $data['client_name'],
                    'address'  => $data['address']  ?? $client->address,
                    'district' => $data['district'] ?? $client->district,
                ]);
            }

            // ── 5. Crear pedido ───────────────────────────────────
            $order = Order::create([
                'client_id'          => $client?->id,
                'client_name'        => $data['client_name'],
                'client_phone'       => $data['client_phone'],
                'type'               => $channel->value,
                'status'             => 'nuevo',
                'address'            => $data['address']          ?? null,
                'reference'          => $data['reference']        ?? null,
                'delivery_zone_id'   => $data['delivery_zone_id'] ?? null,
                'metodo_pago'        => $data['metodo_pago']       ?? null,
                'district'           => $data['district']         ?? null,
                'mesa'               => $data['mesa']             ?? null,
                'note'               => $data['note']             ?? null,
                'lat'                => $data['lat']              ?? null,
                'lng'                => $data['lng']              ?? null,
                // ── Florería ──────────────────────────────────────
                'mensaje_tarjeta'    => $data['mensaje_tarjeta']    ?? null,
                'fecha_entrega'      => $data['fecha_entrega']      ?? null,
                'hora_entrega'       => $data['hora_entrega']       ?? null,
                'entrega_programada' => $data['entrega_programada'] ?? false,
                // ── Totales ───────────────────────────────────────
                'subtotal'           => $subtotal,
                'delivery_fee'       => $deliveryFee,
                'total'              => $subtotal + $deliveryFee,
            ]);

            // ── 6. Crear items y descontar stock ──────────────────
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $qty     = (int)   ($item['qty']        ?? 1);
                $price   = (float) ($item['unit_price']  ?? 0);

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'unit_price'     => $price,
                    'subtotal'       => $price * $qty,
                    'customization'  => $item['customization']  ?? null,
                    'extras'         => $item['extras']         ?? null,
                    'custom_summary' => $item['custom_summary'] ?? null,
                ]);

                $product->reducirStock($qty);
            }

            // ── 7. Actualizar preferencias del cliente ────────────
            try {
                $client?->updatePreferences($data);
            } catch (\Throwable $e) {
                Log::warning('No se pudieron actualizar preferencias: ' . $e->getMessage());
            }

            Log::info('Order created', [
                'order_id' => $order->id,
                'client_id' => $client?->id,
                'channel'  => $channel->value,
                'zone_id'  => $data['delivery_zone_id'] ?? null,
            ]);

            return $order->load('items.product');
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        if ($status === 'entregado') {
            $this->registrarVentaEnCaja($order);
            $this->registrarComision($order);
        }

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'status'   => $status,
        ]);

        return $order->fresh(['items.product']);
    }

    public function updateItems(Order $order, array $items): Order
    {
        return DB::transaction(function () use ($order, $items) {

            if ($order->isFinished() || $order->status === 'en_camino') {
                throw new \Exception('No se puede editar un pedido que ya está en camino.');
            }

            // ── 1. Restaurar stock de los items actuales ──────────
            $order->load('items');
            foreach ($order->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                $product?->restaurarStock($oldItem->qty);
            }

            // ── 2. Validar stock de los items nuevos ──────────────
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (int) ($item['qty'] ?? 1);

                if ($product->controla_stock && !$product->tieneStock($qty)) {
                    throw new \Exception(
                        "Stock insuficiente para: {$product->name}. " .
                            "Disponible: {$product->stock}, solicitado: {$qty}"
                    );
                }
            }

            // ── 3. Reemplazar items ────────────────────────────────
            $order->items()->delete();

            $subtotal = 0;
            foreach ($items as $item) {
                $product      = Product::find($item['product_id']);
                $qty          = (int)   ($item['qty']       ?? 1);
                $price        = (float) ($item['unit_price'] ?? 0);
                $itemSubtotal = $price * $qty;
                $subtotal    += $itemSubtotal;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'unit_price'     => $price,
                    'subtotal'       => $itemSubtotal,
                    'customization'  => $item['customization']  ?? null,
                    'extras'         => $item['extras']         ?? null,
                    'custom_summary' => $item['custom_summary'] ?? null,
                ]);

                $product->reducirStock($qty);
            }

            // ── 4. Recalcular totales (mantiene el delivery_fee actual) ──
            $order->update([
                'subtotal' => $subtotal,
                'total'    => $subtotal + $order->delivery_fee,
            ]);

            Log::info('Order items updated', [
                'order_id'    => $order->id,
                'items_count' => count($items),
            ]);

            return $order->fresh(['items.product']);
        });
    }

    public function cobrarLocal(Order $order, string $metodoPago): Order
    {
        if ($order->type !== \App\Enums\SaleChannel::Local) {
            throw new \Exception('Esta acción solo aplica a pedidos de tipo Local.');
        }

        if ($order->status !== 'listo') {
            throw new \Exception('El pedido debe estar en estado "Listo" para cobrarse.');
        }

        $order->update(['metodo_pago' => $metodoPago]);

        return $this->updateStatus($order, 'entregado');
    }

    private function registrarVentaEnCaja(Order $order): void
    {
        try {
            $caja = Caja::where('fecha', today())
                ->where('estado', 'abierta')
                ->first();

            if (!$caja) return;

            $yaExiste = CajaMovimiento::where('caja_id', $caja->id)
                ->where('order_id', $order->id)
                ->exists();

            if ($yaExiste) return;

            CajaMovimiento::create([
                'caja_id'     => $caja->id,
                'order_id'    => $order->id,
                'type'        => 'venta',
                'amount'      => $order->total,
                'description' => "Pedido #{$order->id} — {$order->client_name}",
                'user_id'     => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar venta en caja: ' . $e->getMessage());
        }
    }

    private function registrarComision(Order $order): void
    {
        try {
            $yaExiste = Comision::where('order_id', $order->id)->exists();
            if ($yaExiste) return;

            $montoComision = (float) ConfiguracionSistema::get(
                'comision_por_pedido',
                0.30
            );

            Comision::create([
                'order_id'       => $order->id,
                'monto_pedido'   => $order->total,
                'monto_comision' => $montoComision,
                'fecha'          => today(),
                'cobrado'        => false,
            ]);

            Log::info('Comisión creada', [
                'order_id' => $order->id,
                'monto'    => $montoComision,
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar comisión: ' . $e->getMessage());
        }
    }

    private function resolveDeliveryFee(
        ?int    $zoneId,
        ?float  $deliveryFee,
        ?string $district,
    ): float {
        if ($zoneId) {
            $zone = DeliveryZone::find($zoneId);
            if ($zone && $zone->activo) {
                return (float) $zone->precio;
            }
        }

        if ($deliveryFee !== null && $deliveryFee > 0) {
            return (float) $deliveryFee;
        }

        return match ($district) {
            'Chiclayo'            => 3.00,
            'José Leonardo Ortiz' => 4.00,
            'La Victoria'         => 4.00,
            'Pimentel'            => 6.00,
            'San José'            => 7.00,
            default               => 5.00,
        };
    }
}
