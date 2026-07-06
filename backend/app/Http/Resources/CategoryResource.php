<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'business_line' => $this->business_line?->value,
            'emoji'         => $this->emoji,
            'sort_order'    => $this->sort_order,
            'active'        => $this->active,
        ];
    }
}
