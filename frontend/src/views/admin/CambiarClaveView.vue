<template>
    <div class="min-h-screen flex items-center justify-center p-5" style="background: #FFFAF5;">
        <div class="w-full max-w-[420px] bg-white rounded-3xl border border-gray-100
                shadow-[0_8px_40px_rgba(0,0,0,0.08)] p-8 sm:p-10">

            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center mb-6">
                <LockClosedIcon class="w-6 h-6 text-amber-600" />
            </div>

            <h2 class="font-black text-gray-900 text-[24px] m-0 mb-1 tracking-tight"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Cambia tu contraseña
            </h2>
            <p class="text-gray-400 text-[14px] m-0 mb-7">
                Tu contraseña fue reseteada. Por seguridad, debes ponerle una nueva antes de continuar.
            </p>

            <form @submit.prevent="handleSubmit" class="flex flex-col gap-5">

                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                        Contraseña temporal (la que usaste para entrar)
                    </label>
                    <input v-model="currentPassword" type="password" required class="form-input"
                        placeholder="••••••••" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                        Nueva contraseña
                    </label>
                    <input v-model="newPassword" type="password" required minlength="8" class="form-input"
                        placeholder="Mínimo 8 caracteres" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                        Confirma la nueva contraseña
                    </label>
                    <input v-model="confirmPassword" type="password" required class="form-input"
                        placeholder="••••••••" />
                </div>

                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-2"
                    leave-to-class="opacity-0">
                    <div v-if="errorMsg"
                        class="flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-red-50 border border-red-200">
                        <ExclamationTriangleIcon class="w-4 h-4 text-red-500 shrink-0" />
                        <p class="text-[13px] text-red-700 font-medium m-0">{{ errorMsg }}</p>
                    </div>
                </Transition>

                <button type="submit" :disabled="loading" class="w-full py-4 rounded-2xl border-none cursor-pointer
                 font-black text-[15px] text-white uppercase tracking-wide mt-1
                 bg-brand-red shadow-red-md hover:bg-brand-red-dark
                 disabled:opacity-50 disabled:cursor-not-allowed
                 transition-all duration-200 flex items-center justify-center gap-2">
                    <span v-if="loading"
                        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                    {{ loading ? 'Guardando...' : 'Guardar y continuar' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'
import { LockClosedIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const admin = useAdminStore()

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const errorMsg = ref('')

async function handleSubmit() {
    errorMsg.value = ''

    if (newPassword.value !== confirmPassword.value) {
        errorMsg.value = 'Las contraseñas nuevas no coinciden'
        return
    }
    if (newPassword.value.length < 8) {
        errorMsg.value = 'La contraseña nueva debe tener al menos 8 caracteres'
        return
    }

    loading.value = true
    const result = await admin.changePassword(
        currentPassword.value,
        newPassword.value,
        confirmPassword.value,
    )
    loading.value = false

    if (result.ok) {
        router.push(admin.homeRoute)
    } else {
        errorMsg.value = result.error ?? 'Error al cambiar la contraseña'
    }
}
</script>

<style scoped>
.form-input {
    width: 100%;
    padding: 0.85rem 1rem;
    border-radius: 1rem;
    border: 2px solid #F3F4F6;
    background: #F9FAFB;
    font-size: 14px;
    color: #111827;
    outline: none;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus {
    border-color: var(--color-brand-primary, #C41E1E);
    background: white;
    box-shadow: 0 0 0 3px rgba(var(--color-brand-primary-rgb, 196, 30, 30), 0.08);
}
</style>