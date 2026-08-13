<?php

namespace App\Http\Controllers\Api;

use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProveedorController extends Controller
{
    // POST /admin/proveedores/{id}/clic — cualquier rol logueado, registra
    // que alguien del equipo abrió la página del proveedor desde el panel.
    public function registrarClic(int $id): JsonResponse
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) return $this->notFound();

        $proveedor->increment('clics');

        return $this->success(null);
    }

    // GET /admin/proveedores — solo rol sistema
    public function adminIndex(): JsonResponse
    {
        $proveedores = Proveedor::orderBy('sort_order')->orderBy('nombre')->get();
        return $this->success($proveedores);
    }

    // POST /admin/proveedores
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:150',
            'descripcion'     => 'nullable|string|max:500',
            'categoria'       => 'nullable|string|max:80',
            'url_externa'     => 'required|url|max:255',
            'descuento_texto' => 'nullable|string|max:150',
            'activo'          => 'sometimes|boolean',
            'logo'            => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('proveedores', 'public');
        }

        $proveedor = Proveedor::create($data);

        return $this->created($proveedor);
    }

    // PUT /admin/proveedores/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) return $this->notFound();

        $data = $request->validate([
            'nombre'          => 'sometimes|string|max:150',
            'descripcion'     => 'nullable|string|max:500',
            'categoria'       => 'nullable|string|max:80',
            'url_externa'     => 'sometimes|url|max:255',
            'descuento_texto' => 'nullable|string|max:150',
            'activo'          => 'sometimes|boolean',
            'logo'            => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($proveedor->logo) Storage::disk('public')->delete($proveedor->logo);
            $data['logo'] = $request->file('logo')->store('proveedores', 'public');
        }

        $proveedor->update($data);

        return $this->success($proveedor, 'Proveedor actualizado');
    }

    // POST /admin/proveedores/reorder
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:proveedores,id',
        ]);

        foreach ($data['ids'] as $index => $id) {
            Proveedor::where('id', $id)->update(['sort_order' => $index]);
        }

        return $this->success(null, 'Orden actualizado');
    }

    // DELETE /admin/proveedores/{id}
    public function destroy(int $id): JsonResponse
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) return $this->notFound();

        if ($proveedor->logo) Storage::disk('public')->delete($proveedor->logo);
        $proveedor->delete();

        return $this->success(null, 'Proveedor eliminado');
    }
}
