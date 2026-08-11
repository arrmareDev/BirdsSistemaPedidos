import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";

export interface PedidoConfig {
  mensaje_activo: boolean;
  mensaje_label: string;
  entrega_programada_activo: boolean;
  entrega_programada_label: string;
}

const DEFAULTS: PedidoConfig = {
  mensaje_activo: true,
  mensaje_label: "Mensaje para la tarjeta",
  entrega_programada_activo: true,
  entrega_programada_label: "¿Cuándo lo necesitas?",
};

export const usePedidoConfigStore = defineStore("pedidoConfig", () => {
  const config = ref<PedidoConfig>({ ...DEFAULTS });
  const loaded = ref(false);

  async function fetch() {
    try {
      const { data } = await api.get("/pedido-config");
      config.value = { ...DEFAULTS, ...data.data };
    } catch {
      config.value = { ...DEFAULTS };
    } finally {
      loaded.value = true;
    }
  }

  return { config, loaded, fetch };
});
