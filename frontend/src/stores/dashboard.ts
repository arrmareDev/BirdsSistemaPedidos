import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";

// Coincide exacto con lo que arma ReportController::sales() en el backend.
export interface DashboardStats {
  sales_today: number;
  sales_yesterday: number;
  sales_week: number;
  sales_month: number;
  orders_today: number;
  orders_month: number;
  avg_ticket: number;
  growth_pct: number;
  by_type: Array<{ type: string; count: number; total: number }>;
  by_status: Array<{ status: string; count: number }>;
  by_hour: Array<{ hour: number; count: number; total: number }>;
  last_7_days: Array<{ date: string; label: string; total: number }>;
  top_products: Array<{
    product: string;
    icon: string;
    qty: number;
    revenue: number;
  }>;
}

export const useDashboardStore = defineStore("dashboard", () => {
  const stats = ref<DashboardStats | null>(null);
  const loading = ref(false);

  async function fetch() {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/reports/sales");
      stats.value = data.data;
    } catch (e) {
      console.error("Error cargando dashboard:", e);
    } finally {
      loading.value = false;
    }
  }

  return { stats, loading, fetch };
});
