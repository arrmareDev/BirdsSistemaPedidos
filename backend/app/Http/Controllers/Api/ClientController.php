<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService
    ) {}

    /**
     * GET /api/v1/admin/clients
     */
    public function index(Request $request): JsonResponse
    {
        $clients = $this->clientService->paginate(
            $request->integer('per_page', 20)
        );

        return $this->success(
            ClientResource::collection($clients)->response()->getData(true)
        );
    }

    /**
     * GET /api/v1/admin/clients/{id}
     */
    public function show(int $id): JsonResponse
    {
        $client = $this->clientService->findOrFail($id);
        return $this->success(new ClientResource($client));
    }

    /**
     * GET /api/v1/clients/preferences?phone=987654321
     */
    public function preferences(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $preferences = $this->clientService->getPreferencesByPhone(
            $request->get('phone')
        );

        return $this->success($preferences ?? []);
    }
}
