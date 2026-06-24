<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // GET /api/v1/categories — público
    public function index(): JsonResponse
    {
        $categories = Category::where('active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    // GET /api/v1/admin/categories — admin
    public function adminIndex(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    // POST /api/v1/admin/categories
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'emoji'      => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
            'active'     => 'nullable|boolean',
        ]);

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
            'emoji'      => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
            'active'     => 'nullable|boolean',
        ]);

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

        $category->delete();

        return $this->success(null, 'Categoría eliminada');
    }
}
