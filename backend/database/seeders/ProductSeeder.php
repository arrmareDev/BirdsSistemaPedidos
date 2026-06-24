<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductExtra;
use App\Models\ProductCustomizationSection;
use App\Models\ProductCustomizationOption;

class ProductSeeder extends Seeder
{
    // ── Opciones globales de personalización ──────────────
    private array $ENVOLTURAS = [
        'Papel kraft',
        'Papel coreano',
        'Tela de yute',
        'Celofán transparente',
        'Sin envoltura',
    ];

    private array $LAZOS = [
        'Lazo de seda rojo',
        'Lazo de seda blanco',
        'Cinta dorada',
        'Cinta rústica',
        'Sin lazo',
    ];

    private array $FOLLAJES = [
        'Eucalipto',
        'Ruscus',
        'Gypsophila (nube)',
        'Helecho',
        'Sin follaje extra',
    ];

    private array $TAMANOS = [
        'Pequeño',
        'Mediano',
        'Grande',
        'Premium',
    ];

    private array $PRESENTACIONES = [
        'Ramo de mano',
        'Caja sombrero',
        'Canasta',
        'Florero de vidrio',
    ];

    // ── Extras comunes ────────────────────────────────────
    private array $EXTRAS_DETALLE = [
        ['name' => 'Caja de chocolates', 'price' => 25.00],
        ['name' => 'Peluche pequeño',    'price' => 20.00],
        ['name' => 'Globo metálico',     'price' => 12.00],
    ];

    private array $EXTRAS_TARJETA = [
        ['name' => 'Tarjeta dedicatoria premium', 'price' => 8.00],
        ['name' => 'Caja de chocolates',          'price' => 25.00],
    ];

    public function run(): void
    {
        // ── CATEGORÍAS ────────────────────────────────────
        $cats = [
            ['name' => 'Ramos',    'slug' => 'ramos',    'emoji' => '💐', 'sort_order' => 1, 'active' => true],
            ['name' => 'Arreglos', 'slug' => 'arreglos', 'emoji' => '🌸', 'sort_order' => 2, 'active' => true],
            ['name' => 'Plantas',  'slug' => 'plantas',  'emoji' => '🪴', 'sort_order' => 3, 'active' => true],
            ['name' => 'Coronas',  'slug' => 'coronas',  'emoji' => '🌿', 'sort_order' => 4, 'active' => true],
            ['name' => 'Regalos',  'slug' => 'regalos',  'emoji' => '🎁', 'sort_order' => 5, 'active' => true],
            ['name' => 'Globos',   'slug' => 'globos',   'emoji' => '🎈', 'sort_order' => 6, 'active' => true],
        ];

        foreach ($cats as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $ramos    = Category::where('slug', 'ramos')->first();
        $arreglos = Category::where('slug', 'arreglos')->first();
        $plantas  = Category::where('slug', 'plantas')->first();
        $coronas  = Category::where('slug', 'coronas')->first();
        $regalos  = Category::where('slug', 'regalos')->first();
        $globos   = Category::where('slug', 'globos')->first();

        // ── PRODUCTOS ─────────────────────────────────────
        $products = [

            // ══ RAMOS ═════════════════════════════════════
            [
                'category_id'    => $ramos->id,
                'name'           => 'Ramo de 12 Rosas Rojas',
                'description'    => 'Clásico ramo de una docena de rosas rojas frescas, símbolo del amor verdadero.',
                'emoji'          => '🌹',
                'price'          => 89.00,
                'popular'        => true,
                'available'      => true,
                'ocasion'        => 'Amor',
                'color'          => 'Rojo',
                'tamano'         => 'Mediano',
                'controla_stock' => true,
                'stock'          => 15,
                'sections'       => [
                    ['seccion' => 'envoltura',   'label' => 'Tipo de envoltura', 'required' => true,  'multiple' => false, 'sort_order' => 1, 'options' => $this->ENVOLTURAS],
                    ['seccion' => 'lazo',        'label' => 'Color del lazo',    'required' => false, 'multiple' => false, 'sort_order' => 2, 'options' => $this->LAZOS],
                    ['seccion' => 'follaje',     'label' => 'Follaje adicional', 'required' => false, 'multiple' => true,  'sort_order' => 3, 'options' => $this->FOLLAJES],
                ],
                'extras' => $this->EXTRAS_DETALLE,
            ],
            [
                'category_id'    => $ramos->id,
                'name'           => 'Ramo de Girasoles',
                'description'    => 'Ramo radiante de girasoles frescos que transmite alegría y energía positiva.',
                'emoji'          => '🌻',
                'price'          => 65.00,
                'popular'        => true,
                'available'      => true,
                'ocasion'        => 'Cumpleaños',
                'color'          => 'Amarillo',
                'tamano'         => 'Mediano',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [
                    ['seccion' => 'envoltura', 'label' => 'Tipo de envoltura', 'required' => true,  'multiple' => false, 'sort_order' => 1, 'options' => $this->ENVOLTURAS],
                    ['seccion' => 'lazo',      'label' => 'Color del lazo',    'required' => false, 'multiple' => false, 'sort_order' => 2, 'options' => $this->LAZOS],
                ],
                'extras' => $this->EXTRAS_DETALLE,
            ],
            [
                'category_id'    => $ramos->id,
                'name'           => 'Ramo Mixto de Temporada',
                'description'    => 'Combinación de flores frescas de temporada en tonos pastel. Diseño único cada día.',
                'emoji'          => '💐',
                'price'          => 75.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Agradecimiento',
                'color'          => 'Multicolor',
                'tamano'         => 'Grande',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [
                    ['seccion' => 'envoltura', 'label' => 'Tipo de envoltura', 'required' => true,  'multiple' => false, 'sort_order' => 1, 'options' => $this->ENVOLTURAS],
                    ['seccion' => 'lazo',      'label' => 'Color del lazo',    'required' => false, 'multiple' => false, 'sort_order' => 2, 'options' => $this->LAZOS],
                    ['seccion' => 'follaje',   'label' => 'Follaje adicional', 'required' => false, 'multiple' => true,  'sort_order' => 3, 'options' => $this->FOLLAJES],
                ],
                'extras' => $this->EXTRAS_TARJETA,
            ],

            // ══ ARREGLOS ══════════════════════════════════
            [
                'category_id'    => $arreglos->id,
                'name'           => 'Arreglo en Caja Sombrero',
                'description'    => 'Rosas y flores de temporada en elegante caja sombrero. Incluye tarjeta dedicatoria.',
                'emoji'          => '🌸',
                'price'          => 129.00,
                'popular'        => true,
                'available'      => true,
                'ocasion'        => 'Aniversario',
                'color'          => 'Rosa',
                'tamano'         => 'Premium',
                'controla_stock' => true,
                'stock'          => 8,
                'sections'       => [
                    ['seccion' => 'presentacion', 'label' => 'Presentación',     'required' => true,  'multiple' => false, 'sort_order' => 1, 'options' => $this->PRESENTACIONES],
                    ['seccion' => 'follaje',      'label' => 'Follaje adicional', 'required' => false, 'multiple' => true,  'sort_order' => 2, 'options' => $this->FOLLAJES],
                    ['seccion' => 'dedicatoria',  'label' => 'Dedicatoria',       'required' => false, 'multiple' => false, 'sort_order' => 3, 'options' => ['Con tarjeta', 'Sin tarjeta']],
                ],
                'extras' => $this->EXTRAS_DETALLE,
            ],
            [
                'category_id'    => $arreglos->id,
                'name'           => 'Arreglo de Tulipanes',
                'description'    => 'Delicado arreglo de tulipanes importados en florero de vidrio.',
                'emoji'          => '🌷',
                'price'          => 95.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Felicitaciones',
                'color'          => 'Lila',
                'tamano'         => 'Mediano',
                'controla_stock' => true,
                'stock'          => 5,
                'sections'       => [
                    ['seccion' => 'presentacion', 'label' => 'Presentación', 'required' => true, 'multiple' => false, 'sort_order' => 1, 'options' => $this->PRESENTACIONES],
                    ['seccion' => 'lazo',         'label' => 'Color del lazo', 'required' => false, 'multiple' => false, 'sort_order' => 2, 'options' => $this->LAZOS],
                ],
                'extras' => $this->EXTRAS_TARJETA,
            ],
            [
                'category_id'    => $arreglos->id,
                'name'           => 'Canasta Floral Primaveral',
                'description'    => 'Abundante canasta con variedad de flores frescas en tonos vivos.',
                'emoji'          => '🧺',
                'price'          => 145.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Recuperación',
                'color'          => 'Multicolor',
                'tamano'         => 'Grande',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [
                    ['seccion' => 'presentacion', 'label' => 'Presentación',     'required' => true,  'multiple' => false, 'sort_order' => 1, 'options' => $this->PRESENTACIONES],
                    ['seccion' => 'follaje',      'label' => 'Follaje adicional', 'required' => false, 'multiple' => true,  'sort_order' => 2, 'options' => $this->FOLLAJES],
                ],
                'extras' => $this->EXTRAS_DETALLE,
            ],

            // ══ PLANTAS ═══════════════════════════════════
            [
                'category_id'    => $plantas->id,
                'name'           => 'Orquídea Phalaenopsis',
                'description'    => 'Elegante orquídea en maceta decorativa. Larga duración y fácil cuidado.',
                'emoji'          => '🪴',
                'price'          => 110.00,
                'popular'        => true,
                'available'      => true,
                'ocasion'        => 'Felicitaciones',
                'color'          => 'Blanco',
                'tamano'         => 'Mediano',
                'controla_stock' => true,
                'stock'          => 6,
                'sections'       => [
                    ['seccion' => 'presentacion', 'label' => 'Tipo de maceta', 'required' => false, 'multiple' => false, 'sort_order' => 1, 'options' => ['Cerámica blanca', 'Cerámica negra', 'Maceta rústica']],
                    ['seccion' => 'dedicatoria',  'label' => 'Dedicatoria',    'required' => false, 'multiple' => false, 'sort_order' => 2, 'options' => ['Con tarjeta', 'Sin tarjeta']],
                ],
                'extras' => $this->EXTRAS_TARJETA,
            ],
            [
                'category_id'    => $plantas->id,
                'name'           => 'Suculentas Surtidas',
                'description'    => 'Set de mini suculentas en macetas decorativas. Ideal para escritorio.',
                'emoji'          => '🌵',
                'price'          => 45.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Agradecimiento',
                'color'          => 'Verde',
                'tamano'         => 'Pequeño',
                'controla_stock' => true,
                'stock'          => 12,
                'sections'       => [],
                'extras'         => [],
            ],
            [
                'category_id'    => $plantas->id,
                'name'           => 'Planta de Interior Pothos',
                'description'    => 'Planta colgante purificadora de aire. Resistente y de bajo mantenimiento.',
                'emoji'          => '🌿',
                'price'          => 55.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Cumpleaños',
                'color'          => 'Verde',
                'tamano'         => 'Mediano',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [],
                'extras'         => [],
            ],

            // ══ CORONAS ═══════════════════════════════════
            [
                'category_id'    => $coronas->id,
                'name'           => 'Corona Fúnebre Clásica',
                'description'    => 'Corona de condolencias con flores blancas y follaje. Incluye banda dedicatoria.',
                'emoji'          => '🌿',
                'price'          => 220.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Condolencias',
                'color'          => 'Blanco',
                'tamano'         => 'Grande',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [
                    ['seccion' => 'dedicatoria', 'label' => 'Banda dedicatoria', 'required' => true, 'multiple' => false, 'sort_order' => 1, 'options' => ['Con banda', 'Sin banda']],
                ],
                'extras' => [],
            ],
            [
                'category_id'    => $coronas->id,
                'name'           => 'Arreglo de Condolencias',
                'description'    => 'Arreglo sobrio en tonos blancos y verdes para expresar respeto y apoyo.',
                'emoji'          => '🕊️',
                'price'          => 160.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Condolencias',
                'color'          => 'Blanco',
                'tamano'         => 'Premium',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [
                    ['seccion' => 'dedicatoria', 'label' => 'Tarjeta dedicatoria', 'required' => false, 'multiple' => false, 'sort_order' => 1, 'options' => ['Con tarjeta', 'Sin tarjeta']],
                ],
                'extras' => [],
            ],

            // ══ REGALOS ═══════════════════════════════════
            [
                'category_id'    => $regalos->id,
                'name'           => 'Caja Sorpresa con Rosas',
                'description'    => 'Caja decorativa con rosas preservadas, chocolates y un peluche. El detalle perfecto.',
                'emoji'          => '🎁',
                'price'          => 135.00,
                'popular'        => true,
                'available'      => true,
                'ocasion'        => 'San Valentín',
                'color'          => 'Rojo',
                'tamano'         => 'Mediano',
                'controla_stock' => true,
                'stock'          => 10,
                'sections'       => [
                    ['seccion' => 'complemento', 'label' => 'Complementos', 'required' => false, 'multiple' => true, 'sort_order' => 1, 'options' => ['Chocolates', 'Peluche', 'Vino', 'Globo']],
                    ['seccion' => 'dedicatoria', 'label' => 'Dedicatoria',  'required' => false, 'multiple' => false, 'sort_order' => 2, 'options' => ['Con tarjeta', 'Sin tarjeta']],
                ],
                'extras' => $this->EXTRAS_DETALLE,
            ],
            [
                'category_id'    => $regalos->id,
                'name'           => 'Rosa Eterna en Cúpula',
                'description'    => 'Rosa preservada bajo cúpula de cristal con luces LED. Dura años sin marchitarse.',
                'emoji'          => '🌹',
                'price'          => 99.00,
                'popular'        => true,
                'available'      => true,
                'ocasion'        => 'Aniversario',
                'color'          => 'Rojo',
                'tamano'         => 'Pequeño',
                'controla_stock' => true,
                'stock'          => 7,
                'sections'       => [
                    ['seccion' => 'dedicatoria', 'label' => 'Dedicatoria', 'required' => false, 'multiple' => false, 'sort_order' => 1, 'options' => ['Con tarjeta', 'Sin tarjeta']],
                ],
                'extras' => $this->EXTRAS_TARJETA,
            ],

            // ══ GLOBOS ════════════════════════════════════
            [
                'category_id'    => $globos->id,
                'name'           => 'Bouquet de Globos Felicidades',
                'description'    => 'Conjunto de globos metálicos y de látex en tonos festivos. Ideal para celebrar.',
                'emoji'          => '🎈',
                'price'          => 50.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Cumpleaños',
                'color'          => 'Multicolor',
                'tamano'         => 'Grande',
                'controla_stock' => false,
                'stock'          => 0,
                'sections'       => [
                    ['seccion' => 'complemento', 'label' => 'Mensaje del globo', 'required' => false, 'multiple' => false, 'sort_order' => 1, 'options' => ['Feliz Cumpleaños', 'Te Amo', 'Felicidades', 'Sin mensaje']],
                ],
                'extras' => $this->EXTRAS_DETALLE,
            ],
            [
                'category_id'    => $globos->id,
                'name'           => 'Globo Burbuja Personalizado',
                'description'    => 'Globo burbuja transparente con flores o confeti en su interior y mensaje a elección.',
                'emoji'          => '🎈',
                'price'          => 38.00,
                'popular'        => false,
                'available'      => true,
                'ocasion'        => 'Felicitaciones',
                'color'          => 'Rosa',
                'tamano'         => 'Mediano',
                'controla_stock' => true,
                'stock'          => 9,
                'sections'       => [
                    ['seccion' => 'complemento', 'label' => 'Relleno', 'required' => false, 'multiple' => false, 'sort_order' => 1, 'options' => ['Flores secas', 'Confeti dorado', 'Plumas']],
                ],
                'extras' => $this->EXTRAS_TARJETA,
            ],
        ];

        // ── Insertar ──────────────────────────────────────
        foreach ($products as $data) {
            $sections = $data['sections'];
            $extras   = $data['extras'];
            unset($data['sections'], $data['extras']);

            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);

            $product = Product::create($data);

            foreach ($sections as $sec) {
                $section = ProductCustomizationSection::create([
                    'product_id' => $product->id,
                    'seccion'    => $sec['seccion'],
                    'label'      => $sec['label'],
                    'required'   => $sec['required'],
                    'multiple'   => $sec['multiple'],
                    'sort_order' => $sec['sort_order'],
                ]);

                foreach ($sec['options'] as $j => $optName) {
                    ProductCustomizationOption::create([
                        'section_id' => $section->id,
                        'name'       => $optName,
                        'sort_order' => $j,
                    ]);
                }
            }

            foreach ($extras as $j => $extra) {
                ProductExtra::create([
                    'product_id' => $product->id,
                    'name'       => $extra['name'],
                    'price'      => $extra['price'],
                    'sort_order' => $j,
                ]);
            }
        }

        $this->command->info('✅ Categorías: ' . Category::count());
        $this->command->info('✅ Productos: '  . Product::count());
        $this->command->info('✅ Secciones: '  . ProductCustomizationSection::count());
        $this->command->info('✅ Opciones: '   . ProductCustomizationOption::count());
        $this->command->info('✅ Extras: '     . ProductExtra::count());
    }
}
