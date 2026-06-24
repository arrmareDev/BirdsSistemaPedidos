<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliveryCentralService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.delivery_central.url');
        $this->apiKey  = config('services.delivery_central.api_key');
    }

    /**
     * Solicita un despacho al backend central de delivery.
     */
    public function solicitarDespacho(int $orderId, array $orderData): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withToken($this->apiKey)
                ->post("{$this->baseUrl}/despachos/solicitar", [
                    'order_id'   => $orderId,
                    'order_data' => $orderData,
                ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::warning('Delivery Central rechazó la solicitud', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->json(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Error al solicitar despacho en Delivery Central: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Consulta el estado actual de un despacho.
     */
    public function consultarEstado(int $orderId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withToken($this->apiKey)
                ->get("{$this->baseUrl}/despachos/{$orderId}/estado");

            return $response->successful() ? $response->json('data') : null;
        } catch (\Throwable $e) {
            Log::error('Error al consultar despacho en Delivery Central: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancela un despacho activo en el central.
     */
    public function cancelarDespacho(int $orderId): bool
    {
        try {
            $response = Http::timeout(10)
                ->withToken($this->apiKey)
                ->post("{$this->baseUrl}/despachos/{$orderId}/cancelar");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Error al cancelar despacho en Delivery Central: ' . $e->getMessage());
            return false;
        }
    }
}
