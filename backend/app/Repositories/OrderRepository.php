<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::with(['items.product'])->latest();

        // Filtro por estado — acepta múltiples separados por coma
        if (!empty($filters['status'])) {
            $statuses = explode(',', $filters['status']);
            $query->whereIn('status', $statuses);
        }

        // Filtro por búsqueda
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('client_name',  'ilike', "%{$search}%")
                    ->orWhere('client_phone', 'ilike', "%{$search}%")
                    ->orWhere('id', (int) ltrim($search, '#') ?: 0);
            });
        }

        // Filtro por fecha exacta
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        // Filtro por rango de fechas
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['items.product'])->find($id);
    }
}
