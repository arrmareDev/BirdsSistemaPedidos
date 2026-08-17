import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";

export interface AdminOrder {
  id: number;
  codigo: number;
  client_name: string;
  client_phone: string;
  type: "local" | "recoger" | "delivery";
  mesa: string | null;
  status: string;
  address: string | null;
  reference: string | null;
  district: string | null;
  note: string | null;
  metodo_pago: string | null;
  lat: number | null;
  lng: number | null;
  subtotal: number;
  delivery_fee: number;
  total: number;
  mensaje_tarjeta: string | null;
  fecha_entrega: string | null;
  hora_entrega: string | null;
  entrega_programada: boolean;
  created_at: string;
  updated_at: string;
  items?: Array<{
    id: number;
    product_id: number;
    qty: number;
    unit_price: number;
    subtotal: number;
    custom_summary: string | null;
    customization: any[];
    extras: any[];
    product?: {
      id: number;
      name: string;
      icon: string;
    } | null;
  }>;
}

export const useOrdersStore = defineStore("orders", () => {
  const orders = ref<AdminOrder[]>([]);
  const loading = ref(false);
  const meta = ref<any>(null);

  // ── Papelera ──────────────────────────────────────────────
  const trashedOrders = ref<AdminOrder[]>([]);
  const trashedMeta = ref<any>(null);
  const loadingTrashed = ref(false);

  async function fetch(params?: {
    status?: string;
    search?: string;
    date?: string;
    date_from?: string;
    date_to?: string;
    type?: string;
    page?: number;
    per_page?: number;
  }) {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/orders", { params });
      if (data.data?.data) {
        orders.value = data.data.data;
        meta.value = data.data.meta;
      } else if (Array.isArray(data.data)) {
        orders.value = data.data;
        meta.value = null;
      } else {
        orders.value = [];
      }
    } catch (e) {
      console.error("Error cargando pedidos:", e);
      orders.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function fetchTrashed(params?: {
    search?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
  }) {
    loadingTrashed.value = true;
    try {
      const { data } = await api.get("/admin/orders/trashed", { params });
      if (data.data?.data) {
        trashedOrders.value = data.data.data;
        trashedMeta.value = data.data.meta;
      } else {
        trashedOrders.value = [];
      }
    } catch (e) {
      console.error("Error cargando pedidos eliminados:", e);
      trashedOrders.value = [];
    } finally {
      loadingTrashed.value = false;
    }
  }

  async function restoreOrder(id: number): Promise<boolean> {
    try {
      await api.post(`/admin/orders/${id}/restore`);
      trashedOrders.value = trashedOrders.value.filter((o) => o.id !== id);
      return true;
    } catch (e) {
      console.error("Error restaurando pedido:", e);
      return false;
    }
  }

  async function forceDeleteOrder(id: number): Promise<boolean> {
    try {
      await api.delete(`/admin/orders/${id}/force`);
      trashedOrders.value = trashedOrders.value.filter((o) => o.id !== id);
      return true;
    } catch (e) {
      console.error("Error eliminando pedido definitivamente:", e);
      return false;
    }
  }

  async function getOne(id: number): Promise<AdminOrder | null> {
    try {
      const { data } = await api.get(`/admin/orders/${id}`);
      return data.data;
    } catch {
      return null;
    }
  }

  async function updateStatus(
    id: number,
    status: string,
  ): Promise<AdminOrder | null> {
    const idx = orders.value.findIndex((o) => o.id === id);
    const prevStatus = idx !== -1 ? orders.value[idx].status : null;

    if (idx !== -1) orders.value[idx] = { ...orders.value[idx], status };

    try {
      const { data } = await api.patch(`/admin/orders/${id}/status`, {
        status,
      });
      if (idx !== -1) orders.value[idx] = data.data;
      return data.data;
    } catch (e) {
      if (idx !== -1 && prevStatus) {
        orders.value[idx] = { ...orders.value[idx], status: prevStatus };
      }
      console.error("Error actualizando estado:", e);
      return null;
    }
  }

  async function updateItems(
    id: number,
    items: Array<{
      product_id: number;
      qty: number;
      unit_price: number;
      customization?: any[];
      extras?: any[];
      custom_summary?: string | null;
    }>,
  ): Promise<AdminOrder | null> {
    try {
      const { data } = await api.put(`/admin/orders/${id}/items`, { items });
      const idx = orders.value.findIndex((o) => o.id === id);
      if (idx !== -1) orders.value[idx] = data.data;
      return data.data;
    } catch (e) {
      console.error("Error actualizando items:", e);
      throw e;
    }
  }

  async function cancelOrder(id: number): Promise<boolean> {
    const idx = orders.value.findIndex((o) => o.id === id);
    const prevStatus = idx !== -1 ? orders.value[idx].status : null;

    if (idx !== -1)
      orders.value[idx] = { ...orders.value[idx], status: "cancelado" };

    try {
      await api.patch(`/admin/orders/${id}/status`, { status: "cancelado" });
      return true;
    } catch {
      if (idx !== -1 && prevStatus) {
        orders.value[idx] = { ...orders.value[idx], status: prevStatus };
      }
      return false;
    }
  }

  async function deleteOrder(id: number): Promise<boolean> {
    try {
      await api.delete(`/admin/orders/${id}`);
      orders.value = orders.value.filter((o) => o.id !== id);
      return true;
    } catch {
      return false;
    }
  }

  async function cobrarLocal(
    id: number,
    metodoPago: string,
  ): Promise<AdminOrder | null> {
    try {
      const { data } = await api.patch(`/admin/orders/${id}/cobrar`, {
        metodo_pago: metodoPago,
      });
      const idx = orders.value.findIndex((o) => o.id === id);
      if (idx !== -1) orders.value[idx] = data.data;
      return data.data;
    } catch (e) {
      console.error("Error al cobrar pedido:", e);
      throw e;
    }
  }

  return {
    orders,
    loading,
    meta,
    trashedOrders,
    trashedMeta,
    loadingTrashed,
    fetch,
    fetchTrashed,
    restoreOrder,
    forceDeleteOrder,
    getOne,
    updateStatus,
    updateItems,
    cancelOrder,
    deleteOrder,
    cobrarLocal,
  };
});
