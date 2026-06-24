<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="isOpen" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 lg:hidden" @click="isOpen = false" />
    </Transition>

    <Transition enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-250 ease-in" enter-from-class="translate-y-full opacity-0"
      leave-to-class="translate-y-full opacity-0">
      <div v-if="isOpen" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-lg z-[60]
               bg-white rounded-t-[28px] max-h-[92vh] flex flex-col
               shadow-[0_-12px_48px_rgba(0,0,0,0.18)] lg:hidden" role="dialog">

        <!-- Handle -->
        <div class="flex justify-center pt-3 pb-1 shrink-0">
          <div class="w-10 h-1 bg-gray-200 rounded-full" />
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3.5 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-2xl bg-red-50 border border-red-100
                        flex items-center justify-center">
              <span class="text-[18px]">🛒</span>
            </div>
            <div>
              <h2 class="font-black text-[17px] text-gray-900 m-0 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Tu pedido
              </h2>
              <p class="text-[11px] text-gray-400 m-0 mt-0.5">Mahoma Chicken</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-75"
              leave-to-class="opacity-0 scale-75">
              <span v-if="cart.count > 0" class="text-[11px] font-black px-2.5 py-1 rounded-full
                       bg-brand-red text-white">
                {{ cart.count }} items
              </span>
            </Transition>
            <button @click="isOpen = false" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
                     justify-center text-gray-500 cursor-pointer border-none
                     hover:bg-gray-200 transition-all duration-150">
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="h-px bg-gray-100 mx-5 shrink-0" />

        <!-- Items -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="cart.isEmpty" class="flex flex-col items-center justify-center
                   py-16 px-6 text-center gap-4">
            <div class="w-20 h-20 rounded-full bg-gray-50 border border-gray-100
                        flex items-center justify-center text-4xl">
              🛒
            </div>
            <div>
              <p class="text-gray-800 font-bold text-[15px] m-0">Carrito vacío</p>
              <p class="text-gray-400 text-[13px] mt-1 m-0">
                Explora el menú y agrega algo rico
              </p>
            </div>
            <button @click="isOpen = false" class="px-6 py-2.5 rounded-2xl bg-brand-red text-white
                     font-bold text-[13px] border-none cursor-pointer
                     hover:bg-red-700 transition-all duration-150
                     uppercase tracking-wide">
              Ver menú 🍗
            </button>
          </div>

          <TransitionGroup v-else tag="div" name="item">
            <div v-for="item in cart.items" :key="item._uid" class="px-5 py-4 border-b border-gray-50 last:border-0">

              <!-- Fila principal -->
              <div class="flex items-start gap-3 mb-2.5">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-100
                            flex items-center justify-center text-2xl shrink-0
                            overflow-hidden">
                  <img v-if="item.imageUrl" :src="item.imageUrl" class="w-full h-full object-cover" />
                  <span v-else>{{ item.emoji }}</span>
                </div>

                <div class="flex-1 min-w-0">
                  <p class="font-bold text-[14px] text-gray-900 m-0 leading-snug">
                    {{ item.name }}
                  </p>
                  <p class="font-black text-[15px] text-brand-red m-0 mt-0.5"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    S/ {{ item.basePrice.toFixed(2) }}
                    <span v-if="item.extrasPrice > 0" class="text-[11px] text-gray-400 font-semibold ml-1">
                      base
                    </span>
                  </p>
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-1 shrink-0">
                  <button @click="editItem(item)" class="w-7 h-7 rounded-xl flex items-center justify-center
                           text-amber-500 cursor-pointer border border-amber-200
                           bg-amber-50 hover:bg-amber-100
                           transition-all duration-150">
                    <PencilIcon class="w-3.5 h-3.5" />
                  </button>
                  <button @click="cart.remove(item._uid)" class="w-7 h-7 rounded-xl flex items-center justify-center
                           text-gray-300 cursor-pointer border-none bg-transparent
                           hover:bg-red-50 hover:text-red-500
                           transition-all duration-150">
                    <TrashIcon class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <!-- Personalización detallada -->
              <div v-if="item.customization.length > 0" class="ml-[3.75rem] pl-3 border-l-2 border-gray-100
                       mb-2.5 flex flex-col gap-1">
                <div v-for="sec in item.customization" :key="sec.section_id" class="flex items-start gap-1.5">
                  <span class="text-[12px] shrink-0 mt-0.5">
                    {{ getSectionEmoji(sec.seccion) }}
                  </span>
                  <p class="text-[12px] text-gray-500 m-0 leading-relaxed">
                    <span class="font-semibold text-gray-600">{{ sec.label }}:</span>
                    {{sec.selections.map(s => s.name).join(', ')}}
                  </p>
                </div>
              </div>

              <!-- Extras -->
              <div v-if="item.extras.length > 0" class="ml-[3.75rem] flex flex-col gap-1.5 mb-2.5">
                <div v-for="extra in item.extras" :key="extra.extra_id" class="flex items-center justify-between px-3 py-2
                         rounded-xl bg-green-50 border border-green-100">
                  <div class="flex items-center gap-1.5">
                    <span class="text-[13px]">➕</span>
                    <span class="text-[12.5px] font-semibold text-gray-700">
                      {{ extra.qty > 1 ? `${extra.name} ×${extra.qty}` : extra.name }}
                    </span>
                  </div>
                  <span class="text-[12px] font-black text-green-700">
                    +S/ {{ (extra.price * extra.qty).toFixed(2) }}
                  </span>
                </div>
              </div>

              <!-- Qty + total -->
              <div class="flex items-center justify-between ml-[3.75rem]">
                <div class="flex items-center gap-1.5 bg-gray-50 rounded-xl
                            border border-gray-100 p-0.5">
                  <button @click="cart.decrementQty(item._uid)" class="w-7 h-7 rounded-lg flex items-center justify-center
                           text-gray-500 cursor-pointer border-none bg-transparent
                           hover:bg-white hover:text-brand-red
                           transition-all duration-150 font-bold text-base">
                    −
                  </button>
                  <span class="text-[13px] font-black min-w-[20px]
                               text-center text-gray-900">
                    {{ item.qty }}
                  </span>
                  <button @click="cart.incrementQty(item._uid)" class="w-7 h-7 rounded-lg flex items-center justify-center
                           text-gray-500 cursor-pointer border-none bg-transparent
                           hover:bg-white hover:text-brand-red
                           transition-all duration-150 font-bold text-base">
                    +
                  </button>
                </div>

                <div class="text-right">
                  <div v-if="item.extrasPrice > 0" class="text-[10px] text-gray-400 font-semibold mb-0.5">
                    (base + extras) × {{ item.qty }}
                  </div>
                  <span class="font-black text-[17px] text-brand-red leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    S/ {{ (item.price * item.qty).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <!-- Footer -->
        <div class="px-5 pt-4 pb-8 border-t border-gray-100 bg-white shrink-0">
          <template v-if="!cart.isEmpty">
            <div class="bg-gray-50 rounded-2xl p-4 mb-4 border border-gray-100">
              <div class="flex justify-between text-[13px] text-gray-500 mb-2">
                <span>Subtotal ({{ cart.count }} items)</span>
                <span class="font-semibold">S/ {{ cart.total.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center
                          pt-2.5 border-t border-gray-200">
                <span class="font-bold text-[15px] text-gray-900">Total</span>
                <div class="flex items-baseline gap-1">
                  <span class="text-[12px] font-semibold text-gray-400">S/</span>
                  <span class="font-black text-[28px] text-brand-red leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ cart.total.toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>

            <RouterLink to="/checkout" @click="isOpen = false" class="flex items-center justify-between w-full py-4 px-5
                     rounded-2xl no-underline bg-brand-red text-white
                     font-black text-[15px]
                     shadow-[0_6px_24px_rgba(196,30,30,0.35)]
                     hover:bg-red-700 hover:-translate-y-0.5
                     active:scale-[0.98] transition-all duration-200
                     uppercase tracking-wide" style="font-family:'Plus Jakarta Sans',sans-serif;">
              <span>Confirmar pedido</span>
              <span class="bg-white/20 px-3 py-1.5 rounded-xl text-[14px] font-black">
                S/ {{ cart.total.toFixed(2) }}
              </span>
            </RouterLink>
          </template>

          <template v-else>
            <button @click="isOpen = false" class="w-full py-3.5 rounded-2xl border-2 border-gray-200
                     text-gray-500 font-semibold cursor-pointer bg-transparent
                     hover:border-brand-red hover:text-brand-red
                     transition-all duration-150 uppercase tracking-wide
                     text-[13.5px]">
              Ver menú →
            </button>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useProductsStore } from '@/stores/products'
import type { CartItem } from '@/stores/cart'
import { XMarkIcon, TrashIcon, PencilIcon } from '@heroicons/vue/24/outline'

const cart = useCartStore()
const productsStore = useProductsStore()
const isOpen = inject<any>('cartOpen')
const customizer = inject<any>('customizer')

// Busca el producto en el store y abre el modal en modo edición
function editItem(item: CartItem) {
  const product = productsStore.products.find(p => p.id === item.productId)
  if (!product) return
  isOpen.value = false // cierra el drawer primero
  setTimeout(() => {
    customizer.openEdit(product, item)
  }, 200) // espera que cierre el drawer
}

function getSectionEmoji(seccion: string): string {
  const m: Record<string, string> = {
    salsas: '🫙',
    ensalada: '🥗',
    papas: '🍟',
    termino: '🔥',
  }
  return m[seccion] ?? '•'
}
</script>

<style scoped>
.item-enter-active {
  transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.item-leave-active {
  transition: all 0.15s ease;
}

.item-enter-from {
  opacity: 0;
  transform: translateX(-12px) scale(0.96);
}

.item-leave-to {
  opacity: 0;
  transform: translateX(8px);
}
</style>