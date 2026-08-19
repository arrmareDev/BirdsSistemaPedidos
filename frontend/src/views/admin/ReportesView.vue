<template>
  <div class="flex flex-col gap-5">

    <!-- ══ Encabezado ══ -->
    <div>
      <p class="text-[11px] font-black uppercase tracking-widest text-brand-red m-0 mb-1">
        Análisis del negocio
      </p>
      <h1 class="font-black text-[22px] text-gray-900 m-0 leading-none"
        style="font-family:'Plus Jakarta Sans',sans-serif;">
        Reportes
      </h1>
    </div>

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

    <!-- ══ TAB VENTAS HISTÓRICAS ══ -->
    <div v-if="tab === 'ventas'" class="flex flex-col gap-5">

      <!-- Selector de granularidad + período -->
      <div
        class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex gap-1 p-1 rounded-xl bg-gray-100">
          <button v-for="p in PERIODOS" :key="p.value" @click="periodo = p.value"
            class="px-4 py-1.5 rounded-lg text-[12.5px] font-bold border-none cursor-pointer transition-colors"
            :class="periodo === p.value ? 'bg-white shadow-sm text-gray-900' : 'bg-transparent text-gray-500 hover:text-gray-700'">
            {{ p.label }}
          </button>
        </div>

        <div v-if="periodo !== 'anio'" class="flex items-center gap-2">
          <select v-if="periodo !== 'mes'" v-model.number="mesSeleccionado" class="px-3 py-2 rounded-xl border-2 border-gray-100 bg-gray-50 text-[13px] font-semibold
                   text-gray-700 outline-none cursor-pointer focus:border-brand-red transition-all duration-150">
            <option v-for="(m, i) in NOMBRES_MES" :key="i" :value="i + 1">{{ m }}</option>
          </select>
          <select v-model.number="anioSeleccionado" class="px-3 py-2 rounded-xl border-2 border-gray-100 bg-gray-50 text-[13px] font-semibold
                   text-gray-700 outline-none cursor-pointer focus:border-brand-red transition-all duration-150">
            <option v-for="a in aniosDisponibles" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
      </div>

      <!-- Resumen comparativo -->
      <div v-if="!loadingHistorico && historico" class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1.5">
            {{ periodo === 'anio' ? 'Total histórico' : `${etiquetaPeriodo} ${historico.anio_actual}` }}
          </p>
          <p class="font-black text-[24px] text-gray-900 leading-none m-0"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            S/ {{ formatMonto(periodo === 'anio' ? (historico.total ?? 0) : (historico.total_actual ?? 0)) }}
          </p>
        </div>
        <div v-if="periodo !== 'anio'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1.5">
            {{ etiquetaPeriodo }} {{ historico.anio_anterior }}
          </p>
          <p class="font-black text-[24px] text-gray-500 leading-none m-0"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            S/ {{ formatMonto(historico.total_anterior ?? 0) }}
          </p>
        </div>
        <div v-if="periodo !== 'anio'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1.5">
            Variación
          </p>
          <div class="flex items-center gap-1.5">
            <component :is="variacionPct >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="w-5 h-5"
              :class="variacionPct >= 0 ? 'text-green-500' : 'text-red-400'" />
            <p class="font-black text-[24px] leading-none m-0"
              :class="variacionPct >= 0 ? 'text-green-600' : 'text-red-500'"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ variacionPct >= 0 ? '+' : '' }}{{ variacionPct }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Gráfico comparativo -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
              <ChartBarIcon class="w-4 h-4 text-brand-red" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ periodo === 'anio' ? 'Ventas por año' : `Comparativa por ${etiquetaPeriodo.toLowerCase()}` }}
            </h3>
          </div>
          <div v-if="periodo !== 'anio'" class="flex items-center gap-3 text-[11.5px] font-semibold">
            <span class="flex items-center gap-1.5 text-gray-700">
              <span class="w-2.5 h-2.5 rounded-sm bg-brand-red" /> {{ historico?.anio_actual }}
            </span>
            <span class="flex items-center gap-1.5 text-gray-400">
              <span class="w-2.5 h-2.5 rounded-sm bg-gray-300" /> {{ historico?.anio_anterior }}
            </span>
          </div>
        </div>

        <div class="px-5 pb-5 pt-14">
          <div v-if="loadingHistorico" class="h-56 bg-gray-50 rounded-xl animate-pulse" />

          <div v-else-if="periodo === 'anio'" class="flex items-end gap-3 h-56">
            <div v-if="!historico?.series?.length"
              class="w-full h-full flex flex-col items-center justify-center gap-2 text-center">
              <ChartBarIcon class="w-8 h-8 text-gray-200" />
              <p class="text-gray-400 text-[13px] m-0">Todavía no hay ventas registradas</p>
            </div>
            <div v-else v-for="s in historico.series" :key="s.clave"
              class="relative flex-1 h-full flex flex-col items-center gap-2 group">
              <div class="absolute -top-1 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 group-hover:-translate-y-2
                          transition-all duration-200 pointer-events-none z-10">
                <div
                  class="bg-gray-900 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg whitespace-nowrap shadow-lg relative">
                  S/ {{ formatMonto(s.total) }}
                  <div
                    class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-[5px] border-transparent border-t-gray-900" />
                </div>
              </div>
              <div class="flex-1 w-full flex items-end justify-center">
                <div class="w-full max-w-[48px] rounded-t-lg bg-brand-red transition-all duration-500"
                  :style="`height: ${barHeight(s.total, historico.series)}px`" />
              </div>
              <span class="text-[11.5px] font-bold text-gray-500">{{ s.clave }}</span>
            </div>
          </div>

          <div
            v-else-if="!historico?.series_actual?.some(s => s.total > 0) && !historico?.series_anterior?.some(s => s.total > 0)"
            class="h-56 flex flex-col items-center justify-center gap-2 text-center">
            <ChartBarIcon class="w-8 h-8 text-gray-200" />
            <p class="text-gray-400 text-[13px] m-0">Sin ventas en este período, en ninguno de los 2 años</p>
          </div>

          <div v-else class="flex items-end gap-2 sm:gap-3 h-56 overflow-visible">
            <div v-for="(s, i) in historico?.series_actual ?? []" :key="s.clave"
              class="relative flex-1 min-w-[28px] h-full flex flex-col items-center gap-2 group">

              <!-- TOOLTIP -->
              <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2
             opacity-0 group-hover:opacity-100
             transition-all duration-200
             pointer-events-none z-50">
                <div class="bg-gray-900 text-white text-[10.5px] font-bold
               px-2.5 py-1.5 rounded-lg whitespace-nowrap
               shadow-lg relative">
                  {{ historico?.anio_actual }}: S/ {{ formatMonto(s.total) }}<br />
                  {{ historico?.anio_anterior }}:
                  S/ {{ formatMonto(historico?.series_anterior?.[i]?.total ?? 0) }}

                  <!-- Flecha del tooltip -->
                  <div class="absolute left-1/2 -translate-x-1/2 top-full
                 w-0 h-0 border-[5px] border-transparent
                 border-t-gray-900"></div>
                </div>
              </div>

              <!-- BARRAS -->
              <div class="flex-1 w-full flex items-end justify-center gap-0.5">
                <div class="flex-1 max-w-[14px] rounded-t-md bg-gray-300 transition-all duration-500" :style="`height: ${barHeight(
                  historico?.series_anterior?.[i]?.total ?? 0,
                  todasLasSeries
                )}px`"></div>

                <div class="flex-1 max-w-[14px] rounded-t-md bg-brand-red transition-all duration-500"
                  :style="`height: ${barHeight(s.total, todasLasSeries)}px`"></div>
              </div>

              <!-- ETIQUETA DEL DÍA -->
              <span class="text-[9.5px] font-bold text-gray-400 truncate w-full text-center">
                {{ s.clave }}
              </span>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ TAB PERSONALIZACIONES ══ -->
    <div v-if="tab === 'personalizaciones'" class="flex flex-col gap-5">

      <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white border border-gray-100 shadow-sm w-fit">
        <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
          <ClipboardDocumentCheckIcon class="w-4 h-4 text-brand-red" />
        </div>
        <div>
          <p class="font-black text-[18px] text-gray-900 leading-none m-0"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            {{ customData?.total_items_analizados ?? 0 }}
          </p>
          <p class="text-[10.5px] text-gray-400 m-0 mt-0.5">items analizados</p>
        </div>
      </div>

      <div v-if="!loadingCustom && seccionesData.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 flex flex-col items-center gap-3 text-center">
        <AdjustmentsHorizontalIcon class="w-10 h-10 text-gray-200" />
        <div>
          <p class="font-bold text-[15px] text-gray-700 m-0">Todavía no hay datos que analizar</p>
          <p class="text-[13px] text-gray-400 m-0 mt-1 max-w-sm">
            En cuanto tus clientes empiecen a personalizar sus pedidos, el ranking aparece aquí solo.
          </p>
        </div>
      </div>

      <div v-else-if="loadingCustom" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div v-for="n in 4" :key="n" class="h-64 rounded-2xl bg-gray-100 animate-pulse" />
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div v-for="sec in seccionesEnriquecidas" :key="sec.seccion"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="sec.bgIcon">
                <AdjustmentsHorizontalIcon class="w-4 h-4" :class="sec.iconColor" />
              </div>
              <h3 class="font-black text-[15px] text-gray-900 m-0 truncate"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ sec.label }}
              </h3>
            </div>
            <span class="text-[11.5px] font-bold text-gray-400 shrink-0">
              {{ sec.totalElecciones }} elecciones
            </span>
          </div>

          <div class="p-5 flex flex-col gap-4">
            <div v-if="sec.options.length === 0" class="py-6 text-center text-gray-400 text-[13px]">
              Sin datos de {{ sec.label.toLowerCase() }}
            </div>

            <div v-else v-for="(opt, i) in sec.options" :key="opt.name" class="flex flex-col gap-1.5">
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                  <span v-if="i === 0" class="w-5 h-5 rounded-full bg-amber-100 text-amber-600 flex items-center
                               justify-center shrink-0" title="La más elegida">
                    <StarIcon class="w-3 h-3" />
                  </span>
                  <span v-else class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 flex items-center
                               justify-center text-[10px] font-black shrink-0">
                    {{ i + 1 }}
                  </span>
                  <span class="text-[13px] font-semibold truncate" :class="i === 0 ? 'text-gray-900' : 'text-gray-700'">
                    {{ opt.name }}
                  </span>
                </div>
                <span class="text-[11.5px] font-bold shrink-0" :class="i === 0 ? 'text-gray-900' : 'text-gray-400'">
                  {{ opt.qty }} · {{ opt.pct }}%
                </span>
              </div>
              <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden ml-[30px]">
                <div class="h-full rounded-full transition-all duration-700"
                  :class="i === 0 ? sec.barColor : 'bg-gray-300'" :style="`width: ${opt.pct}%`" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import api from '@/utils/api'
import {
  AdjustmentsHorizontalIcon,
  ClipboardDocumentCheckIcon,
  StarIcon,
  ChartBarIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline'

// ── Tabs ──────────────────────────────────────────────────
const TABS = [
  { value: 'ventas' as const, label: 'Ventas históricas', icon: ChartBarIcon },
  { value: 'personalizaciones' as const, label: 'Personalizaciones', icon: AdjustmentsHorizontalIcon },
]
const tab = ref<'ventas' | 'personalizaciones'>('ventas')

// ══════════════════════════════════════════════════════════
// TAB VENTAS HISTÓRICAS
// ══════════════════════════════════════════════════════════
interface SerieItem { clave: string; total: number }
interface Historico {
  periodo: string
  anio_actual?: number
  anio_anterior?: number
  mes?: number
  series_actual?: SerieItem[]
  series_anterior?: SerieItem[]
  series?: SerieItem[]
  total_actual?: number
  total_anterior?: number
  total?: number
}

const PERIODOS = [
  { value: 'dia' as const, label: 'Día' },
  { value: 'semana' as const, label: 'Semana' },
  { value: 'mes' as const, label: 'Mes' },
  { value: 'anio' as const, label: 'Año' },
]
const NOMBRES_MES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']

const periodo = ref<'dia' | 'semana' | 'mes' | 'anio'>('mes')
const anioSeleccionado = ref(new Date().getFullYear())
const mesSeleccionado = ref(new Date().getMonth() + 1)
const aniosDisponibles = computed(() => {
  const actual = new Date().getFullYear()
  return Array.from({ length: 6 }, (_, i) => actual - i)
})

const historico = ref<Historico | null>(null)
const loadingHistorico = ref(false)

const etiquetaPeriodo = computed(() => ({
  dia: 'Ventas del día',
  semana: 'Ventas de la semana',
  mes: 'Ventas del mes',
  anio: '',
}[periodo.value]))

const variacionPct = computed(() => {
  if (!historico.value?.total_anterior) return historico.value?.total_actual ? 100 : 0
  const actual = historico.value.total_actual ?? 0
  const anterior = historico.value.total_anterior
  return Math.round(((actual - anterior) / anterior) * 1000) / 10
})

// Todas las series juntas (actual + anterior) — para que las barras de
// ambos años compartan la misma escala y sean comparables a simple vista.
const todasLasSeries = computed(() => [
  ...(historico.value?.series_actual ?? []),
  ...(historico.value?.series_anterior ?? []),
])

function barHeight(val: number, arr: SerieItem[]): number {
  const max = Math.max(...arr.map(s => s.total), 1)
  return Math.max((val / max) * 190, val > 0 ? 4 : 0)
}

async function cargarHistorico() {
  loadingHistorico.value = true
  try {
    const { data } = await api.get('/admin/reports/historico', {
      params: {
        periodo: periodo.value,
        anio: anioSeleccionado.value,
        mes: periodo.value !== 'mes' && periodo.value !== 'anio' ? mesSeleccionado.value : undefined,
      },
    })
    historico.value = data.data
  } catch (e) {
    console.error('Error cargando histórico de ventas:', e)
  } finally {
    loadingHistorico.value = false
  }
}

watch([periodo, anioSeleccionado, mesSeleccionado], cargarHistorico)

// ══════════════════════════════════════════════════════════
// TAB PERSONALIZACIONES
// ══════════════════════════════════════════════════════════
interface CustomOption { name: string; qty: number }
interface CustomSeccion { seccion: string; label: string; options: CustomOption[] }
interface CustomData { secciones: CustomSeccion[]; total_items_analizados: number }

const customData = ref<CustomData | null>(null)
const loadingCustom = ref(false)

const seccionesData = computed(() => customData.value?.secciones ?? [])

const PALETTE = [
  { bgIcon: 'bg-red-50', iconColor: 'text-brand-red', barColor: 'bg-brand-red' },
  { bgIcon: 'bg-orange-50', iconColor: 'text-orange-500', barColor: 'bg-orange-500' },
  { bgIcon: 'bg-green-50', iconColor: 'text-green-500', barColor: 'bg-green-500' },
  { bgIcon: 'bg-blue-50', iconColor: 'text-blue-500', barColor: 'bg-blue-500' },
  { bgIcon: 'bg-purple-50', iconColor: 'text-purple-500', barColor: 'bg-purple-500' },
  { bgIcon: 'bg-amber-50', iconColor: 'text-amber-500', barColor: 'bg-amber-500' },
]

const seccionesEnriquecidas = computed(() =>
  seccionesData.value.map((sec, i) => {
    const totalElecciones = sec.options.reduce((sum, o) => sum + o.qty, 0)
    return {
      ...sec,
      ...PALETTE[i % PALETTE.length],
      totalElecciones,
      options: sec.options.map(o => ({
        ...o,
        pct: totalElecciones > 0 ? Math.round((o.qty / totalElecciones) * 100) : 0,
      })),
    }
  })
)

async function fetchCustom() {
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

// ── Helpers compartidos ────────────────────────────────────
function formatMonto(n: number): string {
  return Number(n).toLocaleString('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => {
  cargarHistorico()
  fetchCustom()
})
</script>