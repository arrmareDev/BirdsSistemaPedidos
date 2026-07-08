import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/utils/api";

export interface AdminUser {
  id: number;
  name: string;
  email: string;
  role: "admin" | "sistema" | "contador" | "atencion" | "salon";
  permissions: {
    dashboard: boolean;
    catalog: boolean;
    orders: boolean;
    caja: boolean;
    clients: boolean;
    reports: boolean;
    users: boolean;
    sistema: boolean;
    can_manage_catalog: boolean;
    can_manage_users: boolean;
    can_cobrar: boolean;
    can_delete: boolean;
    can_write_orders: boolean; // ← NUEVO — false solo para 'salon'
  };
}

export const useAdminStore = defineStore("admin", () => {
  const token = ref<string | null>(localStorage.getItem("brasero_admin_token"));
  const user = ref<AdminUser | null>(
    JSON.parse(localStorage.getItem("brasero_admin_user") ?? "null"),
  );
  const loading = ref(false);

  // ── Computed ──────────────────────────────────────────
  const isAuth = computed(() => !!token.value);
  const role = computed(() => user.value?.role ?? null);
  const isSistema = computed(() => role.value === "sistema");
  const isAdmin = computed(() => role.value === "admin");
  const isContador = computed(() => role.value === "contador");
  const isAtencion = computed(() => role.value === "atencion");
  const isSalon = computed(() => role.value === "salon");

  // Permisos de navegación
  const can = computed(() => ({
    dashboard: user.value?.permissions.dashboard ?? false,
    catalog: user.value?.permissions.catalog ?? false,
    orders: user.value?.permissions.orders ?? false,
    caja: user.value?.permissions.caja ?? false,
    clients: user.value?.permissions.clients ?? false,
    reports: user.value?.permissions.reports ?? false,
    users: user.value?.permissions.users ?? false,
    sistema: user.value?.permissions.sistema ?? false,
    manageCatalog: user.value?.permissions.can_manage_catalog ?? false,
    manageUsers: user.value?.permissions.can_manage_users ?? false,
    cobrar: user.value?.permissions.can_cobrar ?? false,
    delete: user.value?.permissions.can_delete ?? false,
    writeOrders: user.value?.permissions.can_write_orders ?? false, // ← NUEVO
    zones: isAdmin.value || isSistema.value,
  }));

  // Ruta de inicio según rol
  const homeRoute = computed(() => {
    switch (role.value) {
      case "atencion":
      case "salon":
        return "/admin/pedidos"; // ← su única vista disponible
      case "sistema":
        return "/admin/sistema";
      default:
        return "/admin/dashboard";
    }
  });

  // ── Actions ───────────────────────────────────────────
  async function login(email: string, password: string): Promise<boolean> {
    loading.value = true;
    try {
      const { data } = await api.post("/auth/login", { email, password });
      token.value = data.data.token;
      user.value = data.data.user;
      localStorage.setItem("brasero_admin_token", data.data.token);
      localStorage.setItem(
        "brasero_admin_user",
        JSON.stringify(data.data.user),
      );
      return true;
    } catch {
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function logout(): Promise<void> {
    try {
      await api.post("/admin/auth/logout");
    } catch {}
    token.value = null;
    user.value = null;
    localStorage.removeItem("brasero_admin_token");
    localStorage.removeItem("brasero_admin_user");
  }

  async function fetchMe(): Promise<void> {
    try {
      const { data } = await api.get("/admin/auth/me");
      user.value = data.data;
      localStorage.setItem("brasero_admin_user", JSON.stringify(data.data));
    } catch {
      await logout();
    }
  }

  return {
    token,
    user,
    loading,
    isAuth,
    role,
    isSistema,
    isAdmin,
    isContador,
    isAtencion,
    isSalon,
    can,
    homeRoute,
    login,
    logout,
    fetchMe,
  };
});
