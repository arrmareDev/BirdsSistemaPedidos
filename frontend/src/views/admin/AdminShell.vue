<template>
  <div class="flex h-screen overflow-hidden bg-gray-50">

    <!-- Overlay móvil -->
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="sidebarOpen" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-[190] lg:hidden"
        @click="sidebarOpen = false" />
    </Transition>

    <!-- ══ SIDEBAR ══ -->
    <aside class="fixed lg:static inset-y-0 left-0 z-[200] flex flex-col
             w-[220px] shrink-0 bg-white border-r border-gray-100
             transition-transform duration-300 ease-out lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'">

      <!-- Brand -->
      <div class="flex items-center gap-3 px-4 h-[58px]
                  border-b border-gray-100 shrink-0">

        <div class="w-20 h-20 shrink-0 flex items-center justify-center">
          <img src="/images/logobirds.png" alt="Birds Florería" class="w-full h-full object-contain"
            @error="($event.target as HTMLImageElement).style.display = 'none'" />
        </div>

        <div class="min-w-0">
          <!-- ← MARCA: nombre real de la florería -->
          <p class="font-black text-[14px] text-gray-900 leading-none m-0 truncate"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            Florería
          </p>
          <p class="text-[10px] text-gray-400 mt-0.5 m-0 tracking-wide">
            Panel Admin
          </p>
        </div>
      </div>

      <!-- Badge de rol -->
      <div class="px-3 py-2 border-b border-gray-100 shrink-0">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                 text-[10.5px] font-bold uppercase tracking-wide" :class="roleBadgeClass">
          <component :is="roleIcon" class="w-3 h-3" />
          {{ roleLabel }}
        </span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto px-2 py-3 flex flex-col gap-4">
        <div v-for="group in NAV_GROUPS" :key="group.label">
          <p class="text-[9.5px] font-black uppercase tracking-widest
                     text-gray-400 px-3 mb-1.5 m-0">
            {{ group.label }}
          </p>
          <RouterLink v-for="item in group.items" :key="item.to" :to="item.to" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl mb-0.5
                   no-underline text-[13px] font-medium
                   transition-all duration-150 group" :class="isActive(item.to)
                    ? 'bg-red-50 text-brand-red font-semibold'
                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
            <component :is="item.icon" class="w-4 h-4 shrink-0 transition-colors" :class="isActive(item.to)
              ? 'text-brand-red'
              : 'text-gray-400 group-hover:text-gray-600'" />
            <span class="flex-1 leading-none">{{ item.label }}</span>
            <span v-if="item.badge" class="text-[9px] font-black px-1.5 py-0.5 rounded-full
                     bg-brand-red text-white leading-none">
              {{ item.badge }}
            </span>
          </RouterLink>
        </div>
      </nav>

      <!-- Usuario + logout -->
      <div class="px-2 py-3 border-t border-gray-100 shrink-0 flex flex-col gap-1">
        <RouterLink to="/" custom v-slot="{ href }">
          <a :href="href" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2.5 px-3 py-2 rounded-xl no-underline
            text-[12.5px] text-gray-500 font-medium
            hover:bg-gray-50 hover:text-gray-800
            transition-all duration-150 group">
            <ArrowTopRightOnSquareIcon class="w-4 h-4 text-gray-400 group-hover:text-gray-600 shrink-0" />
            Ver tienda
          </a>
        </RouterLink>


        <button @click="handleLogout" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl
                 text-[13px] font-medium text-gray-600 cursor-pointer
                 border-none bg-transparent text-left
                 hover:bg-red-50 hover:text-red-700
                 transition-all duration-150 group">
          <div class="w-7 h-7 rounded-full bg-brand-red flex items-center
                      justify-center text-white text-[11px] font-black
                      shrink-0 leading-none">
            {{ initials }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 text-[12.5px]
                      leading-none m-0 truncate">
              {{ admin.user?.name ?? 'Administrador' }}
            </p>
            <p class="text-gray-400 text-[10.5px] mt-0.5 m-0 truncate">
              {{ admin.user?.email ?? '' }}
            </p>
          </div>
          <ArrowRightOnRectangleIcon class="w-4 h-4 text-gray-400 group-hover:text-red-500
                   shrink-0 transition-colors" />
        </button>
      </div>
    </aside>

    <!-- ══ MAIN ══ -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

      <!-- Topbar -->
      <header class="h-[58px] flex items-center gap-3 px-5 lg:px-6 shrink-0
               bg-white border-b border-gray-100 z-10">

        <!-- Hamburguesa móvil -->
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden w-9 h-9 rounded-xl border border-gray-200 bg-gray-50
                 flex items-center justify-center cursor-pointer shrink-0
                 hover:border-red-300 hover:bg-red-50 hover:text-brand-red
                 transition-all duration-150" aria-label="Abrir menú">
          <Bars3Icon class="w-4 h-4 text-gray-500" />
        </button>

        <!-- Título -->
        <div class="flex-1 min-w-0">
          <h1 class="font-black text-[17px] text-gray-900 leading-none m-0 truncate"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            {{ currentMeta.title }}
          </h1>
          <p v-if="currentMeta.sub" class="text-[11.5px] text-gray-400 mt-0.5 m-0 truncate">
            {{ currentMeta.sub }}
          </p>
        </div>

        <!-- Soporte WhatsApp -->
        <a :href="supportLink" target="_blank" class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl
                 bg-[#25D366]/10 border border-[#25D366]/30 text-[#128C7E]
                 font-semibold text-[12.5px] no-underline shrink-0
                 hover:bg-[#25D366]/20 transition-all duration-150">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94
                     1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059
                     -.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371
                     -.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01
                     -.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198
                     2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719
                     2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.553 4.116 1.522 5.847L.057 23.882l6.197-1.624A11.954
                     11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0
                     01-5.003-1.37l-.36-.214-3.68.965.981-3.595-.233-.369A9.818 9.818 0 1112 21.818z" />
          </svg>
          Soporte
        </a>

        <!-- Reloj -->
        <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                 bg-gray-50 border border-gray-100 shrink-0">
          <ClockIcon class="w-3.5 h-3.5 text-gray-400" />
          <span class="text-[12px] font-mono font-semibold text-gray-600">
            {{ currentTime }}
          </span>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-5 lg:p-6">
        <RouterView v-slot="{ Component, route: r }">
          <Transition enter-active-class="transition-all duration-150" leave-active-class="transition-all duration-100"
            enter-from-class="opacity-0 translate-y-1" leave-to-class="opacity-0" mode="out-in">
            <component :is="Component" :key="r.path" />
          </Transition>
        </RouterView>
      </main>
    </div>
  </div>

  <!-- ══ MODAL CONFIRMAR LOGOUT ══ -->
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="showLogoutModal" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="showLogoutModal = false">
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          leave-to-class="opacity-0 scale-95">
          <div v-if="showLogoutModal" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">

            <div class="w-16 h-16 rounded-2xl bg-red-50 border border-red-100
                        flex items-center justify-center mx-auto mb-5">
              <ArrowRightOnRectangleIcon class="w-8 h-8 text-red-500" />
            </div>

            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              ¿Cerrar sesión?
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
              Se cerrará la sesión de
              <strong class="text-gray-700">{{ admin.user?.name }}</strong>.
              Tendrás que volver a ingresar tus credenciales.
            </p>

            <div class="flex gap-3">
              <button @click="showLogoutModal = false" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                       text-gray-600 font-semibold text-[13.5px]
                       cursor-pointer bg-white hover:border-gray-300
                       transition-all duration-150">
                Cancelar
              </button>
              <button @click="confirmLogout" :disabled="loggingOut" class="flex-1 py-3 rounded-2xl text-white font-bold
                       text-[13.5px] cursor-pointer border-none
                       bg-red-600 hover:bg-red-700
                       disabled:opacity-50 disabled:cursor-not-allowed
                       transition-all duration-150
                       flex items-center justify-center gap-2">
                <span v-if="loggingOut" class="w-4 h-4 border-2 border-white/30 border-t-white
                         rounded-full animate-spin" />
                {{ loggingOut ? 'Cerrando...' : 'Sí, cerrar sesión' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, provide, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'
import {
  Bars3Icon, ClockIcon,
  ArrowRightOnRectangleIcon, ArrowTopRightOnSquareIcon,
  HomeIcon, ClipboardDocumentListIcon,
  TagIcon, BanknotesIcon, UsersIcon,
  ChartBarIcon, StarIcon, CpuChipIcon, MapPinIcon,
} from '@heroicons/vue/24/outline'

// ── Stores ────────────────────────────────────────────────
const route = useRoute()
const router = useRouter()
const admin = useAdminStore()

// ── Estado ────────────────────────────────────────────────
const sidebarOpen = ref(false)
const currentTime = ref('')
const showLogoutModal = ref(false)
const loggingOut = ref(false)

// ── Provide CTA (para vistas que lo necesiten) ────────────
const ctaHandler = ref<(() => void) | null>(null)
provide('registerCta', (fn: () => void) => { ctaHandler.value = fn })

// ── Logout ────────────────────────────────────────────────
function handleLogout() { showLogoutModal.value = true }

async function confirmLogout() {
  loggingOut.value = true
  try {
    await admin.logout()
    router.push('/admin/login')
  } catch {
    router.push('/admin/login')
  } finally {
    loggingOut.value = false
    showLogoutModal.value = false
  }
}

// ── Reloj ─────────────────────────────────────────────────
let clockTimer: ReturnType<typeof setInterval> | null = null

function updateTime() {
  currentTime.value = new Date().toLocaleTimeString('es-PE', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
  })
}

onMounted(() => { updateTime(); clockTimer = setInterval(updateTime, 1_000) })
onUnmounted(() => { if (clockTimer) clearInterval(clockTimer) })

// ── Soporte WhatsApp ──────────────────────────────────────
const supportLink = computed(() => {
  const phone = (import.meta.env.VITE_WA_PHONE ?? '51969943657').replace(/\D/g, '')
  const nombre = admin.user?.name ?? 'Admin'
  // ← MARCA: reemplaza el nombre del negocio en el mensaje
  const mensaje = `Hola, soy *${nombre}* (Panel Admin de la Florería). Necesito soporte técnico.`
  return `https://wa.me/${phone}?text=${encodeURIComponent(mensaje)}`
})

// ── Badge de rol ──────────────────────────────────────────
const roleBadgeClass = computed(() => {
  const map: Record<string, string> = {
    super_admin: 'bg-yellow-50 text-yellow-700 border border-yellow-200',
    admin: 'bg-blue-50   text-blue-700   border border-blue-200',
    cajero: 'bg-green-50  text-green-700  border border-green-200',
    sistema: 'bg-purple-50 text-purple-700 border border-purple-200',
  }
  return map[admin.role ?? ''] ?? 'bg-gray-50 text-gray-600 border border-gray-200'
})

const roleLabel = computed(() => {
  const map: Record<string, string> = {
    super_admin: 'Super Admin',
    admin: 'Administrador',
    cajero: 'Cajero',
    sistema: 'Sistema',
  }
  return map[admin.role ?? ''] ?? 'Usuario'
})

const roleIcon = computed(() => {
  const map: Record<string, any> = {
    super_admin: StarIcon,
    admin: CpuChipIcon,
    cajero: BanknotesIcon,
    sistema: CpuChipIcon,
  }
  return map[admin.role ?? ''] ?? UsersIcon
})

// ── Navegación dinámica por rol ───────────────────────────
interface NavItem { to: string; icon: any; label: string; badge?: string }
interface NavGroup { label: string; items: NavItem[] }

const NAV_GROUPS = computed((): NavGroup[] => {
  const groups: NavGroup[] = []

  // ── Principal ──────────────────────────────────────────
  const principal: NavItem[] = []

  if (admin.can.dashboard) {
    principal.push({
      to: '/admin/dashboard', icon: HomeIcon, label: 'Dashboard',
    })
  }
  if (admin.can.orders) {
    principal.push({
      to: '/admin/pedidos', icon: ClipboardDocumentListIcon, label: 'Pedidos',
    })
  }

  if (principal.length) groups.push({ label: 'Principal', items: principal })

  // ── Gestión ────────────────────────────────────────────
  const gestion: NavItem[] = []

  if (admin.can.catalog) {
    gestion.push({ to: '/admin/catalogo', icon: TagIcon, label: 'Catálogo' })
  }
  if (admin.can.caja) {
    gestion.push({ to: '/admin/caja', icon: BanknotesIcon, label: 'Caja' })
  }
  if (admin.can.clients) {
    gestion.push({ to: '/admin/clientes', icon: UsersIcon, label: 'Clientes' })
  }

  // ── Zonas de delivery (borra este bloque si no usas delivery) ──
  if (admin.can.zones) {
    gestion.push({
      to: '/admin/delivery-zones', icon: MapPinIcon, label: 'Zonas delivery',
    })
  }

  if (gestion.length) groups.push({ label: 'Gestión', items: gestion })

  // ── Análisis ───────────────────────────────────────────
  const analisis: NavItem[] = []

  if (admin.can.reports) {
    analisis.push({
      to: '/admin/reportes', icon: ChartBarIcon, label: 'Reportes',
    })
  }
  if (admin.can.users) {
    analisis.push({
      to: '/admin/usuarios', icon: UsersIcon, label: 'Usuarios',
    })
  }
  if (admin.can.sistema) {
    analisis.push({
      to: '/admin/sistema', icon: CpuChipIcon, label: 'Sistema',
    })
  }

  if (analisis.length) groups.push({ label: 'Análisis', items: analisis })

  return groups
})

// ── Meta por ruta ─────────────────────────────────────────
const META: Record<string, { title: string; sub: string }> = {
  '/admin/dashboard': { title: 'Dashboard', sub: 'Vista general del negocio' },
  '/admin/pedidos': { title: 'Pedidos', sub: 'Gestión de pedidos activos' },
  '/admin/catalogo': { title: 'Catálogo', sub: 'Gestión de productos' },
  '/admin/caja': { title: 'Caja', sub: 'Control de efectivo del día' },
  '/admin/clientes': { title: 'Clientes', sub: 'Base de datos de clientes' },
  '/admin/reportes': { title: 'Reportes', sub: 'Análisis e inteligencia' },
  '/admin/usuarios': { title: 'Usuarios', sub: 'Gestión de accesos y roles' },
  '/admin/sistema': { title: 'Sistema', sub: 'Comisiones y configuración' },
  '/admin/delivery-zones': { title: 'Zonas delivery', sub: 'Gestión de zonas delivery' },
}

const currentMeta = computed(() => META[route.path] ?? { title: 'Admin', sub: '' })

// ── Helpers ───────────────────────────────────────────────
const initials = computed(() => {
  const name = admin.user?.name ?? 'Admin'
  return name.split(' ').map((w: string) => w[0] ?? '').join('').slice(0, 2).toUpperCase()
})

function isActive(path: string): boolean {
  return route.path === path || route.path.startsWith(path + '/')
}
</script>