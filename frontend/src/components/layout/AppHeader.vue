<template>
  <header class="sticky top-0 z-50 bg-white backdrop-blur-xl
                 border-b border-gray-100 shadow-[0_1px_0_rgba(0,0,0,0.06)]">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 h-16">

        <!-- ── Logo ── -->
        <RouterLink to="/" class="flex items-center gap-2.5 no-underline shrink-0
                 group transition-opacity duration-150 hover:opacity-90">
          <!-- Logo imagen -->
          <div class="w-[76px] h-[76px] shrink-0 flex items-center justify-center">
            <img :src="logoUrl" alt="Florería" class="max-w-full max-h-full object-contain" @error="logoError = true"
              v-show="!logoError" />
          </div>
        </RouterLink>

        <!-- ── Nav desktop ── -->
        <nav class="hidden md:flex items-center gap-1 ml-4" aria-label="Navegación principal">
          <RouterLink v-for="link in NAV_LINKS" :key="link.to" :to="link.to" class="px-4 py-2 rounded-full text-[13px] font-semibold no-underline
                   transition-all duration-200" :class="isActiveLink(link.to)
                    ? 'bg-red-50 text-red-600 border border-red-200 font-bold'
                    : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'">
            {{ link.label }}
          </RouterLink>
        </nav>

        <!-- ── Spacer ── -->
        <div class="flex-1" />

        <!-- ── Badge abierto/cerrado ── -->
        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full border" :class="isOpen
          ? 'bg-green-50 border-green-200'
          : 'bg-gray-50 border-gray-200'">
          <div class="w-1.5 h-1.5 rounded-full" :class="isOpen ? 'bg-green-500 animate-pulse' : 'bg-gray-400'" />
          <span class="text-[11.5px] font-semibold" :class="isOpen ? 'text-green-700' : 'text-gray-500'">
            {{ isOpen ? 'Abierto' : 'Cerrado' }}
          </span>
        </div>

        <!-- ── Carrito ── -->
        <button @click="$emit('openCart')" class="relative flex items-center gap-2 px-4 py-2.5 rounded-full
                 font-bold text-[13.5px] cursor-pointer border-2
                 transition-all duration-200" :class="cartStore.count > 0
                  ? 'bg-green-600 text-white border-green-600 shadow-[0_4px_12px_rgba(196,30,30,0.3)] hover:bg-green-700'
                  : 'bg-white text-gray-500 border-gray-200 hover:border-green-400 hover:text-green-600'"
          aria-label="Ver carrito">

          <span class="text-base leading-none">🛒</span>

          <!-- Precio cuando hay items -->
          <span v-if="cartStore.count > 0" class="font-black hidden sm:inline">
            S/ {{ cartStore.total.toFixed(2) }}
          </span>
          <span v-else class="hidden sm:inline">Carrito</span>

          <!-- Badge contador -->
          <Transition enter-active-class="transition-transform duration-200" enter-from-class="scale-0"
            enter-to-class="scale-100" leave-active-class="transition-transform duration-150" leave-to-class="scale-0">
            <span v-if="cartStore.count > 0" class="absolute -top-2 -right-2 w-5 h-5 rounded-full
                     bg-yellow-400 text-gray-900 text-[10px] font-black
                     flex items-center justify-center
                     border-2 border-white shadow-sm">
              {{ cartStore.count > 9 ? '9+' : cartStore.count }}
            </span>
          </Transition>
        </button>

        <!-- ── Menú hamburguesa móvil ── -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden w-9 h-9 rounded-xl border-2 border-gray-200 bg-white
                 flex items-center justify-center text-gray-500 cursor-pointer
                 hover:border-red-400 hover:text-red-600
                 transition-all duration-150" :aria-expanded="mobileMenuOpen" aria-label="Menú">
          <span class="text-lg leading-none">{{ mobileMenuOpen ? '✕' : '☰' }}</span>
        </button>

      </div>
    </div>

    <!-- ── Menú móvil desplegable ── -->
    <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition-all duration-150" leave-to-class="opacity-0 -translate-y-2">
      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white px-4 py-3
               flex flex-col gap-1">
        <RouterLink v-for="link in NAV_LINKS" :key="link.to" :to="link.to" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[14px]
                 font-semibold no-underline transition-all duration-150" :class="isActiveLink(link.to)
                  ? 'bg-red-50 text-green-600'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
          <span>{{ link.icon }}</span>
          {{ link.label }}
        </RouterLink>

        <!-- Separador -->
        <div class="h-px bg-gray-100 my-1" />

        <!-- Info contacto en móvil — ← teléfono real -->
        <a href="tel:+51932488837" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[14px]
                 font-semibold no-underline text-gray-600
                 hover:bg-gray-50 hover:text-gray-900 transition-all duration-150">
          📞 932 488 837
        </a>
      </div>
    </Transition>
  </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useCartStore } from '@/stores/cart'

// ── Emits ─────────────────────────────────────────────────
defineEmits<{ openCart: [] }>()

// ── Composables ───────────────────────────────────────────
const route = useRoute()
const cartStore = useCartStore()

// ── Estado local ──────────────────────────────────────────
const logoError = ref(false)
const mobileMenuOpen = ref(false)

const logoUrl = '/images/logobirds.png'

// ── Navegación ────────────────────────────────────────────
const NAV_LINKS = [
  { to: '/', icon: '🌸', label: 'Catálogo' },
  { to: '/seguimiento', icon: '📦', label: 'Seguir pedido' },
]

function isActiveLink(path: string): boolean {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

// ── Estado abierto/cerrado (Lun–Sáb 9am–8pm) ──────────────
// Footer.vue — corregir isOpen
const isOpen = computed(() => {
  const now = new Date()
  const day = now.getDay()
  if (day === 0) return false // domingo cerrado
  const h = now.getHours()
  return h >= 9 && h < 22   // 9am – 10pm
})
</script>