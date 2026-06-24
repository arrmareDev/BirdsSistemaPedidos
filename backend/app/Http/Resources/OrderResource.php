<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ── Identificación ────────────────────────────────────
            'id'           => $this->id,
            'client_name'  => $this->client_name,
            'client_phone' => $this->client_phone,

            // ── Tipo y estado ─────────────────────────────────────
            'type'   => $this->type,   // recoger | delivery
            'status' => $this->status,

            // ── Delivery ──────────────────────────────────────────
            'address'          => $this->address,
            'reference'        => $this->reference,
            'district'         => $this->district,
            'delivery_zone_id' => $this->delivery_zone_id,
            'lat'              => $this->lat,
            'lng'              => $this->lng,

            // ── Pago ──────────────────────────────────────────────
            'metodo_pago' => $this->metodo_pago,

            // ── Nota general ──────────────────────────────────────
            'note' => $this->note,

            // ── Florería ──────────────────────────────────────────
            'mensaje_tarjeta'    => $this->mensaje_tarjeta,
            'fecha_entrega'      => $this->fecha_entrega?->format('Y-m-d'),
            'hora_entrega'       => $this->hora_entrega,
            'entrega_programada' => $this->entrega_programada,

            // ── Totales ───────────────────────────────────────────
            'subtotal'     => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'total'        => (float) $this->total,

            // ── Items ─────────────────────────────────────────────
            'items' => $this->whenLoaded(
                'items',
                fn() => $this->items->map(fn($item) => [
                    'id'             => $item->id,
                    'product_id'     => $item->product_id,
                    'product'        => $item->product ? [
                        'id'      => $item->product->id,
                        'name'    => $item->product->name,
                        'emoji'   => $item->product->emoji,
                        'ocasion' => $item->product->ocasion,
                        'color'   => $item->product->color,
                        'tamano'  => $item->product->tamano,
                    ] : null,
                    'qty'            => (int)   $item->qty,
                    'unit_price'     => (float) $item->unit_price,
                    'subtotal'       => (float) $item->subtotal,
                    'customization'  => $item->customization,
                    'custom_summary' => $item->custom_summary,
                ])
            ),

            // ── Timestamps ────────────────────────────────────────
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
