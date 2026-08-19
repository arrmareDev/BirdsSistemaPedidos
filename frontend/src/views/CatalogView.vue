<template>
  <div class="w-full catalog-root">

    <div class="catalog-bg" aria-hidden="true">
      <div class="blob blob-1" />
      <div class="blob blob-2" />
      <div class="blob blob-3" />
      <div class="grid-pattern" />
    </div>

    <HeroCarousel @scroll-to-menu="scrollToMenu" />

    <!-- ── Promo bar ── -->
    <div class="promo-bar overflow-hidden py-3 relative z-10">
      <div class="flex whitespace-nowrap promo-marquee">
        <span v-for="n in 4" :key="n" class="inline-flex items-center shrink-0
                 text-[11.5px] font-black text-white uppercase tracking-[0.12em]">
          <span class="promo-star mx-4">✦</span>
          Ramos y Arreglos Florales
          <span class="promo-star mx-4">✦</span>
          Flores Frescas del Día
          <span class="promo-star mx-4">✦</span>
          Delivery a Chiclayo
          <span class="promo-star mx-4">✦</span>
          Detalles para toda Ocasión
        </span>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-start
                max-w-[1400px] mx-auto w-full relative z-10">

      <div class="flex-1 min-w-0" id="menu">

        <!-- ── Categorías principales del catálogo ── -->
        <div v-if="productsStore.rootCategories.length > 1" class="px-4 md:px-8 pt-6 pb-2">
          <div class="flex gap-3 sm:gap-4 overflow-x-auto pb-3 pt-2
                      -mx-4 px-5 md:-mx-8 md:px-9 scrollbar-none">
            <!-- "Todo" — vuelve a ver el catálogo completo, agrupado
                 por categoría. Sin esto no había forma de regresar
                 aquí una vez que entrabas a un grupo específico. -->
            <button @click="changeGroup('all')" class="flex items-center justify-center gap-2.5 sm:gap-3 px-4 py-3.5 sm:px-6 sm:py-4 rounded-full border-2
                     font-black text-[13px] sm:text-[16px] cursor-pointer transition-all duration-300
                     uppercase tracking-widest shrink-0 whitespace-nowrap"
              :class="productsStore.activeGroup === 'all' ? 'cat-btn--active' : 'cat-btn--idle'">
              <Squares2X2Icon class="cat-icon" :style="{ width: '22px', height: '22px' }" />
              Todo
            </button>

            <button v-for="root in productsStore.rootCategories" :key="root.id" @click="changeGroup(root.slug)" class="flex items-center justify-center gap-2.5 sm:gap-3 px-4 py-3.5 sm:px-6 sm:py-4 rounded-full border-2
                     font-black text-[13px] sm:text-[16px] cursor-pointer transition-all duration-300
                     uppercase tracking-widest shrink-0 whitespace-nowrap"
              :class="productsStore.activeGroup === root.slug ? 'cat-btn--active' : 'cat-btn--idle'">
              <AppIcon :name="root.icon" :size="22" class="cat-icon" />
              {{ root.name }}
            </button>
          </div>
        </div>

        <!-- ── Populares ── -->
        <div v-if="productsStore.activeCategory === 'all'" class="px-4 md:px-8 mb-8">
          <div class="flex items-center gap-3 mb-4">
            <div class="section-pill fire">
              <Flame :size="14" />
            </div>
            <h2 class="font-black text-[18px] sm:text-[20px] text-ink m-0 tracking-tight"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Los más pedidos
            </h2>
          </div>

          <div class="flex gap-3 sm:gap-3.5 overflow-x-auto pb-3
                      -mx-4 px-4 md:-mx-8 md:px-8 scrollbar-none">
            <div v-for="product in productsStore.popular" :key="'pop-' + product.id" @click="openProduct(product)"
              class="popular-card group flex-shrink-0
                     w-[145px] sm:w-[185px] overflow-hidden cursor-pointer">

              <div class="relative overflow-hidden" style="aspect-ratio:4/3;">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                <div v-else class="w-full h-full flex items-center justify-center popular-emoji-bg">
                  <AppIcon :name="product.icon" :size="40"
                    class="transition-transform duration-300 group-hover:scale-115 group-hover:-rotate-6" />
                </div>
                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent" />
                <div v-if="product.popular" class="absolute top-2 right-2 popular-badge">
                  <Star :size="12" fill="currentColor" />
                </div>
              </div>

              <div class="p-2.5 sm:p-3 popular-info">
                <p class="font-bold text-[12px] sm:text-[13px] m-0 mb-2 leading-snug line-clamp-2">
                  {{ product.name }}
                </p>
                <div class="flex items-center justify-between">
                  <div class="flex items-baseline gap-0.5">
                    <span class="text-[9px] sm:text-[10px] font-bold opacity-60">S/</span>
                    <span class="font-black text-[17px] sm:text-[19px] leading-none price-text"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ product.price.toFixed(2) }}
                    </span>
                  </div>
                  <div class="add-circle-btn w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center
                              justify-center text-white text-lg sm:text-xl font-black
                              leading-none transition-all duration-200
                              group-hover:scale-110 group-hover:rotate-90">
                    +
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Catálogo completo ── -->
        <div class="px-4 md:px-8" :class="cartStore.count > 0 ? 'pb-32 lg:pb-10' : 'pb-10'">
          <div class="flex items-center justify-between mb-4 sm:mb-5">
            <div class="flex items-center gap-2 sm:gap-3">
              <h2 class="font-black text-[18px] sm:text-[20px] text-ink m-0 tracking-tight"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ categoryLabel }}
              </h2>
              <button v-if="productsStore.searchQuery" @click="productsStore.search('')" class="clear-search-btn flex items-center gap-1 px-2.5 py-1 rounded-full
                       text-[11px] font-bold cursor-pointer border-none transition-all duration-150">
                <X :size="11" :stroke-width="3" /> Limpiar
              </button>
            </div>
            <span class="count-badge">{{ productsStore.meta?.total ?? productsStore.filtered.length }} productos</span>
          </div>

          <!-- Skeleton -->
          <div v-if="productsStore.loading"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
            <div v-for="n in 8" :key="n" class="rounded-2xl animate-pulse skeleton-card" style="aspect-ratio:3/4;" />
          </div>

          <!-- Grid productos Agrupado (Cuando es "Todo") -->
          <div
            v-if="!productsStore.loading && productsStore.activeCategory === 'all' && !productsStore.searchQuery && groupedProducts"
            class="flex flex-col gap-10">
            <div v-for="(group, index) in groupedProducts" :key="group.slug">

              <!-- Título de la Categoría + línea divisora -->
              <div class="flex items-center gap-4 mb-5">
                <h3 class="font-black text-[20px] text-ink m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                  {{ group.name }}
                </h3>
                <div class="h-px flex-1 bg-gray-200"></div>
                <span class="text-[12px] font-semibold text-gray-400 shrink-0">{{ group.total }} en total</span>
              </div>

              <!-- Cuadrícula — misma cantidad de columnas siempre (2/3/4).
                   Si ya se expandió esta categoría, se listan TODOS sus
                   productos aquí mismo; si no, el preview de 8. -->
              <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                <div v-for="product in (expandedGroups[group.slug] ?? group.products)" :key="product.id"
                  @click="product.available && openProduct(product)"
                  class="product-card group rounded-2xl overflow-hidden flex flex-col transition-all duration-300 relative"
                  :class="product.available ? 'cursor-pointer product-card--available' : 'opacity-50 cursor-default'">

                  <!-- Imagen -->
                  <div class="relative overflow-hidden product-img-wrap" style="aspect-ratio:4/3;">
                    <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                    <div v-else class="w-full h-full flex items-center justify-center product-emoji-bg">
                      <AppIcon :name="product.icon" :size="52"
                        class="transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3" />
                    </div>
                    <div
                      class="absolute inset-0 product-img-overlay opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                    <span v-if="product.popular"
                      class="badge-popular absolute top-2 left-2 sm:top-3 sm:left-3 inline-flex items-center gap-1">
                      <Star :size="10" fill="currentColor" /> Popular
                    </span>
                    <div v-if="!product.available"
                      class="absolute inset-0 flex items-center justify-center backdrop-blur-[1px] bg-white/70">
                      <span class="sold-out-badge">Agotado</span>
                    </div>

                    <!-- Botón + flotante en hover (solo pantallas con hover real) -->
                    <div
                      class="hidden sm:block absolute bottom-3 right-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200">
                      <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                        class="float-add-btn w-10 h-10 rounded-full flex items-center justify-center text-white text-2xl font-black border-none cursor-pointer leading-none transition-all duration-150 hover:scale-110 active:scale-95">
                        +
                      </button>
                    </div>
                  </div>

                  <!-- Info del producto -->
                  <div class="flex flex-col flex-1 p-2.5 sm:p-3.5 md:p-4 product-info">
                    <h3
                      class="font-bold text-[12.5px] sm:text-[14px] md:text-[15px] leading-snug m-0 mb-1 sm:mb-1.5 line-clamp-2 product-name">
                      {{ product.name }}
                    </h3>
                    <p
                      class="text-[10.5px] sm:text-[12.5px] leading-snug sm:leading-relaxed m-0 line-clamp-2 flex-1 min-h-0 mb-2 sm:mb-3 product-desc">
                      {{ product.description }}
                    </p>
                    <div class="flex items-center justify-between gap-1.5 sm:gap-2 mt-auto pt-1 sm:pt-0">
                      <div class="flex items-baseline gap-0.5">
                        <span class="text-[10px] sm:text-[11px] font-bold product-currency">S/</span>
                        <span class="font-black text-[17px] sm:text-[20px] md:text-[22px] leading-none product-price"
                          style="font-family:'Plus Jakarta Sans',sans-serif;">
                          {{ product.price.toFixed(2) }}
                        </span>
                      </div>
                      <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                        class="pedir-btn flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-full font-bold text-[10.5px] sm:text-[12px] border-none cursor-pointer hover:-translate-y-0.5 active:scale-95 transition-all duration-150 shrink-0 uppercase tracking-wide">
                        <span class="text-[13px] sm:text-[14px] leading-none font-black">+</span>
                        <span>Pedir</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- "Ver más" — expande ESTA categoría en el mismo lugar,
                   sin navegar ni ocultar las demás categorías de abajo -->
              <div v-if="group.total > group.products.length && !expandedGroups[group.slug]"
                class="flex justify-center mt-6">
                <button @click="expandGroup(group)" :disabled="expandingSlug === group.slug" class="ver-mas-btn flex items-center gap-2 px-6 py-3 rounded-2xl font-bold text-[13.5px]
                         cursor-pointer transition-all duration-200 border-2 disabled:opacity-60">
                  <span v-if="expandingSlug === group.slug"
                    class="w-4 h-4 border-2 border-gray-300 border-t-brand-red rounded-full animate-spin" />
                  {{ expandingSlug === group.slug ? 'Cargando...' : `Ver los ${group.total} productos de ${group.name}`
                  }}
                  <ArrowRightIcon v-if="expandingSlug !== group.slug" class="w-4 h-4" />
                </button>
              </div>

              <!--Separador Aves (Excepto en la última categoría)-->
              <div v-if="index < groupedProducts.length - 1" class="flex justify-center mt-12 mb-2 opacity-50">
                <img src="/images/pajaros.png" alt="Separador Birds" class="h-10 sm:h-12 object-contain" />
              </div>
            </div>
          </div>

          <!-- Grid Clásico (Cuando se filtra por una categoría específica) —
               mismas columnas 2/3/4 que la vista agrupada, para que nunca
               "salte" de 4 a 3 al entrar a ver todo de una categoría -->
          <div v-else-if="!productsStore.loading"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">

            <div v-for="product in productsStore.filtered" :key="product.id"
              @click="product.available && openProduct(product)"
              class="product-card group rounded-2xl overflow-hidden flex flex-col transition-all duration-300 relative"
              :class="product.available ? 'cursor-pointer product-card--available' : 'opacity-50 cursor-default'">

              <!-- Imagen -->
              <div class="relative overflow-hidden product-img-wrap" style="aspect-ratio:4/3;">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                <div v-else class="w-full h-full flex items-center justify-center product-emoji-bg">
                  <AppIcon :name="product.icon" :size="52"
                    class="transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3" />
                </div>
                <div
                  class="absolute inset-0 product-img-overlay opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                <span v-if="product.popular"
                  class="badge-popular absolute top-2 left-2 sm:top-3 sm:left-3 inline-flex items-center gap-1">
                  <Star :size="10" fill="currentColor" /> Popular
                </span>
                <div v-if="!product.available"
                  class="absolute inset-0 flex items-center justify-center backdrop-blur-[1px] bg-white/70">
                  <span class="sold-out-badge">Agotado</span>
                </div>

                <div
                  class="hidden sm:block absolute bottom-3 right-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200">
                  <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                    class="float-add-btn w-10 h-10 rounded-full flex items-center justify-center text-white text-2xl font-black border-none cursor-pointer leading-none transition-all duration-150 hover:scale-110 active:scale-95">
                    +
                  </button>
                </div>
              </div>

              <!-- Info del producto -->
              <div class="flex flex-col flex-1 p-2.5 sm:p-3.5 md:p-4 product-info">
                <h3
                  class="font-bold text-[12.5px] sm:text-[14px] md:text-[15px] leading-snug m-0 mb-1 sm:mb-1.5 line-clamp-2 product-name">
                  {{ product.name }}
                </h3>
                <p
                  class="text-[10.5px] sm:text-[12.5px] leading-snug sm:leading-relaxed m-0 line-clamp-2 flex-1 min-h-0 mb-2 sm:mb-3 product-desc">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between gap-1.5 sm:gap-2 mt-auto pt-1 sm:pt-0">
                  <div class="flex items-baseline gap-0.5">
                    <span class="text-[10px] sm:text-[11px] font-bold product-currency">S/</span>
                    <span class="font-black text-[17px] sm:text-[20px] md:text-[22px] leading-none product-price"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ product.price.toFixed(2) }}
                    </span>
                  </div>
                  <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                    class="pedir-btn flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-full font-bold text-[10.5px] sm:text-[12px] border-none cursor-pointer hover:-translate-y-0.5 active:scale-95 transition-all duration-150 shrink-0 uppercase tracking-wide">
                    <span class="text-[13px] sm:text-[14px] leading-none font-black">+</span>
                    <span>Pedir</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Scroll infinito: sentinel + loader (solo en vista de categoría, no en "Todo") -->
          <div
            v-if="(productsStore.activeCategory !== 'all' || productsStore.searchQuery) && productsStore.filtered.length > 0"
            ref="scrollSentinel" class="flex items-center justify-center py-8">
            <span v-if="productsStore.loadingMore"
              class="w-6 h-6 border-2 border-gray-200 border-t-brand-red rounded-full animate-spin" />
          </div>

          <!-- Empty state -->
          <div v-if="productsStore.filtered.length === 0" class="col-span-full flex flex-col items-center py-20 gap-4">
            <div class="w-20 h-20 rounded-full empty-icon-bg flex items-center
                          justify-center text-gray-300">
              <PackageSearch :size="36" :stroke-width="1.5" />
            </div>
            <p class="m-0 text-[15px] font-semibold text-ink">
              Sin productos en esta categoría
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── FAB móvil ── -->
    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="translate-y-8 opacity-0"
      leave-active-class="transition-all duration-200" leave-to-class="translate-y-4 opacity-0">
      <button v-if="cartStore.count > 0" @click="cartOpen = true" class="fab-mobile fixed bottom-5 left-4 right-4 z-30 lg:hidden
               flex items-center justify-between px-5 py-4 rounded-2xl
               text-white font-bold text-[15px] border-none cursor-pointer
               hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200">
        <div class="fab-shimmer" />
        <div class="flex items-center gap-3 relative z-10">
          <div class="fab-count w-8 h-8 rounded-xl flex items-center
                      justify-center font-black text-base">
            {{ cartStore.count }}
          </div>
          <span>Ver mi pedido</span>
        </div>
        <div class="flex items-baseline gap-1 relative z-10">
          <span class="text-[13px] font-semibold opacity-80">S/</span>
          <span class="font-black text-[19px] leading-none" style="font-family:'Plus Jakarta Sans',sans-serif;">
            {{ cartStore.total.toFixed(2) }}
          </span>
        </div>
      </button>
    </Transition>

    <!-- ── Partículas ── -->
    <Teleport to="body">
      <div v-for="p in particles" :key="p.id" class="particle fixed pointer-events-none z-[999]
               font-black text-[22px] leading-none select-none" :style="{
                left: p.x + 'px',
                top: p.y + 'px',
                '--tx': p.tx + 'px',
                '--ty': p.ty + 'px',
              }">
        <AppIcon :name="p.icon" :size="22" />
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, inject, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import api from '@/utils/api'
import { useHead } from '@vueuse/head'
import HeroCarousel from '@/components/layout/HeroCarousel.vue'
import AppIcon from '@/components/AppIcon.vue'
import type { Product } from '@/stores/products'
import { Star, Flame, PackageSearch, X, ArrowRight as ArrowRightIcon, LayoutGrid as Squares2X2Icon } from 'lucide-vue-next'

useHead({
  title: 'Birds Perú - Florería',
  meta: [
    { name: 'description', content: 'Las flores más frescas de Chiclayo. Diseños que emocionan para cada ocasión especial.' },
  ],
  link: [
    { rel: 'canonical', href: 'https://floreria.birds.pe' },
  ],
})

const router = useRouter()
const productsStore = useProductsStore()
const cartStore = useCartStore()

function changeGroup(group: string) {
  productsStore.setGroup(group)
}

const cartOpen = inject<any>('cartOpen')
const customizer = inject<any>('customizer')

// ── Partículas ────────────────────────────────────────────
interface Particle {
  id: number; x: number; y: number
  tx: number; ty: number; icon: string
}
const particles = ref<Particle[]>([])
let particleId = 0
const PARTICLE_ICONS = ['sparkles', 'star', 'heart', 'party-popper', 'plus']

function spawnParticles(event: MouseEvent) {
  const el = event.currentTarget as HTMLElement
  const rect = el.getBoundingClientRect()
  const cx = rect.left + rect.width / 2
  const cy = rect.top + rect.height / 2
  for (let i = 0; i < 6; i++) {
    const angle = (360 / 6) * i + Math.random() * 25
    const dist = 55 + Math.random() * 45
    const rad = (angle * Math.PI) / 180
    const id = particleId++
    particles.value.push({
      id, x: cx, y: cy,
      tx: Math.cos(rad) * dist,
      ty: Math.sin(rad) * dist - 25,
      icon: PARTICLE_ICONS[Math.floor(Math.random() * PARTICLE_ICONS.length)],
    })
    setTimeout(() => {
      particles.value = particles.value.filter(p => p.id !== id)
    }, 750)
  }
}

// ── Expandir una categoría en el lugar (sin navegar) ──────
// El store comparte un solo array `products` para toda la vista
// "Todo" agrupada — pedirle a setCategory() cambiar a una sola
// categoría BORRA ese array completo, y con él, las demás
// categorías que se veían debajo. Por eso esto pide los productos
// por su cuenta, con api.get() directo, y los guarda aparte.
const expandedGroups = ref<Record<string, Product[]>>({})
const expandingSlug = ref<string | null>(null)

async function expandGroup(group: { slug: string; total: number }) {
  if (expandedGroups.value[group.slug]) return
  expandingSlug.value = group.slug
  try {
    const { data } = await api.get('/products', {
      params: { category: group.slug, per_page: group.total },
    })
    const raw = data?.data?.data ?? []
    expandedGroups.value[group.slug] = productsStore.normalizeProducts(raw)
  } catch (e) {
    console.error('Error expandiendo categoría:', e)
  } finally {
    expandingSlug.value = null
  }
}

// ── Navegación ────────────────────────────────────────────
function openProduct(product: Product) {
  router.push({ name: 'product-detail', params: { slug: product.slug } })
}

function handleAddToCart(product: Product, event: MouseEvent) {
  spawnParticles(event)
  openProduct(product)
}

// ── Computed ──────────────────────────────────────────────
const categoryLabel = computed(() => {
  if (productsStore.searchQuery) return `Resultados para "${productsStore.searchQuery}"`
  if (productsStore.activeCategory !== 'all') {
    const cat = productsStore.categories.find(c => c.slug === productsStore.activeCategory)
    return cat?.name ?? 'Catálogo'
  }
  const group = productsStore.rootCategories.find(c => c.slug === productsStore.activeGroup)
  return group ? group.name : 'Catálogo completo'
})

const categoriesForActiveGroup = computed(() =>
  productsStore.categoriesByGroup(productsStore.activeGroup)
)

// ── Agrupación por Categorías (Catálogo Resumido) ─────────
const groupedProducts = computed(() => {
  // Solo agrupamos si estamos viendo "Todo"
  if (productsStore.activeCategory !== 'all') return null

  // Usamos un mapa para agrupar y contar los totales reales
  const groupsMap = new Map<string, { name: string, slug: string, products: Product[], total: number }>()

  productsStore.filtered.forEach(product => {
    const catSlug = product.category?.slug || 'otros'
    const catName = product.category?.name || 'Otros'

    if (!groupsMap.has(catSlug)) {
      groupsMap.set(catSlug, { name: catName, slug: catSlug, products: [], total: 0 })
    }

    const group = groupsMap.get(catSlug)!
    group.total++ // Contamos el total real de esta categoría

    // Mostramos máximo 8 para no cansar la vista (2 filas de 4 en desktop)
    if (group.products.length < 8) {
      group.products.push(product)
    }
  })

  // Retornamos la lista de grupos
  return Array.from(groupsMap.values())
})

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => productsStore.fetch({
  grupo: productsStore.activeGroup !== 'all' ? productsStore.activeGroup : undefined,
}))

// ── Scroll infinito ───────────────────────────────────────
const scrollSentinel = ref<HTMLElement | null>(null)
let sentinelObserver: IntersectionObserver | null = null

function setupSentinelObserver() {
  sentinelObserver?.disconnect()
  if (!scrollSentinel.value) return
  sentinelObserver = new IntersectionObserver((entries) => {
    if (entries[0]?.isIntersecting) productsStore.loadMore()
  }, { rootMargin: '400px' })
  sentinelObserver.observe(scrollSentinel.value)
}

watch(scrollSentinel, (el) => {
  if (el) setupSentinelObserver()
})

onUnmounted(() => sentinelObserver?.disconnect())

function scrollToMenu() {
  document.getElementById('menu')?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<style scoped>
.catalog-root {
  position: relative;
  background: #ffffff;
  min-height: 100vh;
}


/* ══════════════════════════════════════════════════════════════
   FONDO — desactivado a propósito, la tienda vive en blanco limpio
   ══════════════════════════════════════════════════════════════ */

.catalog-bg {
  display: none;
}


/* ══════════════════════════════════════════════════════════════
   PROMO BAR
   ══════════════════════════════════════════════════════════════ */

.promo-bar {
  background: #10461d;
}

.promo-star {
  color: #F5C518;
  animation: star-pulse 2s ease-in-out infinite;
}

@keyframes star-pulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: .7;
    transform: scale(.85);
  }
}

.promo-marquee {
  animation: marquee 28s linear infinite;
}

@keyframes marquee {
  from {
    transform: translateX(0);
  }

  to {
    transform: translateX(-50%);
  }
}


/* ══════════════════════════════════════════════════════════════
   SECTION PILL
   ══════════════════════════════════════════════════════════════ */

.section-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .07);
  border: 1px solid rgba(var(--color-brand-primary-rgb, 196, 30, 30), .14);
  font-size: 11px;
  font-weight: 900;
  color: var(--color-brand-primary, #C41E1E);
  letter-spacing: .1em;
  text-transform: uppercase;
  white-space: nowrap;
}

.section-pill.fire {
  background: rgba(245, 197, 24, .10);
  border-color: rgba(245, 197, 24, .25);
  color: #B8860B;
}


/* ══════════════════════════════════════════════════════════════
   BOTONES DE CATEGORÍAS
   ══════════════════════════════════════════════════════════════ */

.cat-btn--active {
  background: var(--color-brand-primary, #C41E1E);
  border-color: var(--color-brand-primary, #C41E1E);
  color: white;
  box-shadow:
    0 1px 2px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .15),
    0 8px 20px -4px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .35);
}

.cat-btn--idle {
  background: #ffffff;
  border-color: #EEEEEE;
  color: #4A3728;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .03);
}

.cat-btn--idle:hover {
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .3);
  color: var(--color-brand-primary, #C41E1E);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, .06);
}

.cat-icon {
  transition: transform .2s cubic-bezier(.34, 1.56, .64, 1);
}

button:hover .cat-icon {
  transform: scale(1.15) rotate(-4deg);
}


/* ══════════════════════════════════════════════════════════════
   CARDS — PRODUCTOS POPULARES
   ══════════════════════════════════════════════════════════════ */

.popular-card {
  background: #ffffff;
  border-radius: 18px;
  border: 1px solid rgba(0, 0, 0, .05);
  box-shadow: 0 1px 2px rgba(0, 0, 0, .03), 0 3px 8px rgba(0, 0, 0, .03);
  transition: transform .3s cubic-bezier(.34, 1.1, .64, 1), box-shadow .3s ease, border-color .3s ease;
}

.popular-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, .05), 0 16px 32px -8px rgba(0, 0, 0, .12);
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .16);
}

.popular-emoji-bg {
  background: #FAFAF9;
}

.popular-info {
  background: #ffffff;
}

.popular-badge {
  background: #F5C518;
  color: #6B4E00;
  font-size: 9px;
  font-weight: 900;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 999px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
  letter-spacing: .05em;
}

.add-circle-btn {
  background: var(--color-brand-primary, #C41E1E);
  box-shadow: 0 3px 10px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .32);
}

.price-text {
  color: var(--color-brand-primary, #C41E1E);
}


/* ══════════════════════════════════════════════════════════════
   SKELETON
   ══════════════════════════════════════════════════════════════ */

.skeleton-card {
  background: linear-gradient(90deg, #F6F6F5 25%, #EFEFEE 50%, #F6F6F5 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}


/* ══════════════════════════════════════════════════════════════
   PRODUCT CARD
   ══════════════════════════════════════════════════════════════ */

.product-card {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, .055);
  border-radius: 18px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, .035), 0 2px 6px rgba(0, 0, 0, .03);
  overflow: hidden;
  transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
}

.product-card--available:hover {
  transform: translateY(-6px);
  box-shadow:
    0 2px 6px rgba(0, 0, 0, .05),
    0 20px 40px -12px rgba(0, 0, 0, .14),
    0 0 0 1px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .06);
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .18);
}


/* ══════════════════════════════════════════════════════════════
   IMAGEN DEL PRODUCTO
   ══════════════════════════════════════════════════════════════ */

.product-img-wrap {
  background: #FAFAF9;
}

.product-emoji-bg {
  background: #FAFAF9;
}

.product-img-overlay {
  background: linear-gradient(to top, rgba(0, 0, 0, .10), transparent 55%);
}


/* ══════════════════════════════════════════════════════════════
   BADGE POPULAR
   ══════════════════════════════════════════════════════════════ */

.badge-popular {
  background: #ffffff;
  color: #B8860B;
  font-size: 8.5px;
  font-weight: 900;
  text-transform: uppercase;
  padding: 4px 8px;
  border-radius: 999px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
  letter-spacing: .05em;
  line-height: 1;
}


/* ══════════════════════════════════════════════════════════════
   AGOTADO
   ══════════════════════════════════════════════════════════════ */

.sold-out-badge {
  font-size: 10px;
  font-weight: 900;
  color: #555;
  text-transform: uppercase;
  letter-spacing: .1em;
  background: rgba(255, 255, 255, .96);
  padding: 5px 12px;
  border-radius: 999px;
  border: 1px solid rgba(0, 0, 0, .08);
  box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
}


/* ══════════════════════════════════════════════════════════════
   BOTÓN FLOTANTE +
   ══════════════════════════════════════════════════════════════ */

.float-add-btn {
  background: var(--color-brand-primary, #C41E1E);
  box-shadow: 0 4px 14px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .38);
}


/* ══════════════════════════════════════════════════════════════
   INFORMACIÓN DE PRODUCTO
   ══════════════════════════════════════════════════════════════ */

.product-info {
  background: #ffffff;
}

.product-name {
  color: #171310;
  letter-spacing: -.005em;
}

.product-desc {
  color: #8A8580;
}

.product-price {
  color: #171310;
}

.product-currency {
  color: #A39D96;
}


/* ══════════════════════════════════════════════════════════════
   BOTÓN PEDIR
   ══════════════════════════════════════════════════════════════ */

.pedir-btn {
  background: var(--color-brand-primary, #C41E1E);
  color: white;
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, .18),
    0 3px 10px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .28);
}

.pedir-btn:hover {
  background: var(--color-brand-primary-dark, #A01010);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, .18),
    0 6px 18px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .38);
}


/* ══════════════════════════════════════════════════════════════
   VER MÁS — ahora un único botón consistente, siempre debajo de
   la cuadrícula, en cualquier tamaño de pantalla
   ══════════════════════════════════════════════════════════════ */

.ver-mas-btn {
  background: #ffffff;
  border-color: #ECECEC;
  color: #4A3728;
}

.ver-mas-btn:hover {
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .35);
  color: var(--color-brand-primary, #C41E1E);
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .04);
  transform: translateY(-2px);
}


/* ══════════════════════════════════════════════════════════════
   EMPTY STATE
   ══════════════════════════════════════════════════════════════ */

.empty-icon-bg {
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .05);
}


/* ══════════════════════════════════════════════════════════════
   CONTADOR
   ══════════════════════════════════════════════════════════════ */

.count-badge {
  font-size: 12.5px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .05);
  border: 1px solid rgba(var(--color-brand-primary-rgb, 196, 30, 30), .10);
  color: var(--color-brand-primary, #C41E1E);
  white-space: nowrap;
}


/* ══════════════════════════════════════════════════════════════
   LIMPIAR BÚSQUEDA
   ══════════════════════════════════════════════════════════════ */

.clear-search-btn {
  background: rgba(0, 0, 0, .04);
  color: #777777;
}

.clear-search-btn:hover {
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .07);
  color: var(--color-brand-primary, #C41E1E);
}


/* ══════════════════════════════════════════════════════════════
   FAB MOBILE
   ══════════════════════════════════════════════════════════════ */

.fab-mobile {
  background: var(--color-brand-primary, #C41E1E);
  box-shadow:
    0 8px 28px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .35),
    0 0 0 1px rgba(255, 255, 255, .12) inset;
  overflow: hidden;
}

.fab-shimmer {
  position: absolute;
  inset: 0;
  background: linear-gradient(105deg, transparent 30%, rgba(255, 255, 255, .15) 50%, transparent 70%);
  background-size: 200% 100%;
  animation: fab-shine 3s linear infinite;
}

@keyframes fab-shine {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.fab-count {
  background: rgba(255, 255, 255, .20);
}


/* ══════════════════════════════════════════════════════════════
   PARTICLES
   ══════════════════════════════════════════════════════════════ */

.particle {
  animation: particle-fly .7s cubic-bezier(.22, .61, .36, 1) forwards;
}

@keyframes particle-fly {
  0% {
    transform: translate(0, 0) scale(1) rotate(0deg);
    opacity: 1;
  }

  60% {
    opacity: 1;
  }

  100% {
    transform: translate(var(--tx), var(--ty)) scale(.3) rotate(180deg);
    opacity: 0;
  }
}


/* ══════════════════════════════════════════════════════════════
   AJUSTES RESPONSIVE
   ══════════════════════════════════════════════════════════════ */

@media (max-width: 640px) {
  .product-card {
    border-radius: 14px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .03), 0 2px 6px rgba(0, 0, 0, .03);
  }

  .product-card--available:hover {
    transform: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .04), 0 4px 10px rgba(0, 0, 0, .04);
  }

  .popular-card {
    border-radius: 16px;
  }
}
</style>