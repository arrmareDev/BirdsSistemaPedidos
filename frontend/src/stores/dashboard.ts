import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";

export const useDashboardStore = defineStore("dashboard", () => {
  const stats = ref<any>(null);
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
