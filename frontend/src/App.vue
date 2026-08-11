<template>
  <RouterView v-if="isAdminRoute" />

  <div v-else class="min-h-screen flex flex-col bg-white font-body">
    <AppHeader @open-cart="cartOpen = true" />

    <main class="flex-1 flex flex-col">
      <RouterView v-slot="{ Component }">
        <Transition enter-active-class="transition-opacity duration-200"
          leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0"
          mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>

    <AppFooter />

    <CartDrawer/>
    <CustomizerModal ref="customizerRef" />
  </div>
</template>

<script setup lang="ts">
import { ref, provide, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import CartDrawer from '@/components/cart/CartDrawer.vue'
import CustomizerModal from '@/components/catalog/CustomizerModal.vue'
import { useProductsStore } from '@/stores/products'
import { useBrandingStore } from '@/stores/branding'
import { usePedidoConfigStore } from '@/stores/pedidoconfig'
import type { Product } from '@/stores/products'
import type { CartItem } from '@/stores/cart'

const route = useRoute()
const productsStore = useProductsStore()
const brandingStore = useBrandingStore()
const pedidoConfigStore = usePedidoConfigStore()
const cartOpen = ref(false)
const customizerRef = ref<InstanceType<typeof CustomizerModal> | null>(null)

const isAdminRoute = computed(() => route.path.startsWith('/admin'))

const customizer = {
  open: (p: Product) => customizerRef.value?.open(p),
  openEdit: (p: Product, item: CartItem) => customizerRef.value?.openEdit(p, item),
  close: () => customizerRef.value?.close(),
}

provide('cartOpen', cartOpen)
provide('customizer', customizer)
provide('openCart', () => { cartOpen.value = true })
provide('closeCart', () => { cartOpen.value = false })

onMounted(() => {
  brandingStore.fetch()
  pedidoConfigStore.fetch()
  if (!isAdminRoute.value) productsStore.fetch()
})

watch(isAdminRoute, isAdmin => {
  if (!isAdmin) productsStore.fetch()
})

watch(() => route.path, () => {
  cartOpen.value = false
})
</script>