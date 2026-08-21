<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Cliente ───────────────────────────────────────────
            'client_name'  => 'required|string|max:150',
            'client_phone' => 'required|string|max:20',

            // ── Tipo de pedido ─────────────────────────────────────
            'type' => 'required|in:local,recoger,delivery',

            // ── Local ──────────────────────────────────────────────
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
            'metodo_pago' => 'nullable|in:anticipado',

            // ── Nota general ──────────────────────────────────────
            'note' => 'nullable|string|max:500',

            // ── Entrega programada / mensaje personalizado ─────────
            'mensaje_tarjeta'    => 'nullable|string|max:300',
            'fecha_entrega'      => 'nullable|date|after_or_equal:today',
            'hora_entrega'       => 'nullable|date_format:H:i',
            'entrega_programada' => 'boolean',

            // ── Total ─────────────────────────────────────────────
            'total' => 'required|numeric|min:0',

            // ── Items ─────────────────────────────────────────────
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            // unit_price ya no se usa para calcular el total (el precio
            // real se recalcula en el servidor desde el producto), pero
            // se mantiene el campo por compatibilidad con el payload que
            // ya arma el frontend.
            'items.*.unit_price'     => 'required|numeric|min:0',

            'items.*.customization'                    => 'nullable|array',
            'items.*.customization.*.section_id'       => 'required_with:items.*.customization|integer',
            'items.*.customization.*.selections'       => 'nullable|array',
            'items.*.customization.*.selections.*.option_id' => 'required|integer',

            'items.*.extras'              => 'nullable|array',
            'items.*.extras.*.extra_id'   => 'required|integer',
            // nullable: pedidos creados antes de este cambio no tienen
            // 'type' guardado en su extras[]; calcularPrecioItem() lo
            // asume 'own' cuando falta.
            'items.*.extras.*.type'       => 'nullable|in:own,shared',
            'items.*.extras.*.qty'        => 'nullable|integer|min:1',

            'items.*.custom_summary' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.required'         => 'El nombre del cliente es requerido',
            'client_phone.required'        => 'El teléfono es requerido',
            'type.required'                => 'El tipo de pedido es requerido',
            'type.in'                      => 'El tipo debe ser "local", "recoger" o "delivery"',
            'delivery_zone_id.exists'      => 'La zona de delivery seleccionada no existe',
            'items.required'               => 'El pedido debe tener al menos un producto',
            'items.min'                    => 'El pedido debe tener al menos un producto',
            'items.*.product_id.exists'    => 'Uno de los productos seleccionados no existe',
            'items.*.qty.min'              => 'La cantidad mínima por producto es 1',
            'fecha_entrega.after_or_equal' => 'La fecha de entrega no puede ser en el pasado',
            'hora_entrega.date_format'     => 'La hora debe tener formato HH:MM (ej: 14:30)',
        ];
    }
}
