<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'district',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function updatePreferences(array $orderData = []): void
    {
        // Actualizar dirección y distrito si vienen en el pedido
        $updates = [];
        if (!empty($orderData['address']))  $updates['address']  = $orderData['address'];
        if (!empty($orderData['district'])) $updates['district'] = $orderData['district'];

        // Extraer personalización del array de items del pedido actual
        $salsaCount    = [];
        $ensaladaCount = [];
        $papasCount    = [];
        $terminoCount  = [];

        foreach ($orderData['items'] ?? [] as $item) {
            // customization es array de secciones:
            // [{section_id, seccion, label, selections: [{option_id, name}]}]
            $customization = $item['customization'] ?? [];

            foreach ($customization as $sec) {
                $seccion   = $sec['seccion']    ?? '';
                $selections = $sec['selections'] ?? [];

                foreach ($selections as $sel) {
                    $name = $sel['name'] ?? '';
                    if (empty($name)) continue;

                    match ($seccion) {
                        'salsas'   => $salsaCount[$name]    = ($salsaCount[$name]    ?? 0) + 1,
                        'ensalada' => $ensaladaCount[$name] = ($ensaladaCount[$name] ?? 0) + 1,
                        'papas'    => $papasCount[$name]    = ($papasCount[$name]    ?? 0) + 1,
                        'termino'  => $terminoCount[$name]  = ($terminoCount[$name]  ?? 0) + 1,
                        default    => null,
                    };
                }
            }
        }

        // Mezclar con preferencias existentes para acumular historial
        $existing = $this->preferences ?? [];

        if (!empty($salsaCount)) {
            foreach ($existing['salsas_count'] ?? [] as $name => $count) {
                $salsaCount[$name] = ($salsaCount[$name] ?? 0) + $count;
            }
            arsort($salsaCount);
        }

        if (!empty($ensaladaCount)) {
            foreach ($existing['ensalada_count'] ?? [] as $name => $count) {
                $ensaladaCount[$name] = ($ensaladaCount[$name] ?? 0) + $count;
            }
            arsort($ensaladaCount);
        }

        if (!empty($papasCount)) {
            foreach ($existing['papas_count'] ?? [] as $name => $count) {
                $papasCount[$name] = ($papasCount[$name] ?? 0) + $count;
            }
            arsort($papasCount);
        }

        if (!empty($terminoCount)) {
            foreach ($existing['termino_count'] ?? [] as $name => $count) {
                $terminoCount[$name] = ($terminoCount[$name] ?? 0) + $count;
            }
            arsort($terminoCount);
        }

        $prefs = [
            // Arrays ordenados por frecuencia — para mostrar en UI
            'salsas'   => array_keys($salsaCount)    ?: ($existing['salsas']   ?? []),
            'ensalada' => array_key_first($ensaladaCount) ?? ($existing['ensalada'] ?? null),
            'papas'    => array_key_first($papasCount)    ?? ($existing['papas']    ?? null),
            'termino'  => array_key_first($terminoCount)  ?? ($existing['termino']  ?? null),
            // Contadores para acumular en futuros pedidos
            'salsas_count'   => $salsaCount    ?: ($existing['salsas_count']   ?? []),
            'ensalada_count' => $ensaladaCount ?: ($existing['ensalada_count'] ?? []),
            'papas_count'    => $papasCount    ?: ($existing['papas_count']    ?? []),
            'termino_count'  => $terminoCount  ?: ($existing['termino_count']  ?? []),
        ];

        $updates['preferences'] = $prefs;
        $this->update($updates);
    }
}
