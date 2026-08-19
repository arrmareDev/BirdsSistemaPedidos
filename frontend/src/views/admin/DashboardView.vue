<template>
  <div class="flex flex-col gap-5">

    <!-- ══ Encabezado ══ -->
    <div class="flex items-end justify-between flex-wrap gap-2">
      <div>
        <p class="text-[11px] font-black uppercase tracking-widest text-brand-red m-0 mb-1">
          {{ saludo }}
        </p>
        <h1 class="font-black text-[22px] text-gray-900 m-0 leading-none capitalize"
          style="font-family:'Plus Jakarta Sans',sans-serif;">
          {{ fechaHoy }}
        </h1>
      </div>
    </div>

    <!-- ══ KPIs ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <template v-if="dashStore.loading">
        <div v-for="n in 4" :key="n" class="h-32 rounded-2xl bg-gray-100 animate-pulse" />
      </template>

      <template v-else>
        <div v-for="s in computedStats" :key="s.label" class="relative bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200
                 flex flex-col gap-3 overflow-hidden">
          <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full opacity-[0.06]" :class="s.glow" />

          <div class="flex items-center justify-between relative">
            <span class="text-[10.5px] font-black uppercase tracking-widest text-gray-400">
              {{ s.label }}
            </span>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="s.bgClass">
              <component :is="s.icon" class="w-4 h-4" :class="s.iconColor" />
            </div>
          </div>

          <div class="relative">
            <div class="flex items-center gap-2 flex-wrap">
              <p class="font-black text-[26px] text-gray-900 leading-none m-0"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ s.value }}
              </p>
              <span v-if="s.badge"
                class="inline-flex items-center gap-0.5 text-[10.5px] font-bold px-1.5 py-0.5 rounded-md shrink-0"
                :class="s.badge.positive ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500'">
                <ArrowTrendingUpIcon v-if="s.badge.positive" class="w-3 h-3" />
                <ArrowTrendingDownIcon v-else class="w-3 h-3" />
                {{ s.badge.text }}
              </span>
            </div>
            <p class="text-[11.5px] text-gray-400 m-0 mt-1">{{ s.sub }}</p>
          </div>
        </div>
      </template>
    </div>

    <!-- ══ Grid principal ══ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

      <!-- Top productos -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
          <div class="w-8 h-8 rounded-xl bg-yellow-50 flex items-center justify-center">
            <TrophyIcon class="w-4 h-4 text-yellow-500" />
          </div>
          <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
            Top productos
          </h3>
        </div>
        <div class="p-5 flex flex-col gap-4">
          <template v-if="dashStore.loading">
            <div v-for="n in 5" :key="n" class="h-7 rounded-xl bg-gray-100 animate-pulse" />
          </template>
          <div v-else-if="topProducts.length === 0" class="py-10 flex flex-col items-center gap-2 text-center">
            <TrophyIcon class="w-8 h-8 text-gray-200" />
            <p class="text-gray-400 text-[13px] m-0">Aún no hay ventas para armar un ranking</p>
          </div>
          <div v-else v-for="(p, i) in topProducts" :key="p.product" class="flex flex-col gap-1.5">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10.5px] font-black shrink-0"
                  :class="i === 0 ? 'bg-brand-red text-white' : i === 1 ? 'bg-red-100 text-brand-red' : 'bg-gray-100 text-gray-400'">
                  {{ i + 1 }}
                </span>
                <span class="text-[13.5px] font-semibold text-gray-900 truncate">
                  {{ p.product }}
                </span>
              </div>
              <div class="flex items-center gap-3 shrink-0">
                <span class="text-[11.5px] font-bold text-green-600">
                  S/ {{ formatMonto(p.revenue) }}
                </span>
                <span class="text-[12px] text-gray-400">
                  {{ p.qty }} uds
                </span>
              </div>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden ml-[30px]">
              <div class="h-full rounded-full transition-all duration-700" :class="i === 0 ? 'bg-brand-red'
                : i === 1 ? 'bg-red-400'
                  : 'bg-red-200'" :style="`width: ${topProducts[0]?.qty > 0
                    ? (p.qty / topProducts[0].qty * 100) : 0}%`" />
            </div>
          </div>
        </div>
      </div>

      <!-- Pedidos activos -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
              <ClipboardDocumentListIcon class="w-4 h-4 text-brand-red" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Pedidos activos
            </h3>
          </div>
          <RouterLink to="/admin/pedidos" class="text-[12.5px] font-semibold text-brand-red no-underline
                   hover:underline">
            Ver todos →
          </RouterLink>
        </div>

        <div v-if="ordersStore.loading" class="p-4 flex flex-col gap-2">
          <div v-for="n in 5" :key="n" class="h-10 rounded-xl bg-gray-100 animate-pulse" />
        </div>

        <div v-else-if="ordersStore.orders.length === 0" class="py-10 flex flex-col items-center gap-2 text-center">
          <ClipboardDocumentListIcon class="w-8 h-8 text-gray-200" />
          <p class="text-gray-400 text-[13px] m-0">No hay pedidos activos en este momento</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left text-[10.5px] font-black uppercase
                           tracking-widest text-gray-400 px-5 py-3">
                  #
                </th>
                <th class="text-left text-[10.5px] font-black uppercase
                           tracking-widest text-gray-400 px-5 py-3">
                  Cliente
                </th>
                <th class="text-left text-[10.5px] font-black uppercase
                           tracking-widest text-gray-400 px-5 py-3">
                  Total
                </th>
                <th class="text-left text-[10.5px] font-black uppercase
                           tracking-widest text-gray-400 px-5 py-3">
                  Estado
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="o in ordersStore.orders.slice(0, 5)" :key="o.id" class="border-b border-gray-50 last:border-0 cursor-pointer
                       hover:bg-gray-50/60 transition-colors duration-100" @click="$router.push('/admin/pedidos')">
                <td class="px-5 py-3.5">
                  <span class="text-[11px] font-bold px-2 py-0.5 rounded-lg
                               bg-gray-100 text-gray-600 border border-gray-200
                               font-mono">
                    #{{ o.codigo }}
                  </span>
                </td>
                <td class="px-5 py-3.5 font-semibold text-[13px] text-gray-900 max-w-[140px] truncate">
                  {{ o.client_name }}
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-baseline gap-0.5">
                    <span class="text-[11px] font-semibold text-gray-400">S/</span>
                    <span class="font-black text-[15px] text-brand-red leading-none"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ o.total.toFixed(2) }}
                    </span>
                  </div>
                </td>
                <td class="px-5 py-3.5">
                  <span :class="statusCls(o.status)">
                    {{ statusLabel(o.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ Segunda fila: gráfico de ventas + por tipo de pedido ══ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

      <!-- Ventas últimos 7 días -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
              <ChartBarIcon class="w-4 h-4 text-brand-red" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Ventas últimos 7 días
            </h3>
          </div>
          <span class="text-[13px] font-black text-gray-900" style="font-family:'Plus Jakarta Sans',sans-serif;">
            S/ {{ formatMonto(dashStore.stats?.sales_week ?? 0) }}
          </span>
        </div>

        <div class="p-5 pt-6">
          <div v-if="dashStore.loading" class="h-44 bg-gray-50 rounded-xl animate-pulse" />
          <div v-else-if="!last7.length" class="h-44 flex flex-col items-center justify-center gap-2 text-center">
            <ChartBarIcon class="w-8 h-8 text-gray-200" />
            <p class="text-gray-400 text-[13px] m-0">Aún no hay ventas esta semana</p>
          </div>
          <div v-else class="flex items-end gap-2.5 h-44">
            <div v-for="d in last7" :key="d.date" class="relative flex-1 h-full flex flex-col items-center gap-2 group">

              <!-- Tooltip -->
              <div class="absolute -top-1 opacity-0 group-hover:opacity-100 group-hover:-translate-y-2
                          transition-all duration-200 pointer-events-none z-10">
                <div
                  class="bg-gray-900 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-lg relative">
                  S/ {{ formatMonto(d.total) }}
                  <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                              border-[5px] border-transparent border-t-gray-900" />
                </div>
              </div>

              <!-- Barra -->
              <div class="flex-1 w-full flex items-end justify-center">
                <div class="w-full max-w-[30px] rounded-t-lg transition-all duration-500"
                  :class="esHoy(d) ? 'bg-brand-red' : 'bg-brand-red/25 group-hover:bg-brand-red/45'"
                  :style="`height: ${barHeight(d.total, last7)}px`" />
              </div>

              <span class="text-[10.5px] font-bold capitalize" :class="esHoy(d) ? 'text-brand-red' : 'text-gray-400'">
                {{ d.label }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Ventas por tipo de pedido -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
          <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
            <Squares2X2Icon class="w-4 h-4 text-blue-500" />
          </div>
          <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
            Ventas por tipo · este mes
          </h3>
        </div>

        <div class="p-5 flex flex-col gap-4">
          <template v-if="dashStore.loading">
            <div v-for="n in 3" :key="n" class="h-10 rounded-xl bg-gray-100 animate-pulse" />
          </template>
          <div v-else-if="byTypeEnriquecido.length === 0" class="py-10 flex flex-col items-center gap-2 text-center">
            <Squares2X2Icon class="w-8 h-8 text-gray-200" />
            <p class="text-gray-400 text-[13px] m-0">Aún no hay pedidos este mes</p>
          </div>
          <div v-else v-for="t in byTypeEnriquecido" :key="t.type" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="t.bgIcon">
              <component :is="t.icon" class="w-4 h-4" :class="t.iconColor" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-1.5 gap-2">
                <span class="text-[13px] font-semibold text-gray-800">
                  {{ t.label }}
                </span>
                <span class="text-[11.5px] font-bold text-gray-500 shrink-0">
                  {{ t.count }} pedido{{ t.count !== 1 ? 's' : '' }} · S/ {{ formatMonto(t.total) }}
                </span>
              </div>
              <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700" :class="t.barColor"
                  :style="`width: ${byTypeMax > 0 ? (t.total / byTypeMax * 100) : 0}%`" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useOrdersStore } from '@/stores/orders'
import { useAdminStore } from '@/stores/admin'

import {
  TrophyIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
  CurrencyDollarIcon,
  ShoppingBagIcon,
  CalendarIcon,
  TicketIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  Squares2X2Icon,
  BuildingStorefrontIcon,
  TruckIcon,
} from '@heroicons/vue/24/outline'

const dashStore = useDashboardStore()
const ordersStore = useOrdersStore()
const adminStore = useAdminStore()

onMounted(() => {
  if (adminStore.can.reports) dashStore.fetch()
  ordersStore.fetch({ status: 'nuevo,confirmado,preparando,listo' })
})

// ── Encabezado ─────────────────────────────────────────────
const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

const fechaHoy = computed(() =>
  new Date().toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long' })
)

// ── KPIs ───────────────────────────────────────────────────
const computedStats = computed(() => {
  const s = dashStore.stats
  const growth = s?.growth_pct ?? 0

  return [
    {
      label: 'Ventas hoy',
      value: s ? `S/ ${formatMonto(s.sales_today)}` : '—',
      sub: 'ingresos del día',
      icon: CurrencyDollarIcon,
      bgClass: 'bg-yellow-50',
      iconColor: 'text-yellow-500',
      glow: 'bg-yellow-400',
      badge: s && growth !== 0
        ? { positive: growth > 0, text: `${Math.abs(growth)}%` }
        : null,
    },
    {
      label: 'Pedidos hoy',
      value: s ? String(s.orders_today) : '—',
      sub: 'pedidos recibidos',
      icon: ShoppingBagIcon,
      bgClass: 'bg-green-50',
      iconColor: 'text-green-500',
      glow: 'bg-green-400',
      badge: null,
    },
    {
      label: 'Ticket promedio',
      value: s ? `S/ ${formatMonto(s.avg_ticket)}` : '—',
      sub: 'por pedido, este mes',
      icon: TicketIcon,
      bgClass: 'bg-blue-50',
      iconColor: 'text-blue-500',
      glow: 'bg-blue-400',
      badge: null,
    },
    {
      label: 'Ventas del mes',
      value: s ? `S/ ${formatMonto(s.sales_month)}` : '—',
      sub: 'acumulado del mes',
      icon: CalendarIcon,
      bgClass: 'bg-red-50',
      iconColor: 'text-brand-red',
      glow: 'bg-brand-red',
      badge: null,
    },
  ]
})

const topProducts = computed(() => dashStore.stats?.top_products ?? [])
const last7 = computed(() => dashStore.stats?.last_7_days ?? [])

// ── Por tipo de pedido ───────────────────────────────────
const TYPE_META: Record<string, { label: string; icon: any; bgIcon: string; iconColor: string; barColor: string }> = {
  local: { label: 'Local', icon: BuildingStorefrontIcon, bgIcon: 'bg-purple-50', iconColor: 'text-purple-500', barColor: 'bg-purple-400' },
  recoger: { label: 'Recoger', icon: ShoppingBagIcon, bgIcon: 'bg-amber-50', iconColor: 'text-amber-500', barColor: 'bg-amber-400' },
  delivery: { label: 'Delivery', icon: TruckIcon, bgIcon: 'bg-brand-red/10', iconColor: 'text-brand-red', barColor: 'bg-brand-red' },
}

const byTypeEnriquecido = computed(() => {
  const byType = dashStore.stats?.by_type ?? []
  return byType.map(t => ({
    ...t,
    ...(TYPE_META[t.type] ?? { label: t.type, icon: Squares2X2Icon, bgIcon: 'bg-gray-100', iconColor: 'text-gray-500', barColor: 'bg-gray-400' }),
  }))
})

const byTypeMax = computed(() =>
  Math.max(...byTypeEnriquecido.value.map(t => t.total), 1)
)

// ── Helpers ───────────────────────────────────────────────
function formatMonto(n: number): string {
  return Number(n).toLocaleString('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function barHeight(val: number, arr: Array<{ total: number }>): number {
  const max = Math.max(...arr.map(d => d.total), 1)
  return Math.max((val / max) * 140, 4)
}

function esHoy(d: { date: string }): boolean {
  return d.date === new Date().toISOString().split('T')[0]
}

function statusCls(s: string): string {
  const m: Record<string, string> = {
    nuevo: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200',
    confirmado: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200',
    preparando: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200',
    listo: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200',
    en_camino: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200',
    entregado: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-500 border border-gray-200',
  }
  return m[s] ?? m.entregado
}

function statusLabel(s: string): string {
  const m: Record<string, string> = {
    nuevo: 'Nuevo',
    confirmado: 'Confirmado',
    preparando: 'Preparando',
    listo: 'Listo',
    en_camino: 'En camino',
    entregado: 'Entregado',
  }
  return m[s] ?? s
}
</script>