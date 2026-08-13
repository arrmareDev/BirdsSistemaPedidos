<template>
    <div class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-black text-2xl m-0" style="color: var(--color-ink)">Proveedores</h1>
                <p class="text-sm text-gray-500 m-0 mt-1">
                    Directorio interno de proveedores aliados. Al comprarles, menciona que vienes de nuestro
                    sistema para acceder a su descuento.
                </p>
            </div>
            <button v-if="admin.isSistema" @click="openCreate" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-brand-red text-white font-bold
                       text-[13px] border-none cursor-pointer hover:-translate-y-0.5 transition-all duration-150">
                <PlusIcon class="w-4 h-4" />
                Nuevo proveedor
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <span class="w-8 h-8 border-2 border-gray-200 border-t-brand-red rounded-full animate-spin" />
        </div>

        <div v-else-if="proveedores.length === 0" class="flex flex-col items-center py-20 gap-3 text-center">
            <BuildingStorefrontIcon class="w-12 h-12 text-gray-300" />
            <p class="text-gray-400 text-sm m-0">Todavía no hay proveedores registrados.</p>
        </div>

        <div v-else class="flex flex-col gap-3">
            <div v-for="p in proveedores" :key="p.id" @click="openDetail(p)" class="flex items-center gap-4 p-4 rounded-2xl bg-white border border-gray-100 shadow-sm
                       cursor-pointer hover:border-gray-200 hover:shadow-md transition-all duration-150">

                <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                    <img v-if="p.logo_url" :src="p.logo_url" :alt="p.nombre" class="w-full h-full object-cover" />
                    <BuildingStorefrontIcon v-else class="w-6 h-6 text-gray-400" />
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-bold text-[14.5px] m-0 truncate" style="color: var(--color-ink)">{{ p.nombre }}
                        </p>
                        <span v-if="p.categoria"
                            class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 shrink-0">
                            {{ p.categoria }}
                        </span>
                        <span v-if="!p.activo"
                            class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-50 text-red-500 shrink-0">
                            Inactivo
                        </span>
                    </div>
                    <p class="text-[12.5px] text-gray-500 m-0 mt-0.5 truncate">{{ p.descripcion || p.url_externa }}</p>
                    <p v-if="p.descuento_texto" class="text-[11.5px] text-green-700 m-0 mt-1 font-semibold">
                        🏷️ {{ p.descuento_texto }}
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0" @click.stop>
                    <div class="text-right">
                        <p class="text-[17px] font-black m-0" style="color: var(--color-brand-600, #C41E1E)">{{ p.clics
                            }}</p>
                        <p class="text-[10px] text-gray-400 m-0 uppercase tracking-wide">clics</p>
                    </div>
                    <button @click="visitar(p)"
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-500
                           bg-gray-100 hover:bg-brand-red hover:text-white transition-all duration-150 border-none cursor-pointer"
                        title="Abrir página del proveedor">
                        <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                    </button>
                    <template v-if="admin.isSistema">
                        <button @click="openEdit(p)"
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-500
                               bg-gray-100 hover:bg-brand-red hover:text-white transition-all duration-150 border-none cursor-pointer">
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button @click="askDelete(p)"
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-500
                               bg-gray-100 hover:bg-red-500 hover:text-white transition-all duration-150 border-none cursor-pointer">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- ══ MODAL DETALLE ══ -->
        <Teleport to="body">
            <div v-if="detailTarget"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="detailTarget = null">
                <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl max-h-[90vh] overflow-y-auto">

                    <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                <img v-if="detailTarget.logo_url" :src="detailTarget.logo_url"
                                    :alt="detailTarget.nombre" class="w-full h-full object-cover" />
                                <BuildingStorefrontIcon v-else class="w-7 h-7 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-black text-lg m-0 truncate" style="color: var(--color-ink)">{{
                                    detailTarget.nombre }}</h2>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span v-if="detailTarget.categoria"
                                        class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                        {{ detailTarget.categoria }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                        :class="detailTarget.activo ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500'">
                                        {{ detailTarget.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button @click="detailTarget = null" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400
                               bg-gray-100 border-none cursor-pointer hover:bg-gray-200 shrink-0">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col gap-5">
                        <div v-if="detailTarget.descripcion">
                            <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1.5">¿Qué
                                ofrece?
                            </p>
                            <p class="text-[13.5px] text-gray-700 m-0 leading-relaxed whitespace-pre-line">{{
                                detailTarget.descripcion }}</p>
                        </div>

                        <div v-if="detailTarget.descuento_texto"
                            class="px-4 py-3 rounded-2xl bg-green-50 border border-green-100">
                            <p class="text-[10.5px] font-black uppercase tracking-widest text-green-600 m-0 mb-1">
                                Descuento</p>
                            <p class="text-[13.5px] text-green-800 font-semibold m-0">🏷️ {{
                                detailTarget.descuento_texto }}</p>
                        </div>

                        <div>
                            <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1.5">
                                Página web
                            </p>
                            <p class="text-[13px] text-gray-600 m-0 break-all">{{ detailTarget.url_externa }}</p>
                        </div>

                        <div class="flex items-center gap-6">
                            <div>
                                <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 m-0 mb-1">
                                    Clics
                                    registrados</p>
                                <p class="text-xl font-black m-0" style="color: var(--color-brand-600, #C41E1E)">{{
                                    detailTarget.clics }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 pt-0 flex gap-3">
                        <template v-if="admin.isSistema">
                            <button @click="openEdit(detailTarget); detailTarget = null"
                                class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0
                                   border-2 border-gray-200 text-gray-600 bg-white cursor-pointer hover:border-gray-300 transition-all duration-150">
                                <PencilIcon class="w-4 h-4" />
                            </button>
                        </template>
                        <button @click="visitar(detailTarget)"
                            class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold text-[13.5px]
                               border-none cursor-pointer hover:-translate-y-0.5 transition-all duration-150 flex items-center justify-center gap-2">
                            <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                            Visitar página
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ══ MODAL CREAR/EDITAR ══ -->
        <Teleport to="body">
            <div v-if="showModal"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="closeModal">
                <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-black text-lg m-0" style="color: var(--color-ink)">
                            {{ editingId ? 'Editar proveedor' : 'Nuevo proveedor' }}
                        </h2>
                        <button @click="closeModal" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400
                               bg-gray-100 border-none cursor-pointer hover:bg-gray-200">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Nombre
                                *</label>
                            <input v-model="form.nombre" placeholder="Ej: Floristería Mayorista SAC"
                                class="modal-input w-full" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Logo</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                    <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                    <BuildingStorefrontIcon v-else class="w-6 h-6 text-gray-400" />
                                </div>
                                <input ref="fileInput" type="file" accept="image/*" class="hidden"
                                    @change="onFileChange" />
                                <button type="button" @click="fileInput?.click()" class="px-3.5 py-2 rounded-xl border-2 border-gray-200 text-gray-600
                                       font-bold text-[12px] bg-white cursor-pointer hover:border-gray-300">
                                    {{ logoPreview ? 'Cambiar imagen' : 'Subir imagen' }}
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label
                                class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Descripción</label>
                            <textarea v-model="form.descripcion" rows="2" placeholder="Breve descripción de qué ofrece"
                                class="modal-input w-full resize-none" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Categoría</label>
                                <input v-model="form.categoria" placeholder="Ej: Insumos" class="modal-input w-full" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Estado</label>
                                <select v-model="form.activo" class="modal-input w-full cursor-pointer">
                                    <option :value="true">Activo</option>
                                    <option :value="false">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Enlace
                                externo
                                *</label>
                            <input v-model="form.url_externa" placeholder="https://..." class="modal-input w-full" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10.5px] font-black uppercase tracking-widest text-gray-500">Mensaje de
                                descuento</label>
                            <input v-model="form.descuento_texto" placeholder="Ej: 10% mencionando que vienes de Birds"
                                class="modal-input w-full" />
                            <p class="text-[11px] text-gray-400 m-0">
                                Es solo un mensaje informativo — el descuento lo aplica el proveedor manualmente, no el
                                sistema.
                            </p>
                        </div>

                        <div v-if="modalError"
                            class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-red-50 border border-red-200 text-[12.5px] text-red-600">
                            <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
                            {{ modalError }}
                        </div>
                    </div>

                    <div class="p-6 pt-0 flex gap-3">
                        <button @click="closeModal" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600 font-bold text-[13.5px]
                               bg-white cursor-pointer hover:border-gray-300 transition-all duration-150">
                            Cancelar
                        </button>
                        <button @click="save" :disabled="saving"
                            class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold text-[13.5px]
                               border-none cursor-pointer hover:-translate-y-0.5 disabled:opacity-60 transition-all duration-150">
                            {{ saving ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmModal v-model="showDeleteModal" title="¿Eliminar este proveedor?"
            :message="`«${deleteTarget?.nombre}» se borrará de tu directorio de proveedores.`" variant="danger"
            confirm-label="Sí, eliminar" :loading="deleting" @confirm="confirmDelete" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
    PlusIcon, PencilIcon, TrashIcon, XMarkIcon, BuildingStorefrontIcon,
    ArrowTopRightOnSquareIcon, InformationCircleIcon, ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import api from '@/utils/api'
import { useAdminStore } from '@/stores/admin'
import ConfirmModal from '@/components/ConfirmModal.vue'

interface Proveedor {
    id: number
    nombre: string
    logo: string | null
    logo_url: string | null
    descripcion: string | null
    categoria: string | null
    url_externa: string
    descuento_texto: string | null
    clics: number
    activo: boolean
}

const admin = useAdminStore()
const proveedores = ref<Proveedor[]>([])
const loading = ref(true)

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/proveedores')
        proveedores.value = data.data
    } finally {
        loading.value = false
    }
}

onMounted(load)

// ── Detalle ──────────────────────────────────────────────
const detailTarget = ref<Proveedor | null>(null)

function openDetail(p: Proveedor) {
    detailTarget.value = p
}

function visitar(p: Proveedor) {
    p.clics++ // se ve al instante, sin esperar la respuesta del servidor
    api.post(`/admin/proveedores/${p.id}/clic`).catch(() => { })
    window.open(p.url_externa, '_blank', 'noopener')
}

// ── Modal crear/editar ──────────────────────────────────────
const showModal = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)
const modalError = ref('')
const fileInput = ref<HTMLInputElement | null>(null)
const logoFile = ref<File | null>(null)
const logoPreview = ref<string | null>(null)

const form = ref({
    nombre: '',
    descripcion: '',
    categoria: '',
    url_externa: '',
    descuento_texto: '',
    activo: true,
})

function resetForm() {
    form.value = { nombre: '', descripcion: '', categoria: '', url_externa: '', descuento_texto: '', activo: true }
    logoFile.value = null
    logoPreview.value = null
    modalError.value = ''
}

function openCreate() {
    resetForm()
    editingId.value = null
    showModal.value = true
}

function openEdit(p: Proveedor) {
    resetForm()
    editingId.value = p.id
    form.value = {
        nombre: p.nombre,
        descripcion: p.descripcion ?? '',
        categoria: p.categoria ?? '',
        url_externa: p.url_externa,
        descuento_texto: p.descuento_texto ?? '',
        activo: p.activo,
    }
    logoPreview.value = p.logo_url
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (!file) return
    logoFile.value = file
    logoPreview.value = URL.createObjectURL(file)
}

async function save() {
    if (!form.value.nombre || !form.value.url_externa) {
        modalError.value = 'Nombre y enlace externo son obligatorios'
        return
    }
    saving.value = true
    modalError.value = ''

    const fd = new FormData()
    fd.append('nombre', form.value.nombre)
    fd.append('descripcion', form.value.descripcion)
    fd.append('categoria', form.value.categoria)
    fd.append('url_externa', form.value.url_externa)
    fd.append('descuento_texto', form.value.descuento_texto)
    fd.append('activo', form.value.activo ? '1' : '0')
    if (logoFile.value) fd.append('logo', logoFile.value)

    try {
        const url = editingId.value ? `/admin/proveedores/${editingId.value}` : '/admin/proveedores'
        await api.post(url, fd, { headers: { 'Content-Type': undefined } })
        showModal.value = false
        await load()
    } catch (e: any) {
        modalError.value = e.response?.data?.message ?? 'Error al guardar'
    } finally {
        saving.value = false
    }
}

// ── Eliminar ─────────────────────────────────────────────
const deleteTarget = ref<Proveedor | null>(null)
const showDeleteModal = ref(false)
const deleting = ref(false)

function askDelete(p: Proveedor) {
    deleteTarget.value = p
    showDeleteModal.value = true
}

async function confirmDelete() {
    if (!deleteTarget.value) return
    deleting.value = true
    try {
        await api.delete(`/admin/proveedores/${deleteTarget.value.id}`)
        showDeleteModal.value = false
        deleteTarget.value = null
        await load()
    } finally {
        deleting.value = false
    }
}
</script>