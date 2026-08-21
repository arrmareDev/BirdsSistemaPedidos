<?php

namespace App\Services;

use App\Enums\SaleChannel;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Client;
use App\Models\Comision;
use App\Models\ConfiguracionSistema;
use App\Models\DeliveryTariff;
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

            // ── 1. Recalcular precios desde la BD (nunca confiar en lo que
            // manda el cliente) y validar stock en la misma pasada ───────
            // $pricing/$products quedan indexados por posición del array
            // $items, para reusar el cálculo y la MISMA instancia (ya
            // bloqueada) al crear los OrderItem más abajo.
            //
            // lockForUpdate() bloquea la fila del producto hasta que la
            // transacción termine — sin esto, dos pedidos simultáneos por
            // el último producto en stock podían pasar ambos la
            // validación de tieneStock() antes de que cualquiera de los
            // dos alcanzara a descontar, vendiendo más de lo que había.
            $items    = $data['items'] ?? [];
            $pricing  = [];
            $products = [];
            $subtotal = 0.0;

            foreach ($items as $i => $item) {
                $product = Product::with(['customizationSections.options', 'extras', 'extrasCompartidos'])
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);
                $qty = (int) ($item['qty'] ?? 1);

                if ($product->controla_stock && !$product->tieneStock($qty)) {
                    throw new \Exception(
                        "Stock insuficiente para: {$product->name}. " .
                            "Disponible: {$product->stock}, solicitado: {$qty}"
                    );
                }

                $products[$i] = $product;
                $pricing[$i]  = $this->calcularPrecioItem($product, $item);
                $subtotal    += $pricing[$i]['unit_price'] * $qty;
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
                // ── Entrega programada / mensaje personalizado ─────
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
            // unit_price/customization/extras salen de $pricing (calculado
            // en el paso 1 desde la BD), NUNCA de $item directamente —
            // eso es lo que evita que un unit_price manipulado en el
            // request termine cobrándose. $products[$i] es la MISMA
            // instancia bloqueada del paso 1 — no se vuelve a pedir sin
            // lock, que rompería la protección contra la carrera.
            foreach ($items as $i => $item) {
                $product = $products[$i];
                $qty     = (int) ($item['qty'] ?? 1);
                $calc    = $pricing[$i];

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'unit_price'     => $calc['unit_price'],
                    'subtotal'       => $calc['unit_price'] * $qty,
                    'customization'  => $calc['customization'],
                    'extras'         => $calc['extras'],
                    'custom_summary' => $item['custom_summary'] ?? null,
                ]);

                $product->reducirStock($qty, 'venta', $order->id);
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
        $estadoAnterior = $order->status;
        $order->update(['status' => $status]);

        if ($status === 'cancelado' && $estadoAnterior !== 'cancelado') {
            $this->restaurarStockPorCancelacion($order);
        }

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

    // El stock se descontaba al crear el pedido (o al editarlo), pero
    // cancelar nunca lo devolvía — quedaba como si el producto se
    // hubiera vendido de verdad, aunque el pedido nunca se entregó.
    private function restaurarStockPorCancelacion(Order $order): void
    {
        $order->load('items');
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            $product?->restaurarStock($item->qty, 'cancelacion', $order->id, 'Pedido cancelado');
        }
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
                $product?->restaurarStock($oldItem->qty, 'edicion_pedido', $order->id, 'Item removido al editar el pedido');
            }

            // ── 2. Recalcular precios desde la BD y validar stock ──
            // lockForUpdate() por la misma razón que en create(): evita
            // que dos ediciones/pedidos simultáneos sobre el mismo
            // producto pasen ambos la validación antes de que cualquiera
            // descuente stock.
            $pricing  = [];
            $products = [];
            foreach ($items as $i => $item) {
                $product = Product::with(['customizationSections.options', 'extras', 'extrasCompartidos'])
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);
                $qty = (int) ($item['qty'] ?? 1);

                if ($product->controla_stock && !$product->tieneStock($qty)) {
                    throw new \Exception(
                        "Stock insuficiente para: {$product->name}. " .
                            "Disponible: {$product->stock}, solicitado: {$qty}"
                    );
                }

                $products[$i] = $product;
                $pricing[$i]  = $this->calcularPrecioItem($product, $item);
            }

            // ── 3. Reemplazar items ────────────────────────────────
            $order->items()->delete();

            $subtotal = 0;
            foreach ($items as $i => $item) {
                $product      = $products[$i];
                $qty          = (int) ($item['qty'] ?? 1);
                $calc         = $pricing[$i];
                $itemSubtotal = $calc['unit_price'] * $qty;
                $subtotal    += $itemSubtotal;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'unit_price'     => $calc['unit_price'],
                    'subtotal'       => $itemSubtotal,
                    'customization'  => $calc['customization'],
                    'extras'         => $calc['extras'],
                    'custom_summary' => $item['custom_summary'] ?? null,
                ]);

                $product->reducirStock($qty, 'edicion_pedido', $order->id, 'Item agregado al editar el pedido');
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
        // Ya no se filtra por efectivo — TODAS las ventas entran a
        // caja para ver el panorama completo. El cuadre físico (lo que
        // debe haber en el cajón) sigue contando solo efectivo, pero
        // eso lo decide getTotalVentasAttribute() en el modelo Caja,
        // no este método.
        try {
            // Igual que en CajaController: prioriza la caja que esté
            // realmente abierta (aunque sea de un día anterior sin
            // cerrar), no exige que sea exacto la de "hoy".
            $caja = Caja::where('estado', 'abierta')->first();

            if (!$caja) return;

            $yaExiste = CajaMovimiento::where('caja_id', $caja->id)
                ->where('order_id', $order->id)
                ->exists();

            if ($yaExiste) return;

            CajaMovimiento::create([
                'caja_id'     => $caja->id,
                'order_id'    => $order->id,
                'metodo_pago' => $order->metodo_pago,
                'type'        => 'venta',
                'amount'      => $order->total,
                'description' => "Pedido #{$order->codigo} — {$order->client_name}",
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

    /**
     * Recalcula el precio real de un item de pedido desde la base de
     * datos — nunca desde $item['unit_price'], $item['customization'][...]
     * ['price_modifier'] ni $item['extras'][...]['price'], que son valores
     * que el cliente puede manipular libremente antes de enviarlos.
     *
     * $product debe venir con customizationSections.options, extras y
     * extrasCompartidos ya cargados (evita N+1 en el foreach del caller).
     *
     * Devuelve unit_price + las versiones "limpias" de customization/extras
     * (solo con los ids que sí pertenecen al producto), listas para
     * guardarse tal cual en el OrderItem.
     */
    private function calcularPrecioItem(Product $product, array $item): array
    {
        $unitPrice = (float) $product->precio_final;

        // ── Personalización: cada selección debe pertenecer a una
        // sección real de ESTE producto. Un section_id/option_id que no
        // exista o que sea de otro producto simplemente se ignora.
        $customizationOut = [];
        foreach ($item['customization'] ?? [] as $sec) {
            $section = $product->customizationSections
                ->firstWhere('id', $sec['section_id'] ?? null);
            if (!$section) continue;

            $selectionsOut = [];
            foreach ($sec['selections'] ?? [] as $sel) {
                $option = $section->options->firstWhere('id', $sel['option_id'] ?? null);
                if (!$option) continue;

                $unitPrice += (float) $option->price_modifier;
                $selectionsOut[] = [
                    'option_id'      => $option->id,
                    'name'           => $option->name,
                    'price_modifier' => (float) $option->price_modifier,
                ];
            }

            if ($selectionsOut) {
                $customizationOut[] = [
                    'section_id' => $section->id,
                    'seccion'    => $section->seccion,
                    'label'      => $section->label,
                    'selections' => $selectionsOut,
                ];
            }
        }

        // ── Extras: 'own' busca en los extras propios del producto
        // (product_extras), 'shared' busca en los extras compartidos
        // vinculados a este producto (tabla Extra vía extra_product).
        // Ambas tablas tienen su propio id autoincremental — el 'type'
        // es lo que evita ambigüedad entre ellas.
        $extrasOut = [];
        foreach ($item['extras'] ?? [] as $ex) {
            $type  = ($ex['type'] ?? 'own') === 'shared' ? 'shared' : 'own';
            $qtyEx = max(1, (int) ($ex['qty'] ?? 1));

            $extra = $type === 'shared'
                ? $product->extrasCompartidos->firstWhere('id', $ex['extra_id'] ?? null)
                : $product->extras->firstWhere('id', $ex['extra_id'] ?? null);

            if (!$extra) continue;

            $unitPrice += (float) $extra->price * $qtyEx;
            $extrasOut[] = [
                'extra_id' => $extra->id,
                'type'     => $type,
                'name'     => $extra->name,
                'price'    => (float) $extra->price,
                'qty'      => $qtyEx,
            ];
        }

        return [
            'unit_price'    => round($unitPrice, 2),
            'customization' => $customizationOut ?: null,
            'extras'        => $extrasOut ?: null,
        ];
    }

    private function resolveDeliveryFee(
        ?int    $zoneId,
        ?float  $deliveryFee,
        ?string $district,
    ): float {
        // $zoneId en realidad referencia una DeliveryTariff (tarifa por
        // distancia) — es lo que devuelve /delivery-zones/detectar, que es
        // el flujo real de checkout. Antes esto buscaba en DeliveryZone
        // (una tabla vieja con distritos de Chiclayo, nunca poblada desde
        // el checkout real) y casi siempre fallaba silenciosamente.
        if ($zoneId) {
            $tarifa = DeliveryTariff::find($zoneId);
            if ($tarifa && $tarifa->activo) {
                return (float) $tarifa->precio;
            }
        }

        if ($deliveryFee !== null && $deliveryFee > 0) {
            return (float) $deliveryFee;
        }

        // Última red de seguridad si no hay tarifa ni fee manual válido —
        // configurable por negocio, sin asumir ninguna ciudad o distrito.
        return (float) ConfiguracionSistema::get('delivery_fee_fallback', '5.00');
    }
}
