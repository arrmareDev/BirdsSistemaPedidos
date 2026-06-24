<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function __construct(private Product $model) {}

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->orderBy('sort_order')
            ->orderBy('name');

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['available'])) {
            $query->where('available', $filters['available']);
        }

        return $query->get();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
