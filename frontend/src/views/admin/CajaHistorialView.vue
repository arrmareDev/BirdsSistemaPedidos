<template>
    <div class="flex flex-col gap-5">

        <!-- ══ Encabezado ══ -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <RouterLink to="/admin/caja" class="w-9 h-9 rounded-xl border border-gray-200 bg-white
                 flex items-center justify-center text-gray-500 no-underline
                 hover:border-brand-red hover:text-brand-red transition-all duration-150">
                    <ArrowLeftIcon class="w-4 h-4" />
                </RouterLink>
                <div>
                    <h1 class="font-black text-[22px] text-gray-900 m-0 leading-none"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        Historial de cajas
                    </h1>
                    <p class="text-[13px] text-gray-400 m-0 mt-1">
                        Revisa cierres pasados y encuentra días con diferencias
                    </p>
                </div>
            </div>
        </div>

        <!-- ══ Filtros ══ -->
        <div class="flex flex-wrap items-center gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Desde</label>
                <input v-model="filtros.desde" type="date" class="px-3 py-2 rounded-xl border-2 border-gray-100 bg-gray-50 text-[13px]
                 outline-none focus:border-brand-red transition-all duration-150" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Hasta</label>
                <input v-model="filtros.hasta" type="date" class="px-3 py-2 rounded-xl border-2 border-gray-100 bg-gray-50 text-[13px]
                 outline-none focus:border-brand-red transition-all duration-150" />
            </div>
            <label class="flex items-center gap-2 mt-4 cursor-pointer select-none">
                <input v-model="filtros.soloConDiferencia" type="checkbox"
                    class="w-4 h-4 rounded accent-brand-red cursor-pointer" />
                <span class="text-[12.5px] font-semibold text-gray-600">Solo con diferencia</span>
            </label>
            <button v-if="filtros.desde || filtros.hasta || filtros.soloConDiferencia" @click="limpiarFiltros" class="mt-4 text-[12px] font-semibold text-gray-400 bg-transparent border-none
               cursor-pointer hover:text-red-500">
                Limpiar filtros
            </button>
        </div>

        <!-- ══ Tabla ══ -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div v-if="loading" class="flex flex-col gap-2 p-4">
                <div v-for="n in 6" :key="n" class="h-12 rounded-xl bg-gray-100 animate-pulse" />
            </div>

            <div v-else-if="cajas.length === 0" class="flex flex-col items-center py-16 gap-3 text-center">
                <ClipboardDocumentListIcon class="w-10 h-10 text-gray-200" />
                <p class="text-gray-400 text-[13.5px] m-0">No hay cajas que coincidan con estos filtros.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">Fecha
                            </th>
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">
                                Estado</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-right">
                                Apertura</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-right">
                                Esperado</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-right">
                                Contado</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-right">
                                Diferencia</th>
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">Abrió
                                / Cerró</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in cajas" :key="c.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors duration-100"
                            :class="tieneDiferencia(c) ? 'bg-amber-50/40' : ''">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-[13px] text-gray-900 m-0 capitalize">{{
                                    formatFecha(c.fecha) }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full border" :class="c.estado === 'abierta'
                                    ? 'bg-green-50 text-green-700 border-green-200'
                                    : 'bg-gray-100 text-gray-500 border-gray-200'">
                                    {{ c.estado === 'abierta' ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-[13px] text-gray-600">
                                S/ {{ formatMonto(c.monto_apertura) }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-[13px] text-gray-600">
                                {{ c.monto_cierre !== null ? `S/ ${formatMonto(c.monto_cierre)}` : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-[13px] font-semibold text-gray-800">
                                {{ c.monto_contado !== null ? `S/ ${formatMonto(c.monto_contado)}` : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span v-if="c.diferencia === null" class="text-[13px] text-gray-300">—</span>
                                <span v-else-if="c.diferencia === 0" class="text-[12.5px] font-bold text-green-600">✓
                                    Cuadra</span>
                                <div v-else class="inline-flex flex-col items-end">
                                    <span class="text-[13px] font-black"
                                        :class="c.diferencia > 0 ? 'text-blue-600' : 'text-red-500'">
                                        {{ c.diferencia > 0 ? '+' : '' }}S/ {{ formatMonto(c.diferencia) }}
                                    </span>
                                    <span v-if="c.motivo_diferencia"
                                        class="text-[10.5px] text-gray-400 italic max-w-[160px] truncate">
                                        {{ c.motivo_diferencia }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-[12px] text-gray-500 m-0">{{ c.abierta_por ?? '—' }}</p>
                                <p class="text-[11px] text-gray-400 m-0">{{ c.cerrada_por ? `Cerró: ${c.cerrada_por}` :
                                    'Sin cerrar' }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta && meta.last_page > 1"
                class="flex items-center justify-between px-5 py-4 border-t border-gray-100">
                <p class="text-[12.5px] text-gray-400 m-0">
                    Página {{ meta.current_page }} de {{ meta.last_page }} · {{ meta.total }} en total
                </p>
                <div class="flex gap-2">
                    <button @click="cambiarPagina(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="px-3 py-1.5 rounded-xl border-2 border-gray-200 text-[12.5px] font-bold text-gray-600
                   cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:border-gray-300 bg-white">
                        Anterior
                    </button>
                    <button @click="cambiarPagina(meta.current_page + 1)"
                        :disabled="meta.current_page >= meta.last_page" class="px-3 py-1.5 rounded-xl border-2 border-gray-200 text-[12.5px] font-bold text-gray-600
                   cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:border-gray-300 bg-white">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue'
import api from '@/utils/api'
import { ArrowLeftIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

interface CajaHistorialItem {
    id: number
    fecha: string
    estado: 'abierta' | 'cerrada'
    monto_apertura: number
    monto_cierre: number | null
    monto_contado: number | null
    diferencia: number | null
    motivo_diferencia: string | null
    abierta_por: string | null
    cerrada_por: string | null
}

interface Meta {
    current_page: number
    last_page: number
    total: number
    per_page: number
}

const cajas = ref<CajaHistorialItem[]>([])
const meta = ref<Meta | null>(null)
const loading = ref(false)
const page = ref(1)

const filtros = reactive({
    desde: '',
    hasta: '',
    soloConDiferencia: false,
})

let debounceTimer: ReturnType<typeof setTimeout> | null = null

async function cargar() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/caja/historial', {
            params: {
                page: page.value,
                desde: filtros.desde || undefined,
                hasta: filtros.hasta || undefined,
                solo_con_diferencia: filtros.soloConDiferencia || undefined,
            },
        })
        cajas.value = data.data.data
        meta.value = data.data.meta
    } catch (e) {
        console.error('Error cargando historial de caja:', e)
    } finally {
        loading.value = false
    }
}

function cambiarPagina(p: number) {
    if (!meta.value || p < 1 || p > meta.value.last_page) return
    page.value = p
    cargar()
}

function limpiarFiltros() {
    filtros.desde = ''
    filtros.hasta = ''
    filtros.soloConDiferencia = false
}

watch(filtros, () => {
    page.value = 1
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(cargar, 300)
})

function tieneDiferencia(c: CajaHistorialItem): boolean {
    return c.diferencia !== null && c.diferencia !== 0
}

function formatMonto(n: number): string {
    return Number(n).toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

function formatFecha(f: string): string {
    return new Date(f + 'T00:00:00').toLocaleDateString('es-PE', {
        weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
    })
}

onMounted(cargar)
</script>