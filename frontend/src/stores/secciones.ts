import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";

export interface SeccionConfig {
  label: string;
  activo: boolean;
}

export interface SeccionesConfig {
  envoltura: SeccionConfig;
  lazo: SeccionConfig;
  follaje: SeccionConfig;
  dedicatoria: SeccionConfig;
  presentacion: SeccionConfig;
  complemento: SeccionConfig;
}

const DEFAULTS: SeccionesConfig = {
  envoltura: { label: "Envoltura", activo: true },
  lazo: { label: "Lazo / Cinta", activo: true },
  follaje: { label: "Follaje", activo: true },
  dedicatoria: { label: "Dedicatoria", activo: true },
  presentacion: { label: "Presentación", activo: true },
  complemento: { label: "Complemento", activo: true },
};

export const useSeccionesStore = defineStore("secciones", () => {
  const secciones = ref<SeccionesConfig>({ ...DEFAULTS });
  const loaded = ref(false);

  async function fetch() {
    try {
      const { data } = await api.get("/secciones-config");
      secciones.value = { ...DEFAULTS, ...data.data };
    } catch {
      secciones.value = { ...DEFAULTS };
    } finally {
      loaded.value = true;
    }
  }

  return { secciones, loaded, fetch };
});
