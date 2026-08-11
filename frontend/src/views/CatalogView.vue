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
            <button v-for="root in productsStore.rootCategories" :key="root.id" @click="changeGroup(root.slug)" class="flex items-center justify-center gap-2.5 sm:gap-3 px-4 py-3.5 sm:px-6 sm:py-4 rounded-full border-2
                     font-black text-[13px] sm:text-[16px] cursor-pointer transition-all duration-300
                     uppercase tracking-widest shrink-0 whitespace-nowrap"
              :class="productsStore.activeGroup === root.slug
                ? 'border-brand-red bg-brand-red text-white shadow-[0_8px_24px_rgba(var(--color-brand-primary-rgb,196,30,30),0.35)] scale-105'
                : 'border-surface-border bg-white text-ink-muted hover:border-brand-red/40 hover:text-brand-red shadow-sm hover:shadow-md hover:-translate-y-0.5'">
              <AppIcon :name="root.icon" :size="22" />
              {{ root.name }}
            </button>
          </div>
        </div>

        <!-- ── Categorías ── -->
        <div class="px-4 md:px-8 pt-8 pb-6">
          <div class="flex items-center gap-3 mb-5">
            <div class="section-pill">CATÁLOGO</div>
            <h2 class="font-black text-[20px] sm:text-[22px] text-ink m-0 tracking-tight"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              ¿Qué estás buscando?
            </h2>
          </div>

          <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 sm:gap-2.5">
            <button @click="productsStore.setCategory('all')" class="cat-btn flex flex-col items-center gap-1.5 sm:gap-2
                     py-3 sm:py-3.5 px-2 rounded-2xl border-2 font-bold
                     text-[11px] sm:text-[12px] cursor-pointer transition-all duration-250"
              :class="productsStore.activeCategory === 'all' ? 'cat-btn--active' : 'cat-btn--idle'">
              <AppIcon name="layout-grid" :size="24" class="cat-icon" />
              <span>Todo</span>
            </button>

            <button v-for="cat in categoriesForActiveGroup" :key="cat.id" @click="productsStore.setCategory(cat.slug)"
              class="cat-btn flex flex-col items-center gap-1.5 sm:gap-2
                     py-3 sm:py-3.5 px-2 rounded-2xl border-2 font-bold
                     text-[11px] sm:text-[12px] cursor-pointer transition-all duration-250"
              :class="productsStore.activeCategory === cat.slug ? 'cat-btn--active' : 'cat-btn--idle'">
              <AppIcon :name="cat.icon" :size="24" class="cat-icon" />
              <span class="truncate w-full text-center">{{ cat.name }}</span>
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
        <div class="px-4 md:px-8 pb-32 lg:pb-10">
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
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            <div v-for="n in 8" :key="n" class="rounded-2xl animate-pulse skeleton-card" style="aspect-ratio:3/4;" />
          </div>

          <!-- Grid productos Agrupado (Cuando es "Todo") -->
          <div
            v-if="!productsStore.loading && productsStore.activeCategory === 'all' && !productsStore.searchQuery && groupedProducts"
            class="flex flex-col gap-10">
            <div v-for="(group, index) in groupedProducts" :key="group.slug">

              <!-- Título de la Categoría, Línea divisora y Botón superior -->
              <div class="flex items-center gap-4 mb-5">
                <h3 class="font-black text-[20px] text-ink m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                  {{ group.name }}
                </h3>
                <div class="h-px flex-1 bg-gray-200"></div>
                <button v-if="group.total > group.products.length" @click="productsStore.setCategory(group.slug)"
                  class="hidden sm:block text-[12.5px] font-bold text-brand-red cursor-pointer bg-red-50 hover:bg-red-100 px-4 py-1.5 rounded-full transition-colors border-none shrink-0">
                  Ver todo ({{ group.total }})
                </button>
              </div>

              <!-- Cuadrícula de 6 productos -->
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <div v-for="product in group.products" :key="product.id"
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
                      class="badge-popular absolute top-2.5 left-0 inline-flex items-center gap-1">
                      <Star :size="11" fill="currentColor" /> Popular
                    </span>
                    <div v-if="!product.available"
                      class="absolute inset-0 flex items-center justify-center backdrop-blur-[1px] bg-white/70">
                      <span class="sold-out-badge">Agotado</span>
                    </div>

                    <!-- Botón + flotante en hover -->
                    <div
                      class="absolute bottom-3 right-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200">
                      <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                        class="float-add-btn w-10 h-10 rounded-full flex items-center justify-center text-white text-2xl font-black border-none cursor-pointer leading-none transition-all duration-150 hover:scale-110 active:scale-95">
                        +
                      </button>
                    </div>
                  </div>

                  <!-- Info del producto -->
                  <div class="flex flex-col flex-1 p-3 sm:p-4 product-info">
                    <h3 class="font-bold text-[14px] sm:text-[15px] leading-snug m-0 mb-1.5 line-clamp-2 product-name">
                      {{ product.name }}
                    </h3>
                    <p class="text-[12px] sm:text-[12.5px] leading-relaxed m-0 line-clamp-2 flex-1 mb-3 product-desc">
                      {{ product.description }}
                    </p>
                    <div class="flex items-center justify-between gap-2 mt-auto">
                      <div class="flex items-baseline gap-0.5">
                        <span class="text-[11px] font-bold product-currency">S/</span>
                        <span class="font-black text-[22px] sm:text-[24px] leading-none product-price"
                          style="font-family:'Plus Jakarta Sans',sans-serif;">
                          {{ product.price.toFixed(2) }}
                        </span>
                      </div>
                      <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                        class="pedir-btn flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-full font-bold text-[12px] border-none cursor-pointer hover:-translate-y-0.5 active:scale-95 transition-all duration-150 shrink-0 uppercase tracking-wide">
                        <span class="text-[14px] leading-none font-black">+</span>
                        <span>Pedir</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botón grande para celulares para Ver Más -->
              <div v-if="group.total > group.products.length" class="flex justify-center mt-5 sm:hidden">
                <button @click="productsStore.setCategory(group.slug)"
                  class="w-full py-3 rounded-2xl border-2 border-gray-200 text-gray-600 font-bold text-[13px] bg-white cursor-pointer active:scale-95 transition-all">
                  Explorar todos los {{ group.name }} →
                </button>
              </div>

              <!--Separador Aves (Excepto en la última categoría)-->
              <div v-if="index < groupedProducts.length - 1" class="flex justify-center mt-12 mb-2 opacity-50">
                <img src="/images/pajaros.png" alt="Separador Birds" class="h-10 sm:h-12 object-contain" />
              </div>
            </div>
          </div>

          <!-- Grid Clásico (Cuando se filtra por una categoría específica) -->
          <div v-else-if="!productsStore.loading"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-3 sm:gap-4">

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
                  class="badge-popular absolute top-2.5 left-0 inline-flex items-center gap-1">
                  <Star :size="11" fill="currentColor" /> Popular
                </span>
                <div v-if="!product.available"
                  class="absolute inset-0 flex items-center justify-center backdrop-blur-[1px] bg-white/70">
                  <span class="sold-out-badge">Agotado</span>
                </div>

                <!-- Botón + flotante en hover -->
                <div
                  class="absolute bottom-3 right-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200">
                  <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                    class="float-add-btn w-10 h-10 rounded-full flex items-center justify-center text-white text-2xl font-black border-none cursor-pointer leading-none transition-all duration-150 hover:scale-110 active:scale-95">
                    +
                  </button>
                </div>
              </div>

              <!-- Info del producto -->
              <div class="flex flex-col flex-1 p-3 sm:p-4 product-info">
                <h3 class="font-bold text-[14px] sm:text-[15px] leading-snug m-0 mb-1.5 line-clamp-2 product-name">
                  {{ product.name }}
                </h3>
                <p class="text-[12px] sm:text-[12.5px] leading-relaxed m-0 line-clamp-2 flex-1 mb-3 product-desc">
                  {{ product.description }}
                </p>
                <div class="flex items-center justify-between gap-2 mt-auto">
                  <div class="flex items-baseline gap-0.5">
                    <span class="text-[11px] font-bold product-currency">S/</span>
                    <span class="font-black text-[22px] sm:text-[24px] leading-none product-price"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ product.price.toFixed(2) }}
                    </span>
                  </div>
                  <button v-if="product.available" @click.stop="handleAddToCart(product, $event)"
                    class="pedir-btn flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-full font-bold text-[12px] border-none cursor-pointer hover:-translate-y-0.5 active:scale-95 transition-all duration-150 shrink-0 uppercase tracking-wide">
                    <span class="text-[14px] leading-none font-black">+</span>
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
import { useHead } from '@vueuse/head'
import HeroCarousel from '@/components/layout/HeroCarousel.vue'
import AppIcon from '@/components/AppIcon.vue'
import type { Product } from '@/stores/products'
import { Star, Flame, Dot, Plus, PackageSearch, X } from 'lucide-vue-next'

useHead({
  title: 'Birds Perú - Florería',
  meta: [
    { name: 'description', content: 'Las flores más frescas de Chiclayo. Diseños que emocionan para cada ocasión especial.' },
  ],
  link: [
    { rel: 'canonical', href: 'https://catalogo.birds.pe' },
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

    // Mostramos máximo 6 para no cansar la vista
    if (group.products.length < 6) {
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
  background: #FFFAF5;
}

.catalog-bg {
  position: fixed;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
  z-index: 0;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.35;
}

.blob-1 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(var(--color-brand-primary-rgb, 196, 30, 30), .18), rgba(245, 197, 24, .08));
  top: -200px;
  right: -150px;
  animation: blob-float 18s ease-in-out infinite;
}

.blob-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(245, 197, 24, .12), rgba(var(--color-brand-primary-rgb, 196, 30, 30), .06));
  bottom: 10%;
  left: -100px;
  animation: blob-float 22s ease-in-out infinite reverse;
}

.blob-3 {
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(var(--color-brand-primary-rgb, 196, 30, 30), .1), transparent);
  top: 40%;
  left: 40%;
  animation: blob-float 16s ease-in-out infinite 4s;
}

.grid-pattern {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(var(--color-brand-primary-rgb, 196, 30, 30), .03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(var(--color-brand-primary-rgb, 196, 30, 30), .03) 1px, transparent 1px);
  background-size: 48px 48px;
}

@keyframes blob-float {

  0%,
  100% {
    transform: translate(0, 0) scale(1)
  }

  33% {
    transform: translate(30px, -40px) scale(1.05)
  }

  66% {
    transform: translate(-20px, 20px) scale(.95)
  }
}

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
    transform: scale(1)
  }

  50% {
    opacity: .7;
    transform: scale(.85)
  }
}

.promo-marquee {
  animation: marquee 28s linear infinite;
}

@keyframes marquee {
  from {
    transform: translateX(0)
  }

  to {
    transform: translateX(-50%)
  }
}

.section-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .08);
  border: 1.5px solid rgba(var(--color-brand-primary-rgb, 196, 30, 30), .15);
  font-size: 11px;
  font-weight: 900;
  color: var(--color-brand-primary, #C41E1E);
  letter-spacing: .1em;
  text-transform: uppercase;
  white-space: nowrap;
}

.section-pill.fire {
  background: rgba(245, 197, 24, .12);
  border-color: rgba(245, 197, 24, .3);
  color: #B8860B;
}

.cat-btn {
  backdrop-filter: blur(8px);
}

.cat-btn--active {
  background: linear-gradient(135deg, var(--color-brand-primary, #C41E1E), var(--color-brand-primary-dark, #A01010));
  border-color: var(--color-brand-primary, #C41E1E);
  color: white;
  box-shadow: 0 6px 24px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .35), 0 0 0 3px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .1);
  transform: scale(.97);
}

.cat-btn--idle {
  background: rgba(255, 255, 255, .85);
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .1);
  color: #4A3728;
}

.cat-btn--idle:hover {
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .05);
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .25);
  transform: translateY(-2px);
}

.cat-icon {
  transition: transform .2s cubic-bezier(.34, 1.56, .64, 1);
}

.cat-btn:hover .cat-icon {
  transform: scale(1.2) rotate(-5deg);
}

.popular-card {
  background: white;
  border-radius: 20px;
  border: 1.5px solid rgba(var(--color-brand-primary-rgb, 196, 30, 30), .08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
  transition: all .3s cubic-bezier(.34, 1.1, .64, 1);
}

.popular-card:hover {
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 16px 40px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .18);
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .2);
}

.popular-emoji-bg {
  background: radial-gradient(circle at 50% 60%, rgba(236, 72, 153, .08), rgba(16, 185, 129, .04) 60%, transparent);
}

.popular-info {
  background: white;
}

.popular-badge {
  background: #F5C518;
  color: #7A5500;
  font-size: 9px;
  font-weight: 900;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 999px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
  letter-spacing: .05em;
}

.add-circle-btn {
  background: linear-gradient(135deg, var(--color-brand-primary, #C41E1E), var(--color-brand-primary-dark, #A01010));
  box-shadow: 0 4px 12px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .4);
}

.price-text {
  color: var(--color-brand-primary, #C41E1E);
}

.skeleton-card {
  background: linear-gradient(90deg,
      rgba(var(--color-brand-primary-rgb, 196, 30, 30), .04) 25%,
      rgba(var(--color-brand-primary-rgb, 196, 30, 30), .08) 50%,
      rgba(var(--color-brand-primary-rgb, 196, 30, 30), .04) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0
  }

  100% {
    background-position: -200% 0
  }
}

.product-card {
  background: rgba(255, 255, 255, .92);
  border: 1.5px solid rgba(var(--color-brand-primary-rgb, 196, 30, 30), .08);
  box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
  backdrop-filter: blur(4px);
}

.product-card--available:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 36px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .14);
  border-color: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .22);
}

.product-emoji-bg {
  background: radial-gradient(circle at 50% 60%, rgba(236, 72, 153, .06), rgba(16, 185, 129, .03) 60%, transparent);
}

.product-img-overlay {
  background: linear-gradient(to top, rgba(var(--color-brand-primary-rgb, 196, 30, 30), .08), transparent 60%);
}

.badge-popular {
  background: linear-gradient(90deg, #F5C518, #E8B800);
  color: #7A5500;
  font-size: 9px;
  font-weight: 900;
  text-transform: uppercase;
  padding: 4px 10px 4px 8px;
  border-radius: 0 999px 999px 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
  letter-spacing: .05em;
  line-height: 1;
}

.sold-out-badge {
  font-size: 11px;
  font-weight: 900;
  color: #555;
  text-transform: uppercase;
  letter-spacing: .12em;
  background: rgba(255, 255, 255, .95);
  padding: 6px 14px;
  border-radius: 999px;
  border: 1px solid rgba(0, 0, 0, .1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
}

.float-add-btn {
  background: linear-gradient(135deg, var(--color-brand-primary, #C41E1E), var(--color-brand-primary-dark, #A01010));
  box-shadow: 0 4px 16px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .5);
}

.product-info {
  background: white;
}

.product-name {
  color: #1C1713;
}

.product-desc {
  color: #8A7B70;
}

.product-price {
  color: #1C1713;
}

.product-currency {
  color: #8A7B70;
}

.pedir-btn {
  background: linear-gradient(135deg, var(--color-brand-primary, #C41E1E) 0%, var(--color-brand-primary-dark, #A01010) 100%);
  color: white;
  box-shadow: 0 4px 14px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .35);
}

.pedir-btn:hover {
  box-shadow: 0 6px 20px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .45);
}

.empty-icon-bg {
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .06);
}

.count-badge {
  font-size: 12.5px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .06);
  border: 1.5px solid rgba(var(--color-brand-primary-rgb, 196, 30, 30), .12);
  color: var(--color-brand-primary, #C41E1E);
  white-space: nowrap;
}

.clear-search-btn {
  background: rgba(0, 0, 0, .05);
  color: var(--color-ink-muted, #7A9987);
}

.clear-search-btn:hover {
  background: rgba(var(--color-brand-primary-rgb, 196, 30, 30), .08);
  color: var(--color-brand-primary, #C41E1E);
}

.fab-mobile {
  background: linear-gradient(135deg, var(--color-brand-primary, #C41E1E) 0%, var(--color-brand-primary-dark, #9A0F0F) 100%);
  box-shadow: 0 8px 32px rgba(var(--color-brand-primary-rgb, 196, 30, 30), .5), 0 0 0 1.5px rgba(255, 255, 255, .15) inset;
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
    background-position: 200% 0
  }

  100% {
    background-position: -200% 0
  }
}

.fab-count {
  background: rgba(255, 255, 255, .22);
}

.particle {
  animation: particle-fly .7s cubic-bezier(.22, .61, .36, 1) forwards;
}

@keyframes particle-fly {
  0% {
    transform: translate(0, 0) scale(1) rotate(0deg);
    opacity: 1
  }

  60% {
    opacity: 1
  }

  100% {
    transform: translate(var(--tx), var(--ty)) scale(.3) rotate(180deg);
    opacity: 0
  }
}
</style>