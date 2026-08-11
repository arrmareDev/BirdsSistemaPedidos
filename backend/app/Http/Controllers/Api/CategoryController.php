<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // GET /api/v1/categories?parent_id=3 — público
    // Sin parent_id devuelve todas las categorías activas (raíces + subcategorías).
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('parent')
            ->where('active', true)
            ->orderBy('sort_order');

        $this->applyParentFilter($query, $request);

        return $this->success(CategoryResource::collection($query->get()));
    }

    // GET /api/v1/admin/categories?parent_id=3 — admin
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Category::with('parent')
            ->withCount('products')
            ->orderBy('sort_order');

        $this->applyParentFilter($query, $request);

        return $this->success(CategoryResource::collection($query->get()));
    }

    // POST /api/v1/admin/categories
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'parent_id'  => ['nullable', Rule::exists('categories', 'id')],
            'icon'       => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'active'     => 'nullable|boolean',
        ]);

        $this->assertParentIsRoot($data['parent_id'] ?? null);

        $data['slug'] = Str::slug($data['name']);

        $category = Category::create($data);

        return $this->created(new CategoryResource($category), 'Categoría creada');
    }

    // PUT /api/v1/admin/categories/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:100',
            'parent_id'  => ['nullable', Rule::exists('categories', 'id')],
            'icon'       => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'active'     => 'nullable|boolean',
        ]);

        if (array_key_exists('parent_id', $data)) {
            if ((int) $data['parent_id'] === $category->id) {
                return $this->error('Una categoría no puede ser su propia subcategoría', 422);
            }
            if ($category->children()->exists() && $data['parent_id']) {
                return $this->error('Esta categoría tiene subcategorías, no puede pasar a ser subcategoría de otra', 422);
            }
            $this->assertParentIsRoot($data['parent_id']);
        }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $this->success(new CategoryResource($category), 'Categoría actualizada');
    }

    // DELETE /api/v1/admin/categories/{id}
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            return $this->error(
                'No puedes eliminar una categoría con productos',
                422
            );
        }

        if ($category->children()->exists()) {
            return $this->error(
                'No puedes eliminar una categoría con subcategorías',
                422
            );
        }

        $category->delete();

        return $this->success(null, 'Categoría eliminada');
    }

    // ── Helpers ───────────────────────────────────────────

    private function applyParentFilter($query, Request $request): void
    {
        if (!$request->has('parent_id')) return;

        $value = $request->get('parent_id');

        // parent_id=null (o vacío) → solo categorías principales (raíz)
        if ($value === null || $value === '' || $value === 'null') {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $value);
        }
    }

    // Solo se permite jerarquía de 2 niveles: una subcategoría no puede
    // colgar de otra subcategoría, solo de una categoría raíz.
    private function assertParentIsRoot(?int $parentId): void
    {
        if (!$parentId) return;

        $parent = Category::find($parentId);

        if ($parent && !$parent->isRoot()) {
            abort(422, 'La categoría padre debe ser una categoría principal (no otra subcategoría)');
        }
    }
}
