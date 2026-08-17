<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Services\DeliveryCentralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DespachoController extends Controller
{
    public function __construct(
        private DeliveryCentralService $deliveryCentral,
        private OrderRepository        $orderRepository,
    ) {}

    // POST /admin/despachos/solicitar
    public function solicitar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
        ]);

        $order = $this->orderRepository->findById($data['order_id']);
        if (!$order) return $this->notFound('Pedido no encontrado');

        $order->load('items.product');

        $orderData = [
            'client_name'  => $order->client_name,
            'client_phone' => $order->client_phone,
            'address'      => $order->address,
            'district'     => $order->district,
            'reference'    => $order->reference,
            'subtotal'     => (float) $order->subtotal,
            'delivery_fee' => (float) $order->delivery_fee,
            'total'        => (float) $order->total,
            'metodo_pago'  => $order->metodo_pago,
            // Ya no existe contraentrega — todo pedido que llega a
            // solicitar un despacho se entiende como ya pagado.
            'pagado'       => true,
            'lat'          => $order->lat,
            'lng'          => $order->lng,
            'note'         => $order->note,
            'items'        => $order->items->map(fn($i) => [
                'name'         => $i->product?->name ?? 'Producto',
                'qty'          => $i->qty,
                'unit_price'   => (float) $i->unit_price,
                'subtotal'     => (float) $i->subtotal,
                'custom_summary' => $i->custom_summary,
            ])->toArray(),
        ];
        $despacho = $this->deliveryCentral->solicitarDespacho($order->codigo, $orderData);

        if (!$despacho) {
            return $this->error('No se pudo conectar con el servicio de delivery. Intenta de nuevo.', 502);
        }

        return $this->success($despacho, 'Despacho solicitado correctamente');
    }

    // GET /admin/despachos/{order_id}/estado
    public function estado(int $orderId): JsonResponse
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) return $this->notFound('Pedido no encontrado');

        $despacho = $this->deliveryCentral->consultarEstado($order->codigo);

        if (!$despacho) {
            return $this->notFound('Sin despacho activo para este pedido');
        }

        return $this->success($despacho);
    }

    // POST /admin/despachos/{order_id}/cancelar
    public function cancelar(int $orderId): JsonResponse
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) return $this->notFound('Pedido no encontrado');

        $ok = $this->deliveryCentral->cancelarDespacho($order->codigo);

        if (!$ok) {
            return $this->error('No se pudo cancelar el despacho', 502);
        }

        return $this->success(null, 'Despacho cancelado');
    }
}
