import { createRouter, createWebHistory } from "vue-router";

function getStoredUser(): { must_change_password?: boolean } | null {
  try {
    return JSON.parse(localStorage.getItem("brasero_admin_user") ?? "null");
  } catch {
    return null;
  }
}

function requireAuth(_to: any, _from: any, next: any) {
  const token = localStorage.getItem("brasero_admin_token");
  if (!token) return next("/admin/login");
  if (getStoredUser()?.must_change_password)
    return next("/admin/cambiar-clave");
  next();
}

function redirectIfAuth(_to: any, _from: any, next: any) {
  const token = localStorage.getItem("brasero_admin_token");
  if (!token) return next();
  if (getStoredUser()?.must_change_password)
    return next("/admin/cambiar-clave");
  next("/admin/dashboard");
}

// La pantalla de cambio de contraseña obligatorio: solo entra quien tiene
// la bandera activa, y quien ya la cambió no puede quedarse dando vueltas ahí.
function requirePasswordChangePending(_to: any, _from: any, next: any) {
  const token = localStorage.getItem("brasero_admin_token");
  if (!token) return next("/admin/login");
  if (!getStoredUser()?.must_change_password) return next("/admin/dashboard");
  next();
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // ══ TIENDA PÚBLICA ══
    {
      path: "/",
      component: () => import("@/views/CatalogView.vue"),
      name: "catalog",
    },
    {
      path: "/producto/:slug",
      component: () => import("@/views/ProductDetailView.vue"),
      name: "product-detail",
    },
    {
      path: "/checkout",
      component: () => import("@/views/CheckoutView.vue"),
      name: "checkout",
    },
    {
      path: "/confirmado",
      component: () => import("@/views/SuccessView.vue"),
      name: "success",
    },
    {
      path: "/seguimiento/:id?",
      component: () => import("@/views/TrackingView.vue"),
      name: "tracking",
    },

    // ══ ADMIN ══
    {
      path: "/admin/login",
      component: () => import("@/views/admin/AdminLogin.vue"),
      name: "admin-login",
      beforeEnter: redirectIfAuth,
    },
    {
      path: "/admin/cambiar-clave",
      component: () => import("@/views/admin/CambiarClaveView.vue"),
      name: "admin-cambiar-clave",
      beforeEnter: requirePasswordChangePending,
    },
    {
      path: "/admin",
      component: () => import("@/views/admin/AdminShell.vue"),
      beforeEnter: requireAuth,
      children: [
        { path: "", redirect: "/admin/dashboard" },
        {
          path: "dashboard",
          component: () => import("@/views/admin/DashboardView.vue"),
          name: "admin-dashboard",
        },
        {
          path: "pedidos",
          component: () => import("@/views/admin/PedidosView.vue"),
          name: "admin-pedidos",
        },
        {
          path: "catalogo",
          component: () => import("@/views/admin/CatalogoView.vue"),
          name: "admin-catalogo",
        },
        {
          path: "caja",
          component: () => import("@/views/admin/CajaView.vue"),
          name: "admin-caja",
        },
        {
          path: "caja/historial",
          component: () => import("@/views/admin/CajaHistorialView.vue"),
          name: "admin-caja-historial",
        },
        {
          path: "clientes",
          component: () => import("@/views/admin/ClientesView.vue"),
          name: "admin-clientes",
        },
        {
          path: "reportes",
          component: () => import("@/views/admin/ReportesView.vue"),
          name: "admin-reportes",
        },
        {
          path: "/admin/usuarios",
          component: () => import("@/views/admin/UsuariosView.vue"),
          meta: { requiresAuth: true, role: ["super_admin"] },
        },

        {
          path: "/admin/delivery-zones",
          name: "delivery-zones",
          component: () => import("@/views/admin/DeliveryZonesView.vue"),
          meta: { auth: true },
        },

        {
          path: "/admin/proveedores",
          name: "proveedores",
          component: () => import("@/views/admin/ProveedoresView.vue"),
          meta: { auth: true },
        },

        {
          path: "/admin/sistema",
          component: () => import("@/views/admin/SistemaView.vue"),
          meta: { requiresAuth: true, role: ["sistema"] },
        },
      ],
    },
  ],
});

export default router;
