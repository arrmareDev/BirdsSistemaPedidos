<?php

namespace App\Http\Controllers\Api;

use App\Models\SeccionTipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeccionTipoController extends Controller
{
    // GET /admin/seccion-tipos — panel admin, incluye inactivos
    public function adminIndex(): JsonResponse
    {
        $tipos = SeccionTipo::orderBy('sort_order')->get();
        return $this->success($tipos);
    }

    // POST /admin/seccion-tipos
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:40',
            'icono'  => 'sometimes|string|max:40',
        ]);

        $maxOrden = SeccionTipo::max('sort_order') ?? -1;

        $tipo = SeccionTipo::create([
            'nombre'     => $data['nombre'],
            'icono'      => $data['icono'] ?? 'sparkles',
            'activo'     => true,
            'sort_order' => $maxOrden + 1,
        ]);

        return $this->created($tipo, 'Tipo de sección creado');
    }

    // PUT /admin/seccion-tipos/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $tipo = SeccionTipo::find($id);
        if (!$tipo) return $this->notFound('Tipo de sección no encontrado');

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:40',
            'icono'  => 'sometimes|string|max:40',
            'activo' => 'sometimes|boolean',
        ]);

        $tipo->update($data);

        return $this->success($tipo, 'Tipo de sección actualizado');
    }

    // DELETE /admin/seccion-tipos/{id}
    public function destroy(int $id): JsonResponse
    {
        $tipo = SeccionTipo::find($id);
        if (!$tipo) return $this->notFound('Tipo de sección no encontrado');

        // No se borra si algún producto ya tiene una sección de este tipo
        // (por nombre, ya que ProductCustomizationSection guarda el label
        // libremente, no una relación estricta) — evita etiquetas huérfanas.
        $enUso = \App\Models\ProductCustomizationSection::where('seccion', $tipo->nombre)->exists();
        if ($enUso) {
            return $this->error('No se puede eliminar: hay productos usando este tipo de sección', 422);
        }

        $tipo->delete();

        return $this->success(null, 'Tipo de sección eliminado');
    }

    // POST /admin/seccion-tipos/reorder — body: { orden: [3,1,2] }
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orden'   => 'required|array',
            'orden.*' => 'integer|exists:seccion_tipos,id',
        ]);

        foreach ($data['orden'] as $index => $id) {
            SeccionTipo::where('id', $id)->update(['sort_order' => $index]);
        }

        return $this->success(null, 'Orden actualizado');
    }
}
