<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Datos base ──
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'emoji'       => 'nullable|string|max:16',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,id',
            'image'       => 'nullable|image|max:2048',

            // ── Flags ──
            'available'   => 'nullable|boolean',
            'popular'     => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',

            // ── Atributos florería ──
            'ocasion'        => 'nullable|string|max:60',
            'color'          => 'nullable|string|max:40',
            'tamano'         => 'nullable|string|max:40',
            'controla_stock' => 'nullable|boolean',
            'stock'          => 'nullable|integer|min:0',

            // ── JSON strings desde FormData ──
            'sections' => 'nullable|string',
            'extras'   => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'available'      => filter_var($this->available, FILTER_VALIDATE_BOOLEAN),
            'popular'        => filter_var($this->popular,   FILTER_VALIDATE_BOOLEAN),
            'controla_stock' => filter_var($this->controla_stock, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
