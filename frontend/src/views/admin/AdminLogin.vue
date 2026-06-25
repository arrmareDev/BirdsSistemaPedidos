<template>
  <div class="min-h-screen flex" style="background: #FFFAF5;">

    <!-- ══ PANEL IZQUIERDO ══ -->
    <div class="hidden lg:flex w-[45%] flex-col items-center justify-center
                relative overflow-hidden"
      style="background: linear-gradient(160deg, #8B0000 0%, #C41E1E 45%, #A01010 100%);">

      <!-- Patrón -->
      <div class="absolute inset-0 opacity-[0.07]" style="background-image: repeating-conic-gradient(
          rgba(245,197,24,0.8) 0deg, transparent 5deg,
          transparent 15deg, rgba(245,197,24,0.8) 20deg
        );" />

      <!-- Gradiente inferior -->
      <div class="absolute bottom-0 inset-x-0 h-48"
        style="background: linear-gradient(to top, rgba(139,0,0,0.8), transparent)" />

      <!-- Contenido -->
      <div class="relative z-10 text-center px-10">
        <div class="w-24 h-24 rounded-3xl bg-white/10 border border-white/20
                    flex items-center justify-center mx-auto mb-6 backdrop-blur-sm">
          <img src="/images/logobirds.png" alt="Mahoma Chicken" class="w-16 h-16 object-contain rounded-2xl"
            @error="($event.target as HTMLImageElement).style.display = 'none'" />
        </div>

        <h1 class="font-black text-white text-[36px] leading-tight m-0 mb-3
                   uppercase tracking-tight" style="font-family:'Plus Jakarta Sans',sans-serif;
                 text-shadow: 0 4px 24px rgba(0,0,0,0.3);">
          Mahoma<br>Chicken
        </h1>

        <p class="text-white/60 text-[15px] m-0 mb-10">
          Sistema de gestión de pedidos
        </p>

        <!-- Stats decorativos -->
        <div class="flex gap-8 justify-center">
          <div v-for="stat in STATS" :key="stat.label" class="flex flex-col items-center gap-1.5">
            <span class="font-black text-[22px] leading-none"
              style="color:#F5C518; font-family:'Plus Jakarta Sans',sans-serif;">
              {{ stat.value }}
            </span>
            <span class="text-white/50 text-[11px] font-medium uppercase tracking-wider">
              {{ stat.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Emoji decorativo flotante -->
      <div class="absolute bottom-8 right-8 text-[80px] opacity-10
                  select-none floating-emoji">
        🍗
      </div>

      <!-- Roles disponibles -->
      <div class="absolute bottom-6 left-6 flex flex-col gap-1.5">
        <div v-for="rol in ROLES_INFO" :key="rol.label" class="flex items-center gap-2 px-3 py-1.5 rounded-xl
                 bg-white/8 border border-white/10 backdrop-blur-sm w-fit">
          <component :is="rol.icon" class="w-3.5 h-3.5 shrink-0" :class="rol.color" />
          <span class="text-[11px] font-semibold text-white/70">
            {{ rol.label }}
          </span>
          <span class="text-[10px] text-white/40">{{ rol.desc }}</span>
        </div>
      </div>
    </div>

    <!-- ══ PANEL DERECHO ══ -->
    <div class="flex-1 flex items-center justify-center p-5 lg:p-12">
      <div class="w-full max-w-[420px] bg-white rounded-3xl
                  border border-gray-100
                  shadow-[0_8px_40px_rgba(0,0,0,0.08)]
                  p-8 sm:p-10">

        <!-- Logo móvil -->
        <div class="flex items-center gap-3 mb-8 lg:hidden">
          <div class="w-11 h-11 rounded-2xl overflow-hidden
                      border-2 border-red-100 shrink-0">
            <img src="/images/logobirds.png" alt="Mahoma" class="w-full h-full object-cover"
              @error="($event.target as HTMLImageElement).style.display = 'none'" />
          </div>
          <div>
            <p class="font-black text-gray-900 text-[17px] m-0 leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Mahoma Chicken
            </p>
            <p class="text-[12px] text-gray-400 m-0 mt-0.5">
              Panel de administración
            </p>
          </div>
        </div>

        <!-- Encabezado -->
        <div class="mb-7">
          <h2 class="font-black text-gray-900 text-[26px] m-0 mb-1 tracking-tight"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            Bienvenido 👋
          </h2>
          <p class="text-gray-400 text-[14px] m-0">
            Ingresa tus credenciales para continuar
          </p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="handleLogin" novalidate class="flex flex-col gap-5">

          <!-- Email -->
          <div class="flex flex-col gap-1.5">
            <label for="email" class="text-[11px] font-black uppercase tracking-widest text-gray-500">
              Correo electrónico
            </label>
            <div class="relative">
              <EnvelopeIcon class="absolute left-3.5 top-1/2 -translate-y-1/2
                       w-4 h-4 text-gray-400 pointer-events-none" />
              <input id="email" v-model.trim="email" type="email" placeholder="admin@mahoma.pe" autocomplete="email"
                required :class="[
                  'w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 text-[14px]',
                  'text-gray-900 font-medium outline-none bg-gray-50',
                  'placeholder:text-gray-300 transition-all duration-200',
                  fieldError.email
                    ? 'border-red-300 bg-red-50/30 focus:border-red-400 focus:shadow-[0_0_0_4px_rgba(196,30,30,0.08)]'
                    : 'border-gray-100 focus:border-brand-red focus:bg-white focus:shadow-[0_0_0_4px_rgba(196,30,30,0.08)]',
                ]" @input="clearFieldError('email')" />
            </div>
            <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">
              <p v-if="fieldError.email" class="text-[11.5px] text-red-500 font-medium m-0
                       flex items-center gap-1">
                <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0" />
                {{ fieldError.email }}
              </p>
            </Transition>
          </div>

          <!-- Contraseña -->
          <div class="flex flex-col gap-1.5">
            <label for="password" class="text-[11px] font-black uppercase tracking-widest text-gray-500">
              Contraseña
            </label>
            <div class="relative">
              <LockClosedIcon class="absolute left-3.5 top-1/2 -translate-y-1/2
                       w-4 h-4 text-gray-400 pointer-events-none" />
              <input id="password" v-model="password" :type="showPass ? 'text' : 'password'" placeholder="••••••••"
                autocomplete="current-password" required :class="[
                  'w-full pl-10 pr-12 py-3.5 rounded-2xl border-2 text-[14px]',
                  'text-gray-900 font-medium outline-none bg-gray-50',
                  'placeholder:text-gray-300 transition-all duration-200',
                  fieldError.password
                    ? 'border-red-300 bg-red-50/30 focus:border-red-400 focus:shadow-[0_0_0_4px_rgba(196,30,30,0.08)]'
                    : 'border-gray-100 focus:border-brand-red focus:bg-white focus:shadow-[0_0_0_4px_rgba(196,30,30,0.08)]',
                ]" @input="clearFieldError('password')" />
              <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2
                       w-7 h-7 flex items-center justify-center
                       text-gray-400 cursor-pointer border-none bg-transparent
                       rounded-lg hover:bg-gray-100 hover:text-gray-600
                       transition-all duration-150"
                :aria-label="showPass ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                <EyeSlashIcon v-if="showPass" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
            <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">
              <p v-if="fieldError.password" class="text-[11.5px] text-red-500 font-medium m-0
                       flex items-center gap-1">
                <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0" />
                {{ fieldError.password }}
              </p>
            </Transition>
          </div>

          <!-- Error global -->
          <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
            <div v-if="globalError" class="flex items-center gap-2.5 px-4 py-3 rounded-2xl
                     bg-red-50 border border-red-200">
              <ExclamationTriangleIcon class="w-4 h-4 text-red-500 shrink-0" />
              <p class="text-[13px] text-red-700 font-medium m-0">
                {{ globalError }}
              </p>
            </div>
          </Transition>

          <!-- Botón submit -->
          <button type="submit" :disabled="loading" class="w-full py-4 rounded-2xl border-none cursor-pointer
                   font-black text-[15px] text-white uppercase tracking-wide
                   bg-brand-red mt-1
                   shadow-[0_6px_24px_rgba(196,30,30,0.30)]
                   hover:bg-red-700 hover:-translate-y-0.5
                   hover:shadow-[0_10px_32px_rgba(196,30,30,0.38)]
                   active:scale-[0.98] active:translate-y-0
                   disabled:opacity-50 disabled:cursor-not-allowed
                   disabled:hover:translate-y-0 disabled:hover:shadow-none
                   transition-all duration-200
                   flex items-center justify-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
            <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                     rounded-full animate-spin" />
            <template v-else>
              <ArrowRightEndOnRectangleIcon class="w-4 h-4" />
              Ingresar al panel
            </template>
          </button>
        </form>

        <!-- Footer card -->
        <div class="mt-7 pt-6 border-t border-gray-100
                    flex items-center justify-between">
          <RouterLink to="/" class="text-[13px] text-gray-400 no-underline
                   hover:text-gray-700 transition-colors duration-150
                   flex items-center gap-1">
            <ArrowLeftIcon class="w-3.5 h-3.5" />
            Volver a la tienda
          </RouterLink>
          <span class="text-[12px] text-gray-300 select-none">
            v1.0 · Mahoma
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'
import {
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  ArrowRightEndOnRectangleIcon,
  ArrowLeftIcon,
  StarIcon,
  FireIcon,
  BanknotesIcon,
  CpuChipIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const admin = useAdminStore()

// ── Estado ────────────────────────────────────────────────
const email = ref('')
const password = ref('')
const showPass = ref(false)
const loading = ref(false)
const globalError = ref('')
const fieldError = reactive({ email: '', password: '' })

// ── Panel izquierdo ───────────────────────────────────────
const STATS = [
  { value: '100%', label: 'Leña natural' },
  { value: '4.9⭐', label: 'Calificación' },
  { value: 'Diario', label: 'Servicio' },
]

const ROLES_INFO = [
  { label: 'Super Admin', desc: 'Acceso total', icon: StarIcon, color: 'text-yellow-400' },
  { label: 'Admin', desc: 'Gestión completa', icon: CpuChipIcon, color: 'text-blue-400' },
  { label: 'Cajero', desc: 'Pedidos & caja', icon: BanknotesIcon, color: 'text-green-400' },
]

// ── Validación ────────────────────────────────────────────
function validate(): boolean {
  let ok = true
  fieldError.email = ''
  fieldError.password = ''

  if (!email.value.trim()) {
    fieldError.email = 'El correo es requerido'
    ok = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    fieldError.email = 'Ingresa un correo válido'
    ok = false
  }

  if (!password.value) {
    fieldError.password = 'La contraseña es requerida'
    ok = false
  } else if (password.value.length < 6) {
    fieldError.password = 'Mínimo 6 caracteres'
    ok = false
  }

  return ok
}

function clearFieldError(field: 'email' | 'password') {
  fieldError[field] = ''
  globalError.value = ''
}

// ── Login ─────────────────────────────────────────────────
async function handleLogin() {
  globalError.value = ''
  fieldError.email = ''
  fieldError.password = ''

  if (!validate()) return

  loading.value = true
  try {
    const ok = await admin.login(email.value.trim(), password.value)
    if (ok) {
      // Redirige según el rol del usuario
      router.push(admin.homeRoute)
    } else {
      globalError.value = 'Correo o contraseña incorrectos. Intenta de nuevo.'
    }
  } catch {
    globalError.value = 'Error de conexión. Verifica tu red e intenta de nuevo.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.floating-emoji {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {

  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-14px);
  }
}
</style>