<template>
    <div class="flex flex-col gap-6 max-w-2xl">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 m-0 leading-none">
                    Tarifas de delivery
                </h1>
                <p class="text-[13px] text-gray-400 mt-1 m-0">
                    Precio según distancia desde el local.
                </p>
            </div>
            <button @click="abrirCrear" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl font-bold text-[13px]
                       text-white bg-red-600 border-none cursor-pointer
                       hover:bg-red-700 transition-all duration-150">
                + Nueva tarifa
            </button>
        </div>

        <!-- Info del local -->
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4 flex items-start gap-3">
            <MapPin :size="20" class="shrink-0 text-blue-600" />
            <div>
                <p class="font-bold text-[13px] text-blue-800 m-0">Torres Paz Mz.144 - Lt.30 - Chiclayo</p>
                <p class="text-[11.5px] text-blue-600 m-0 mt-0.5">
                    Todas las tarifas se calculan desde este punto usando distancia real en línea recta
                </p>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="flex flex-col gap-3">
            <div v-for="n in 5" :key="n" class="h-16 rounded-2xl bg-gray-100 animate-pulse" />
        </div>

        <!-- Empty -->
        <div v-else-if="tarifas.length === 0" class="flex flex-col items-center py-16 text-gray-400 gap-3">
            <p class="font-bold text-gray-600 text-sm">Sin tarifas registradas todavía</p>
        </div>

        <!-- Lista de tarifas -->
        <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50
                        grid grid-cols-3 gap-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                    Hasta (km)
                </span>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                    Precio
                </span>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">
                    Acciones
                </span>
            </div>

            <div class="divide-y divide-gray-50">
                <div v-for="(t, i) in tarifas" :key="t.id"
                    class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50/50 transition-colors">

                    <!-- Rango visual -->
                    <div class="flex-1 grid grid-cols-3 gap-3 items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                                <span class="text-[10px] font-black text-red-600">{{ i + 1 }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-[13.5px] text-gray-900 m-0">
                                    {{ i === 0 ? '0' : tarifas[i - 1].distancia_max_km }} –
                                    {{ t.distancia_max_km }} km
                                </p>
                                <p class="text-[10.5px] text-gray-400 m-0">
                                    {{ t.activo ? 'Activa' : 'Inactiva' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-baseline gap-0.5">
                            <span class="text-[11px] text-gray-400">S/</span>
                            <span class="font-black text-[18px] text-gray-900 leading-none">
                                {{ t.precio.toFixed(2) }}
                            </span>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <button @click="abrirEditar(t)" class="px-3 py-1.5 rounded-xl text-[11.5px] font-bold border
                                       border-gray-200 bg-white text-gray-600 cursor-pointer
                                       hover:border-gray-300 transition-all duration-150">
                                Editar
                            </button>
                            <button @click="askEliminar(t)" class="px-3 py-1.5 rounded-xl text-[11.5px] font-bold border
                                       border-red-200 bg-red-50 text-red-600 cursor-pointer
                                       hover:bg-red-100 transition-all duration-150">
                                <X :size="13" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total cobertura -->
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50
                        flex items-center justify-between">
                <span class="text-[12px] text-gray-500 font-medium">
                    Cobertura máxima
                </span>
                <span class="font-black text-[13px] text-gray-700">
                    {{ tarifas.length > 0 ? tarifas[tarifas.length - 1].distancia_max_km : 0 }} km
                </span>
            </div>
        </div>

        <!-- ══ MODAL CREAR/EDITAR ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="modal.show" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-sm
                         flex items-center justify-center p-4" @click.self="modal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="modal.show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6">

                            <h3 class="font-black text-[18px] text-gray-900 m-0 mb-5">
                                {{ modal.editing ? 'Editar tarifa' : 'Nueva tarifa' }}
                            </h3>

                            <div class="flex flex-col gap-4">
                                <div>
                                    <label class="block text-[10.5px] font-black uppercase
                                                  tracking-widest text-gray-400 mb-1.5">
                                        Distancia máxima (km)
                                    </label>
                                    <input v-model.number="form.distancia_max_km" type="number" step="0.1" min="0.1"
                                        max="50" placeholder="Ej: 2.5" class="zona-input" />
                                    <p class="text-[11px] text-gray-400 mt-1 m-0">
                                        Esta tarifa aplica desde
                                        {{ form.distancia_max_km > 0 ? anteriorKm + ' km hasta ' : '' }}
                                        {{ form.distancia_max_km }} km del local
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-[10.5px] font-black uppercase
                                                  tracking-widest text-gray-400 mb-1.5">
                                        Precio de delivery (S/)
                                    </label>
                                    <input v-model.number="form.precio" type="number" step="0.50" min="0"
                                        placeholder="Ej: 8.00" class="zona-input" />
                                </div>

                                <Transition enter-active-class="transition-all duration-150"
                                    enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                                    <div v-if="modalError" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                                               text-[13px] text-red-600 flex items-center gap-2">
                                        <TriangleAlert :size="15" class="shrink-0" /> {{ modalError }}
                                    </div>
                                </Transition>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button @click="modal.show = false" :disabled="guardando" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                                           text-gray-600 font-semibold text-[13.5px] cursor-pointer
                                           bg-white hover:border-gray-300 transition-all duration-150
                                           disabled:opacity-50">
                                    Cancelar
                                </button>
                                <button @click="guardar" :disabled="guardando" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px]
                                           cursor-pointer border-none bg-red-600 hover:bg-red-700
                                           disabled:opacity-50 transition-all duration-150
                                           flex items-center justify-center gap-2">
                                    <span v-if="guardando" class="w-4 h-4 border-2 border-white/30 border-t-white
                                               rounded-full animate-spin" />
                                    {{ guardando ? 'Guardando...' : 'Guardar tarifa' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- Confirmación eliminar -->
        <ConfirmModal v-model="confirmEliminar.show" title="¿Eliminar esta tarifa?"
            :message="`La tarifa hasta ${confirmEliminar.target?.distancia_max_km} km (S/ ${confirmEliminar.target?.precio}) se eliminará permanentemente.`"
            variant="danger" confirm-label="Sí, eliminar" :loading="confirmEliminar.loading"
            @confirm="executeEliminar" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { MapPin, X, TriangleAlert } from 'lucide-vue-next'
import api from '@/utils/api'
import ConfirmModal from '@/components/ConfirmModal.vue'

interface Tarifa {
    id: number
    distancia_max_km: number
    precio: number
    orden: number
    activo: boolean
}

const tarifas = ref<Tarifa[]>([])
const loading = ref(false)

onMounted(() => fetchTarifas())

async function fetchTarifas() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/delivery-zones')
        tarifas.value = data.data
    } catch { }
    finally { loading.value = false }
}

// ── Modal ────────────────────────────────────────────────
const modal = reactive({ show: false, editing: null as Tarifa | null })
const modalError = ref('')
const guardando = ref(false)

const form = reactive({ distancia_max_km: 0, precio: 0 })

const anteriorKm = computed(() => {
    if (!modal.editing) return tarifas.value[tarifas.value.length - 1]?.distancia_max_km ?? 0
    const idx = tarifas.value.findIndex(t => t.id === modal.editing!.id)
    return idx > 0 ? tarifas.value[idx - 1].distancia_max_km : 0
})

function abrirCrear() {
    modal.editing = null
    modalError.value = ''
    form.distancia_max_km = 0
    form.precio = 0
    modal.show = true
}

function abrirEditar(t: Tarifa) {
    modal.editing = t
    modalError.value = ''
    form.distancia_max_km = t.distancia_max_km
    form.precio = t.precio
    modal.show = true
}

async function guardar() {
    modalError.value = ''
    if (!form.distancia_max_km || form.distancia_max_km <= 0) {
        modalError.value = 'La distancia máxima debe ser mayor a 0'
        return
    }
    if (form.precio < 0) {
        modalError.value = 'El precio no puede ser negativo'
        return
    }

    guardando.value = true
    try {
        if (modal.editing) {
            await api.put(`/admin/delivery-zones/${modal.editing.id}`, form)
        } else {
            await api.post('/admin/delivery-zones', form)
        }
        modal.show = false
        await fetchTarifas()
    } catch (e: any) {
        modalError.value = e.response?.data?.message ?? 'Error al guardar la tarifa'
    } finally {
        guardando.value = false
    }
}

// ── Eliminar ───────────────────────────────────────────────
const confirmEliminar = reactive({ show: false, loading: false, target: null as Tarifa | null })

function askEliminar(t: Tarifa) {
    confirmEliminar.target = t
    confirmEliminar.show = true
}

async function executeEliminar() {
    if (!confirmEliminar.target) return
    confirmEliminar.loading = true
    try {
        await api.delete(`/admin/delivery-zones/${confirmEliminar.target.id}`)
        await fetchTarifas()
    } catch { }
    finally {
        confirmEliminar.loading = false
        confirmEliminar.show = false
    }
}
</script>

<style scoped>
.zona-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border: 2px solid #f3f4f6;
    background: #f9fafb;
    font-size: 14px;
    color: #111827;
    outline: none;
    transition: all 0.2s;
}

.zona-input:focus {
    border-color: #dc2626;
    background: white;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
}
</style>