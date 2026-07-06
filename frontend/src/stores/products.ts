import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/utils/api";

export interface CustomizationOption {
  id: number;
  name: string;
  price_modifier: number; // ← NUEVO
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

export interface Product {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  emoji: string | null;
  image_url: string | null;
  price: number;
  popular: boolean;
  available: boolean;
  category: {
    id: number;
    name: string;
    slug: string;
    business_line: string | null; // ← NUEVO
  } | null;
  customization_sections: CustomizationSection[];

  ocasion?: string | null;
  color?: string | null;
  tamano?: string | null;
  stock?: number;
  controla_stock?: boolean;

  extras: ProductExtra[];
  extras_compartidos: ProductExtra[]; // ← NUEVO — extras reutilizables (cafetería/menú)
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  business_line: string; // ← NUEVO — 'floreria' | 'cafeteria' | 'menu'
  emoji: string | null;
  sort_order: number;
  active: boolean;
}

export const useProductsStore = defineStore("products", () => {
  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const activeCategory = ref<string>("all");
  const activeLine = ref<string>("all"); // ← NUEVO — 'all' | 'floreria' | 'cafeteria' | 'menu'
  const loading = ref(false);

  const filtered = computed((): Product[] => {
    let result = products.value;

    if (activeLine.value !== "all") {
      result = result.filter(
        (p) => p.category?.business_line === activeLine.value,
      );
    }
    if (activeCategory.value !== "all") {
      result = result.filter((p) => p.category?.slug === activeCategory.value);
    }
    return result;
  });

  const categoriesByLine = computed(() => (line: string): Category[] => {
    if (line === "all") return categories.value;
    return categories.value.filter((c) => c.business_line === line);
  });

  const popular = computed((): Product[] =>
    products.value.filter((p) => p.popular && p.available).slice(0, 6),
  );

  // ── Catálogo público — solo disponibles ───────────────
  async function fetch(linea?: string) {
    loading.value = true;
    try {
      const params = linea && linea !== "all" ? { linea } : {};
      const [prodRes, catRes] = await Promise.all([
        api.get("/products", { params }),
        api.get("/categories", { params }),
      ]);
      products.value = prodRes.data.data;
      categories.value = catRes.data.data;
    } catch (e) {
      console.error("Error cargando catálogo:", e);
    } finally {
      loading.value = false;
    }
  }

  // ── Admin — todos los productos sin filtrar ───────────
  async function fetchAdmin(linea?: string) {
    loading.value = true;
    try {
      const params = linea && linea !== "all" ? { linea } : {};
      const [prodRes, catRes] = await Promise.all([
        api.get("/admin/products", { params }),
        api.get("/admin/categories", { params }),
      ]);
      products.value = prodRes.data.data;
      categories.value = catRes.data.data;
    } catch (e) {
      console.error("Error cargando catálogo admin:", e);
    } finally {
      loading.value = false;
    }
  }

  function setCategory(slug: string) {
    activeCategory.value = slug;
  }

  function setLine(line: string) {
    activeLine.value = line;
    activeCategory.value = "all"; // reset categoría al cambiar de línea
  }

  return {
    products,
    categories,
    activeCategory,
    activeLine,
    loading,
    filtered,
    categoriesByLine,
    popular,
    fetch,
    fetchAdmin,
    setCategory,
    setLine,
  };
});
