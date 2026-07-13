<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::with(['items.product'])->latest();
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    // ── NUEVO — lista solo los eliminados ──────────────────────
    public function paginateTrashed(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::onlyTrashed()->with(['items.product'])->latest('deleted_at');
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['items.product'])->find($id);
    }

    // ── NUEVO — busca incluso entre los eliminados (para restore) ──
    public function findTrashedById(int $id): ?Order
    {
        return Order::onlyTrashed()->with(['items.product'])->find($id);
    }

    // ── Filtros compartidos entre paginate() y paginateTrashed() ──
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $statuses = explode(',', $filters['status']);
            $query->whereIn('status', $statuses);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('client_name',  'ilike', "%{$search}%")
                    ->orWhere('client_phone', 'ilike', "%{$search}%")
                    ->orWhere('id', (int) ltrim($search, '#') ?: 0);
            });
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
