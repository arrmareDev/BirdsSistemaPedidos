<template>
    <div class="flex flex-col gap-5">

        <!-- ══ MODAL CREAR / EDITAR ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-250"
                leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-[300] bg-black/50 backdrop-blur-sm
                 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeModal">
                    <Transition enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="translate-y-4 opacity-0 sm:scale-95" leave-to-class="translate-y-4 opacity-0">
                        <div v-if="showModal" class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl
                     shadow-2xl flex flex-col overflow-hidden
                     max-h-[95vh] sm:max-h-[85vh]">

                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-4
                          border-b border-gray-100 shrink-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-50 border border-red-100
                              flex items-center justify-center">
                                        <UserPlusIcon class="w-5 h-5 text-brand-red" />
                                    </div>
                                    <div>
                                        <h2 class="font-black text-[17px] text-gray-900 m-0 leading-none"
                                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                                            {{ editingUser ? 'Editar usuario' : 'Nuevo usuario' }}
                                        </h2>
                                        <p class="text-[11px] text-gray-400 m-0 mt-0.5">
                                            {{ editingUser ? editingUser.email : 'Crear acceso al panel' }}
                                        </p>
                                    </div>
                                </div>
                                <button @click="closeModal" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
                         justify-center cursor-pointer border-none
                         hover:bg-gray-200 transition-colors">
                                    <XMarkIcon class="w-4 h-4 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-4">

                                <!-- Nombre -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10.5px] font-black uppercase
                                tracking-widest text-gray-500">
                                        Nombre completo *
                                    </label>
                                    <input v-model="form.name" placeholder="Ej: Juan García" class="modal-input" />
                                </div>

                                <!-- Email -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10.5px] font-black uppercase
                                tracking-widest text-gray-500">
                                        Correo electrónico *
                                    </label>
                                    <input v-model="form.email" type="email" placeholder="usuario@mahoma.pe"
                                        class="modal-input" />
                                </div>

                                <!-- Contraseña -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10.5px] font-black uppercase
                                tracking-widest text-gray-500">
                                        {{ editingUser ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}
                                    </label>
                                    <div class="relative">
                                        <input v-model="form.password" :type="showPass ? 'text' : 'password'"
                                            placeholder="Mínimo 8 caracteres" class="modal-input pr-12" />
                                        <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2
                             w-7 h-7 flex items-center justify-center
                             text-gray-400 cursor-pointer border-none
                             bg-transparent rounded-lg hover:bg-gray-100
                             transition-colors">
                                            <EyeSlashIcon v-if="showPass" class="w-4 h-4" />
                                            <EyeIcon v-else class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Rol -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10.5px] font-black uppercase
                                tracking-widest text-gray-500">
                                        Rol *
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button v-for="r in ROLES" :key="r.value" type="button"
                                            @click="form.role = r.value" class="flex items-center gap-2.5 px-3.5 py-3 rounded-2xl
                             border-2 text-left cursor-pointer
                             transition-all duration-150" :class="form.role === r.value
                                ? 'border-brand-red bg-red-50'
                                : 'border-gray-100 bg-gray-50 hover:border-red-200'">
                                            <div class="w-7 h-7 rounded-lg flex items-center
                                  justify-center shrink-0" :class="r.bg">
                                                <component :is="r.icon" class="w-4 h-4" :class="r.color" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-[12.5px] text-gray-900 m-0
                                  leading-none">
                                                    {{ r.label }}
                                                </p>
                                                <p class="text-[10.5px] text-gray-400 m-0 mt-0.5 leading-snug">
                                                    {{ r.desc }}
                                                </p>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Error -->
                                <Transition enter-active-class="transition-all duration-150"
                                    enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                                    <div v-if="modalError" class="flex items-center gap-2.5 px-4 py-3 rounded-2xl
                           bg-red-50 border border-red-200">
                                        <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                                        <p class="text-[12.5px] text-red-700 m-0">{{ modalError }}</p>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Footer -->
                            <div class="p-5 border-t border-gray-100 shrink-0">
                                <button @click="saveUser" :disabled="submitting || !canSave" class="w-full py-3.5 rounded-2xl font-black text-[14px]
                         text-white border-none cursor-pointer
                         bg-brand-red uppercase tracking-wide
                         shadow-[0_4px_20px_rgba(196,30,30,0.3)]
                         hover:bg-red-700 hover:-translate-y-0.5
                         active:scale-[0.98] transition-all duration-200
                         disabled:opacity-40 disabled:cursor-not-allowed
                         disabled:hover:translate-y-0
                         flex items-center justify-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
                                    <span v-if="submitting" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                                    <CheckCircleIcon v-else class="w-4 h-4" />
                                    {{ submitting ? 'Guardando...' : (editingUser ? 'Guardar cambios' : 'Crear usuario')
                                    }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ MODAL CONFIRMAR ELIMINAR ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="deleteModal.show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="deleteModal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="deleteModal.show"
                            class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-red-50 mx-auto mb-4
                          flex items-center justify-center">
                                <TrashIcon class="w-7 h-7 text-red-500" />
                            </div>
                            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                ¿Eliminar usuario?
                            </h3>
                            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
                                Se eliminará a
                                <strong class="text-gray-700">{{ deleteModal.user?.name }}</strong>
                                permanentemente. Esta acción
                                <strong class="text-red-600">no se puede deshacer</strong>.
                            </p>
                            <div class="flex gap-3">
                                <button @click="deleteModal.show = false" class="flex-1 py-3 rounded-2xl border-2 border-gray-200
                         text-gray-600 font-semibold text-[13.5px]
                         cursor-pointer bg-white hover:border-gray-300
                         transition-all duration-150">
                                    Cancelar
                                </button>
                                <button @click="confirmDelete" :disabled="deleteModal.loading" class="flex-1 py-3 rounded-2xl text-white font-bold
                         text-[13.5px] cursor-pointer border-none
                         bg-red-600 hover:bg-red-700
                         disabled:opacity-50
                         transition-all duration-150
                         flex items-center justify-center gap-2">
                                    <span v-if="deleteModal.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                                    {{ deleteModal.loading ? 'Eliminando...' : 'Sí, eliminar' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ HEADER ══ -->
        <div class="flex items-center gap-3 flex-wrap">

            <!-- Búsqueda -->
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2
                 w-3.5 h-3.5 text-gray-400" />
                <input v-model="search" placeholder="Buscar usuario..." class="pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 bg-white
                 text-[13px] text-gray-900 outline-none w-48
                 focus:border-brand-red transition-all duration-200
                 placeholder:text-gray-300" />
            </div>

            <!-- Filtro por rol -->
            <div class="flex gap-1.5 flex-wrap">
                <button v-for="r in ROLE_FILTERS" :key="r.value" @click="roleFilter = r.value" class="px-3 py-1.5 rounded-full text-[12px] font-semibold border
                 transition-all duration-150 cursor-pointer" :class="roleFilter === r.value
                    ? 'bg-brand-red text-white border-brand-red shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:border-red-300'">
                    {{ r.label }}
                </button>
            </div>

            <!-- Nuevo usuario -->
            <button @click="openModal()" class="ml-auto flex items-center gap-1.5 px-4 py-1.5 rounded-xl
               bg-brand-red text-white font-bold text-[13px] border-none
               cursor-pointer shadow-sm hover:bg-red-700
               transition-all duration-150">
                <PlusIcon class="w-3.5 h-3.5" />
                Nuevo usuario
            </button>
        </div>

        <!-- ══ SKELETON ══ -->
        <div v-if="loading" class="flex flex-col gap-3">
            <div v-for="n in 4" :key="n" class="h-20 rounded-2xl bg-gray-100 animate-pulse" />
        </div>

        <!-- ══ EMPTY ══ -->
        <div v-else-if="filtered.length === 0" class="flex flex-col items-center py-20 gap-4 text-gray-400">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center">
                <UsersIcon class="w-10 h-10 text-gray-300" />
            </div>
            <div class="text-center">
                <p class="font-bold text-gray-600 text-[15px] m-0">Sin usuarios</p>
                <p class="text-[13px] m-0 mt-1">
                    {{ search ? `No encontramos "${search}"` : 'Crea el primer usuario' }}
                </p>
            </div>
        </div>

        <!-- ══ TABLA USUARIOS ══ -->
        <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <!-- Header tabla -->
            <div class="grid grid-cols-[1fr_auto_auto_auto] gap-4 px-5 py-3
                  border-b border-gray-100 bg-gray-50/50">
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                    Usuario
                </span>
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                    Rol
                </span>
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400
                     hidden sm:block">
                    Creado
                </span>
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
                    Acciones
                </span>
            </div>

            <!-- Filas -->
            <TransitionGroup name="user-row">
                <div v-for="u in filtered" :key="u.id" class="grid grid-cols-[1fr_auto_auto_auto] gap-4 items-center
                 px-5 py-4 border-b border-gray-50 last:border-0
                 hover:bg-gray-50/50 transition-colors duration-100">

                    <!-- Info usuario -->
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center
                        text-white text-[13px] font-black shrink-0" :class="avatarBg(u.role)">
                            {{ initials(u.name) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-[14px] text-gray-900 m-0 leading-none truncate">
                                    {{ u.name }}
                                </p>
                                <!-- Tú mismo -->
                                <span v-if="u.id === adminStore.user?.id" class="text-[9px] font-black px-1.5 py-0.5 rounded-full
                         bg-blue-50 text-blue-600 border border-blue-200
                         leading-none shrink-0">
                                    TÚ
                                </span>
                            </div>
                            <p class="text-[12px] text-gray-400 m-0 mt-0.5 truncate">
                                {{ u.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Rol -->
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                         text-[11px] font-bold" :class="roleCls(u.role)">
                            <component :is="roleIcon(u.role)" class="w-3 h-3 shrink-0" />
                            {{ roleLabel(u.role) }}
                        </span>
                    </div>

                    <!-- Fecha -->
                    <span class="text-[12px] text-gray-400 hidden sm:block shrink-0">
                        {{ formatDate(u.created_at) }}
                    </span>

                    <!-- Acciones -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button @click="openModal(u)" class="w-8 h-8 rounded-xl flex items-center justify-center
                     border border-gray-200 bg-white text-gray-400 cursor-pointer
                     hover:border-brand-red hover:text-brand-red
                     transition-all duration-150" title="Editar usuario">
                            <PencilIcon class="w-3.5 h-3.5" />
                        </button>
                        <button v-if="u.id !== adminStore.user?.id" @click="askDelete(u)" class="w-8 h-8 rounded-xl flex items-center justify-center
                     border border-gray-200 bg-white text-gray-400 cursor-pointer
                     hover:border-red-400 hover:text-red-500
                     transition-all duration-150" title="Eliminar usuario">
                            <TrashIcon class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div v-for="kpi in kpiRoles" :key="kpi.label"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" :class="kpi.bg">
                        <component :is="kpi.icon" class="w-4 h-4" :class="kpi.color" />
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-400">
                        {{ kpi.label }}
                    </span>
                </div>
                <p class="font-black text-[28px] text-gray-900 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ kpi.count }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import api from '@/utils/api'
import { useAdminStore } from '@/stores/admin'
import {
    PlusIcon, XMarkIcon, TrashIcon, PencilIcon,
    MagnifyingGlassIcon, CheckCircleIcon,
    UsersIcon, UserPlusIcon, ExclamationCircleIcon,
    EyeIcon, EyeSlashIcon,
    StarIcon, CpuChipIcon, BanknotesIcon, FireIcon,
} from '@heroicons/vue/24/outline'

const adminStore = useAdminStore()

// ── Estado ────────────────────────────────────────────────
const users = ref<any[]>([])
const loading = ref(false)
const search = ref('')
const roleFilter = ref('')
const showModal = ref(false)
const showPass = ref(false)
const submitting = ref(false)
const modalError = ref('')
const editingUser = ref<any>(null)

const deleteModal = reactive({
    show: false,
    user: null as any,
    loading: false,
})

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: 'admin' as string,
})

// ── Constantes ────────────────────────────────────────────
const ROLES = [
    {
        value: 'super_admin',
        label: 'Super Admin',
        desc: 'Acceso total',
        icon: StarIcon,
        bg: 'bg-yellow-50',
        color: 'text-yellow-600',
    },
    {
        value: 'admin',
        label: 'Admin',
        desc: 'Gestión completa',
        icon: CpuChipIcon,
        bg: 'bg-blue-50',
        color: 'text-blue-600',
    },
    {
        value: 'cajero',
        label: 'Cajero',
        desc: 'Pedidos & caja',
        icon: BanknotesIcon,
        bg: 'bg-green-50',
        color: 'text-green-600',
    },

]

const ROLE_FILTERS = [
    { value: '', label: 'Todos' },
    { value: 'super_admin', label: 'Super Admin' },
    { value: 'admin', label: 'Admin' },
    { value: 'cajero', label: 'Cajero' },
    { value: 'sistema', label: 'Sistema' },
]

// ── Computed ──────────────────────────────────────────────
const filtered = computed(() => {
    let list = users.value
    if (roleFilter.value) {
        list = list.filter(u => u.role === roleFilter.value)
    }
    if (search.value.trim()) {
        const q = search.value.toLowerCase()
        list = list.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q)
        )
    }
    return list
})

const canSave = computed(() =>
    form.name.trim() &&
    form.email.trim() &&
    form.role &&
    (editingUser.value ? true : form.password.length >= 8)
)

const kpiRoles = computed(() => ROLES.map(r => ({
    label: r.label,
    count: users.value.filter(u => u.role === r.value).length,
    icon: r.icon,
    bg: r.bg,
    color: r.color,
})))

// ── API ───────────────────────────────────────────────────
async function fetchUsers() {
    loading.value = true
    try {
        const { data } = await api.get('/admin/users')
        users.value = data.data ?? data
    } catch (e) {
        console.error('Error cargando usuarios:', e)
    } finally {
        loading.value = false
    }
}

async function saveUser() {
    if (!canSave.value) return
    submitting.value = true
    modalError.value = ''
    try {
        const payload: any = {
            name: form.name.trim(),
            email: form.email.trim(),
            role: form.role,
        }
        if (form.password) payload.password = form.password

        if (editingUser.value) {
            await api.put(`/admin/users/${editingUser.value.id}`, payload)
        } else {
            await api.post('/admin/users', payload)
        }
        await fetchUsers()
        closeModal()
    } catch (e: any) {
        modalError.value = e.response?.data?.message ?? 'Error al guardar usuario'
    } finally {
        submitting.value = false
    }
}

async function confirmDelete() {
    if (!deleteModal.user) return
    deleteModal.loading = true
    try {
        await api.delete(`/admin/users/${deleteModal.user.id}`)
        await fetchUsers()
    } catch (e: any) {
        console.error('Error eliminando:', e)
    } finally {
        deleteModal.show = false
        deleteModal.loading = false
        deleteModal.user = null
    }
}

// ── Modal ─────────────────────────────────────────────────
function openModal(user?: any) {
    editingUser.value = user ?? null
    modalError.value = ''
    showPass.value = false
    if (user) {
        form.name = user.name
        form.email = user.email
        form.role = user.role
        form.password = ''
    } else {
        form.name = ''
        form.email = ''
        form.role = 'admin'
        form.password = ''
    }
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingUser.value = null
    modalError.value = ''
}

function askDelete(user: any) {
    deleteModal.user = user
    deleteModal.show = true
    deleteModal.loading = false
}

// ── Helpers ───────────────────────────────────────────────
function initials(name: string): string {
    return name.split(' ')
        .map(w => w[0] ?? '')
        .join('')
        .slice(0, 2)
        .toUpperCase()
}

function avatarBg(role: string): string {
    const m: Record<string, string> = {
        super_admin: 'bg-yellow-500',
        admin: 'bg-blue-500',
        cajero: 'bg-green-500',        
        sistema: 'bg-gray-500',
    }
    return m[role] ?? 'bg-gray-400'
}

function roleCls(role: string): string {
    const m: Record<string, string> = {
        super_admin: 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        admin: 'bg-blue-50 text-blue-700 border border-blue-200',
        cajero: 'bg-green-50 text-green-700 border border-green-200',
        sistema: 'bg-gray-50 text-gray-700 border border-gray-200',
    }
    return m[role] ?? 'bg-gray-50 text-gray-600 border border-gray-200'
}

function roleLabel(role: string): string {
    const m: Record<string, string> = {
        super_admin: 'Super Admin',
        admin: 'Admin',
        cajero: 'Cajero',
        sistema: 'Sistema',
    }
    return m[role] ?? role
}

function roleIcon(role: string): any {
    const m: Record<string, any> = {
        super_admin: StarIcon,
        admin: CpuChipIcon,
        cajero: BanknotesIcon,
        sistema: CpuChipIcon,
    }
    return m[role] ?? UsersIcon
}

function formatDate(d: string): string {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric',
    })
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => fetchUsers())
</script>

<style scoped>
.modal-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border-radius: 0.875rem;
    border: 2px solid #f3f4f6;
    background: #f9fafb;
    font-size: 13px;
    color: #111827;
    outline: none;
    transition: all 0.2s;
}

.modal-input::placeholder {
    color: #d1d5db;
}

.modal-input:focus {
    border-color: #C41E1E;
    background: white;
    box-shadow: 0 0 0 3px rgba(196, 30, 30, 0.08);
}

.user-row-enter-active {
    transition: all 0.2s ease;
}

.user-row-leave-active {
    transition: all 0.15s ease;
}

.user-row-enter-from {
    opacity: 0;
    transform: translateX(-8px);
}

.user-row-leave-to {
    opacity: 0;
}
</style>