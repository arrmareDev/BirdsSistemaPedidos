<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'parent_id'  => $this->parent_id,
            'parent'     => $this->whenLoaded('parent', fn() => $this->parent ? [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
                'slug' => $this->parent->slug,
            ] : null),
            'icon'       => $this->icon,
            'sort_order' => $this->sort_order,
            'active'     => $this->active,
            'products_count' => $this->when(
                isset($this->products_count),
                fn() => $this->products_count
            ),
        ];
    }
}
