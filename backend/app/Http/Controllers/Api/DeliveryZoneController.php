<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTariff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    // GET /delivery-zones — público, lista de tarifas para mostrar en checkout
    public function index(): JsonResponse
    {
        $tarifas = DeliveryTariff::activas()->get(['id', 'distancia_max_km', 'precio']);

        return $this->success($tarifas);
    }

    // GET /delivery-zones/detectar?lat=...&lng=... — público
    public function detectar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $localLat = (float) config('services.local.lat');
        $localLng = (float) config('services.local.lng');
        $radioMax = (float) config('services.local.radio_max_km', 7.0);

        $distancia = $this->haversine($data['lat'], $data['lng'], $localLat, $localLng);

        if ($distancia > $radioMax) {
            return $this->notFound('Lo sentimos, tu dirección está fuera de nuestra zona de delivery');
        }

        $tarifa = DeliveryTariff::where('activo', true)
            ->where('distancia_max_km', '>=', $distancia)
            ->orderBy('distancia_max_km')
            ->first();

        if (!$tarifa) {
            return $this->notFound('No hay tarifa disponible para esta distancia');
        }

        return $this->success([
            'id'            => $tarifa->id,
            'nombre'        => $this->nombreZona($distancia),
            'precio'        => (float) $tarifa->precio,
            'distancia_km'  => round($distancia, 2),
        ]);
    }

    // GET /admin/delivery-zones — panel admin, lista de tarifas
    public function adminIndex(): JsonResponse
    {
        $tarifas = DeliveryTariff::orderBy('orden')->get();
        return $this->success($tarifas);
    }

    // POST /admin/delivery-zones
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'distancia_max_km' => 'required|numeric|min:0.1|max:50',
            'precio'           => 'required|numeric|min:0',
        ]);

        $maxOrden = DeliveryTariff::max('orden') ?? 0;

        $tarifa = DeliveryTariff::create([
            ...$data,
            'activo' => true,
            'orden'  => $maxOrden + 1,
        ]);

        return $this->created($tarifa, 'Tarifa creada correctamente');
    }

    // PUT /admin/delivery-zones/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $tarifa = DeliveryTariff::find($id);
        if (!$tarifa) return $this->notFound('Tarifa no encontrada');

        $data = $request->validate([
            'distancia_max_km' => 'sometimes|numeric|min:0.1|max:50',
            'precio'           => 'sometimes|numeric|min:0',
        ]);

        $tarifa->update($data);

        return $this->success($tarifa, 'Tarifa actualizada correctamente');
    }

    // DELETE /admin/delivery-zones/{id}
    public function destroy(int $id): JsonResponse
    {
        $tarifa = DeliveryTariff::find($id);
        if (!$tarifa) return $this->notFound('Tarifa no encontrada');

        $tarifa->delete();

        return $this->success(null, 'Tarifa eliminada correctamente');
    }

    // PATCH /admin/delivery-zones/{id}/toggle
    public function toggle(int $id): JsonResponse
    {
        $tarifa = DeliveryTariff::find($id);
        if (!$tarifa) return $this->notFound('Tarifa no encontrada');

        $tarifa->update(['activo' => !$tarifa->activo]);

        return $this->success($tarifa, 'Estado actualizado');
    }

    // POST /admin/delivery-zones/reorder
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orden'   => 'required|array',
            'orden.*' => 'integer|exists:delivery_tariffs,id',
        ]);

        foreach ($data['orden'] as $index => $id) {
            DeliveryTariff::where('id', $id)->update(['orden' => $index + 1]);
        }

        return $this->success(null, 'Orden actualizado');
    }

    // ── Helpers ───────────────────────────────────────────────
    private function nombreZona(float $distanciaKm): string
    {
        return 'Zona ' . number_format($distanciaKm, 1) . ' km';
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
