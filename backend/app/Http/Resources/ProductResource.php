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
            'icon'        => $this->icon,
            'image_url'   => $this->image_url,
            // Galería de fotos generales (además de la imagen principal)
            'images' => $this->whenLoaded(
                'images',
                fn() => $this->images->map(fn($img) => [
                    'id'         => $img->id,
                    'image_url'  => $img->image_url,
                    'sort_order' => $img->sort_order,
                ])
            ),
            'price'       => (float) $this->price,
            'popular'     => $this->popular,
            'available'   => $this->available,
            // Atributos configurables (etiqueta editable desde el panel:
            // "Ocasión"/"Color"/"Tamaño" para Birds, lo que sea para otro rubro)
            'atributo_1'  => $this->atributo_1,
            'atributo_2'  => $this->atributo_2,
            'atributo_3'  => $this->atributo_3,
            'category'    => $this->whenLoaded('category', fn() => [
                'id'        => $this->category->id,
                'name'      => $this->category->name,
                'slug'      => $this->category->slug,
                'parent_id' => $this->category->parent_id,
                'root_slug' => $this->category->root()?->slug,
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
                        'image_url'      => $o->image_url,
                    ]),
                ])
            ),
            // Extras únicos por producto
            'extras' => $this->whenLoaded(
                'extras',
                fn() => $this->extras->map(fn($e) => [
                    'id'    => $e->id,
                    'name'  => $e->name,
                    'price' => (float) $e->price,
                ])
            ),
            // Extras compartidos/reutilizables (aplican a varios productos)
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
