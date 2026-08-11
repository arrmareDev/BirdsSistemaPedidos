import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/utils/api";

export interface Client {
  id: number;
  name: string;
  phone: string;
  address: string | null;
  district: string | null;
  preferences: {
    secciones?: Record<string, {
      label: string;
      top: string;
      counts: Record<string, number>;
    }>;
  } | null;
  orders_count: number;
  total_spent: number;
  last_order_at: string | null;
  created_at: string;
}

export const useClientsStore = defineStore("clients", () => {
  const clients = ref<Client[]>([]);
  const loading = ref(false);
  const meta = ref<any>(null);

  // ── Computed para fidelización ─────────────────────────
  const vips = computed(
    () => clients.value.filter((c) => c.orders_count >= 5).length,
  );
  const nuevos = computed(
    () => clients.value.filter((c) => c.orders_count === 1).length,
  );
  const recurrentes = computed(
    () =>
      clients.value.filter((c) => c.orders_count >= 2 && c.orders_count < 5)
        .length,
  );
  const totalGastado = computed(() =>
    clients.value.reduce((s, c) => s + (c.total_spent ?? 0), 0),
  );

  async function fetch(page = 1, perPage = 10) {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/clients", {
        params: { page, per_page: perPage },
      });
      clients.value = data.data.data ?? data.data;
      meta.value = data.data.meta ?? null;
    } catch (e) {
      console.error("Error cargando clientes:", e);
    } finally {
      loading.value = false;
    }
  }

  async function getOne(id: number): Promise<Client | null> {
    try {
      const { data } = await api.get(`/admin/clients/${id}`);
      return data.data;
    } catch {
      return null;
    }
  }

  return {
    clients,
    loading,
    meta,
    vips,
    nuevos,
    recurrentes,
    totalGastado,
    fetch,
    getOne,
  };
});
