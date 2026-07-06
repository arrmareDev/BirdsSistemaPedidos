<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCustomizationSection;
use App\Models\ProductCustomizationOption;
use App\Models\ProductExtra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private function withRelations(): array
    {
        return ['category', 'customizationSections.options', 'extras', 'extrasCompartidos'];
    }

    // GET /api/v1/products?linea=cafeteria — público
    public function index(Request $request): JsonResponse
    {
        $products = Product::with($this->withRelations())
            ->where('available', true)
            ->when(
                $request->get('category'),
                fn($q, $cat) => $q->whereHas(
                    'category',
                    fn($c) => $c->where('slug', $cat)
                )
            )
            ->when(
                $request->get('linea'),
                fn($q, $linea) => $q->whereHas(
                    'category',
                    fn($c) => $c->where('business_line', $linea)
                )
            )
            ->orderBy('id')
            ->get();

        return $this->success(ProductResource::collection($products));
    }

    // GET /api/v1/admin/products?linea=cafeteria — admin
    public function adminIndex(Request $request): JsonResponse
    {
        $products = Product::with($this->withRelations())
            ->when(
                $request->get('category_id'),
                fn($q, $id) => $q->where('category_id', $id)
            )
            ->when(
                $request->get('linea'),
                fn($q, $linea) => $q->whereHas(
                    'category',
                    fn($c) => $c->where('business_line', $linea)
                )
            )
            ->orderBy('id')
            ->get();

        return $this->success(ProductResource::collection($products));
    }

    // GET /api/v1/products/{id}
    public function show(int $id): JsonResponse
    {
        $product = Product::with($this->withRelations())->findOrFail($id);
        return $this->success(new ProductResource($product));
    }

    // POST /api/v1/admin/products
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'           => 'required|string|max:200',
            'category_id'    => 'nullable|exists:categories,id',
            'description'    => 'nullable|string',
            'emoji'          => 'nullable|string|max:10',
            'price'          => 'required|numeric|min:0',
            'ocasion'        => 'nullable|string|max:60',
            'color'          => 'nullable|string|max:40',
            'tamano'         => 'nullable|string|max:40',
            'stock'          => 'nullable|integer|min:0',
            'controla_stock' => 'nullable',
        ]);

        $available = $this->parseBool($request->input('available', '1'));
        $popular   = $this->parseBool($request->input('popular', '0'));
        $sections  = $this->parseJson($request->input('sections'));
        $extras    = $this->parseJson($request->input('extras'));
        $extraIds  = $this->parseJson($request->input('extra_ids'));

        return DB::transaction(function () use (
            $request,
            $available,
            $popular,
            $sections,
            $extras,
            $extraIds
        ) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')
                    ->store('products', 'public');
            }

            $product = Product::create([
                'category_id' => $request->input('category_id') ?: null,
                'name'        => $request->input('name'),
                'slug'        => Str::slug($request->input('name'))
                    . '-' . Str::random(4),
                'description' => $request->input('description'),
                'emoji'       => $request->input('emoji'),
                'image'       => $imagePath,
                'price'       => $request->input('price'),
                'popular'     => $popular,
                'available'   => $available,
                ...$this->floreriaAttributes($request),
            ]);

            $this->syncSections($product, $sections);
            $this->syncExtras($product, $extras);
            $this->syncExtrasCompartidos($product, $extraIds);

            $product->load($this->withRelations());
            return $this->created(new ProductResource($product), 'Producto creado');
        });
    }

    // PUT /api/v1/admin/products/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::with($this->withRelations())->findOrFail($id);

        $request->validate([
            'name'           => 'sometimes|string|max:200',
            'category_id'    => 'nullable|exists:categories,id',
            'description'    => 'nullable|string',
            'emoji'          => 'nullable|string|max:10',
            'price'          => 'sometimes|numeric|min:0',
            'ocasion'        => 'nullable|string|max:60',
            'color'          => 'nullable|string|max:40',
            'tamano'         => 'nullable|string|max:40',
            'stock'          => 'nullable|integer|min:0',
            'controla_stock' => 'nullable',
        ]);

        $available = $this->parseBool(
            $request->input('available', $product->available ? '1' : '0')
        );
        $popular = $this->parseBool(
            $request->input('popular', $product->popular ? '1' : '0')
        );
        $sections = $this->parseJson($request->input('sections'));
        $extras   = $this->parseJson($request->input('extras'));
        $extraIds = $this->parseJson($request->input('extra_ids'));

        return DB::transaction(function () use (
            $request,
            $product,
            $available,
            $popular,
            $sections,
            $extras,
            $extraIds
        ) {
            $imagePath = $product->image;
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $request->file('image')
                    ->store('products', 'public');
            }

            $product->update([
                'category_id' => $request->input('category_id')
                    ?: $product->category_id,
                'name'        => $request->input('name', $product->name),
                'description' => $request->input('description', $product->description),
                'emoji'       => $request->input('emoji', $product->emoji),
                'image'       => $imagePath,
                'price'       => $request->input('price', $product->price),
                'popular'     => $popular,
                'available'   => $available,
                ...$this->floreriaAttributes($request),
            ]);

            if ($request->has('sections')) {
                $this->syncSections($product, $sections);
            }
            if ($request->has('extras')) {
                $this->syncExtras($product, $extras);
            }
            if ($request->has('extra_ids')) {
                $this->syncExtrasCompartidos($product, $extraIds);
            }

            $product->load($this->withRelations());
            return $this->success(
                new ProductResource($product),
                'Producto actualizado'
            );
        });
    }

    // DELETE /api/v1/admin/products/{id}
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return $this->success(null, 'Producto eliminado');
    }

    // POST /api/v1/admin/products/{id}/toggle
    public function toggle(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['available' => !$product->available]);
        return $this->success([
            'id'        => $product->id,
            'available' => $product->available,
        ], 'Disponibilidad actualizada');
    }

    // ── Helpers ───────────────────────────────────────────

    /**
     * Atributos específicos de florería normalizados.
     * Strings vacíos → null; stock forzado a 0 si no se controla.
     */
    private function floreriaAttributes(Request $request): array
    {
        $controlaStock = $this->parseBool($request->input('controla_stock', '0'));

        return [
            'ocasion'        => $this->nullableString($request->input('ocasion')),
            'color'          => $this->nullableString($request->input('color')),
            'tamano'         => $this->nullableString($request->input('tamano')),
            'controla_stock' => $controlaStock,
            'stock'          => $controlaStock
                ? (int) $request->input('stock', 0)
                : 0,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return filled($value) ? $value : null;
    }

    private function parseBool(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on']);
    }

    private function parseJson(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function syncSections(Product $product, array $sections): void
    {
        $product->customizationSections()->each(function ($s) {
            $s->options()->delete();
            $s->delete();
        });

        foreach ($sections as $i => $sec) {
            if (empty($sec['seccion'])) continue;

            $section = ProductCustomizationSection::create([
                'product_id' => $product->id,
                'seccion'    => $sec['seccion'],
                'label'      => $sec['label']    ?? $sec['seccion'],
                'required'   => $sec['required'] ?? false,
                'multiple'   => $sec['multiple'] ?? false,
                'sort_order' => $sec['sort_order'] ?? $i,
            ]);

            foreach ($sec['options'] ?? [] as $j => $opt) {
                if (empty($opt['name'])) continue;
                ProductCustomizationOption::create([
                    'section_id'     => $section->id,
                    'name'           => $opt['name'],
                    'price_modifier' => $opt['price_modifier'] ?? 0,
                    'sort_order'     => $opt['sort_order'] ?? $j,
                ]);
            }
        }
    }

    private function syncExtras(Product $product, array $extras): void
    {
        $product->extras()->delete();

        foreach ($extras as $i => $extra) {
            if (empty($extra['name'])) continue;
            ProductExtra::create([
                'product_id' => $product->id,
                'name'       => $extra['name'],
                'price'      => $extra['price']     ?? 0,
                'sort_order' => $extra['sort_order'] ?? $i,
            ]);
        }
    }

    /**
     * Sincroniza extras compartidos/reutilizables (tabla Extra + pivote extra_product).
     * Se usan para cafetería/menú donde un mismo extra (ej: "leche de almendra")
     * aplica a múltiples productos a la vez.
     *
     * $extraIds es un array simple de IDs: [1, 3, 7]
     */
    private function syncExtrasCompartidos(Product $product, array $extraIds): void
    {
        $product->extrasCompartidos()->sync($extraIds);
    }
}
