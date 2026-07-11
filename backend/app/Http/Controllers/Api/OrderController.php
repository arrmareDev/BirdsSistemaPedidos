<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService    $orderService,
        private OrderRepository $orderRepository,
    ) {}

    // GET /admin/orders
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->paginate(
            filters: [
                'status'    => $request->get('status'),
                'search'    => $request->get('search'),
                'date'      => $request->get('date'),
                'date_from' => $request->get('date_from'),
                'date_to'   => $request->get('date_to'),
                'type'      => $request->get('type'),
            ],
            perPage: $request->integer('per_page', 10)
        );

        return $this->success(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    // POST /orders — público (cliente hace pedido desde la web)
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->create($request->validated());
            $order->load('items.product');
            return $this->created(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // GET /orders/{id}/status — público
    public function status(int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');

        return $this->success([
            'id'         => $order->id,
            'status'     => $order->status,
            'updated_at' => $order->updated_at?->toISOString(),
        ]);
    }

    // GET /admin/orders/{id}
public function show(int $id): JsonResponse
{
    $order = $this->orderRepository->findById($id);
    if (!$order) return $this->notFound('Pedido no encontrado');
    $order->load('items.product');   // ← ESTA LÍNEA FALTABA
    return $this->success(new OrderResource($order));
}

    // POST /admin/orders — admin crea pedido manual
    public function adminStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            // ── Cliente ───────────────────────────────────────────
            'client_name'  => 'required|string|max:150',
            'client_phone' => 'required|string|max:20',

            // ── Tipo ──────────────────────────────────────────────
            'type' => 'required|in:local,recoger,delivery',

            // ── Local ─────────────────────────────────────────────
            'mesa' => 'nullable|string|max:20',

            // ── Delivery ──────────────────────────────────────────
            'address'          => 'nullable|string|max:255',
            'reference'        => 'nullable|string|max:255',
            'district'         => 'nullable|string|max:100',
            'delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'delivery_fee'     => 'nullable|numeric|min:0',
            'lat'              => 'nullable|numeric',
            'lng'              => 'nullable|numeric',

            // ── Pago ──────────────────────────────────────────────
            'metodo_pago' => 'nullable|in:anticipado,contraentrega_efectivo,contraentrega_yape',

            // ── Nota y florería ───────────────────────────────────
            'note'               => 'nullable|string|max:500',
            'mensaje_tarjeta'    => 'nullable|string|max:300',
            'fecha_entrega'      => 'nullable|date|after_or_equal:today',
            'hora_entrega'       => 'nullable|date_format:H:i',
            'entrega_programada' => 'boolean',

            // ── Total e items ─────────────────────────────────────
            'total'                  => 'required|numeric|min:0',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.customization'  => 'nullable|array',
            'items.*.extras'         => 'nullable|array',
            'items.*.custom_summary' => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->orderService->create($data);
            $order->load('items.product');
            return $this->created(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // PATCH /admin/orders/{id}/status
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:confirmado,preparando,listo,en_camino,entregado,cancelado',
        ]);

        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');

        $order = $this->orderService->updateStatus($order, $data['status']);

        return $this->success(new OrderResource($order));
    }

    // DELETE /admin/orders/{id}
    public function destroy(int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');
        $order->delete();
        return $this->success(null, 'Pedido eliminado');
    }

    // GET /orders/{id}/track — público (seguimiento del cliente)
    public function track(Request $request, int $id): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');

        $phoneInput = ltrim(preg_replace('/\D/', '', $request->phone), '51');
        $phoneOrder = ltrim(preg_replace('/\D/', '', $order->client_phone), '51');

        if ($phoneInput !== $phoneOrder) {
            return $this->error('Datos incorrectos', 403);
        }

        $order->load('items.product');

        return $this->success([
            'id'                 => $order->id,
            'status'             => $order->status,
            'client_name'        => $order->client_name,
            'client_phone'       => $order->client_phone,
            'type'               => $order->type,
            'mesa'               => $order->mesa,
            'total'              => (float) $order->total,
            'subtotal'           => (float) $order->subtotal,
            'delivery_fee'       => (float) $order->delivery_fee,
            'note'               => $order->note,
            'address'            => $order->address,
            'reference'          => $order->reference,
            'district'           => $order->district,
            'lat'                => $order->lat,
            'lng'                => $order->lng,
            // ── Florería ──────────────────────────────────────────
            'mensaje_tarjeta'    => $order->mensaje_tarjeta,
            'fecha_entrega'      => $order->fecha_entrega?->format('Y-m-d'),
            'hora_entrega'       => $order->hora_entrega,
            'entrega_programada' => $order->entrega_programada,
            'created_at'         => $order->created_at?->toISOString(),
            'updated_at'         => $order->updated_at?->toISOString(),
            'items'              => $order->items->map(fn($i) => [
                'name'           => $i->product?->name  ?? 'Producto',
                'emoji'          => $i->product?->emoji ?? '💐',
                'qty'            => (int)   $i->qty,
                'unit_price'     => (float) $i->unit_price,
                'subtotal'       => (float) $i->subtotal,
                'customization'  => $i->customization ?? [],
                'extras'         => $i->extras         ?? [],
                'custom_summary' => $i->custom_summary,
            ])->toArray(),
            'status_history' => $this->buildStatusHistory($order->status, $order->type->value),
        ]);
    }

    // POST /orders/search — público
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'phone'    => 'required|string',
        ]);

        $order = $this->orderRepository->findById($request->order_id);

        $phoneInput = ltrim(preg_replace('/\D/', '', $request->phone ?? ''), '51');
        $phoneOrder = $order
            ? ltrim(preg_replace('/\D/', '', $order->client_phone), '51')
            : '';

        if (!$order || $phoneInput !== $phoneOrder) {
            return $this->error('No encontramos un pedido con esos datos', 404);
        }

        return $this->success([
            'id'     => $order->id,
            'status' => $order->status,
            'type'   => $order->type,
            'total'  => (float) $order->total,
        ]);
    }

    // ── Historia de estados ───────────────────────────────────
    // $type: 'local' | 'recoger' | 'delivery' — los pedidos que no son
    // delivery nunca pasan por "en_camino" (no hay motorizado de por medio).
private function buildStatusHistory(string $currentStatus, string $type = 'delivery'): array
    {
        $flow = [
            'nuevo'      => ['label' => 'Pedido recibido',  'icon' => '📝'],
            'confirmado' => ['label' => 'Confirmado',       'icon' => '✅'],
            'preparando' => ['label' => 'Alistando pedido', 'icon' => '💐'], // ← LÍNEA MODIFICADA
            'listo'      => ['label' => $type === 'delivery' ? 'Listo para entrega' : 'Acercate a recoger tu pedido', 'icon' => '🎉'],
        ];

        if ($type === 'delivery') {
            $flow['en_camino'] = ['label' => 'En camino', 'icon' => '🛵'];
        }

        $flow['entregado'] = ['label' => 'Entregado', 'icon' => '🏠'];

        $statuses   = array_keys($flow);
        $currentIdx = array_search($currentStatus, $statuses);
        if ($currentIdx === false) $currentIdx = 0;

        return array_map(
            fn($status, $idx) => [
                'status' => $status,
                'label'  => $flow[$status]['label'],
                'icon'   => $flow[$status]['icon'],
                'state'  => match (true) {
                    $idx < $currentIdx   => 'done',
                    $idx === $currentIdx => 'active',
                    default              => 'pending',
                },
            ],
            $statuses,
            array_keys($statuses)
        );
    }
}
