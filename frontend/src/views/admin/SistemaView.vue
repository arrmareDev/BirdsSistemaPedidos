<template>
    <div class="flex flex-col gap-5">

        <!-- Config — solo rol sistema -->
        <div v-if="isSistema" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                        <CpuChipIcon class="w-4 h-4 text-purple-600" />
                    </div>
                    <h3 class="font-black text-[15px] text-gray-900 m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        Configuración del sistema
                    </h3>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full
                     bg-purple-50 text-purple-700 border border-purple-200">
                    Solo sistema
                </span>
            </div>
            <div class="p-5 flex items-end gap-4 flex-wrap">
                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                        Comisión por pedido entregado (S/)
                    </label>
                    <input v-model.number="configForm.comision" type="number" min="0" max="10" step="0.05" class="px-4 py-3 rounded-2xl border-2 border-gray-100
                   bg-gray-50 text-[15px] font-bold text-gray-900 outline-none
                   focus:border-purple-500 focus:bg-white
                   focus:shadow-[0_0_0_3px_rgba(147,51,234,0.08)]
                   transition-all duration-200 w-44" />
                </div>
                <button @click="saveConfig" :disabled="savingConfig" class="flex items-center gap-2 px-5 py-3 rounded-2xl font-bold
                 text-[13.5px] text-white border-none cursor-pointer
                 bg-purple-600 hover:bg-purple-700
                 disabled:opacity-50 transition-all duration-150">
                    <span v-if="savingConfig" class="w-4 h-4 border-2 border-white/30 border-t-white
                   rounded-full animate-spin" />
                    <CheckCircleIcon v-else class="w-4 h-4" />
                    {{ savingConfig ? 'Guardando...' : 'Guardar' }}
                </button>
                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0"
                    leave-to-class="opacity-0">
                    <span v-if="configSaved" class="text-[13px] text-green-600 font-semibold flex items-center gap-1.5">
                        <CheckCircleIcon class="w-4 h-4" />
                        Guardado
                    </span>
                </Transition>
            </div>
        </div>

        <!-- Banner readonly para admin -->
        <div v-else class="flex items-center gap-3 px-4 py-3.5 rounded-2xl
             bg-blue-50 border border-blue-100">
            <InformationCircleIcon class="w-4 h-4 text-blue-500 shrink-0" />
            <p class="text-[13px] text-blue-700 m-0">
                Vista de solo lectura — el cobro lo gestiona el equipo de sistema.
            </p>
        </div>

        <!-- ══ FILTROS ══ -->
        <div class="flex items-center gap-2 flex-wrap">

            <!-- Período -->
            <div class="flex gap-1.5 flex-wrap">
                <button v-for="p in PERIODOS" :key="p.value" @click="setPeriodo(p.value)" class="px-4 py-1.5 rounded-full text-[12.5px] font-semibold
                 border transition-all duration-150 cursor-pointer" :class="periodo === p.value
                    ? 'bg-purple-600 text-white border-purple-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:border-purple-300'">
                    {{ p.label }}
                </button>
            </div>

            <!-- Filtro cobrado/pendiente -->
            <div class="flex gap-1.5 ml-2">
                <button v-for="f in FILTROS" :key="f.value" @click="setFiltro(f.value)" class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold
                 border transition-all duration-150 cursor-pointer"
                    :class="filtro === f.value ? f.activeClass : 'bg-white border-gray-200 text-gray-500'">
                    {{ f.label }}
                </button>
            </div>

            <!-- Rango personalizado -->
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0">
                <div v-if="periodo === 'custom'" class="flex items-center gap-2">
                    <input v-model="customDesde" type="date" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white
                   text-[13px] outline-none focus:border-purple-500
                   transition-all duration-200" />
                    <span class="text-gray-400 text-[12px]">al</span>
                    <input v-model="customHasta" type="date" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white
                   text-[13px] outline-none focus:border-purple-500
                   transition-all duration-200" />
                    <button @click="fetchDashboard(1)" class="px-4 py-1.5 rounded-xl bg-purple-600 text-white font-bold
                   text-[12px] border-none cursor-pointer hover:bg-purple-700
                   transition-all duration-150">
                        Filtrar
                    </button>
                </div>
            </Transition>

            <span class="ml-auto text-[12px] text-gray-400">{{ periodoLabel }}</span>
        </div>

        <!-- ══ KPIs ══ -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-if="loading" v-for="n in 4" :key="n" class="h-28 rounded-2xl bg-gray-100 animate-pulse" />

            <template v-else>

                <!-- Pedidos -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                            Pedidos
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                            <ClipboardDocumentListIcon class="w-4 h-4 text-blue-500" />
                        </div>
                    </div>
                    <p class="font-black text-[32px] text-gray-900 leading-none m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ kpis.pedidos }}
                    </p>
                    <p class="text-[12px] text-gray-400 m-0 mt-1">entregados</p>
                </div>

                <!-- Total generado -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                            Total generado
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                            <CurrencyDollarIcon class="w-4 h-4 text-purple-500" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[12px] font-semibold text-gray-400">S/</span>
                        <span class="font-black text-[32px] text-purple-600 leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ formatMonto(kpis.total_comision) }}
                        </span>
                    </div>
                    <p class="text-[12px] m-0 mt-1 flex items-center gap-1"
                        :class="kpis.crecimiento_pct >= 0 ? 'text-green-500' : 'text-red-400'">
                        <ArrowTrendingUpIcon v-if="kpis.crecimiento_pct >= 0" class="w-3 h-3" />
                        <ArrowTrendingDownIcon v-else class="w-3 h-3" />
                        {{ Math.abs(kpis.crecimiento_pct) }}% vs período anterior
                    </p>
                </div>

                <!-- Por cobrar -->
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                            Por cobrar
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                            <ClockIcon class="w-4 h-4 text-amber-500" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[12px] font-semibold text-gray-400">S/</span>
                        <span class="font-black text-[32px] text-amber-600 leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ formatMonto(kpis.total_pendiente) }}
                        </span>
                    </div>
                    <p class="text-[12px] text-gray-400 m-0 mt-1">pendiente de cobro</p>
                </div>

                <!-- Ya cobrado + botón cobrar -->
                <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                            Ya cobrado
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center">
                            <CheckCircleIcon class="w-4 h-4 text-green-500" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[12px] font-semibold text-gray-400">S/</span>
                        <span class="font-black text-[32px] text-green-600 leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ formatMonto(kpis.total_cobrado) }}
                        </span>
                    </div>
                    <!-- Botón cobrar — solo rol sistema -->
                    <button v-if="isSistema" @click="showCobrarModal = true" :disabled="kpis.total_pendiente <= 0"
                        class="mt-2 text-[11.5px] font-bold text-green-600 cursor-pointer
                   border-none bg-transparent p-0 hover:text-green-700
                   disabled:opacity-40 disabled:cursor-not-allowed
                   transition-colors">
                        Cobrar período →
                    </button>
                </div>
            </template>
        </div>

        <!-- ══ Botones de cobro rápido — solo sistema ══ -->
        <div v-if="isSistema && !loading" class="flex flex-wrap gap-2">
            <button v-for="cobro in COBROS_RAPIDOS" :key="cobro.value" @click="abrirCobro(cobro)"
                :disabled="kpis.total_pendiente <= 0" class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold
               text-[12.5px] border cursor-pointer transition-all duration-150
               disabled:opacity-40 disabled:cursor-not-allowed" :class="cobro.class">
                <component :is="cobro.icon" class="w-3.5 h-3.5" />
                {{ cobro.label }}
            </button>
        </div>

        <!-- ══ GRÁFICO ══ -->
        <div v-if="!loading && porDia.length > 0"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                    <ChartBarIcon class="w-4 h-4 text-purple-500" />
                </div>
                <h3 class="font-black text-[15px] text-gray-900 m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Comisiones por día
                </h3>
            </div>
            <div class="p-5">
                <div class="flex items-end gap-2 h-40">
                    <div v-for="d in porDia" :key="d.fecha" class="flex-1 flex flex-col items-center gap-1.5 group">
                        <span class="text-[10px] font-bold text-gray-400
                         opacity-0 group-hover:opacity-100 transition-opacity">
                            S/{{ d.total.toFixed(2) }}
                        </span>
                        <div class="w-full rounded-t-xl transition-all duration-500
                        bg-purple-100 group-hover:bg-purple-200 relative" :style="`height: ${barHeight(d.total)}px`" />
                        <span class="text-[10px] font-semibold text-gray-400">
                            {{ d.label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ TABLA DETALLE ══ -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                        <TableCellsIcon class="w-4 h-4 text-gray-500" />
                    </div>
                    <h3 class="font-black text-[15px] text-gray-900 m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        Detalle de comisiones
                    </h3>
                </div>
                <span class="text-[12px] font-bold text-gray-400 bg-gray-100
                     px-2.5 py-1 rounded-full">
                    {{ detalleTotal }} registros
                </span>
            </div>

            <!-- Skeleton -->
            <div v-if="loading" class="divide-y divide-gray-50">
                <div v-for="n in 5" :key="n" class="h-14 bg-gray-50 animate-pulse" />
            </div>

            <!-- Empty -->
            <div v-else-if="detalle.length === 0" class="flex flex-col items-center py-16 text-gray-400 gap-2">
                <ChartBarIcon class="w-10 h-10 text-gray-200" />
                <p class="m-0 text-[13px]">Sin comisiones en este período</p>
            </div>

            <!-- Filas -->
            <div v-else class="divide-y divide-gray-50">
                <div v-for="c in detalle" :key="c.id" class="flex items-center gap-4 px-5 py-3.5
                 hover:bg-gray-50/50 transition-colors duration-100">

                    <span class="text-[11px] font-black text-gray-500 font-mono shrink-0">
                        #{{ c.order_id }}
                    </span>

                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[13px] text-gray-900 m-0 truncate">
                            {{ c.order?.client_name ?? '—' }}
                        </p>
                        <p class="text-[11.5px] text-gray-400 m-0">
                            {{ formatDate(c.fecha) }}
                        </p>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-[11px] text-gray-400 m-0">Pedido</p>
                        <p class="font-semibold text-[13px] text-gray-700 m-0">
                            S/ {{ formatMonto(c.monto_pedido) }}
                        </p>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-[11px] text-gray-400 m-0">Comisión</p>
                        <p class="font-black text-[15px] text-purple-600 m-0"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            S/ {{ formatMonto(c.monto_comision) }}
                        </p>
                    </div>

                    <!-- Estado -->
                    <span class="shrink-0 text-[11px] font-bold px-2.5 py-1 rounded-full" :class="c.cobrado
                        ? 'bg-green-50 text-green-700 border border-green-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200'">
                        {{ c.cobrado ? 'Cobrado' : 'Pendiente' }}
                    </span>

                    <!-- Cobrado_at -->
                    <p v-if="c.cobrado && c.cobrado_at"
                        class="text-[10.5px] text-gray-400 m-0 shrink-0 hidden lg:block">
                        {{ formatDate(c.cobrado_at) }}
                    </p>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-5 py-4
               border-t border-gray-100 bg-gray-50/50">
                <span class="text-[12.5px] text-gray-400">
                    Página {{ meta.current_page }} de {{ meta.last_page }}
                    · {{ meta.total }} registros
                </span>
                <div class="flex gap-2">
                    <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px]
                   font-semibold text-gray-600 cursor-pointer bg-white
                   hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
                        ← Anterior
                    </button>
                    <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                        class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px]
                   font-semibold text-gray-600 cursor-pointer bg-white
                   hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
                        Siguiente →
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ MODAL COBRAR — solo sistema ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="showCobrarModal" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="showCobrarModal = false">

                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="showCobrarModal"
                            class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">

                            <div class="w-14 h-14 rounded-2xl bg-green-50 mx-auto mb-5
                          flex items-center justify-center">
                                <CheckCircleIcon class="w-7 h-7 text-green-500" />
                            </div>

                            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                Confirmar cobro
                            </h3>
                            <p class="text-[13.5px] text-gray-400 m-0 mb-5 leading-relaxed">
                                Se marcarán como cobradas todas las comisiones
                                pendientes del período seleccionado.
                            </p>

                            <div class="bg-gray-50 rounded-2xl p-4 mb-6 text-left
                          border border-gray-100 flex flex-col gap-2">
                                <div class="flex justify-between text-[13px]">
                                    <span class="text-gray-500">Período</span>
                                    <span class="font-semibold text-gray-700">
                                        {{ cobrarTarget?.label ?? periodoLabel }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-[13px]">
                                    <span class="text-gray-500">Desde</span>
                                    <span class="font-semibold text-gray-700">
                                        {{ cobrarTarget?.desde ?? periodoDesdeStr }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-[13px]">
                                    <span class="text-gray-500">Hasta</span>
                                    <span class="font-semibold text-gray-700">
                                        {{ cobrarTarget?.hasta ?? hoyStr }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-[13px] pt-2
                            border-t border-gray-200">
                                    <span class="text-gray-500 font-semibold">Monto a cobrar</span>
                                    <span class="font-black text-purple-600">
                                        S/ {{ formatMonto(kpis.total_pendiente) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button @click="showCobrarModal = false" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                         text-gray-600 font-semibold text-[13.5px]
                         cursor-pointer bg-white hover:border-gray-300
                         transition-all duration-150">
                                    Cancelar
                                </button>
                                <button @click="marcarCobrado" :disabled="cobrandoLoad" class="flex-1 py-3 rounded-2xl text-white font-bold
                         text-[13.5px] cursor-pointer border-none
                         bg-green-600 hover:bg-green-700
                         disabled:opacity-50 transition-all duration-150
                         flex items-center justify-center gap-2">
                                    <span v-if="cobrandoLoad" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                                    {{ cobrandoLoad ? 'Procesando...' : 'Confirmar cobro' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useAdminStore } from '@/stores/admin'
import api from '@/utils/api'
import {
    CpuChipIcon, CheckCircleIcon, ClipboardDocumentListIcon,
    CurrencyDollarIcon, ClockIcon, ChartBarIcon,
    ArrowTrendingUpIcon, ArrowTrendingDownIcon,
    TableCellsIcon, InformationCircleIcon,
    CalendarDaysIcon, CalendarIcon, SunIcon,
} from '@heroicons/vue/24/outline'

const adminStore = useAdminStore()

// ── Rol ───────────────────────────────────────────────────
const isSistema = computed(() => adminStore.role === 'sistema')

// ── Estado ────────────────────────────────────────────────
const loading = ref(false)
const savingConfig = ref(false)
const configSaved = ref(false)
const cobrandoLoad = ref(false)
const showCobrarModal = ref(false)
const cobrarTarget = ref<any>(null)

const periodo = ref('mes')
const filtro = ref('todos')
const customDesde = ref('')
const customHasta = ref('')
const currentPage = ref(1)

const kpis = ref({
    pedidos: 0, total_comision: 0,
    total_cobrado: 0, total_pendiente: 0,
    crecimiento_pct: 0,
})
const porDia = ref<any[]>([])
const detalle = ref<any[]>([])
const meta = ref<any>(null)
const configForm = reactive({ comision: 0.30 })

// ── Constantes ────────────────────────────────────────────
const PERIODOS = [
    { value: 'hoy', label: 'Hoy' },
    { value: 'semana', label: 'Semana' },
    { value: 'mes', label: 'Mes' },
    { value: 'año', label: 'Año' },
    { value: 'custom', label: 'Rango' },
]

const FILTROS = [
    { value: 'todos', label: 'Todos', activeClass: 'bg-gray-900 text-white border-gray-900' },
    { value: 'pendiente', label: 'Pendiente', activeClass: 'bg-amber-500 text-white border-amber-500' },
    { value: 'cobrado', label: 'Cobrado', activeClass: 'bg-green-600 text-white border-green-600' },
]

const COBROS_RAPIDOS = [
    {
        value: 'hoy',
        label: 'Cobrar hoy',
        icon: SunIcon,
        class: 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100',
        getDesde: () => hoyStr.value,
    },
    {
        value: 'semana',
        label: 'Cobrar semana',
        icon: CalendarDaysIcon,
        class: 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100',
        getDesde: () => {
            const d = new Date()
            d.setDate(d.getDate() - d.getDay())
            return d.toISOString().slice(0, 10)
        },
    },
    {
        value: 'mes',
        label: 'Cobrar mes',
        icon: CalendarIcon,
        class: 'bg-purple-50 border-purple-200 text-purple-700 hover:bg-purple-100',
        getDesde: () => {
            const d = new Date()
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`
        },
    },
]

// ── Computed ──────────────────────────────────────────────
const hoyStr = computed(() => new Date().toISOString().slice(0, 10))

const periodoDesdeStr = computed(() => getDesdeStr(periodo.value))

const detalleTotal = computed(() => meta.value?.total ?? detalle.value.length)

const periodoLabel = computed(() => {
    const m: Record<string, string> = {
        hoy: 'Hoy',
        semana: 'Esta semana',
        mes: 'Este mes',
        año: 'Este año',
        custom: customDesde.value && customHasta.value
            ? `${customDesde.value} al ${customHasta.value}`
            : 'Rango personalizado',
    }
    return m[periodo.value] ?? periodo.value
})

// ── API ───────────────────────────────────────────────────
async function fetchDashboard(page = 1) {
    loading.value = true
    try {
        const params: any = {
            periodo: periodo.value,
            filtro: filtro.value,
            per_page: 10,
            page,
        }
        if (periodo.value === 'custom') {
            params.desde = customDesde.value
            params.hasta = customHasta.value
        }
        const { data } = await api.get('/admin/sistema/dashboard', { params })
        kpis.value = data.data.kpis
        porDia.value = data.data.por_dia
        detalle.value = data.data.detalle.data ?? []
        meta.value = data.data.detalle.meta ?? null
    } catch (e) {
        console.error('Error cargando sistema:', e)
    } finally {
        loading.value = false
    }
}

async function fetchConfig() {
    try {
        const { data } = await api.get('/admin/sistema/config')
        configForm.comision = data.data.comision_por_pedido
    } catch { }
}

async function saveConfig() {
    savingConfig.value = true
    try {
        await api.put('/admin/sistema/config', {
            comision_por_pedido: configForm.comision,
        })
        configSaved.value = true
        setTimeout(() => { configSaved.value = false }, 2_500)
    } catch { }
    finally { savingConfig.value = false }
}

async function marcarCobrado() {
    cobrandoLoad.value = true
    try {
        const desde = cobrarTarget.value?.desde ?? periodoDesdeStr.value
        const hasta = cobrarTarget.value?.hasta ?? hoyStr.value
        await api.post('/admin/sistema/cobrar', { desde, hasta })
        showCobrarModal.value = false
        cobrarTarget.value = null
        await fetchDashboard(currentPage.value)
    } catch { }
    finally { cobrandoLoad.value = false }
}

// ── Acciones ──────────────────────────────────────────────
function setPeriodo(p: string) {
    periodo.value = p
    currentPage.value = 1
    if (p !== 'custom') fetchDashboard(1)
}

function setFiltro(f: string) {
    filtro.value = f
    currentPage.value = 1
    fetchDashboard(1)
}

function changePage(page: number) {
    currentPage.value = page
    fetchDashboard(page)
}

function abrirCobro(cobro: any) {
    cobrarTarget.value = {
        label: cobro.label,
        desde: cobro.getDesde(),
        hasta: hoyStr.value,
    }
    showCobrarModal.value = true
}

// ── Helpers ───────────────────────────────────────────────
function getDesdeStr(p: string): string {
    const now = new Date()
    if (p === 'hoy') return now.toISOString().slice(0, 10)
    if (p === 'semana') {
        const d = new Date(now)
        d.setDate(d.getDate() - d.getDay())
        return d.toISOString().slice(0, 10)
    }
    if (p === 'mes') {
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`
    }
    if (p === 'año') return `${now.getFullYear()}-01-01`
    if (p === 'custom') return customDesde.value
    return now.toISOString().slice(0, 10)
}

function barHeight(val: number): number {
    const max = Math.max(...porDia.value.map(d => d.total), 1)
    return Math.max((val / max) * 120, 4)
}

function formatMonto(n: number): string {
    return Number(n).toLocaleString('es-PE', {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
    })
}

function formatDate(d: string): string {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric',
    })
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
    await fetchDashboard(1)
    if (isSistema.value) await fetchConfig()
})
</script>