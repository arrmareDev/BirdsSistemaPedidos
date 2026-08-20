import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/utils/api";
import { isAxiosError } from "axios";
import type { Product } from "@/stores/products";

// Coincide exacto con lo que arma MovimientoStockController::format().
export interface MovimientoStock {
  id: number;
  tipo: "venta" | "cancelacion" | "edicion_pedido" | "reposicion" | "ajuste";
  cantidad: number;
  stock_resultante: number;
  motivo: string | null;
  order_codigo: number | null;
  usuario: string | null;
  created_at: string;
}

export interface MovimientosMeta {
  current_page: number;
  last_page: number;
  total: number;
}

interface ProductsAdminResponse {
  data: Product[];
  meta: { total: number };
}

interface ReponerAjustarResponse {
  stock: number;
}

interface MovimientosResponse {
  data: MovimientoStock[];
  meta: MovimientosMeta;
}

export const useInventarioStore = defineStore("inventario", () => {
  const productos = ref<Product[]>([]);
  const loading = ref(false);

  const historial = ref<MovimientoStock[]>([]);
  const historialMeta = ref<MovimientosMeta | null>(null);
  const historialLoading = ref(false);

  // Solo los productos con control de stock activado — este es el
  // universo real del inventario, el resto del catálogo no aplica aquí.
  const conControlDeStock = computed((): Product[] =>
    productos.value.filter((p) => p.controla_stock === true),
  );

  const conStockBajo = computed((): Product[] =>
    conControlDeStock.value.filter((p) => p.stock_bajo === true),
  );

  const agotados = computed((): Product[] =>
    conControlDeStock.value.filter((p) => (p.stock ?? 0) <= 0),
  );

  async function fetchProductos(): Promise<void> {
    loading.value = true;
    try {
      const { data } = await api.get<{ data: ProductsAdminResponse }>(
        "/admin/products",
        { params: { per_page: 300 } },
      );
      productos.value = data.data.data;
    } finally {
      loading.value = false;
    }
  }

  async function fetchHistorial(productId: number, page = 1): Promise<void> {
    historialLoading.value = true;
    try {
      const { data } = await api.get<{ data: MovimientosResponse }>(
        `/admin/products/${productId}/movimientos-stock`,
        { params: { page } },
      );
      historial.value = data.data.data;
      historialMeta.value = data.data.meta;
    } finally {
      historialLoading.value = false;
    }
  }

  function actualizarStockLocal(productId: number, nuevoStock: number): void {
    const producto = productos.value.find((p) => p.id === productId);
    if (producto) producto.stock = nuevoStock;
  }

  async function reponerStock(
    productId: number,
    cantidad: number,
    motivo?: string,
  ): Promise<{ ok: boolean; message?: string }> {
    try {
      const { data } = await api.post<{ data: ReponerAjustarResponse }>(
        `/admin/products/${productId}/reponer-stock`,
        { cantidad, motivo: motivo || undefined },
      );
      actualizarStockLocal(productId, data.data.stock);
      return { ok: true };
    } catch (e: unknown) {
      const message = isAxiosError<{ message?: string }>(e)
        ? (e.response?.data?.message ?? "No se pudo reponer el stock")
        : "No se pudo reponer el stock";
      return { ok: false, message };
    }
  }

  async function ajustarStock(
    productId: number,
    stockNuevo: number,
    motivo: string,
  ): Promise<{ ok: boolean; message?: string }> {
    try {
      const { data } = await api.post<{ data: ReponerAjustarResponse }>(
        `/admin/products/${productId}/ajustar-stock`,
        { stock_nuevo: stockNuevo, motivo },
      );
      actualizarStockLocal(productId, data.data.stock);
      return { ok: true };
    } catch (e: unknown) {
      const message = isAxiosError<{ message?: string }>(e)
        ? (e.response?.data?.message ?? "No se pudo ajustar el stock")
        : "No se pudo ajustar el stock";
      return { ok: false, message };
    }
  }

  return {
    productos,
    loading,
    conControlDeStock,
    conStockBajo,
    agotados,
    historial,
    historialMeta,
    historialLoading,
    fetchProductos,
    fetchHistorial,
    reponerStock,
    ajustarStock,
  };
});
