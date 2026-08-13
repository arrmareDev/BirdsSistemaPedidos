<template>
  <header class="sticky top-0 z-50 bg-white backdrop-blur-xl
                 border-b border-gray-100 shadow-[0_1px_0_rgba(0,0,0,0.06)]">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 h-16">

        <RouterLink to="/" class="flex items-center gap-2.5 no-underline shrink-0
                 group transition-opacity duration-150 hover:opacity-90">
          <div class="w-[76px] h-[76px] shrink-0 flex items-center justify-center">
            <div v-if="!brandingStore.loaded" class="w-10 h-10 rounded-xl bg-gray-100 animate-pulse" />
            <img v-else :src="brandingStore.branding.logo_url" :alt="brandingStore.branding.nombre_negocio"
              class="max-w-full max-h-full object-contain" @error="logoError = true" v-show="!logoError" />
          </div>
        </RouterLink>

        <nav class="hidden md:flex items-center gap-1 ml-4 shrink-0" aria-label="Navegación principal">
          <RouterLink v-for="link in NAV_LINKS" :key="link.to" :to="link.to" class="px-4 py-2 rounded-full text-[13px] font-semibold no-underline
                    transition-all duration-200" :class="isActiveLink(link.to)
                      ? 'bg-brand-bg text-brand-green border border-brand-green3 font-bold'
                      : 'text-gray-500 hover:text-brand-green hover:bg-surface-warm'">
            {{ link.label }}
          </RouterLink>
        </nav>

        <!-- Buscador general — siempre visible, con resultados en vivo -->
        <div class="flex-1" />

        <div ref="searchWrapRef" class="relative w-44 sm:w-56 lg:w-72 ml-2">
          <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
          <input v-model="searchInput" @input="onSearchInput" @keydown.enter="onSearchEnter"
            @focus="searchFocused = true" type="search" placeholder="Buscar productos..." class="search-input-clean w-full pl-9 pr-8 py-2 rounded-full border-2 border-gray-200 bg-gray-50
                   text-[13px] text-gray-900 outline-none focus:border-brand-green focus:bg-white
                   transition-all duration-150 placeholder:text-gray-400" />
          <button v-if="searchInput" @click="clearSearch" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full
                   flex items-center justify-center border-none bg-transparent cursor-pointer
                   text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all duration-150"
            aria-label="Limpiar búsqueda">
            <X :size="13" />
          </button>

          <!-- Dropdown de resultados en vivo -->
          <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
            leave-to-class="opacity-0">
            <div v-if="showDropdown" class="absolute top-[calc(100%+8px)] right-0 w-[300px] sm:w-[380px]
                     bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">

              <div v-if="searchLoading" class="flex items-center justify-center py-8">
                <span class="w-5 h-5 border-2 border-gray-200 border-t-brand-green rounded-full animate-spin" />
              </div>

              <div v-else-if="searchResults.length === 0" class="px-4 py-6 text-center">
                <p class="text-[13px] text-gray-400 m-0">
                  Sin resultados para «{{ searchInput }}»
                </p>
              </div>

              <template v-else>
                <RouterLink v-for="p in searchResults" :key="p.id" :to="`/producto/${p.slug}`" @click="clearSearch"
                  class="flex items-center gap-3 px-4 py-2.5 no-underline
                         hover:bg-surface-warm transition-colors duration-100 border-b border-gray-50 last:border-b-0">
                  <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 shrink-0
                              flex items-center justify-center overflow-hidden">
                    <img v-if="p.image_url" :src="p.image_url" class="w-full h-full object-cover" />
                    <AppIcon v-else :name="p.icon" :size="16" class="text-gray-300" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] font-semibold text-gray-900 m-0 truncate">{{ p.name }}</p>
                    <p class="text-[12px] font-black text-brand-red m-0">S/ {{ p.price.toFixed(2) }}</p>
                  </div>
                </RouterLink>

                <button v-if="searchMeta && searchMeta.total > searchResults.length" @click="viewAllResults" class="w-full py-2.5 text-[12px] font-bold text-brand-green cursor-pointer
                         border-none bg-surface-warm hover:bg-brand-bg transition-colors duration-150">
                  Ver los {{ searchMeta.total }} resultados →
                </button>
              </template>
            </div>
          </Transition>
        </div>

        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full border shrink-0" :class="isOpen
          ? 'bg-brand-bg border-brand-green/30'
          : 'bg-gray-50 border-gray-200'">
          <div class="w-1.5 h-1.5 rounded-full" :class="isOpen ? 'bg-brand-green animate-pulse' : 'bg-gray-400'" />
          <span class="text-[11.5px] font-semibold" :class="isOpen ? 'text-brand-green' : 'text-gray-500'">
            {{ isOpen ? 'Abierto' : 'Cerrado' }}
          </span>
        </div>

        <button @click="$emit('openCart')" class="relative flex items-center gap-2 px-4 py-2.5 rounded-full
                 font-bold text-[13.5px] cursor-pointer border-2 shrink-0
                 transition-all duration-200" :class="cartStore.count > 0
                  ? 'bg-brand-green text-white border-brand-green shadow-green-sm hover:bg-brand-green2'
                  : 'bg-white text-gray-500 border-gray-200 hover:border-brand-green hover:text-brand-green'"
          aria-label="Ver carrito">

          <ShoppingCart :size="16" />

          <span v-if="cartStore.count > 0" class="font-black hidden sm:inline">
            S/ {{ cartStore.total.toFixed(2) }}
          </span>
          <span v-else class="hidden sm:inline">Carrito</span>

          <Transition enter-active-class="transition-transform duration-200" enter-from-class="scale-0"
            enter-to-class="scale-100" leave-active-class="transition-transform duration-150" leave-to-class="scale-0">
            <span v-if="cartStore.count > 0" class="absolute -top-2 -right-2 w-5 h-5 rounded-full
                     bg-brand-accent text-brand-dark text-[10px] font-black
                     flex items-center justify-center
                     border-2 border-white shadow-sm">
              {{ cartStore.count > 9 ? '9+' : cartStore.count }}
            </span>
          </Transition>
        </button>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden w-9 h-9 rounded-xl border-2 border-gray-200 bg-white
                 flex items-center justify-center text-gray-500 cursor-pointer shrink-0
                 hover:border-brand-green hover:text-brand-green
                 transition-all duration-150" :aria-expanded="mobileMenuOpen" aria-label="Menú">
          <X v-if="mobileMenuOpen" :size="18" />
          <Menu v-else :size="18" />
        </button>

      </div>
    </div>

    <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition-all duration-150" leave-to-class="opacity-0 -translate-y-2">
      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white px-4 py-3
               flex flex-col gap-1 shadow-card">
        <RouterLink v-for="link in NAV_LINKS" :key="link.to" :to="link.to" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[14px]
                 font-semibold no-underline transition-all duration-150" :class="isActiveLink(link.to)
                  ? 'bg-brand-bg text-brand-green font-bold'
                  : 'text-gray-600 hover:bg-surface-warm hover:text-brand-green'">
          <AppIcon :name="link.icon" :size="18" />
          {{ link.label }}
        </RouterLink>

        <div class="h-px bg-gray-100 my-1" />

        <a v-if="brandingStore.branding.telefono" :href="`tel:+51${brandingStore.branding.telefono}`" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[14px]
                 font-semibold no-underline text-brand-green
                 hover:bg-brand-bg transition-all duration-150">
          <Phone :size="16" /> {{ brandingStore.branding.telefono }}
        </a>
      </div>
    </Transition>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useBrandingStore } from '@/stores/branding'
import { useProductsStore } from '@/stores/products'
import { ShoppingCart, X, Menu, Phone, Search } from 'lucide-vue-next'
import AppIcon from '@/components/AppIcon.vue'
import api from '@/utils/api'

// ── Emits ─────────────────────────────────────────────────
defineEmits<{ openCart: [] }>()

// ── Composables ───────────────────────────────────────────
const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const brandingStore = useBrandingStore()
const productsStore = useProductsStore()

// ── Estado local ──────────────────────────────────────────
const logoError = ref(false)
const mobileMenuOpen = ref(false)

// ── Búsqueda general con resultados en vivo ────────────────
interface SearchHit { id: number; slug: string; name: string; price: number; icon: string | null; image_url: string | null }

const searchInput = ref('')
const searchFocused = ref(false)
const searchResults = ref<SearchHit[]>([])
const searchLoading = ref(false)
const searchMeta = ref<{ total: number } | null>(null)
const searchWrapRef = ref<HTMLElement | null>(null)
let searchDebounce: ReturnType<typeof setTimeout> | null = null
let searchRequestId = 0

const showDropdown = computed(() => searchFocused.value && searchInput.value.trim().length > 0)

function onSearchInput() {
  clearTimeout(searchDebounce!)
  const term = searchInput.value.trim()
  if (!term) {
    searchResults.value = []
    searchMeta.value = null
    return
  }
  searchDebounce = setTimeout(() => fetchLiveResults(term), 300)
}

async function fetchLiveResults(term: string) {
  const requestId = ++searchRequestId
  searchLoading.value = true
  try {
    const { data } = await api.get('/products', { params: { q: term, per_page: 5 } })
    if (requestId !== searchRequestId) return // llegó una búsqueda más nueva mientras esperábamos
    searchResults.value = data.data.data
    searchMeta.value = data.data.meta
  } catch {
    if (requestId === searchRequestId) { searchResults.value = []; searchMeta.value = null }
  } finally {
    if (requestId === searchRequestId) searchLoading.value = false
  }
}

// Enter: si hay una sola coincidencia, va directo al producto.
// Si hay varias (o ninguna todavía cargada), muestra todos los resultados.
function onSearchEnter() {
  if (searchResults.value.length === 1) {
    clearSearch()
    router.push(`/producto/${searchResults.value[0].slug}`)
  } else if (searchInput.value.trim()) {
    viewAllResults()
  }
}

async function viewAllResults() {
  searchFocused.value = false
  if (route.path !== '/') await router.push('/')
  productsStore.search(searchInput.value)
}

function clearSearch() {
  searchInput.value = ''
  searchResults.value = []
  searchMeta.value = null
  searchFocused.value = false
  if (productsStore.searchQuery) productsStore.search('')
}

// Cierra el dropdown al hacer clic fuera del buscador
function onDocumentClick(e: MouseEvent) {
  if (searchWrapRef.value && !searchWrapRef.value.contains(e.target as Node)) {
    searchFocused.value = false
  }
}
onMounted(() => document.addEventListener('mousedown', onDocumentClick))
onUnmounted(() => document.removeEventListener('mousedown', onDocumentClick))

// ── Navegación ────────────────────────────────────────────
const NAV_LINKS = [
  { to: '/', icon: 'layout-grid', label: 'Catálogo' },
  { to: '/seguimiento', icon: 'package', label: 'Seguir pedido' },
]

function isActiveLink(path: string): boolean {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

// ── Estado abierto/cerrado ──────────────
const isOpen = computed(() => {
  const now = new Date()
  const day = now.getDay()
  if (day === 0) return false // domingo cerrado
  const h = now.getHours()
  return h >= 9 && h < 22   // 9am – 10pm
})
</script>

<style scoped>
/* Oculta el botón nativo de "limpiar" que el navegador agrega solo a
   los input type="search" — usamos el nuestro para poder resetear
   también el estado de la búsqueda, no solo el texto. */
.search-input-clean::-webkit-search-cancel-button,
.search-input-clean::-webkit-search-decoration {
  -webkit-appearance: none;
  appearance: none;
}

.search-input-clean::-ms-clear {
  display: none;
}
</style>