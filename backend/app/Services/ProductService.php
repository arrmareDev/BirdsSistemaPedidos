<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->productRepository->all($filters);
    }

    public function getAvailable(string $category = null): Collection
    {
        $filters = ['available' => true];
        if ($category) $filters['category'] = $category;
        return $this->productRepository->all($filters);
    }

    public function findOrFail(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Producto #{$id} no encontrado"
            );
        }

        return $product;
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->productRepository->update($product, $data);
    }

    public function delete(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }
}
