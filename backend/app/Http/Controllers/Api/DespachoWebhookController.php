<?php

namespace App\Http\Controllers\Api;

use App\Events\DespachoActualizado;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DespachoWebhookController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    // POST /v1/webhooks/despacho — llamado por Delivery Central
    public function handle(Request $request): JsonResponse
    {
        // ── Verificar firma del webhook ──────────────────────
        $signature = $request->header('X-Webhook-Signature');
        $secret    = config('services.delivery_central.webhook_secret');
        $payload   = $request->getContent();

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        Log::info('Webhook recibido', [
            'signature_recibida' => $signature,
            'signature_esperada' => $expectedSignature,
            'coinciden'          => hash_equals($expectedSignature, $signature ?? ''),
            'estado'             => $request->input('estado'),
            'order_id'           => $request->input('order_id'),
        ]);

        if (!$signature || !hash_equals($expectedSignature, $signature)) {
            Log::warning('Webhook con firma inválida rechazado', [
                'signature_recibida' => $signature,
                'signature_esperada' => $expectedSignature,
            ]);
            return $this->error('Firma inválida', 401);
        }

        $data = $request->validate([
            'despacho_id'   => 'required|integer',
            'order_id'      => 'required|integer',
            'estado'        => 'required|string',
            'monto_cobrado' => 'nullable|numeric',
            'motorizado'    => 'nullable|array',
        ]);

        $order = Order::where('codigo', $data['order_id'])->first();

        if (!$order) {
            Log::warning('Webhook recibido para pedido inexistente', [
                'order_id' => $data['order_id'],
            ]);
            return $this->success(null, 'Pedido no encontrado, ignorado');
        }

        // ── Mapeo de estados del despacho central → estados de Mahoma ──
        $estadoMap = [
            'aceptado'  => 'en_camino', // motorizado aceptó el pedido
            'recogido'  => 'en_camino', // motorizado recogió en el local (sigue en_camino)
            'entregado' => 'entregado', // motorizado entregó al cliente
            'cancelado' => 'listo',     // despacho cancelado → vuelve a listo para re-solicitar
        ];

        if (isset($estadoMap[$data['estado']])) {
            $nuevoStatus = $estadoMap[$data['estado']];
            // Antes hacía $order->update() directo — eso se saltaba por
            // completo el registro en caja, el registro de comisión, y
            // la restauración de stock al cancelar. Todo pedido que
            // llegaba por delivery (o sea, prácticamente todos los
            // públicos) nunca disparaba ninguno de esos efectos.
            $this->orderService->updateStatus($order, $nuevoStatus);

            Log::info('Webhook aplicado — estado actualizado', [
                'order_id'      => $order->id,
                'estado_central' => $data['estado'],
                'status_mahoma' => $nuevoStatus,
            ]);
        } else {
            Log::info('Webhook recibido — estado sin mapeo, ignorado', [
                'estado' => $data['estado'],
            ]);
        }

        // ── Reenviar evento en tiempo real al admin de Mahoma ──
        broadcast(new DespachoActualizado(
            $data['despacho_id'],
            $data['order_id'],
            $data['estado'],
            $data['motorizado'] ?? null,
            $data['monto_cobrado'] ?? null,
        ));

        return $this->success(null, 'Webhook procesado');
    }
}
