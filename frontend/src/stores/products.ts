import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/utils/api";

export interface CustomizationOption {
  id: number;
  name: string;
}

export interface CustomizationSection {
  id: number;
  seccion: string; // antes: "salsas" | "ensalada" | "papas" | "termino"
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
  category: { id: number; name: string; slug: string } | null;
  customization_sections: CustomizationSection[];

  ocasion?: string | null;
  color?: string | null;
  tamano?: string | null;
  stock?: number;
  controla_stock?: boolean;

  extras: ProductExtra[];
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  emoji: string | null;
  sort_order: number;
  active: boolean;
}

export const useProductsStore = defineStore("products", () => {
  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const activeCategory = ref<string>("all");
  const loading = ref(false);

  const filtered = computed((): Product[] => {
    if (activeCategory.value === "all") return products.value;
    return products.value.filter(
      (p) => p.category?.slug === activeCategory.value,
    );
  });

  const popular = computed((): Product[] =>
    products.value.filter((p) => p.popular && p.available).slice(0, 6),
  );

  // ── Catálogo público — solo disponibles ───────────────
  async function fetch() {
    loading.value = true;
    try {
      const [prodRes, catRes] = await Promise.all([
        api.get("/products"),
        api.get("/categories"),
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
  async function fetchAdmin() {
    loading.value = true;
    try {
      const [prodRes, catRes] = await Promise.all([
        api.get("/admin/products"), // ← sin filtro available
        api.get("/admin/categories"),
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

  return {
    products,
    categories,
    activeCategory,
    loading,
    filtered,
    popular,
    fetch,
    fetchAdmin,
    setCategory,
  };
});
