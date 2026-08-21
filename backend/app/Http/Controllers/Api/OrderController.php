<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
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

            // Notificar por push a todo el staff con acceso a pedidos —
            // solo pedidos de clientes reales, no los que arma el propio
            // staff manualmente desde el panel (adminStore).
            //
            // Esto va DESPUÉS de responderle al cliente (dispatch()->afterResponse()),
            // no antes — el envío push hace peticiones HTTP reales a cada
            // navegador suscrito, y si una suscripción quedó vieja/rota,
            // esa llamada puede quedarse esperando en vez de fallar rápido.
            // Antes esto corría ANTES de responder, así que un pedido nuevo
            // podía tardar 15+ segundos en confirmarse — y en el servidor
            // de desarrollo (de un solo proceso), bloqueaba también
            // cualquier otra pestaña que estuviera esperando otra respuesta.
            dispatch(function () use ($order) {
                try {
                    $staff = \App\Models\User::all()->filter(fn($u) => $u->hasViewAccess('orders'));
                    \Illuminate\Support\Facades\Notification::send(
                        $staff,
                        new \App\Notifications\NewOrderNotification($order)
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('No se pudo enviar notificación push: ' . $e->getMessage());
                }
            })->afterResponse();

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
            'codigo'     => $order->codigo,
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
            'client_phone' => 'nullable|required_unless:type,local|string|max:20',

            // ── Tipo ──────────────────────────────────────────────
            'type' => 'required|in:local,recoger,delivery',

            // ── Local ─────────────────────────────────────────────
            'mesa' => 'nullable|string|max:20',

            // ── Delivery ──────────────────────────────────────────
            'address'          => 'nullable|string|max:255',
            'reference'        => 'nullable|string|max:255',
            'district'         => 'nullable|string|max:100',
            'delivery_zone_id' => 'nullable|exists:delivery_tariffs,id',
            'delivery_fee'     => 'nullable|numeric|min:0',
            'lat'              => 'nullable|numeric',
            'lng'              => 'nullable|numeric',

            // ── Pago ──────────────────────────────────────────────
            'metodo_pago' => 'nullable|in:anticipado,efectivo,yape,tarjeta',
            // ── Nota y entrega personalizada ───────────────────────
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

            'items.*.customization'                          => 'nullable|array',
            'items.*.customization.*.section_id'              => 'required_with:items.*.customization|integer',
            'items.*.customization.*.selections'              => 'nullable|array',
            'items.*.customization.*.selections.*.option_id'  => 'required|integer',

            'items.*.extras'             => 'nullable|array',
            'items.*.extras.*.extra_id'  => 'required|integer',
            // nullable: pedidos creados antes de este cambio no tienen
            // 'type' guardado en su extras[]; calcularPrecioItem() lo
            // asume 'own' cuando falta.
            'items.*.extras.*.type'      => 'nullable|in:own,shared',
            'items.*.extras.*.qty'       => 'nullable|integer|min:1',

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

    // PATCH /admin/orders/{id}/cobrar — solo Local, marca método de pago y entrega el pedido
    public function cobrar(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'metodo_pago' => 'required|in:efectivo,yape,tarjeta',
        ]);

        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');

        try {
            $order = $this->orderService->cobrarLocal($order, $data['metodo_pago']);
            return $this->success(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // PUT /admin/orders/{id}/items
    public function updateItems(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',

            'items.*.customization'                          => 'nullable|array',
            'items.*.customization.*.section_id'              => 'required_with:items.*.customization|integer',
            'items.*.customization.*.selections'              => 'nullable|array',
            'items.*.customization.*.selections.*.option_id'  => 'required|integer',

            'items.*.extras'             => 'nullable|array',
            'items.*.extras.*.extra_id'  => 'required|integer',
            // nullable: pedidos creados antes de este cambio no tienen
            // 'type' guardado en su extras[]; calcularPrecioItem() lo
            // asume 'own' cuando falta.
            'items.*.extras.*.type'      => 'nullable|in:own,shared',
            'items.*.extras.*.qty'       => 'nullable|integer|min:1',

            'items.*.custom_summary' => 'nullable|string|max:500',
        ]);

        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');

        try {
            $order = $this->orderService->updateItems($order, $data['items']);
            return $this->success(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // DELETE /admin/orders/{id}
    public function destroy(int $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);
        if (!$order) return $this->notFound('Pedido no encontrado');
        $order->delete();
        return $this->success(null, 'Pedido eliminado');
    }

    // GET /admin/orders/trashed
    public function trashed(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->paginateTrashed(
            filters: [
                'search'    => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to'   => $request->get('date_to'),
            ],
            perPage: $request->integer('per_page', 10)
        );

        return $this->success(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    // DELETE /admin/orders/{id}/force — borrado definitivo, solo desde la papelera
    public function forceDestroy(int $id): JsonResponse
    {
        $order = $this->orderRepository->findTrashedById($id);
        if (!$order) return $this->notFound('Pedido eliminado no encontrado');

        // Borra los items asociados para no dejar registros huérfanos
        $order->items()->delete();
        $order->forceDelete();

        return $this->success(null, 'Pedido eliminado definitivamente');
    }

    // POST /admin/orders/{id}/restore
    public function restore(int $id): JsonResponse
    {
        $order = $this->orderRepository->findTrashedById($id);
        if (!$order) return $this->notFound('Pedido eliminado no encontrado');

        $order->restore();

        return $this->success(new OrderResource($order), 'Pedido restaurado');
    }

    // GET /orders/{codigo}/track — público (seguimiento del cliente)
    public function track(Request $request, int $id): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $order = Order::where('codigo', $id)->first();
        if (!$order) return $this->notFound('Pedido no encontrado');

        $phoneInput = $this->normalizarTelefono($request->phone);
        $phoneOrder = $this->normalizarTelefono($order->client_phone);

        if ($phoneInput !== $phoneOrder) {
            return $this->error('Datos incorrectos', 403);
        }

        $order->load('items.product');

        return $this->success([
            'id'                 => $order->id,
            'codigo'             => $order->codigo,
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
            // ── Entrega programada / mensaje personalizado ─────────
            'mensaje_tarjeta'    => $order->mensaje_tarjeta,
            'fecha_entrega'      => $order->fecha_entrega?->format('Y-m-d'),
            'hora_entrega'       => $order->hora_entrega,
            'entrega_programada' => $order->entrega_programada,
            'created_at'         => $order->created_at?->toISOString(),
            'updated_at'         => $order->updated_at?->toISOString(),
            'items'              => $order->items->map(fn($i) => [
                'name'           => $i->product?->name ?? 'Producto',
                'icon'           => $i->product?->icon ?? 'package',
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

        $order = Order::where('codigo', $request->order_id)->first();

        $phoneInput = $this->normalizarTelefono($request->phone ?? '');
        $phoneOrder = $order
            ? $this->normalizarTelefono($order->client_phone)
            : '';

        if (!$order || $phoneInput !== $phoneOrder) {
            return $this->error('No encontramos un pedido con esos datos', 404);
        }

        return $this->success([
            'id'     => $order->id,
            'codigo' => $order->codigo,
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
            'nuevo'      => ['label' => 'Pedido recibido',  'icon' => 'clipboard-list'],
            'confirmado' => ['label' => 'Confirmado',       'icon' => 'check-circle'],
            'preparando' => ['label' => 'Alistando pedido', 'icon' => 'package'],
            'listo'      => ['label' => $type === 'delivery' ? 'Listo para entrega' : 'Acercate a recoger tu pedido', 'icon' => 'party-popper'],
        ];

        if ($type === 'delivery') {
            $flow['en_camino'] = ['label' => 'En camino', 'icon' => 'bike'];
        }

        $flow['entregado'] = ['label' => 'Entregado', 'icon' => 'home'];

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

    // Deja solo dígitos y quita el prefijo de país "51" SI está al
    // inicio — antes se usaba ltrim($str, '51'), que en PHP no quita un
    // prefijo: quita cualquier '5' o '1' suelto que encuentre al
    // comienzo, uno por uno, mientras existan. Con un celular peruano
    // normal (empieza en 9) casi nunca se nota, pero es el mecanismo que
    // "autentica" el acceso al pedido de un cliente — no puede depender
    // de una coincidencia.
    private function normalizarTelefono(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return preg_replace('/^51/', '', $digits);
    }
}
