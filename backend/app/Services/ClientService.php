<?php

namespace App\Services;

use App\Models\Client;
use App\Repositories\ClientRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientService
{
    public function __construct(
        private ClientRepository $clientRepository
    ) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->clientRepository->paginate($perPage);
    }

    public function findOrFail(int $id): Client
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Cliente #{$id} no encontrado"
            );
        }

        return $client;
    }

    public function getPreferencesByPhone(string $phone): ?array
    {
        $client = $this->clientRepository->findByPhone($phone);
        return $client?->preferences;
    }
}
