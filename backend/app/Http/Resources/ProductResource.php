<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'emoji'       => $this->emoji,
            'image_url'   => $this->image_url,
            'price'       => (float) $this->price,
            'popular'     => $this->popular,
            'available'   => $this->available,
            'category'    => $this->whenLoaded('category', fn() => [
                'id'            => $this->category->id,
                'name'          => $this->category->name,
                'slug'          => $this->category->slug,
                'business_line' => $this->category->business_line?->value,
            ]),
            // Secciones de personalización (con modificador de precio, ej: tamaños)
            'customization_sections' => $this->whenLoaded(
                'customizationSections',
                fn() => $this->customizationSections->map(fn($s) => [
                    'id'       => $s->id,
                    'seccion'  => $s->seccion,
                    'label'    => $s->label,
                    'required' => $s->required,
                    'multiple' => $s->multiple,
                    'options'  => $s->options->map(fn($o) => [
                        'id'             => $o->id,
                        'name'           => $o->name,
                        'price_modifier' => (float) $o->price_modifier,
                    ]),
                ])
            ),
            // Extras únicos por producto (florería)
            'extras' => $this->whenLoaded(
                'extras',
                fn() => $this->extras->map(fn($e) => [
                    'id'    => $e->id,
                    'name'  => $e->name,
                    'price' => (float) $e->price,
                ])
            ),
            // Extras compartidos/reutilizables (cafetería, menú)
            'extras_compartidos' => $this->whenLoaded(
                'extrasCompartidos',
                fn() => $this->extrasCompartidos->map(fn($e) => [
                    'id'    => $e->id,
                    'name'  => $e->name,
                    'price' => (float) $e->price,
                ])
            ),
        ];
    }
}
