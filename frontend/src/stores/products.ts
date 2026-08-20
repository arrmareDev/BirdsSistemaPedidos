import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/utils/api";

export interface CustomizationOption {
  id: number;
  name: string;
  price_modifier: number;
  image_url: string | null;
}

export interface CustomizationSection {
  id: number;
  seccion: string;
  label: string;
  required: boolean;
  multiple: boolean;
  options: CustomizationOption[];
}

export interface ProductExtra {
  id: number;
  name: string;
  price: number;
}

export interface ProductDescuento {
  tipo: string;
  valor: number;
  desde: string | null;
  hasta: string | null;
  porcentaje: number | null;
}

export interface ProductDescuentoConfig {
  tipo: string | null;
  valor: number | null;
  desde: string | null;
  hasta: string | null;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  image_url: string | null;

  images?: {
    id: number;
    image_url: string;
    sort_order: number;
  }[];

  price: number;
  // Solo viene poblado si el descuento está vigente HOY — así el
  // catálogo público no necesita repetir el cálculo de fechas.
  descuento: ProductDescuento | null;
  precio_final: number;
  // La config cruda (vigente o no) — el admin la usa para poder
  // editar un descuento vencido o programado a futuro.
  descuento_config: ProductDescuentoConfig;
  popular: boolean;
  // Referenciado en ProductCard.vue pero nunca provisto por la API —
  // queda opcional para que ese v-if simplemente nunca se cumpla,
  // en vez de que TypeScript marque una propiedad inexistente.
  isNew?: boolean;
  available: boolean;

  category: {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    root_slug: string | null;
  } | null;

  customization_sections: CustomizationSection[];

  stock?: number;
  controla_stock?: boolean;

  extras: ProductExtra[];
  extras_compartidos: ProductExtra[];
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  parent_id: number | null;
  parent: {
    id: number;
    name: string;
    slug: string;
  } | null;
  icon: string | null;
  sort_order: number;
  active: boolean;
  products_count?: number;
}

interface PageMeta {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
}

// La vista "Todo" agrupa por categoría y muestra unos pocos por grupo —
// para que esa vista previa no quede vacía en categorías que caigan
// "más atrás" en el orden, pedimos un lote más grande solo ahí (no es
// "traer todo el catálogo", sigue acotado).
// Tope de la carga de "todo el catálogo" (vista Todo) — se pide de
// una sola vez, sin paginar, para poder mostrar TODOS los productos
// de cada categoría sin ningún recorte. 300 da margen amplio para
// que el catálogo crezca bastante antes de necesitar revisarse.
const OVERVIEW_PER_PAGE = 300;
const DEFAULT_PER_PAGE = 24;
const ADMIN_PER_PAGE = 30;

export const useProductsStore = defineStore("products", () => {
  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const activeCategory = ref("all");
  const activeGroup = ref("all");
  const loading = ref(false);
  const loadingMore = ref(false);
  const meta = ref<PageMeta | null>(null);

  // ============================================================
  // NORMALIZACIÓN — cualquier dato que llegue del API se pasa por
  // acá antes de entrar al store, así el resto de la app nunca
  // recibe un precio en string, un booleano indefinido, etc.
  // ============================================================

  function normalizeNumber(value: unknown, fallback = 0): number {
    if (value === null || value === undefined || value === "") {
      return fallback;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : fallback;
  }

  function normalizeProduct(product: any): Product {
    return {
      ...product,

      price: normalizeNumber(product?.price),
      descuento: product?.descuento
        ? {
            tipo: product.descuento.tipo ?? "",
            valor: normalizeNumber(product.descuento.valor),
            desde: product.descuento.desde ?? null,
            hasta: product.descuento.hasta ?? null,
            porcentaje:
              product.descuento.porcentaje !== null &&
              product.descuento.porcentaje !== undefined
                ? normalizeNumber(product.descuento.porcentaje)
                : null,
          }
        : null,
      precio_final: normalizeNumber(
        product?.precio_final,
        normalizeNumber(product?.price),
      ),
      descuento_config: {
        tipo: product?.descuento_config?.tipo ?? null,
        valor:
          product?.descuento_config?.valor !== null &&
          product?.descuento_config?.valor !== undefined
            ? normalizeNumber(product.descuento_config.valor)
            : null,
        desde: product?.descuento_config?.desde ?? null,
        hasta: product?.descuento_config?.hasta ?? null,
      },
      popular: Boolean(product?.popular),
      available: product?.available !== false,

      category: product?.category
        ? {
            ...product.category,
            parent_id:
              product.category.parent_id !== undefined
                ? product.category.parent_id
                : null,
          }
        : null,

      images: Array.isArray(product?.images)
        ? product.images.map((image: any) => ({
            id: normalizeNumber(image?.id),
            image_url: image?.image_url ?? "",
            sort_order: normalizeNumber(image?.sort_order),
          }))
        : [],

      customization_sections: Array.isArray(product?.customization_sections)
        ? product.customization_sections.map((section: any) => ({
            ...section,
            required: Boolean(section?.required),
            multiple: Boolean(section?.multiple),
            options: Array.isArray(section?.options)
              ? section.options.map((option: any) => ({
                  ...option,
                  id: normalizeNumber(option?.id),
                  name: option?.name ?? "",
                  price_modifier: normalizeNumber(option?.price_modifier),
                  image_url: option?.image_url ?? null,
                }))
              : [],
          }))
        : [],

      extras: Array.isArray(product?.extras)
        ? product.extras.map((extra: any) => ({
            ...extra,
            id: normalizeNumber(extra?.id),
            name: extra?.name ?? "",
            price: normalizeNumber(extra?.price),
          }))
        : [],

      extras_compartidos: Array.isArray(product?.extras_compartidos)
        ? product.extras_compartidos.map((extra: any) => ({
            ...extra,
            id: normalizeNumber(extra?.id),
            name: extra?.name ?? "",
            price: normalizeNumber(extra?.price),
          }))
        : [],

      stock:
        product?.stock !== undefined && product?.stock !== null
          ? normalizeNumber(product.stock)
          : undefined,

      controla_stock:
        product?.controla_stock !== undefined
          ? Boolean(product.controla_stock)
          : undefined,
    };
  }

  // Recibe el array plano de productos (ya extraído de la respuesta
  // paginada) y lo normaliza. Si por algún motivo no es un array,
  // avisa en consola y devuelve una lista vacía en vez de romper la app.
  function normalizeProducts(data: unknown): Product[] {
    if (!Array.isArray(data)) {
      console.warn("La API no devolvió un array de productos:", data);
      return [];
    }
    return data.map(normalizeProduct);
  }

  // ============================================================
  // CATEGORÍAS PRINCIPALES / POR GRUPO
  // ============================================================

  const rootCategories = computed((): Category[] =>
    categories.value.filter((c) => c.parent_id === null),
  );

  const categoriesByGroup = computed(() => (groupSlug: string): Category[] => {
    if (groupSlug === "all") return categories.value;
    return categories.value.filter((c) => c.parent?.slug === groupSlug);
  });

  // `products` ya viene filtrado y paginado desde el servidor — este
  // alias se mantiene por compatibilidad con el resto de la app.
  const filtered = computed((): Product[] => products.value);

  const hasMore = computed(
    () => !!meta.value && meta.value.current_page < meta.value.last_page,
  );

  const popular = computed((): Product[] =>
    products.value.filter((p) => p.popular && p.available).slice(0, 6),
  );

  // ============================================================
  // CATÁLOGO PÚBLICO — paginado real de servidor
  // ============================================================

  async function fetch(
    opts: {
      grupo?: string;
      category?: string;
      q?: string;
      perPage?: number;
      append?: boolean;
    } = {},
  ) {
    const isOverview =
      (!opts.grupo || opts.grupo === "all") &&
      (!opts.category || opts.category === "all") &&
      !opts.q;
    const perPage =
      opts.perPage ?? (isOverview ? OVERVIEW_PER_PAGE : DEFAULT_PER_PAGE);
    const page = opts.append ? (meta.value?.current_page ?? 0) + 1 : 1;

    if (opts.append) loadingMore.value = true;
    else loading.value = true;

    try {
      const params: Record<string, string | number> = {
        page,
        per_page: perPage,
      };
      if (opts.grupo && opts.grupo !== "all") params.grupo = opts.grupo;
      if (opts.category && opts.category !== "all")
        params.category = opts.category;
      if (opts.q) params.q = opts.q;

      const requests: Promise<any>[] = [api.get("/products", { params })];
      if (!opts.append) requests.push(api.get("/categories"));
      const [prodRes, catRes] = await Promise.all(requests);

      // Respuesta paginada de Laravel: { data: [...], links, meta }
      const page_ = prodRes.data?.data;
      const list = normalizeProducts(page_?.data);
      products.value = opts.append ? [...products.value, ...list] : list;
      meta.value = page_?.meta ?? null;

      if (catRes) {
        categories.value = Array.isArray(catRes.data?.data)
          ? catRes.data.data
          : [];
      }
    } catch (e) {
      console.error("Error cargando catálogo:", e);
      if (!opts.append) {
        products.value = [];
        categories.value = [];
      }
    } finally {
      loading.value = false;
      loadingMore.value = false;
    }
  }

  // Búsqueda de texto del catálogo público (server-side)
  const searchQuery = ref("");

  // Trae la siguiente página respetando los filtros activos actuales.
  async function loadMore() {
    if (!hasMore.value || loadingMore.value || loading.value) return;
    await fetch({
      grupo: activeGroup.value,
      category: activeCategory.value,
      q: searchQuery.value || undefined,
      perPage: meta.value?.per_page,
      append: true,
    });
  }

  // ============================================================
  // ADMIN — paginado real + búsqueda/filtro de servidor
  // ============================================================

  async function fetchAdmin(
    opts: {
      grupo?: string;
      categoryId?: number | string;
      q?: string;
      perPage?: number;
      append?: boolean;
    } = {},
  ) {
    const perPage = opts.perPage ?? ADMIN_PER_PAGE;
    const page = opts.append ? (meta.value?.current_page ?? 0) + 1 : 1;

    if (opts.append) loadingMore.value = true;
    else loading.value = true;

    try {
      const params: Record<string, string | number> = {
        page,
        per_page: perPage,
      };
      if (opts.grupo && opts.grupo !== "all") params.grupo = opts.grupo;
      if (opts.categoryId) params.category_id = opts.categoryId;
      if (opts.q) params.q = opts.q;

      const requests: Promise<any>[] = [api.get("/admin/products", { params })];
      if (!opts.append) requests.push(api.get("/admin/categories"));
      const [prodRes, catRes] = await Promise.all(requests);

      const page_ = prodRes.data?.data;
      const list = normalizeProducts(page_?.data);
      products.value = opts.append ? [...products.value, ...list] : list;
      meta.value = page_?.meta ?? null;

      if (catRes) {
        categories.value = Array.isArray(catRes.data?.data)
          ? catRes.data.data
          : [];
      }
    } catch (e) {
      console.error("Error cargando catálogo admin:", e);
      if (!opts.append) {
        products.value = [];
        categories.value = [];
      }
    } finally {
      loading.value = false;
      loadingMore.value = false;
    }
  }

  async function loadMoreAdmin() {
    if (!hasMore.value || loadingMore.value || loading.value) return;
    await fetchAdmin({
      grupo: activeGroup.value,
      categoryId:
        activeCategory.value !== "all" ? activeCategory.value : undefined,
      perPage: meta.value?.per_page,
      append: true,
    });
  }

  // ============================================================
  // FILTROS — cambiar de categoría/grupo dispara un fetch nuevo,
  // ya no filtra en el cliente (el catálogo completo ya no vive
  // en memoria una vez que hay paginación real).
  // ============================================================

  async function setCategory(slug: string) {
    activeCategory.value = slug;
    searchQuery.value = "";
    await fetch({ grupo: activeGroup.value, category: slug });
  }

  async function setGroup(group: string) {
    activeGroup.value = group;
    activeCategory.value = "all";
    searchQuery.value = "";
    await fetch({ grupo: group, category: "all" });
  }

  // Búsqueda general: es independiente de las pestañas de categoría —
  // al buscar, se muestran resultados de todo el catálogo (no solo de
  // la categoría que estuviera activa), para no confundir "busco esto
  // en toda la tienda" con "busco esto en Ramos".
  async function search(term: string) {
    searchQuery.value = term.trim();
    if (searchQuery.value) {
      activeGroup.value = "all";
      activeCategory.value = "all";
    }
    await fetch({ q: searchQuery.value || undefined });
  }

  return {
    products,
    categories,
    activeCategory,
    activeGroup,
    loading,
    loadingMore,
    meta,
    hasMore,
    searchQuery,

    rootCategories,
    categoriesByGroup,
    filtered,
    popular,

    fetch,
    loadMore,
    fetchAdmin,
    loadMoreAdmin,
    search,

    setCategory,
    setGroup,

    // Expuesto para cuando un componente necesita traer productos por
    // su cuenta (sin pisar el listado compartido del store) y aun así
    // normalizarlos igual que el resto de la app.
    normalizeProducts,
  };
});
