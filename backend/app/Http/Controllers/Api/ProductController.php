<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCustomizationSection;
use App\Models\ProductCustomizationOption;
use App\Models\ProductExtra;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private function withRelations(): array
    {
        return ['category', 'customizationSections.options', 'extras', 'extrasCompartidos', 'images'];
    }

    // GET /api/v1/products?category=bebidas-calientes&grupo=bebidas&page=2 — público
    // "category" filtra por el slug exacto de la categoría/subcategoría.
    // "grupo" filtra por la categoría principal (incluye sus subcategorías).
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
                $request->get('grupo'),
                fn($q, $grupo) => $this->filterByGrupo($q, $grupo)
            )
            ->when(
                $request->get('q'),
                fn($q, $term) => $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'ilike', "%{$term}%")
                        ->orWhere('description', 'ilike', "%{$term}%");
                })
            )
            ->orderBy('id')
            ->paginate($request->integer('per_page', 24));

        return $this->success(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    // GET /api/v1/admin/products?category_id=3&grupo=bebidas&q=rosa&page=2 — admin
    public function adminIndex(Request $request): JsonResponse
    {
        $products = Product::with($this->withRelations())
            ->when(
                $request->get('category_id'),
                fn($q, $id) => $q->where('category_id', $id)
            )
            ->when(
                $request->get('grupo'),
                fn($q, $grupo) => $this->filterByGrupo($q, $grupo)
            )
            ->when(
                $request->get('q'),
                fn($q, $term) => $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'ilike', "%{$term}%")
                        ->orWhere('description', 'ilike', "%{$term}%");
                })
            )
            ->orderBy('id')
            ->paginate($request->integer('per_page', 30));

        return $this->success(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    // Filtra productos cuya categoría (o cuya categoría padre) tenga ese slug.
    private function filterByGrupo($query, string $grupo)
    {
        return $query->whereHas('category', function ($c) use ($grupo) {
            $c->where('slug', $grupo)
                ->orWhereHas('parent', fn($p) => $p->where('slug', $grupo));
        });
    }

    // GET /api/v1/products/{slug} — búsqueda directa por slug, no depende
    // de tener el catálogo completo cargado (antes no existía este
    // endpoint: la ficha de producto "buscaba" dentro de la lista ya
    // cargada, lo cual se rompe en cuanto el catálogo se pagina).
    public function show(string $slug): JsonResponse
    {
        $product = Product::with($this->withRelations())
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success(new ProductResource($product));
    }

    // POST /api/v1/admin/products
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'           => 'required|string|max:200',
            'category_id'    => 'nullable|exists:categories,id',
            'description'    => 'nullable|string',
            'icon'           => 'nullable|string|max:50',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'nullable|integer|min:0',
            'stock_minimo'   => 'nullable|integer|min:0',
            'controla_stock' => 'nullable',
            ...$this->descuentoRules($request),
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
                'icon'        => $request->input('icon'),
                'image'       => $imagePath,
                'price'       => $request->input('price'),
                'popular'     => $popular,
                'available'   => $available,
                ...$this->catalogAttributes($request),
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
            'icon'           => 'nullable|string|max:50',
            'price'          => 'sometimes|numeric|min:0',
            'stock'          => 'nullable|integer|min:0',
            'stock_minimo'   => 'nullable|integer|min:0',
            'controla_stock' => 'nullable',
            ...$this->descuentoRules($request),
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
                'icon'        => $request->input('icon', $product->icon),
                'image'       => $imagePath,
                'price'       => $request->input('price', $product->price),
                'popular'     => $popular,
                'available'   => $available,
                ...$this->catalogAttributes($request),
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

    // POST /api/v1/admin/products/{productId}/options/{optionId}/image
    // Sube (o reemplaza) la foto de una opción de personalización puntual
    // (ej: la foto de "Rojo" dentro de la sección "Color"). Es un endpoint
    // aparte del guardado del producto para no tener que reenviar el
    // catálogo completo de secciones solo para cambiar una foto.
    public function uploadOptionImage(Request $request, int $productId, int $optionId): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $option = ProductCustomizationOption::whereHas(
            'section',
            fn($q) => $q->where('product_id', $productId)
        )->findOrFail($optionId);

        if ($option->image) {
            Storage::disk('public')->delete($option->image);
        }

        $path = $request->file('image')->store('customization-options', 'public');
        $option->update(['image' => $path]);

        return $this->success([
            'id'        => $option->id,
            'image_url' => $option->image_url,
        ], 'Imagen actualizada');
    }

    // DELETE /api/v1/admin/products/{productId}/options/{optionId}/image
    public function deleteOptionImage(int $productId, int $optionId): JsonResponse
    {
        $option = ProductCustomizationOption::whereHas(
            'section',
            fn($q) => $q->where('product_id', $productId)
        )->findOrFail($optionId);

        if ($option->image) {
            Storage::disk('public')->delete($option->image);
            $option->update(['image' => null]);
        }

        return $this->success(null, 'Imagen eliminada');
    }

    // POST /api/v1/admin/products/{id}/images — sube una o varias fotos
    // generales a la galería del producto (además de la imagen principal)
    public function uploadImages(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $product = Product::findOrFail($id);
        $maxOrden = $product->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $i => $file) {
            $path = $file->store('products/gallery', 'public');
            $product->images()->create([
                'image'      => $path,
                'sort_order' => $maxOrden + 1 + $i,
            ]);
        }

        return $this->success(
            $product->images()->get()->map(fn($img) => [
                'id' => $img->id,
                'image_url' => $img->image_url,
                'sort_order' => $img->sort_order,
            ]),
            'Fotos agregadas'
        );
    }

    // DELETE /api/v1/admin/products/{productId}/images/{imageId}
    public function deleteImage(int $productId, int $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);
        if ($image->image) Storage::disk('public')->delete($image->image);
        $image->delete();

        return $this->success(null, 'Foto eliminada');
    }

    // POST /api/v1/admin/products/{id}/images/reorder — body: { ids: [3,1,2] }
    public function reorderImages(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        foreach ($data['ids'] as $orden => $imageId) {
            ProductImage::where('product_id', $id)->where('id', $imageId)
                ->update(['sort_order' => $orden]);
        }

        return $this->success(null, 'Orden actualizado');
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
     * Strings vacíos → null; stock forzado a 0 si no se controla.
     */
    private function catalogAttributes(Request $request): array
    {
        $controlaStock = $this->parseBool($request->input('controla_stock', '0'));

        // Si mandan descuento_tipo vacío/null, se apaga el descuento
        // por completo (los otros 3 campos quedan en null con él).
        $tieneDescuento = $this->nullableString($request->input('descuento_tipo'));

        return [
            'controla_stock' => $controlaStock,
            'stock'          => $controlaStock
                ? (int) $request->input('stock', 0)
                : 0,
            'stock_minimo'   => $controlaStock
                ? $this->nullableString($request->input('stock_minimo'))
                : null,
            'descuento_tipo'    => $tieneDescuento,
            'descuento_valor'   => $tieneDescuento ? $request->input('descuento_valor') : null,
            'descuento_desde'   => $tieneDescuento ? $this->nullableString($request->input('descuento_desde')) : null,
            'descuento_hasta'   => $tieneDescuento ? $this->nullableString($request->input('descuento_hasta')) : null,
        ];
    }

    // Reglas de validación del descuento — compartidas entre store()
    // y update() para no repetirlas. La comprobación de que el
    // porcentaje no pase de 100 va en un closure porque solo aplica
    // cuando descuento_tipo es 'porcentaje', no en monto_fijo.
    private function descuentoRules(Request $request): array
    {
        return [
            'descuento_tipo'  => 'nullable|in:porcentaje,monto_fijo',
            'descuento_valor' => [
                'nullable',
                'numeric',
                'min:0.01',
                'required_with:descuento_tipo',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('descuento_tipo') === 'porcentaje' && $value > 100) {
                        $fail('El descuento en porcentaje no puede ser mayor a 100.');
                    }
                },
            ],
            'descuento_desde' => 'nullable|date',
            'descuento_hasta' => 'nullable|date|after_or_equal:descuento_desde',
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

    /**
     * Sincroniza secciones y opciones de personalización actualizando por
     * ID en vez de borrar-y-recrear — así una opción que ya tiene una foto
     * subida no la pierde solo porque el admin guardó otro cambio del
     * producto (precio, nombre, etc.).
     */
    private function syncSections(Product $product, array $sections): void
    {
        $sectionIdsVistos = [];

        foreach ($sections as $i => $sec) {
            if (empty($sec['seccion'])) continue;

            $sectionData = [
                'seccion'    => $sec['seccion'],
                'label'      => $sec['label']    ?? $sec['seccion'],
                'required'   => $sec['required'] ?? false,
                'multiple'   => $sec['multiple'] ?? false,
                'sort_order' => $sec['sort_order'] ?? $i,
            ];

            $section = !empty($sec['id'])
                ? $product->customizationSections()->find($sec['id'])
                : null;

            if ($section) {
                $section->update($sectionData);
            } else {
                $section = $product->customizationSections()->create($sectionData);
            }

            $sectionIdsVistos[] = $section->id;

            $optionIdsVistos = [];

            foreach ($sec['options'] ?? [] as $j => $opt) {
                if (empty($opt['name'])) continue;

                $optionData = [
                    'name'           => $opt['name'],
                    'price_modifier' => $opt['price_modifier'] ?? 0,
                    'sort_order'     => $opt['sort_order'] ?? $j,
                ];

                $option = !empty($opt['id'])
                    ? $section->options()->find($opt['id'])
                    : null;

                if ($option) {
                    $option->update($optionData);
                } else {
                    $option = $section->options()->create($optionData);
                }

                $optionIdsVistos[] = $option->id;
            }

            // Borrar solo las opciones que ya no vienen en el payload
            // (borra también su imagen del disco, si tenía)
            $section->options()->whereNotIn('id', $optionIdsVistos ?: [0])
                ->get()
                ->each(function ($opt) {
                    if ($opt->image) Storage::disk('public')->delete($opt->image);
                    $opt->delete();
                });
        }

        // Borrar solo las secciones que ya no vienen en el payload
        $product->customizationSections()
            ->whereNotIn('id', $sectionIdsVistos ?: [0])
            ->each(function ($s) {
                $s->options()->get()->each(function ($opt) {
                    if ($opt->image) Storage::disk('public')->delete($opt->image);
                });
                $s->options()->delete();
                $s->delete();
            });
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
     * Se usan cuando un mismo extra (ej: "leche de almendra") aplica a
     * múltiples productos a la vez.
     *
     * $extraIds es un array simple de IDs: [1, 3, 7]
     */
    private function syncExtrasCompartidos(Product $product, array $extraIds): void
    {
        $product->extrasCompartidos()->sync($extraIds);
    }
}
