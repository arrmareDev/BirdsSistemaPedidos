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
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            // Secciones de personalización (sin precio)
            'customization_sections' => $this->whenLoaded(
                'customizationSections',
                fn() => $this->customizationSections->map(fn($s) => [
                    'id'       => $s->id,
                    'seccion'  => $s->seccion,
                    'label'    => $s->label,
                    'required' => $s->required,
                    'multiple' => $s->multiple,
                    'options'  => $s->options->map(fn($o) => [
                        'id'   => $o->id,
                        'name' => $o->name,
                    ]),
                ])
            ),
            // Extras con precio
            'extras' => $this->whenLoaded(
                'extras',
                fn() => $this->extras->map(fn($e) => [
                    'id'    => $e->id,
                    'name'  => $e->name,
                    'price' => (float) $e->price,
                ])
            ),
        ];
    }
}
