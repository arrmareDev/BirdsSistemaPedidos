<template>
  <div class="flex flex-col gap-5">

    <!-- ══ KPIs ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-if="dashStore.loading" v-for="n in 4" :key="n" class="h-28 rounded-2xl bg-gray-100 animate-pulse" />

      <template v-else>
        <div v-for="s in computedStats" :key="s.label" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                 hover:shadow-md hover:-translate-y-0.5
                 transition-all duration-200 flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
              {{ s.label }}
            </span>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" :class="s.bgClass">
              <component :is="s.icon" class="w-4 h-4" :class="s.iconColor" />
            </div>
          </div>
          <div>
            <p class="font-black text-[28px] text-gray-900 leading-none m-0"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ s.value }}
            </p>
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
            Top Productos
          </h3>
        </div>
        <div class="p-5 flex flex-col gap-4">
          <div v-if="dashStore.loading" v-for="n in 5" :key="n" class="h-7 rounded-xl bg-gray-100 animate-pulse" />
          <div v-else-if="topProducts.length === 0" class="py-8 text-center text-gray-400 text-[13px]">
            Sin datos aún
          </div>
          <div v-else v-for="(p, i) in topProducts" :key="p.product" class="flex flex-col gap-1.5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-[11px] font-black text-gray-400 w-5 text-right shrink-0">
                  {{ i + 1 }}
                </span>
                <span class="text-[13.5px] font-semibold text-gray-900">
                  {{ p.product }}
                </span>
              </div>
              <div class="flex items-center gap-3 shrink-0">
                <span class="text-[11.5px] font-semibold text-green-600">
                  S/ {{ formatMonto(p.revenue) }}
                </span>
                <span class="text-[12px] text-gray-400">
                  {{ p.qty }} uds
                </span>
              </div>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden ml-7">
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
              Pedidos Activos
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
              <tr v-if="ordersStore.orders.length === 0">
                <td colspan="4" class="text-center py-10 text-gray-400 text-[13px]">
                  Sin pedidos activos
                </td>
              </tr>
              <tr v-for="o in ordersStore.orders.slice(0, 5)" :key="o.id" class="border-b border-gray-50 last:border-0 cursor-pointer
                       hover:bg-gray-50/60 transition-colors duration-100" @click="$router.push('/admin/pedidos')">
                <td class="px-5 py-3.5">
                  <span class="text-[11px] font-bold px-2 py-0.5 rounded-lg
                               bg-gray-100 text-gray-600 border border-gray-200
                               font-mono">
                    #{{ o.id }}
                  </span>
                </td>
                <td class="px-5 py-3.5 font-semibold text-[13px] text-gray-900">
                  {{ o.client_name }}
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-baseline gap-0.5">
                    <span class="text-[11px] font-semibold text-gray-400">S/</span>
                    <span class="font-black text-[15px] text-brand-red leading-none"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ parseFloat(o.total).toFixed(2) }}
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

    <!-- ══ Ventas últimos 7 días ══ -->
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
        <span class="text-[12px] font-bold text-gray-400">
          S/ {{ formatMonto(dashStore.stats?.sales_week ?? 0) }}
        </span>
      </div>

      <div class="p-5">
        <div v-if="dashStore.loading" class="h-40 bg-gray-50 rounded-xl animate-pulse" />
        <div v-else-if="!last7.length" class="h-40 flex items-center justify-center
                 text-gray-400 text-[13px]">
          Sin datos
        </div>
        <div v-else class="flex items-end gap-2 h-44">
          <div v-for="d in last7" :key="d.date" class="flex-1 flex flex-col items-center gap-1.5 group">
            <span class="text-[10px] font-bold text-gray-400
                         opacity-0 group-hover:opacity-100 transition-opacity">
              S/{{ Math.round(d.total) }}
            </span>
            <div class="w-full rounded-t-xl transition-all duration-500
                        bg-brand-red/20 group-hover:bg-brand-red/50 relative"
              :style="`height: ${barHeight(d.total, last7)}px`" />
            <span class="text-[10.5px] font-semibold text-gray-500">
              {{ d.label }}
            </span>
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
import {
  TrophyIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
  CurrencyDollarIcon,
  ShoppingBagIcon,
  CalendarDaysIcon,
  CalendarIcon,
} from '@heroicons/vue/24/outline'

const dashStore = useDashboardStore()
const ordersStore = useOrdersStore()

onMounted(() => {
  dashStore.fetch()
  ordersStore.fetch({ status: 'nuevo,confirmado,preparando,listo' })
})

// ── Computed ──────────────────────────────────────────────
const computedStats = computed(() => {
  const s = dashStore.stats
  return [
    {
      label: 'Ventas Hoy',
      value: s ? `S/ ${formatMonto(s.sales_today)}` : '—',
      sub: 'ingresos del día',
      icon: CurrencyDollarIcon,
      bgClass: 'bg-yellow-50',
      iconColor: 'text-yellow-500',
    },
    {
      label: 'Pedidos Hoy',
      value: s ? String(s.orders_today) : '—',
      sub: 'pedidos recibidos',
      icon: ShoppingBagIcon,
      bgClass: 'bg-green-50',
      iconColor: 'text-green-500',
    },
    {
      label: 'Ventas Semana',
      value: s ? `S/ ${formatMonto(s.sales_week)}` : '—',
      sub: 'últimos 7 días',
      icon: CalendarDaysIcon,
      bgClass: 'bg-blue-50',
      iconColor: 'text-blue-500',
    },
    {
      label: 'Ventas Mes',
      value: s ? `S/ ${formatMonto(s.sales_month)}` : '—',
      sub: 'este mes',
      icon: CalendarIcon,
      bgClass: 'bg-red-50',
      iconColor: 'text-brand-red',
    },
  ]
})

const topProducts = computed(() => dashStore.stats?.top_products ?? [])
const last7 = computed(() => dashStore.stats?.last_7_days ?? [])

// ── Helpers ───────────────────────────────────────────────
function formatMonto(n: number): string {
  return Number(n).toLocaleString('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function barHeight(val: number, arr: any[]): number {
  const max = Math.max(...arr.map(d => d.total), 1)
  return Math.max((val / max) * 140, 4)
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