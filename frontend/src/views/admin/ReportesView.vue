<template>
  <div class="flex flex-col gap-5">

    <!-- ══ TABS ══ -->
    <div class="flex gap-1.5 bg-gray-100 p-1 rounded-2xl w-fit">
      <button v-for="t in TABS" :key="t.value" @click="tab = t.value" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px]
               font-semibold transition-all duration-150 border-none cursor-pointer" :class="tab === t.value
                ? 'bg-white text-gray-900 shadow-sm border border-gray-200'
                : 'bg-transparent text-gray-500 hover:text-gray-700'">
        <component :is="t.icon" class="w-4 h-4" />
        {{ t.label }}
      </button>
    </div>

    <!-- ══ TAB VENTAS ══ -->
    <div v-if="tab === 'ventas'">

      <!-- KPIs -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div v-if="loading" v-for="n in 4" :key="n" class="h-28 rounded-2xl bg-gray-100 animate-pulse" />

        <div v-else v-for="kpi in KPIs" :key="kpi.label" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4
                 flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
              {{ kpi.label }}
            </span>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" :class="kpi.bgIcon">
              <component :is="kpi.icon" class="w-4 h-4" :class="kpi.iconColor" />
            </div>
          </div>
          <div>
            <p class="font-black text-[26px] text-gray-900 leading-none m-0"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ kpi.value }}
            </p>
            <p class="text-[11.5px] m-0 mt-1 flex items-center gap-1" :class="kpi.subColor ?? 'text-gray-400'">
              <component v-if="kpi.subIcon" :is="kpi.subIcon" class="w-3 h-3" />
              {{ kpi.sub }}
            </p>
          </div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

        <!-- Últimos 7 días -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
                <ChartBarIcon class="w-4 h-4 text-brand-red" />
              </div>
              <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                Últimos 7 días
              </h3>
            </div>
            <span class="text-[12px] font-bold text-gray-400">
              S/ {{ formatMonto(salesData?.sales_week ?? 0) }}
            </span>
          </div>

          <div class="p-5">
            <div v-if="loading" class="h-40 bg-gray-50 rounded-xl animate-pulse" />
            <div v-else-if="!last7.length" class="h-40 flex items-center justify-center text-gray-400 text-[13px]">
              Sin datos
            </div>
            <div v-else class="flex items-end gap-2 h-44">
              <div v-for="d in last7" :key="d.date" class="flex-1 flex flex-col items-center gap-1.5 group">
                <span class="text-[10px] font-bold text-gray-400
                             opacity-0 group-hover:opacity-100 transition-opacity">
                  S/{{ Math.round(d.total) }}
                </span>
                <div class="w-full rounded-t-xl transition-all duration-500
                            bg-brand-red/20 group-hover:bg-brand-red/40
                            relative overflow-hidden" :style="`height: ${barHeight(d.total, last7)}px`">
                  <div class="absolute inset-0 bg-brand-red opacity-0
                              group-hover:opacity-20 transition-opacity" />
                </div>
                <span class="text-[10.5px] font-semibold text-gray-500">
                  {{ d.label }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Por tipo de pedido -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
              <PieChartIcon class="w-4 h-4 text-blue-500" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Por tipo de pedido
            </h3>
          </div>
          <div class="p-5 flex flex-col gap-3">
            <div v-if="loading" v-for="n in 3" :key="n" class="h-10 rounded-xl bg-gray-50 animate-pulse" />
            <div v-else-if="!byType.length" class="py-8 text-center text-gray-400 text-[13px]">
              Sin datos
            </div>
            <div v-else v-for="t in byTypeEnriquecido" :key="t.type" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50
                     transition-colors duration-100">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="t.bgIcon">
                <component :is="t.icon" class="w-4 h-4" :class="t.iconColor" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center mb-1.5">
                  <span class="text-[13px] font-semibold text-gray-800">
                    {{ t.label }}
                  </span>
                  <span class="text-[12px] font-bold text-gray-500">
                    {{ t.count }} pedidos · S/ {{ formatMonto(t.total) }}
                  </span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-600" :class="t.barColor"
                    :style="`width: ${byTypeMax > 0 ? (t.total / byTypeMax * 100) : 0}%`" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top productos -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-yellow-50 flex items-center justify-center">
              <TrophyIcon class="w-4 h-4 text-yellow-500" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Top productos
            </h3>
          </div>
          <span class="text-[12px] text-gray-400 font-medium">
            Este mes
          </span>
        </div>

        <div class="p-5">
          <div v-if="loading" class="flex flex-col gap-4">
            <div v-for="n in 5" :key="n" class="h-8 bg-gray-50 rounded-xl animate-pulse" />
          </div>
          <div v-else-if="!topProducts.length" class="py-10 text-center text-gray-400 text-[13px]">
            Sin datos de ventas aún
          </div>
          <div v-else class="flex flex-col gap-4">
            <div v-for="(p, i) in topProducts" :key="p.product" class="flex items-center gap-4">
              <!-- Rank -->
              <div class="w-7 h-7 rounded-full flex items-center justify-center
                          text-[12px] font-black shrink-0" :class="i === 0 ? 'bg-yellow-400 text-yellow-900'
                            : i === 1 ? 'bg-gray-200 text-gray-600'
                              : i === 2 ? 'bg-orange-200 text-orange-700'
                                : 'bg-gray-100 text-gray-500'">
                {{ i < 3 ? ['🥇', '🥈', '🥉'][i] : i + 1 }} </div>

                  <!-- Barra -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-[13.5px] font-semibold text-gray-900">
                        {{ p.emoji }} {{ p.product }}
                      </span>
                      <div class="flex items-center gap-3 shrink-0">
                        <span class="text-[11.5px] font-semibold text-green-600">
                          S/ {{ formatMonto(p.revenue) }}
                        </span>
                        <span class="text-[12px] text-gray-400">
                          {{ p.qty }} uds
                        </span>
                      </div>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                      <div class="h-full rounded-full transition-all duration-700" :class="i === 0 ? 'bg-brand-red'
                        : i === 1 ? 'bg-red-400'
                          : 'bg-red-200'"
                        :style="`width: ${topProducts[0]?.qty > 0 ? (p.qty / topProducts[0].qty * 100) : 0}%`" />
                    </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ TAB PERSONALIZACIONES ══ -->
      <div v-if="tab === 'personalizaciones'">

        <!-- Info -->
        <div class="flex items-start gap-3 px-4 py-3.5 rounded-2xl
                  bg-blue-50 border border-blue-100 mb-5">
          <InformationCircleIcon class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
          <p class="text-[13px] text-blue-700 m-0">
            Análisis basado en
            <strong>{{ customData?.total_items_analizados ?? 0 }} items</strong>
            con personalización registrada.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

          <!-- Cremas -->
          <RankingCard title="Cremas más pedidas" icon="🫙" :items="salsas" :loading="loadingCustom"
            color-class="bg-orange-500" empty-text="Sin datos de cremas" />

          <!-- Ensaladas -->
          <RankingCard title="Ensaladas preferidas" icon="🥗" :items="ensaladas" :loading="loadingCustom"
            color-class="bg-green-500" empty-text="Sin datos de ensaladas" />

          <!-- Papas -->
          <RankingCard title="Tipo de papas" icon="🍟" :items="papas" :loading="loadingCustom"
            color-class="bg-yellow-500" empty-text="Sin datos de papas" />

          <!-- Término -->
          <RankingCard title="Término de cocción" icon="🔥" :items="terminos" :loading="loadingCustom"
            color-class="bg-red-500" empty-text="Sin datos de término" />
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, defineComponent, h } from 'vue'
import api from '@/utils/api'
import {
  ChartBarIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ShoppingBagIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ReceiptPercentIcon,
  InformationCircleIcon,
  CalendarDaysIcon,
  ClockIcon,
  TruckIcon,
  BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline'

// ── Ícono Pie (heroicons no tiene, usamos uno simple) ─────
const PieChartIcon = defineComponent({
  setup() {
    return () => h('svg', {
      xmlns: 'http://www.w3.org/2000/svg',
      fill: 'none', viewBox: '0 0 24 24',
      'stroke-width': '1.5', stroke: 'currentColor',
    }, [
      h('path', {
        'stroke-linecap': 'round', 'stroke-linejoin': 'round',
        d: 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z',
      }),
      h('path', {
        'stroke-linecap': 'round', 'stroke-linejoin': 'round',
        d: 'M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z',
      }),
    ])
  },
})

// ── Componente RankingCard reutilizable ───────────────────
const RankingCard = defineComponent({
  props: {
    title: { type: String, required: true },
    icon: { type: String, required: true },
    items: { type: Array as () => Array<{ name: string; qty: number }>, default: () => [] },
    loading: { type: Boolean, default: false },
    colorClass: { type: String, default: 'bg-brand-red' },
    emptyText: { type: String, default: 'Sin datos' },
  },
  setup(props) {
    const max = computed(() => props.items[0]?.qty ?? 1)
    return () => h('div', {
      class: 'bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden',
    }, [
      h('div', {
        class: 'flex items-center gap-3 px-5 py-4 border-b border-gray-100',
      }, [
        h('span', { class: 'text-xl' }, props.icon),
        h('h3', {
          class: 'font-black text-[15px] text-gray-900 m-0',
          style: "font-family:'Plus Jakarta Sans',sans-serif;",
        }, props.title),
      ]),
      h('div', { class: 'p-5 flex flex-col gap-3.5' },
        props.loading
          ? Array.from({ length: 4 }, (_, i) =>
            h('div', { key: i, class: 'h-7 bg-gray-50 rounded-xl animate-pulse' })
          )
          : !props.items.length
            ? [h('div', {
              class: 'py-8 text-center text-gray-400 text-[13px]',
            }, props.emptyText)]
            : props.items.map((item, i) =>
              h('div', { key: item.name, class: 'flex flex-col gap-1.5' }, [
                h('div', { class: 'flex justify-between items-center' }, [
                  h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                      class: 'text-[11px] font-black text-gray-400 w-5 text-right',
                    }, `${i + 1}`),
                    h('span', {
                      class: 'text-[13px] font-semibold text-gray-800',
                    }, item.name),
                  ]),
                  h('span', {
                    class: 'text-[12px] font-bold text-gray-500',
                  }, `${item.qty}`),
                ]),
                h('div', { class: 'h-1.5 bg-gray-100 rounded-full overflow-hidden ml-7' }, [
                  h('div', {
                    class: `h-full rounded-full transition-all duration-600 ${props.colorClass}`,
                    style: `width: ${(item.qty / max.value) * 100}%; opacity: ${1 - i * 0.12}`,
                  }),
                ]),
              ])
            )
      ),
    ])
  },
})

// ── Tabs ──────────────────────────────────────────────────
const TABS = [
  { value: 'ventas', label: 'Ventas', icon: ChartBarIcon },
  { value: 'personalizaciones', label: 'Personalizaciones', icon: ReceiptPercentIcon },
]

// ── Estado ────────────────────────────────────────────────
const tab = ref('ventas')
const salesData = ref<any>(null)
const customData = ref<any>(null)
const loading = ref(false)
const loadingCustom = ref(false)

// ── Computed ventas ───────────────────────────────────────
const last7 = computed(() => salesData.value?.last_7_days ?? [])
const topProducts = computed(() => salesData.value?.top_products ?? [])
const byType = computed(() => salesData.value?.by_type ?? [])
const byTypeMax = computed(() => Math.max(...byType.value.map((t: any) => t.total), 1))

const TYPE_CONFIG: Record<string, any> = {
  llevar: { label: 'Para llevar', icon: ShoppingBagIcon, bgIcon: 'bg-red-50', iconColor: 'text-red-500', barColor: 'bg-red-500' },
  delivery: { label: 'Delivery', icon: TruckIcon, bgIcon: 'bg-blue-50', iconColor: 'text-blue-500', barColor: 'bg-blue-500' },
  local: { label: 'En local', icon: BuildingStorefrontIcon, bgIcon: 'bg-green-50', iconColor: 'text-green-500', barColor: 'bg-green-500' },
}

const byTypeEnriquecido = computed(() =>
  byType.value.map((t: any) => ({
    ...t,
    ...(TYPE_CONFIG[t.type] ?? {
      label: t.type, icon: ShoppingBagIcon,
      bgIcon: 'bg-gray-50', iconColor: 'text-gray-400', barColor: 'bg-gray-400',
    }),
  }))
)

const growth = computed(() => salesData.value?.growth_pct ?? 0)

const KPIs = computed(() => {
  const s = salesData.value
  return [
    {
      label: 'Ventas hoy',
      value: s ? `S/ ${formatMonto(s.sales_today)}` : '—',
      sub: growth.value > 0
        ? `+${growth.value}% vs ayer`
        : growth.value < 0
          ? `${growth.value}% vs ayer`
          : 'Sin cambio vs ayer',
      subColor: growth.value > 0 ? 'text-green-500' : growth.value < 0 ? 'text-red-400' : 'text-gray-400',
      subIcon: growth.value > 0 ? ArrowTrendingUpIcon : growth.value < 0 ? ArrowTrendingDownIcon : null,
      icon: CurrencyDollarIcon,
      bgIcon: 'bg-red-50',
      iconColor: 'text-brand-red',
    },
    {
      label: 'Ventas semana',
      value: s ? `S/ ${formatMonto(s.sales_week)}` : '—',
      sub: `${s?.orders_today ?? 0} pedidos hoy`,
      icon: CalendarDaysIcon,
      bgIcon: 'bg-green-50',
      iconColor: 'text-green-500',
    },
    {
      label: 'Ventas mes',
      value: s ? `S/ ${formatMonto(s.sales_month)}` : '—',
      sub: `${s?.orders_month ?? 0} pedidos este mes`,
      icon: ChartBarIcon,
      bgIcon: 'bg-blue-50',
      iconColor: 'text-blue-500',
    },
    {
      label: 'Ticket promedio',
      value: s ? `S/ ${formatMonto(s.avg_ticket)}` : '—',
      sub: 'por pedido este mes',
      icon: ReceiptPercentIcon,
      bgIcon: 'bg-yellow-50',
      iconColor: 'text-yellow-500',
    },
  ]
})

// ── Computed personalizaciones ────────────────────────────
const salsas = computed(() => customData.value?.salsas ?? [])
const ensaladas = computed(() => customData.value?.ensaladas ?? [])
const papas = computed(() => customData.value?.papas ?? [])
const terminos = computed(() => customData.value?.terminos ?? [])

// ── Helpers ───────────────────────────────────────────────
function formatMonto(n: number): string {
  return n.toLocaleString('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function barHeight(val: number, arr: any[]): number {
  const max = Math.max(...arr.map(d => d.total), 1)
  return Math.max((val / max) * 140, 4)
}

// ── API ───────────────────────────────────────────────────
async function fetchSales() {
  if (salesData.value) return
  loading.value = true
  try {
    const { data } = await api.get('/admin/reports/sales')
    salesData.value = data.data
  } catch (e) {
    console.error('Error cargando ventas:', e)
  } finally {
    loading.value = false
  }
}

async function fetchCustom() {
  if (customData.value) return
  loadingCustom.value = true
  try {
    const { data } = await api.get('/admin/reports/customizations')
    customData.value = data.data
  } catch (e) {
    console.error('Error cargando personalizaciones:', e)
  } finally {
    loadingCustom.value = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => fetchSales())

watch(tab, (val) => {
  if (val === 'ventas') fetchSales()
  if (val === 'personalizaciones') fetchCustom()
})
</script>