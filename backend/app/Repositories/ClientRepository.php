<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository
{
    public function __construct(private Client $model) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->withCount('orders')
            ->withSum('orders', 'total')       // → orders_sum_total
            ->withMax('orders', 'created_at')  // → orders_max_created_at
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Client
    {
        return $this->model
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->withMax('orders', 'created_at')
            ->find($id);
    }

    public function findByPhone(string $phone): ?Client
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function firstOrCreate(string $phone, array $data): Client
    {
        return $this->model->firstOrCreate(
            ['phone' => $phone],
            $data
        );
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);
        return $client->fresh();
    }
}
