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

        <!-- ── Línea de negocio (Florería / Cafetería / Menú) ── -->
        <div class="px-4 md:px-8 pt-6">
          <div class="flex gap-2 sm:gap-2.5 overflow-x-auto scrollbar-none pb-1">
            <button v-for="line in BUSINESS_LINES" :key="line.value" @click="changeLine(line.value)" class="flex items-center gap-2 px-4 py-2.5 rounded-full border-2
             font-bold text-[12.5px] cursor-pointer transition-all duration-200
             uppercase tracking-wide shrink-0" :class="productsStore.activeLine === line.value
              ? 'border-brand-red bg-brand-red text-white shadow-red-sm'
              : 'border-surface-border bg-white text-ink-muted hover:border-brand-red/40 hover:text-brand-red'">
              <span class="text-[16px] leading-none">{{ line.icon }}</span>
              {{ line.label }}
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
              <span class="text-[24px] sm:text-[26px] leading-none cat-icon"></span>
              <span>Todo</span>
            </button>

            <button v-for="cat in categoriesForActiveLine" :key="cat.id" @click="productsStore.setCategory(cat.slug)"
              class="cat-btn flex flex-col items-center gap-1.5 sm:gap-2
                     py-3 sm:py-3.5 px-2 rounded-2xl border-2 font-bold
                     text-[11px] sm:text-[12px] cursor-pointer transition-all duration-250"
              :class="productsStore.activeCategory === cat.slug ? 'cat-btn--active' : 'cat-btn--idle'">
              <span class="text-[24px] sm:text-[26px] leading-none cat-icon">{{ cat.emoji }}</span>
              <span class="truncate w-full text-center">{{ cat.name }}</span>
            </button>
          </div>
        </div>

        <!-- ── Populares ── -->
        <div v-if="productsStore.activeCategory === 'all'" class="px-4 md:px-8 mb-8">
          <div class="flex items-center gap-3 mb-4">
            <div class="section-pill fire">⭐</div>
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
                  <span class="text-[40px] sm:text-[48px] leading-none transition-transform
                               duration-300 group-hover:scale-115 group-hover:-rotate-6">
                    {{ product.emoji || '' }}
                  </span>
                </div>
                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent" />
                <div v-if="product.popular" class="absolute top-2 right-2 popular-badge">⭐</div>
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
            </div>
            <span class="count-badge">{{ productsStore.filtered.length }} productos</span>
          </div>

          <!-- Skeleton -->
          <div v-if="productsStore.loading"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            <div v-for="n in 8" :key="n" class="rounded-2xl animate-pulse skeleton-card" style="aspect-ratio:3/4;" />
          </div>

          <!-- Grid productos -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-3 sm:gap-4">

            <div v-for="product in productsStore.filtered" :key="product.id"
              @click="product.available && openProduct(product)" class="product-card group rounded-2xl overflow-hidden flex flex-col
                     transition-all duration-300 relative"
              :class="product.available ? 'cursor-pointer product-card--available' : 'opacity-50 cursor-default'">

              <!-- Imagen -->
              <div class="relative overflow-hidden product-img-wrap" style="aspect-ratio:4/3;">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                <div v-else class="w-full h-full flex items-center justify-center product-emoji-bg">
                  <span class="text-[56px] sm:text-[64px] leading-none transition-all duration-300
                               group-hover:scale-110 group-hover:-rotate-3">
                    {{ product.emoji || '💐' }}
                  </span>
                </div>

                <div class="absolute inset-0 product-img-overlay opacity-0 group-hover:opacity-100
                            transition-opacity duration-300" />

                <span v-if="product.popular" class="badge-popular absolute top-2.5 left-0">
                  ⭐ Popular
                </span>

                <div v-if="!product.available" class="absolute inset-0 flex items-center justify-center
                         backdrop-blur-[1px] bg-white/70">
                  <span class="sold-out-badge">Agotado</span>
                </div>

                <!-- Botón + flotante en hover -->
                <div class="absolute bottom-3 right-3 opacity-0 translate-y-2
                            group-hover:opacity-100 group-hover:translate-y-0
                            transition-all duration-200">
                  <button v-if="product.available" @click.stop="handleAddToCart(product, $event)" class="float-add-btn w-10 h-10 rounded-full flex items-center
                           justify-center text-white text-2xl font-black border-none
                           cursor-pointer leading-none transition-all duration-150
                           hover:scale-110 active:scale-95">
                    +
                  </button>
                </div>
              </div>

              <!-- Info del producto -->
              <div class="flex flex-col flex-1 p-3 sm:p-4 product-info">
                <h3 class="font-bold text-[14px] sm:text-[15px] leading-snug
                           m-0 mb-1.5 line-clamp-2 product-name">
                  {{ product.name }}
                </h3>
                <p class="text-[12px] sm:text-[12.5px] leading-relaxed m-0
                          line-clamp-2 flex-1 mb-3 product-desc">
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

                  <button v-if="product.available" @click.stop="handleAddToCart(product, $event)" class="pedir-btn flex items-center gap-1.5 px-3 sm:px-3.5 py-2
                           rounded-full font-bold text-[12px] border-none cursor-pointer
                           hover:-translate-y-0.5 active:scale-95 transition-all duration-150
                           shrink-0 uppercase tracking-wide">
                    <span class="text-[14px] leading-none font-black">+</span>
                    <span>Pedir</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty state -->
            <div v-if="productsStore.filtered.length === 0"
              class="col-span-full flex flex-col items-center py-20 gap-4">
              <div class="w-20 h-20 rounded-full empty-icon-bg flex items-center
                          justify-center text-4xl">
                🌷
              </div>
              <p class="m-0 text-[15px] font-semibold text-ink">
                Sin productos en esta categoría
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ SIDEBAR CARRITO DESKTOP ══ -->
      <aside class="hidden lg:flex flex-col w-[360px] xl:w-[400px] shrink-0
                    sticky top-16 h-[calc(100vh-4rem)] sidebar-cart">

        <div class="px-5 py-4 border-b border-cream-border">
          <div class="flex items-center justify-between mb-1">
            <h2 class="font-black text-[18px] text-ink m-0 tracking-tight"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Tu pedido
            </h2>
            <Transition name="badge-pop">
              <span v-if="cartStore.count > 0" key="b" class="cart-count-badge">
                {{ cartStore.count }} items
              </span>
            </Transition>
          </div>
          <p class="text-[12px] text-ink-muted m-0">Florería · Flores Frescas</p>
        </div>

        <div class="flex-1 overflow-y-auto">
          <div v-if="cartStore.isEmpty"
            class="flex flex-col items-center justify-center h-full gap-5 px-6 py-12 text-center">
            <div class="cart-empty-icon w-24 h-24 rounded-full flex items-center
                        justify-center text-5xl">
              🛒
            </div>
            <div>
              <p class="font-bold text-ink text-[15px] m-0 mb-1">Tu carrito está vacío</p>
              <p class="text-ink-muted text-[13px] m-0 leading-snug">
                Elige un arreglo del catálogo y personalízalo a tu gusto
              </p>
            </div>
            <button @click="scrollToMenu" class="see-menu-btn px-6 py-2.5 rounded-full font-bold text-[13px]
                     border-none cursor-pointer transition-all duration-150 hover:-translate-y-0.5">
              Ver catálogo ↑
            </button>
          </div>

          <TransitionGroup v-else tag="div" name="cart-item">
            <div v-for="item in cartStore.items" :key="item._uid" class="px-4 py-4 border-b border-cream-border/60
                     hover:bg-cream-warm/30 transition-colors duration-100">

              <div class="flex items-start gap-3 mb-2">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-100
                            flex items-center justify-center text-xl shrink-0 overflow-hidden">
                  <img v-if="item.imageUrl" :src="item.imageUrl" class="w-full h-full object-cover" />
                  <span v-else>{{ item.emoji || '💐' }}</span>
                </div>

                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-ink text-[13px] leading-snug m-0">{{ item.name }}</p>
                  <p class="font-black text-[14px] text-brand-red m-0 mt-0.5 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    S/ {{ item.basePrice.toFixed(2) }}
                    <span v-if="item.extrasPrice > 0" class="text-[10px] text-gray-400 font-semibold ml-1">
                      base
                    </span>
                  </p>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                  <button @click="editCartItem(item)" class="w-6 h-6 rounded-lg flex items-center justify-center
                           text-amber-500 cursor-pointer border border-amber-200
                           bg-amber-50 hover:bg-amber-100 transition-all duration-150">
                    <PencilIcon class="w-3 h-3" />
                  </button>
                  <button @click="cartStore.remove(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center
                           text-ink-faint cursor-pointer border-none bg-transparent
                           hover:bg-red-50 hover:text-red-500 transition-all duration-150">
                    <TrashIcon class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <div v-if="item.customization.length > 0"
                class="ml-14 pl-2.5 border-l-2 border-gray-100 mb-2 flex flex-col gap-0.5">
                <div v-for="sec in item.customization" :key="sec.section_id" class="flex items-start gap-1">
                  <span class="text-[11px] shrink-0 mt-0.5">{{ getSectionEmoji(sec.seccion) }}</span>
                  <p class="text-[11px] text-gray-500 m-0 leading-relaxed">
                    <span class="font-semibold text-gray-600">{{ sec.label }}:</span>
                    {{sec.selections.map(s => s.name).join(', ')}}
                  </p>
                </div>
              </div>

              <div v-if="item.extras.length > 0" class="ml-14 flex flex-col gap-1 mb-2">
                <div v-for="extra in item.extras" :key="extra.extra_id" class="flex items-center justify-between px-2.5 py-1.5
                         rounded-lg bg-green-50 border border-green-100">
                  <span class="text-[11.5px] font-semibold text-gray-700">
                    ➕ {{ extra.qty > 1 ? `${extra.name} ×${extra.qty}` : extra.name }}
                  </span>
                  <span class="text-[11px] font-black text-green-700">
                    +S/ {{ (extra.price * extra.qty).toFixed(2) }}
                  </span>
                </div>
              </div>

              <div class="flex items-center justify-between ml-14">
                <div class="qty-control flex items-center gap-1.5 rounded-xl border border-cream-border p-0.5">
                  <button @click="cartStore.decrementQty(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center
                           text-ink-mid cursor-pointer border-none bg-transparent
                           hover:bg-white hover:text-brand-red transition-all duration-150 text-sm font-bold">
                    −
                  </button>
                  <span class="text-[12px] font-black min-w-[18px] text-center text-ink">
                    {{ item.qty }}
                  </span>
                  <button @click="cartStore.incrementQty(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center
                           text-ink-mid cursor-pointer border-none bg-transparent
                           hover:bg-white hover:text-brand-red transition-all duration-150 text-sm font-bold">
                    +
                  </button>
                </div>

                <div class="text-right">
                  <div v-if="item.extrasPrice > 0" class="text-[9.5px] text-gray-400 font-semibold mb-0.5">
                    × {{ item.qty }}
                  </div>
                  <span class="font-black text-[15px] text-ink leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    S/ {{ (item.price * item.qty).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <div class="p-4 border-t border-cream-border bg-white">
          <template v-if="!cartStore.isEmpty">
            <div class="cart-summary rounded-2xl p-4 mb-3">
              <div class="flex justify-between text-[13px] text-ink-muted mb-2">
                <span>Subtotal ({{ cartStore.count }} items)</span>
                <span>S/ {{ cartStore.total.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center pt-2.5 mt-2.5 border-t border-cream-border">
                <span class="font-bold text-[15px] text-ink">Total</span>
                <div class="flex items-baseline gap-1">
                  <span class="text-[13px] font-semibold text-ink-muted">S/</span>
                  <span class="font-black text-[28px] text-ink leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ cartStore.total.toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>
            <RouterLink to="/checkout" class="checkout-btn flex items-center justify-between w-full py-4 px-5
                     rounded-2xl no-underline font-bold text-[15px] text-white
                     hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200">
              <span>Confirmar pedido</span>
              <span class="checkout-price px-3 py-1 rounded-full font-black text-[15px]"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                S/ {{ cartStore.total.toFixed(2) }}
              </span>
            </RouterLink>
          </template>
          <template v-else>
            <p class="text-center text-[12.5px] text-ink-muted leading-relaxed m-0">
              Agrega productos para hacer tu pedido 💐
            </p>
          </template>
        </div>
      </aside>
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
        {{ p.emoji }}
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, inject, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import { useHead } from '@vueuse/head'
import HeroCarousel from '@/components/layout/HeroCarousel.vue'
import type { Product } from '@/stores/products'
import type { CartItem } from '@/stores/cart'
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

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

// ── Líneas de negocio ─────────────────────────────────────
const BUSINESS_LINES = [
  { value: 'all', icon: '🛍️', label: 'Todo' },
  { value: 'floreria', icon: '💐', label: 'Florería' },
  { value: 'cafeteria', icon: '☕', label: 'Cafetería' },
  { value: 'menu', icon: '🍽️', label: 'Menú' },
] as const

function changeLine(line: string) {
  productsStore.setLine(line)
  productsStore.fetch(line === 'all' ? undefined : line)
}

const cartOpen = inject<any>('cartOpen')
const customizer = inject<any>('customizer')

// ── Partículas ────────────────────────────────────────────
interface Particle {
  id: number; x: number; y: number
  tx: number; ty: number; emoji: string
}
const particles = ref<Particle[]>([])
let particleId = 0
const EMOJIS = ['🌹', '💐', '🌸', '✨', '💕']

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
      emoji: EMOJIS[Math.floor(Math.random() * EMOJIS.length)],
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

function editCartItem(item: CartItem) {
  const product = productsStore.products.find(p => p.id === item.productId)
  if (!product) return
  customizer.openEdit(product, item)
}

function getSectionEmoji(seccion: string): string {
  const m: Record<string, string> = {
    envoltura: '🎁', lazo: '🎀', follaje: '🌿',
    dedicatoria: '✍️', presentacion: '🪴', complemento: '🧸',
    salsas: '🫙', ensalada: '🥗', papas: '🍟', termino: '🔥',
  }
  return m[seccion] ?? '🌸'
}

// ── Computed ──────────────────────────────────────────────
const categoryLabel = computed(() => {
  if (productsStore.activeCategory !== 'all') {
    const cat = productsStore.categories.find(c => c.slug === productsStore.activeCategory)
    return cat && cat.name ? `${cat.emoji || ''} ${cat.name}` : 'Catálogo'
  }
  const line = BUSINESS_LINES.find(l => l.value === productsStore.activeLine)
  return line && line.value !== 'all' ? `${line.icon} ${line.label}` : 'Catálogo completo'
})

const categoriesForActiveLine = computed(() =>
  productsStore.categoriesByLine(productsStore.activeLine)
)

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => productsStore.fetch(
  productsStore.activeLine !== 'all' ? productsStore.activeLine : undefined
))

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
  background: radial-gradient(circle, rgba(196, 30, 30, .18), rgba(245, 197, 24, .08));
  top: -200px;
  right: -150px;
  animation: blob-float 18s ease-in-out infinite;
}

.blob-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(245, 197, 24, .12), rgba(196, 30, 30, .06));
  bottom: 10%;
  left: -100px;
  animation: blob-float 22s ease-in-out infinite reverse;
}

.blob-3 {
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(196, 30, 30, .1), transparent);
  top: 40%;
  left: 40%;
  animation: blob-float 16s ease-in-out infinite 4s;
}

.grid-pattern {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(196, 30, 30, .03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(196, 30, 30, .03) 1px, transparent 1px);
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
  background: rgba(196, 30, 30, .08);
  border: 1.5px solid rgba(196, 30, 30, .15);
  font-size: 11px;
  font-weight: 900;
  color: #C41E1E;
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
  background: linear-gradient(135deg, #C41E1E, #A01010);
  border-color: #C41E1E;
  color: white;
  box-shadow: 0 6px 24px rgba(196, 30, 30, .35), 0 0 0 3px rgba(196, 30, 30, .1);
  transform: scale(.97);
}

.cat-btn--idle {
  background: rgba(255, 255, 255, .85);
  border-color: rgba(196, 30, 30, .1);
  color: #4A3728;
}

.cat-btn--idle:hover {
  background: rgba(196, 30, 30, .05);
  border-color: rgba(196, 30, 30, .25);
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
  border: 1.5px solid rgba(196, 30, 30, .08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
  transition: all .3s cubic-bezier(.34, 1.1, .64, 1);
}

.popular-card:hover {
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 16px 40px rgba(196, 30, 30, .18);
  border-color: rgba(196, 30, 30, .2);
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
  background: linear-gradient(135deg, #C41E1E, #A01010);
  box-shadow: 0 4px 12px rgba(196, 30, 30, .4);
}

.price-text {
  color: #C41E1E;
}

.skeleton-card {
  background: linear-gradient(90deg,
      rgba(196, 30, 30, .04) 25%,
      rgba(196, 30, 30, .08) 50%,
      rgba(196, 30, 30, .04) 75%);
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
  border: 1.5px solid rgba(196, 30, 30, .08);
  box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
  backdrop-filter: blur(4px);
}

.product-card--available:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 36px rgba(196, 30, 30, .14);
  border-color: rgba(196, 30, 30, .22);
}

.product-emoji-bg {
  background: radial-gradient(circle at 50% 60%, rgba(236, 72, 153, .06), rgba(16, 185, 129, .03) 60%, transparent);
}

.product-img-overlay {
  background: linear-gradient(to top, rgba(196, 30, 30, .08), transparent 60%);
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
  background: linear-gradient(135deg, #C41E1E, #A01010);
  box-shadow: 0 4px 16px rgba(196, 30, 30, .5);
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
  background: linear-gradient(135deg, #C41E1E 0%, #A01010 100%);
  color: white;
  box-shadow: 0 4px 14px rgba(196, 30, 30, .35);
}

.pedir-btn:hover {
  box-shadow: 0 6px 20px rgba(196, 30, 30, .45);
}

.empty-icon-bg {
  background: rgba(196, 30, 30, .06);
}

.sidebar-cart {
  background: rgba(255, 255, 255, .95);
  border-left: 1.5px solid rgba(196, 30, 30, .08);
  backdrop-filter: blur(12px);
}

.cart-count-badge {
  font-size: 11.5px;
  font-weight: 900;
  padding: 4px 10px;
  border-radius: 999px;
  background: linear-gradient(135deg, #C41E1E, #A01010);
  color: white;
  box-shadow: 0 2px 8px rgba(196, 30, 30, .3);
}

.cart-empty-icon {
  background: rgba(196, 30, 30, .06);
}

.see-menu-btn {
  background: linear-gradient(135deg, #C41E1E, #A01010);
  color: white;
  box-shadow: 0 4px 14px rgba(196, 30, 30, .3);
}

.qty-control {
  background: rgba(255, 250, 245, .9);
}

.cart-summary {
  background: rgba(255, 250, 245, .8);
  border: 1.5px solid rgba(196, 30, 30, .08);
}

.checkout-btn {
  background: linear-gradient(135deg, #C41E1E 0%, #9A0F0F 100%);
  box-shadow: 0 6px 24px rgba(196, 30, 30, .4);
}

.checkout-btn:hover {
  box-shadow: 0 10px 32px rgba(196, 30, 30, .5);
}

.checkout-price {
  background: rgba(255, 255, 255, .2);
}

.count-badge {
  font-size: 12.5px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(196, 30, 30, .06);
  border: 1.5px solid rgba(196, 30, 30, .12);
  color: #C41E1E;
  white-space: nowrap;
}

.fab-mobile {
  background: linear-gradient(135deg, #C41E1E 0%, #9A0F0F 100%);
  box-shadow: 0 8px 32px rgba(196, 30, 30, .5), 0 0 0 1.5px rgba(255, 255, 255, .15) inset;
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

.cart-item-enter-active {
  transition: all .3s cubic-bezier(.34, 1.56, .64, 1);
}

.cart-item-leave-active {
  transition: all .2s ease;
}

.cart-item-enter-from {
  opacity: 0;
  transform: translateX(-16px) scale(.94);
}

.cart-item-leave-to {
  opacity: 0;
  transform: translateX(12px);
  height: 0;
  padding: 0;
}

.badge-pop-enter-active {
  transition: all .35s cubic-bezier(.34, 1.56, .64, 1);
}

.badge-pop-leave-active {
  transition: all .15s ease;
}

.badge-pop-enter-from {
  opacity: 0;
  transform: scale(.4) rotate(-15deg);
}

.badge-pop-leave-to {
  opacity: 0;
  transform: scale(.5);
}
</style>