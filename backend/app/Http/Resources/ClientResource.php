<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'district'     => $this->district,
            'preferences'  => $this->preferences ?? [],

            // Frontend usa orders_count
            'orders_count' => (int) ($this->orders_count ?? 0),

            // Frontend usa total_spent
            'total_spent'  => (float) ($this->orders_sum_total ?? 0),

            // Frontend usa last_order_at
            'last_order_at' => $this->orders_max_created_at,

            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
