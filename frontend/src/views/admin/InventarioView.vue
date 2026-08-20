<template>
    <div class="flex flex-col gap-5">

        <!-- ══ Encabezado ══ -->
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-brand-red m-0 mb-1">
                    Control de stock
                </p>
                <h1 class="font-black text-[22px] text-gray-900 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Inventario
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <button @click="descargar('pdf')" :disabled="descargando !== null" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12.5px] font-bold
                 border cursor-pointer transition-all duration-150 disabled:opacity-50
                 border-gray-200 text-gray-600 bg-white hover:border-red-300 hover:text-brand-red">
                    <DocumentArrowDownIcon class="w-4 h-4" />
                    {{ descargando === 'pdf' ? 'Generando...' : 'PDF' }}
                </button>
                <button @click="descargar('excel')" :disabled="descargando !== null" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12.5px] font-bold
                 border cursor-pointer transition-all duration-150 disabled:opacity-50
                 border-gray-200 text-gray-600 bg-white hover:border-green-300 hover:text-green-600">
                    <TableCellsIcon class="w-4 h-4" />
                    {{ descargando === 'excel' ? 'Generando...' : 'Excel' }}
                </button>
            </div>
        </div>

        <!-- ══ Resumen ══ -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1.5">
                    Con control de stock
                </p>
                <p class="font-black text-[26px] text-gray-900 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ store.conControlDeStock.length }}
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-5 bg-amber-50/40">
                <p
                    class="text-[10.5px] font-black uppercase tracking-widest text-amber-600 m-0 mb-1.5 flex items-center gap-1">
                    <ExclamationTriangleIcon class="w-3.5 h-3.5" /> Stock bajo
                </p>
                <p class="font-black text-[26px] text-amber-600 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ store.conStockBajo.length }}
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-red-200 shadow-sm p-5 bg-red-50/40">
                <p
                    class="text-[10.5px] font-black uppercase tracking-widest text-brand-red m-0 mb-1.5 flex items-center gap-1">
                    <XCircleIcon class="w-3.5 h-3.5" /> Agotados
                </p>
                <p class="font-black text-[26px] text-brand-red leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ store.agotados.length }}
                </p>
            </div>
        </div>

        <!-- ══ Filtros ══ -->
        <div class="flex flex-wrap items-center gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="relative flex-1 min-w-[200px]">
                <MagnifyingGlassIcon class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input v-model="buscar" placeholder="Buscar producto..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-gray-100 bg-gray-50
                 text-[13px] outline-none focus:border-brand-red transition-all duration-150" />
            </div>
            <button @click="soloProblema = !soloProblema"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-bold border-2 cursor-pointer transition-all duration-150"
                :class="soloProblema
                    ? 'border-amber-300 bg-amber-50 text-amber-700'
                    : 'border-gray-100 bg-white text-gray-500 hover:border-gray-200'">
                <ExclamationTriangleIcon class="w-4 h-4" />
                Solo con stock bajo o agotado
            </button>
            <select v-model="filtroCategoria" class="px-4 py-2.5 rounded-xl border-2 border-gray-100 bg-white text-[13px] font-semibold
               text-gray-600 outline-none cursor-pointer focus:border-brand-red transition-all duration-150">
                <option value="">Todas las categorías</option>
                <option v-for="cat in categoriasDisponibles" :key="cat" :value="cat">{{ cat }}</option>
            </select>
        </div>

        <!-- ══ Tabla ══ -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div v-if="store.loading" class="flex flex-col gap-2 p-4">
                <div v-for="n in 6" :key="n" class="h-14 rounded-xl bg-gray-100 animate-pulse" />
            </div>

            <div v-else-if="productosFiltrados.length === 0" class="flex flex-col items-center py-16 gap-3 text-center">
                <ArchiveBoxIcon class="w-10 h-10 text-gray-200" />
                <div>
                    <p class="font-bold text-[15px] text-gray-700 m-0">
                        {{ store.conControlDeStock.length === 0 ? 'Ningún producto tiene control de stock activado' :
                        'Sin resultados' }}
                    </p>
                    <p v-if="store.conControlDeStock.length === 0" class="text-[13px] text-gray-400 m-0 mt-1 max-w-sm">
                        Actívalo desde Catálogo → editar producto → "Controlar inventario"
                    </p>
                </div>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">
                                Producto</th>
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">
                                Categoría</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-right">
                                Precio</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-center">
                                Stock actual</th>
                            <th
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3 text-center">
                                Mínimo</th>
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">
                                Estado</th>
                            <th class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 px-5 py-3">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in productosPagina" :key="p.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors duration-100">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center relative">
                                        <img v-if="p.image_url" :src="p.image_url" :alt="p.name"
                                            class="w-full h-full object-cover" />
                                        <AppIcon v-else :name="p.icon ?? 'package'" :size="18" class="text-gray-400" />
                                        <div v-if="!p.available"
                                            class="absolute inset-0 bg-white/70 flex items-center justify-center">
                                            <EyeSlashIcon class="w-3.5 h-3.5 text-gray-500" />
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="font-semibold text-[13.5px] text-gray-900 max-w-[200px] truncate">{{
                                                p.name }}</span>
                                            <StarIcon v-if="p.popular" class="w-3.5 h-3.5 text-amber-400 shrink-0"
                                                title="Popular" />
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span v-if="!p.available" class="text-[10px] font-bold text-gray-400">No
                                                disponible</span>
                                            <span v-if="p.descuento"
                                                class="text-[10px] font-bold text-brand-red bg-red-50 px-1.5 py-0.5 rounded">
                                                -{{ p.descuento.porcentaje }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12.5px] text-gray-500">{{ p.category?.name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-[13px] font-semibold text-gray-700">S/ {{ p.price.toFixed(2) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-black text-[17px]" style="font-family:'Plus Jakarta Sans',sans-serif;"
                                    :class="estadoStock(p).color">
                                    {{ p.stock ?? 0 }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-[13px] text-gray-400">{{ p.stock_minimo ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="text-[10.5px] font-bold px-2.5 py-1 rounded-full border inline-flex items-center gap-1"
                                    :class="estadoStock(p).badge">
                                    <component :is="estadoStock(p).icon" class="w-3 h-3" />
                                    {{ estadoStock(p).label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <button @click="abrirReponer(p)" title="Reponer stock" class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150
                           border-gray-200 text-gray-600 bg-white hover:border-green-300 hover:text-green-600">
                                        <PlusIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="abrirAjustar(p)" title="Ajustar stock (conteo físico)" class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150
                           border-gray-200 text-gray-600 bg-white hover:border-blue-300 hover:text-blue-600">
                                        <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="abrirHistorial(p)" title="Ver historial" class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150
                           border-gray-200 text-gray-600 bg-white hover:border-gray-300">
                                        <ClockIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="totalPaginas > 1" class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100">
                <p class="text-[12.5px] text-gray-400 m-0">
                    Página {{ pagina }} de {{ totalPaginas }} · {{ productosFiltrados.length }} en total
                </p>
                <div class="flex gap-2">
                    <button @click="pagina--" :disabled="pagina <= 1" class="px-3 py-1.5 rounded-xl border-2 border-gray-200 text-[12.5px] font-bold text-gray-600
                   cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:border-gray-300 bg-white">
                        Anterior
                    </button>
                    <button @click="pagina++" :disabled="pagina >= totalPaginas" class="px-3 py-1.5 rounded-xl border-2 border-gray-200 text-[12.5px] font-bold text-gray-600
                   cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:border-gray-300 bg-white">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ MODAL REPONER ══ -->
        <Teleport to="body">
            <div v-if="reponerModal.producto"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="reponerModal.producto = null">
                <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-11 h-11 rounded-2xl bg-green-50 flex items-center justify-center shrink-0">
                            <PlusIcon class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <h3 class="font-black text-[16px] text-gray-900 m-0"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                Reponer stock
                            </h3>
                            <p class="text-[12px] text-gray-400 m-0">{{ reponerModal.producto.name }}</p>
                        </div>
                    </div>
                    <p class="text-[12.5px] text-gray-400 mt-3 mb-4">
                        Stock actual: <strong class="text-gray-700">{{ reponerModal.producto.stock ?? 0 }}</strong>
                    </p>

                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="field-label-sm">¿Cuánto llegó?</label>
                            <input v-model.number="reponerForm.cantidad" type="number" min="1" placeholder="0" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-100 bg-gray-50 text-[15px] font-bold
                       outline-none focus:border-green-400 transition-all duration-150" />
                        </div>
                        <div>
                            <label class="field-label-sm">Motivo (opcional)</label>
                            <input v-model="reponerForm.motivo" placeholder="Ej: Llegó pedido del proveedor" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-100 bg-gray-50 text-[13px]
                       outline-none focus:border-green-400 transition-all duration-150" />
                        </div>
                        <p v-if="reponerModal.error" class="text-[12.5px] text-red-600 m-0">{{ reponerModal.error }}</p>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="reponerModal.producto = null"
                            class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                     font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                            Cancelar
                        </button>
                        <button @click="confirmarReponer" :disabled="reponerModal.loading" class="flex-1 py-3 rounded-2xl bg-green-600 text-white font-bold text-[13.5px]
                     cursor-pointer border-none hover:bg-green-700 disabled:opacity-60 transition-all duration-150">
                            {{ reponerModal.loading ? 'Guardando...' : 'Reponer' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ══ MODAL AJUSTAR ══ -->
        <Teleport to="body">
            <div v-if="ajustarModal.producto"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="ajustarModal.producto = null">
                <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                            <AdjustmentsHorizontalIcon class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="font-black text-[16px] text-gray-900 m-0"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                Ajustar stock
                            </h3>
                            <p class="text-[12px] text-gray-400 m-0">{{ ajustarModal.producto.name }}</p>
                        </div>
                    </div>
                    <p class="text-[12.5px] text-gray-400 mt-3 mb-4">
                        El sistema dice: <strong class="text-gray-700">{{ ajustarModal.producto.stock ?? 0 }}</strong> —
                        escribe lo que contaste de verdad.
                    </p>

                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="field-label-sm">Stock real (conteo físico)</label>
                            <input v-model.number="ajustarForm.stockNuevo" type="number" min="0" placeholder="0" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-100 bg-gray-50 text-[15px] font-bold
                       outline-none focus:border-blue-400 transition-all duration-150" />
                        </div>
                        <div v-if="diferenciaAjuste !== null"
                            class="px-3.5 py-2.5 rounded-xl text-[12.5px] font-semibold"
                            :class="diferenciaAjuste === 0 ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                            {{ diferenciaAjuste === 0
                                ? 'Sin diferencia'
                                : `${diferenciaAjuste > 0 ? 'Sobran' : 'Faltan'} ${Math.abs(diferenciaAjuste)} unidades` }}
                        </div>
                        <div>
                            <label class="field-label-sm">Motivo (obligatorio)</label>
                            <input v-model="ajustarForm.motivo"
                                placeholder="Ej: Conteo físico mensual, producto dañado..." class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-100 bg-gray-50 text-[13px]
                       outline-none focus:border-blue-400 transition-all duration-150" />
                        </div>
                        <p v-if="ajustarModal.error" class="text-[12.5px] text-red-600 m-0">{{ ajustarModal.error }}</p>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="ajustarModal.producto = null"
                            class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                     font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                            Cancelar
                        </button>
                        <button @click="confirmarAjustar" :disabled="ajustarModal.loading" class="flex-1 py-3 rounded-2xl bg-blue-600 text-white font-bold text-[13.5px]
                     cursor-pointer border-none hover:bg-blue-700 disabled:opacity-60 transition-all duration-150">
                            {{ ajustarModal.loading ? 'Guardando...' : 'Ajustar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ══ MODAL HISTORIAL ══ -->
        <Teleport to="body">
            <div v-if="historialModal.producto"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="historialModal.producto = null">
                <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl max-h-[80vh] flex flex-col overflow-hidden">
                    <div class="p-6 pb-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="font-black text-[16px] text-gray-900 m-0"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                Historial de stock
                            </h3>
                            <p class="text-[12px] text-gray-400 m-0 mt-0.5">{{ historialModal.producto.name }}</p>
                        </div>
                        <button @click="historialModal.producto = null" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center
                     cursor-pointer border-none hover:bg-gray-200 transition-colors shrink-0">
                            <XMarkIcon class="w-4 h-4 text-gray-500" />
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                        <div v-if="store.historialLoading" class="flex flex-col gap-2">
                            <div v-for="n in 5" :key="n" class="h-14 rounded-xl bg-gray-100 animate-pulse" />
                        </div>

                        <div v-else-if="store.historial.length === 0" class="py-10 text-center">
                            <p class="text-[13px] text-gray-400 m-0">Todavía no hay movimientos registrados</p>
                        </div>

                        <div v-else class="flex flex-col gap-1.5">
                            <div v-for="m in store.historial" :key="m.id"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    :class="tipoMeta(m.tipo).bg">
                                    <component :is="tipoMeta(m.tipo).icon" class="w-4 h-4"
                                        :class="tipoMeta(m.tipo).color" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12.5px] font-semibold text-gray-800 m-0">{{ tipoMeta(m.tipo).label
                                        }}</p>
                                    <p class="text-[11px] text-gray-400 m-0 truncate">
                                        {{ formatFecha(m.created_at) }}
                                        <span v-if="m.order_codigo"> · Pedido #{{ m.order_codigo }}</span>
                                        <span v-if="m.usuario"> · {{ m.usuario }}</span>
                                    </p>
                                    <p v-if="m.motivo" class="text-[11px] text-gray-400 m-0 italic mt-0.5">{{ m.motivo
                                        }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-[14px] m-0"
                                        :class="m.cantidad >= 0 ? 'text-green-600' : 'text-red-500'"
                                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                                        {{ m.cantidad >= 0 ? '+' : '' }}{{ m.cantidad }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 m-0">quedó en {{ m.stock_resultante }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="store.historialMeta && store.historialMeta.last_page > 1"
                        class="flex items-center justify-between px-6 py-3.5 border-t border-gray-100 shrink-0">
                        <p class="text-[12px] text-gray-400 m-0">
                            Página {{ store.historialMeta.current_page }} de {{ store.historialMeta.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <button @click="cambiarPaginaHistorial(store.historialMeta.current_page - 1)"
                                :disabled="store.historialMeta.current_page <= 1" class="px-3 py-1.5 rounded-lg border-2 border-gray-200 text-[12px] font-bold text-gray-600
                       cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:border-gray-300 bg-white">
                                Anterior
                            </button>
                            <button @click="cambiarPaginaHistorial(store.historialMeta.current_page + 1)"
                                :disabled="store.historialMeta.current_page >= store.historialMeta.last_page" class="px-3 py-1.5 rounded-lg border-2 border-gray-200 text-[12px] font-bold text-gray-600
                       cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed hover:border-gray-300 bg-white">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import type { Component } from 'vue'
import { useInventarioStore, type MovimientoStock } from '@/stores/inventario'
import type { Product } from '@/stores/products'
import api from '@/utils/api'
import AppIcon from '@/components/AppIcon.vue'
import {
    ArchiveBoxIcon, MagnifyingGlassIcon, ExclamationTriangleIcon, XCircleIcon,
    CheckCircleIcon, PlusIcon, AdjustmentsHorizontalIcon, ClockIcon, XMarkIcon,
    ShoppingBagIcon, ArrowUturnLeftIcon, PencilSquareIcon, ArrowDownTrayIcon,
    EyeSlashIcon, StarIcon, DocumentArrowDownIcon, TableCellsIcon,
} from '@heroicons/vue/24/outline'

const store = useInventarioStore()

onMounted(() => store.fetchProductos())

// ── Filtros ───────────────────────────────────────────────
const buscar = ref('')
const soloProblema = ref(false)
const filtroCategoria = ref('')

// Derivadas directo de los productos ya cargados — no hace falta
// pedirle nada nuevo al backend solo para armar este selector.
const categoriasDisponibles = computed((): string[] => {
    const nombres = store.conControlDeStock
        .map((p) => p.category?.name)
        .filter((n): n is string => !!n)
    return Array.from(new Set(nombres)).sort()
})

const productosFiltrados = computed((): Product[] => {
    let lista = store.conControlDeStock

    if (soloProblema.value) {
        lista = lista.filter((p) => p.stock_bajo === true || (p.stock ?? 0) <= 0)
    }

    if (filtroCategoria.value) {
        lista = lista.filter((p) => p.category?.name === filtroCategoria.value)
    }

    // Búsqueda completa: nombre, descripción, y categoría — no solo
    // el nombre del producto.
    const termino = buscar.value.trim().toLowerCase()
    if (termino) {
        lista = lista.filter((p) =>
            p.name.toLowerCase().includes(termino) ||
            (p.description?.toLowerCase().includes(termino) ?? false) ||
            (p.category?.name?.toLowerCase().includes(termino) ?? false),
        )
    }

    return lista
})

// ── Paginación ────────────────────────────────────────────
const POR_PAGINA = 12
const pagina = ref(1)

const totalPaginas = computed((): number =>
    Math.max(1, Math.ceil(productosFiltrados.value.length / POR_PAGINA)),
)

const productosPagina = computed((): Product[] => {
    const inicio = (pagina.value - 1) * POR_PAGINA
    return productosFiltrados.value.slice(inicio, inicio + POR_PAGINA)
})

// Si cambia la búsqueda o el filtro, siempre se vuelve a la página 1 —
// si no, se puede quedar viendo una página vacía sin darse cuenta.
watch([buscar, soloProblema, filtroCategoria], () => {
    pagina.value = 1
})

// ── Estado visual por producto ───────────────────────────
interface EstadoStock {
    label: string
    color: string
    badge: string
    icon: Component
}

function estadoStock(p: Product): EstadoStock {
    const stock = p.stock ?? 0
    if (stock <= 0) {
        return {
            label: 'Agotado', color: 'text-brand-red',
            badge: 'bg-red-50 text-brand-red border-red-200',
            icon: XCircleIcon,
        }
    }
    if (p.stock_bajo) {
        return {
            label: 'Stock bajo', color: 'text-amber-600',
            badge: 'bg-amber-50 text-amber-700 border-amber-200',
            icon: ExclamationTriangleIcon,
        }
    }
    return {
        label: 'Bien', color: 'text-gray-900',
        badge: 'bg-green-50 text-green-700 border-green-200',
        icon: CheckCircleIcon,
    }
}

// ── Reponer ───────────────────────────────────────────────
const reponerModal = reactive({
    producto: null as Product | null,
    loading: false,
    error: '',
})
const reponerForm = reactive({ cantidad: 0, motivo: '' })

function abrirReponer(p: Product) {
    reponerModal.producto = p
    reponerModal.error = ''
    Object.assign(reponerForm, { cantidad: 0, motivo: '' })
}

async function confirmarReponer() {
    if (!reponerModal.producto) return
    if (!reponerForm.cantidad || reponerForm.cantidad <= 0) {
        reponerModal.error = 'Ingresa una cantidad válida'
        return
    }
    reponerModal.loading = true
    reponerModal.error = ''
    const result = await store.reponerStock(
        reponerModal.producto.id,
        reponerForm.cantidad,
        reponerForm.motivo.trim() || undefined,
    )
    reponerModal.loading = false
    if (result.ok) {
        reponerModal.producto = null
    } else {
        reponerModal.error = result.message ?? 'No se pudo reponer el stock'
    }
}

// ── Ajustar ───────────────────────────────────────────────
const ajustarModal = reactive({
    producto: null as Product | null,
    loading: false,
    error: '',
})
const ajustarForm = reactive({ stockNuevo: 0, motivo: '' })

const diferenciaAjuste = computed((): number | null => {
    if (!ajustarModal.producto) return null
    return ajustarForm.stockNuevo - (ajustarModal.producto.stock ?? 0)
})

function abrirAjustar(p: Product) {
    ajustarModal.producto = p
    ajustarModal.error = ''
    Object.assign(ajustarForm, { stockNuevo: p.stock ?? 0, motivo: '' })
}

async function confirmarAjustar() {
    if (!ajustarModal.producto) return
    if (!ajustarForm.motivo.trim()) {
        ajustarModal.error = 'El motivo es obligatorio'
        return
    }
    ajustarModal.loading = true
    ajustarModal.error = ''
    const result = await store.ajustarStock(
        ajustarModal.producto.id,
        ajustarForm.stockNuevo,
        ajustarForm.motivo.trim(),
    )
    ajustarModal.loading = false
    if (result.ok) {
        ajustarModal.producto = null
    } else {
        ajustarModal.error = result.message ?? 'No se pudo ajustar el stock'
    }
}

// ── Historial ─────────────────────────────────────────────
const historialModal = reactive({ producto: null as Product | null })

async function abrirHistorial(p: Product) {
    historialModal.producto = p
    await store.fetchHistorial(p.id)
}

function cambiarPaginaHistorial(pagina: number) {
    if (!historialModal.producto) return
    store.fetchHistorial(historialModal.producto.id, pagina)
}

interface TipoMeta {
    label: string
    icon: Component
    bg: string
    color: string
}

function tipoMeta(tipo: MovimientoStock['tipo']): TipoMeta {
    const mapa: Record<MovimientoStock['tipo'], TipoMeta> = {
        venta: { label: 'Venta', icon: ShoppingBagIcon, bg: 'bg-red-50', color: 'text-brand-red' },
        cancelacion: { label: 'Pedido cancelado', icon: ArrowUturnLeftIcon, bg: 'bg-green-50', color: 'text-green-600' },
        edicion_pedido: { label: 'Edición de pedido', icon: PencilSquareIcon, bg: 'bg-gray-100', color: 'text-gray-500' },
        reposicion: { label: 'Reposición', icon: ArrowDownTrayIcon, bg: 'bg-green-50', color: 'text-green-600' },
        ajuste: { label: 'Ajuste manual', icon: AdjustmentsHorizontalIcon, bg: 'bg-blue-50', color: 'text-blue-600' },
    }
    return mapa[tipo]
}

function formatFecha(f: string): string {
    return new Date(f).toLocaleDateString('es-PE', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
    })
}

// ── Descarga de reportes ──────────────────────────────────
// Manda los mismos filtros activos en pantalla, para que el PDF/Excel
// nunca muestre algo distinto de lo que se está viendo en ese momento.
const descargando = ref<'pdf' | 'excel' | null>(null)

async function descargar(tipo: 'pdf' | 'excel') {
    descargando.value = tipo
    try {
        const categoriaSeleccionada = store.conControlDeStock.find(
            (p) => p.category?.name === filtroCategoria.value,
        )

        const { data } = await api.get(`/admin/reportes/inventario/${tipo}`, {
            params: {
                q: buscar.value || undefined,
                category_id: categoriaSeleccionada?.category?.id ?? undefined,
                solo_problema: soloProblema.value || undefined,
            },
            responseType: 'blob',
        })
        const url = URL.createObjectURL(new Blob([data]))
        const link = document.createElement('a')
        link.href = url
        link.download = `inventario.${tipo === 'pdf' ? 'pdf' : 'xlsx'}`
        link.click()
        URL.revokeObjectURL(url)
    } catch (e) {
        console.error('Error generando el reporte:', e)
    } finally {
        descargando.value = null
    }
}
</script>

<style scoped>
.field-label-sm {
    display: block;
    font-size: 10.5px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #9CA3AF;
    margin-bottom: 6px;
}
</style>