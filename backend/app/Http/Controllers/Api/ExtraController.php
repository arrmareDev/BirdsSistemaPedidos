<?php

namespace App\Http\Controllers\Api;

use App\Enums\BusinessLine;
use App\Models\Extra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraController extends Controller
{
    // GET /api/v1/admin/extras?linea=cafeteria
    public function index(Request $request): JsonResponse
    {
        $query = Extra::orderBy('sort_order');

        if ($linea = $request->get('linea')) {
            $query->where('business_line', $linea);
        }

        return $this->success($query->get());
    }

    // POST /api/v1/admin/extras
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'business_line' => ['required', Rule::enum(BusinessLine::class)],
            'sort_order'    => 'nullable|integer',
        ]);

        $extra = Extra::create($data);
        return $this->created($extra, 'Extra creado');
    }

    // PUT /api/v1/admin/extras/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $extra = Extra::findOrFail($id);

        $data = $request->validate([
            'name'          => 'sometimes|string|max:100',
            'price'         => 'sometimes|numeric|min:0',
            'business_line' => ['sometimes', Rule::enum(BusinessLine::class)],
            'active'        => 'nullable|boolean',
            'sort_order'    => 'nullable|integer',
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
