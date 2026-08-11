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

    // Acumula qué opciones elige más un cliente, por cada sección de
    // personalización que realmente exista en sus pedidos — sin asumir de
    // antemano cuáles son esas secciones (cada negocio define las suyas).
    public function updatePreferences(array $orderData = []): void
    {
        // Actualizar dirección y distrito si vienen en el pedido
        $updates = [];
        if (!empty($orderData['address']))  $updates['address']  = $orderData['address'];
        if (!empty($orderData['district'])) $updates['district'] = $orderData['district'];

        // seccion => ['label' => ..., 'counts' => [opcion => qty]] — solo de este pedido
        $countsThisOrder = [];

        foreach ($orderData['items'] ?? [] as $item) {
            // customization es array de secciones:
            // [{section_id, seccion, label, selections: [{option_id, name}]}]
            $customization = $item['customization'] ?? [];

            foreach ($customization as $sec) {
                $seccion    = $sec['seccion']    ?? '';
                $label      = $sec['label']      ?? $seccion;
                $selections = $sec['selections'] ?? [];

                if (empty($seccion)) continue;

                $countsThisOrder[$seccion]['label'] = $label;

                foreach ($selections as $sel) {
                    $name = $sel['name'] ?? '';
                    if (empty($name)) continue;

                    $countsThisOrder[$seccion]['counts'][$name] =
                        ($countsThisOrder[$seccion]['counts'][$name] ?? 0) + 1;
                }
            }
        }

        // Mezclar con el historial acumulado de pedidos anteriores
        $existing = $this->preferences['secciones'] ?? [];
        $secciones = $existing;

        foreach ($countsThisOrder as $seccion => $data) {
            $secciones[$seccion]['label'] = $data['label'];
            foreach ($data['counts'] ?? [] as $name => $qty) {
                $secciones[$seccion]['counts'][$name] =
                    ($secciones[$seccion]['counts'][$name] ?? 0) + $qty;
            }
            arsort($secciones[$seccion]['counts']);
            $secciones[$seccion]['top'] = array_key_first($secciones[$seccion]['counts']);
        }

        $updates['preferences'] = ['secciones' => $secciones];
        $this->update($updates);
    }
}
