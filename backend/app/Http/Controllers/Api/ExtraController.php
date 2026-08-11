<?php

namespace App\Http\Controllers\Api;

use App\Models\Extra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    // GET /api/v1/admin/extras
    public function index(Request $request): JsonResponse
    {
        $extras = Extra::orderBy('sort_order')->get();
        return $this->success($extras);
    }

    // POST /api/v1/admin/extras
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'price'      => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $extra = Extra::create($data);
        return $this->created($extra, 'Extra creado');
    }

    // PUT /api/v1/admin/extras/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $extra = Extra::findOrFail($id);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:100',
            'price'      => 'sometimes|numeric|min:0',
            'active'     => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $extra->update($data);
        return $this->success($extra, 'Extra actualizado');
    }

    // DELETE /api/v1/admin/extras/{id}
    public function destroy(int $id): JsonResponse
    {
        Extra::findOrFail($id)->delete();
        return $this->success(null, 'Extra eliminado');
    }
}
