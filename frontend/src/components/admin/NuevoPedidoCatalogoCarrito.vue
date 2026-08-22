<template>
  <div class="flex-1 flex flex-col min-h-0 overflow-hidden bg-gray-50/40">

    <!-- Tabs -->
    <div class="flex border-b border-gray-100 shrink-0 bg-white">
      <button @click="$emit('update:rightTab', 'catalogo')" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-[13px] font-semibold
               border-none cursor-pointer transition-all duration-150"
        :class="rightTab === 'catalogo' ? 'bg-white text-brand-red border-b-2 border-brand-red' : 'bg-gray-50 text-gray-400 hover:text-gray-700'">
        <TagIcon class="w-4 h-4" />
        Catálogo
      </button>
      <button @click="$emit('update:rightTab', 'carrito')" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-[13px] font-semibold
               border-none cursor-pointer transition-all duration-150 relative"
        :class="rightTab === 'carrito' ? 'bg-white text-brand-red border-b-2 border-brand-red' : 'bg-gray-50 text-gray-400 hover:text-gray-700'">
        <ShoppingCartIcon class="w-4 h-4" />
        Carrito
        <span v-if="cartItems.length > 0"
          class="inline-flex w-5 h-5 rounded-full bg-brand-red text-white text-[10px] font-black items-center justify-center shadow-sm">
          {{ cartItems.length }}
        </span>
      </button>
    </div>

    <!-- CATÁLOGO -->
    <div v-if="rightTab === 'catalogo'" class="flex-1 overflow-y-auto flex flex-col">
      <div
        class="flex gap-2 overflow-x-auto px-4 py-3 border-b border-gray-100 bg-white scrollbar-none shrink-0">
        <button @click="$emit('update:activeCat', 'all')" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold
                 border whitespace-nowrap shrink-0 cursor-pointer transition-all duration-150" :class="activeCat === 'all'
                  ? 'bg-brand-red text-white border-brand-red shadow-sm'
                  : 'bg-white border-gray-200 text-gray-500 hover:border-red-300 hover:text-brand-red'">
          <LayoutGrid :size="13" /> Todo
        </button>
        <button v-for="cat in productsStore.categories" :key="cat.id" @click="$emit('update:activeCat', cat.slug)" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold
                 border whitespace-nowrap shrink-0 cursor-pointer transition-all duration-150" :class="activeCat === cat.slug
                  ? 'bg-brand-red text-white border-brand-red shadow-sm'
                  : 'bg-white border-gray-200 text-gray-500 hover:border-red-300 hover:text-brand-red'">
          <AppIcon :name="cat.icon" :size="13" /> {{ cat.name }}
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-4">
        <div v-if="filteredCatalog.length === 0"
          class="flex flex-col items-center py-16 text-gray-400 gap-2">
          <PackageSearch :size="36" />
          <p class="m-0 text-[13px]">Sin productos en esta categoría</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
          <div v-for="p in filteredCatalog" :key="p.id" @click="p.available && $emit('openProduct', p)"
            class="bg-white rounded-2xl border overflow-hidden transition-all duration-200 group" :class="p.available
              ? 'border-gray-100 cursor-pointer hover:border-red-200 hover:shadow-sm hover:-translate-y-0.5'
              : 'border-gray-100 opacity-50 cursor-not-allowed'">
            <div class="relative h-24 bg-gray-50 flex items-center justify-center overflow-hidden">
              <img v-if="p.image_url" :src="p.image_url"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
              <AppIcon v-else :name="p.icon" :size="36" class="text-gray-300" />
              <div v-if="p.popular"
                class="absolute top-2 right-0 bg-pink-400 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-l-md">
                Popular
              </div>
              <div v-if="p.controla_stock && p.stock != null && p.stock > 0 && p.stock <= 5"
                class="absolute bottom-1 left-1 bg-amber-400 text-amber-900 text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md">
                Últimos {{ p.stock }}
              </div>
              <div v-if="!p.available"
                class="absolute inset-0 bg-white/70 flex items-center justify-center">
                <span class="text-[10px] font-black text-gray-500 uppercase">Agotado</span>
              </div>
            </div>
            <div class="p-3">
              <p class="font-semibold text-[13px] text-gray-900 m-0 leading-snug mb-1">{{ p.name }}</p>
              <div class="flex items-center justify-between">
                <div class="flex items-baseline gap-0.5">
                  <span class="text-[11px] font-semibold text-gray-400">S/</span>
                  <span class="font-black text-[17px] text-brand-red leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ p.price.toFixed(2) }}
                  </span>
                </div>
                <div v-if="p.available"
                  class="w-7 h-7 rounded-full bg-brand-red text-white text-xl flex items-center justify-center font-black shadow-sm hover:bg-red-700 transition-colors leading-none">
                  +
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CARRITO -->
    <div v-if="rightTab === 'carrito'" class="flex-1 overflow-y-auto flex flex-col">
      <div v-if="cartItems.length === 0"
        class="flex-1 flex flex-col items-center justify-center py-16 text-gray-400 gap-4">
        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
          <ShoppingCartIcon class="w-8 h-8 text-gray-300" />
        </div>
        <div class="text-center">
          <p class="font-bold text-gray-600 text-[14px] m-0">Carrito vacío</p>
          <p class="text-[12.5px] m-0 mt-1">Agrega productos del catálogo</p>
        </div>
        <button @click="$emit('update:rightTab', 'catalogo')" class="px-5 py-2.5 rounded-xl bg-brand-red text-white font-bold text-[13px]
                 border-none cursor-pointer hover:bg-red-700 shadow-sm transition-all duration-150">
          Ver catálogo →
        </button>
      </div>

      <div v-else class="p-4 flex flex-col gap-2.5">
        <TransitionGroup name="cart-item">
          <div v-for="item in cartItems" :key="item._uid"
            class="flex items-start gap-3 p-3.5 rounded-2xl bg-white border border-gray-100 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-gray-50 shrink-0 flex items-center justify-center
                        text-2xl overflow-hidden border border-gray-100">
              <img v-if="item.imageUrl" :src="item.imageUrl" class="w-full h-full object-cover" />
              <AppIcon v-else :name="item.icon" :size="20" class="text-gray-300" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-[13.5px] text-gray-900 m-0 leading-snug">{{ item.name }}</p>
              <p v-if="item.customSummary" class="text-[11.5px] text-gray-400 mt-0.5 m-0 line-clamp-2">
                {{ item.customSummary }}
              </p>
              <div class="flex items-center gap-2 mt-2.5">
                <div class="flex items-center gap-1 bg-gray-50 rounded-xl border border-gray-100 p-0.5">
                  <button @click="cartStore.decrementQty(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-500
                           cursor-pointer border-none bg-transparent hover:bg-white hover:text-brand-red
                           transition-all duration-150 text-base font-bold">−</button>
                  <span class="text-[13px] font-black min-w-[20px] text-center text-gray-900">{{ item.qty
                    }}</span>
                  <button @click="cartStore.incrementQty(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-500
                           cursor-pointer border-none bg-transparent hover:bg-white hover:text-brand-red
                           transition-all duration-150 text-base font-bold">+</button>
                </div>
                <div class="flex items-baseline gap-0.5 ml-auto">
                  <span class="text-[11px] text-gray-400 font-semibold">S/</span>
                  <span class="font-black text-[15px] text-brand-red leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ (item.price * item.qty).toFixed(2) }}
                  </span>
                </div>
                <button @click="cartStore.remove(item._uid)" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                         cursor-pointer border-none bg-transparent hover:bg-red-50 hover:text-red-500
                         transition-all duration-150">
                  <TrashIcon class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </TransitionGroup>

        <div class="mt-1 p-4 rounded-2xl bg-white border border-gray-100">
          <div class="flex justify-between text-[12.5px] text-gray-400 mb-2">
            <span>{{ cartItems.length }} items</span>
            <span>S/ {{ orderTotal.toFixed(2) }}</span>
          </div>
          <div v-if="!isEditMode && formType === 'delivery' && deliveryFeeAmount > 0"
            class="flex justify-between text-[12.5px] text-gray-400 mb-2">
            <span>Delivery</span>
            <span>+ S/ {{ deliveryFeeAmount.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center pt-2.5 border-t border-gray-100">
            <span class="font-semibold text-[14px] text-gray-700">Total</span>
            <div class="flex items-baseline gap-1">
              <span class="text-[12px] text-gray-400 font-semibold">S/</span>
              <span class="font-black text-[22px] text-brand-red leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ isEditMode ? orderTotal.toFixed(2) : totalConDelivery.toFixed(2) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { TagIcon, ShoppingCartIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { LayoutGrid, PackageSearch } from 'lucide-vue-next'
import AppIcon from '@/components/AppIcon.vue'
import { useProductsStore } from '@/stores/products'
import type { Product } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import type { CartItem } from '@/stores/cart'

const productsStore = useProductsStore()
const cartStore = useCartStore()

defineProps<{
  rightTab: 'catalogo' | 'carrito'
  activeCat: string
  filteredCatalog: Product[]
  cartItems: CartItem[]
  orderTotal: number
  deliveryFeeAmount: number
  totalConDelivery: number
  isEditMode: boolean
  formType: string
}>()

defineEmits<{
  'update:rightTab': [value: 'catalogo' | 'carrito']
  'update:activeCat': [value: string]
  openProduct: [product: Product]
}>()
</script>

<style scoped>
.cart-item-enter-active {
  transition: all 0.2s ease;
}

.cart-item-leave-active {
  transition: all 0.15s ease;
}

.cart-item-enter-from {
  opacity: 0;
  transform: translateX(-8px);
}

.cart-item-leave-to {
  opacity: 0;
  transform: translateX(8px);
}
</style>
