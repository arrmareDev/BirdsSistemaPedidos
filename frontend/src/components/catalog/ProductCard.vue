<template>
  <article class="group relative rounded-[1.75rem] overflow-hidden cursor-pointer
           transition-all duration-300 outline-none select-none" :style="[
            !product.available
              ? 'opacity: 0.55; cursor: not-allowed;'
              : 'background: #EDE8E0; box-shadow: 8px 8px 16px #C8C3BB, -8px -8px 16px #F8F4EF;',
          ]" :tabindex="product.available ? 0 : -1" role="button" @click="product.available && emit('add', product)"
    @keydown.enter="product.available && emit('add', product)" @mousedown="isPressed = true"
    @mouseup="isPressed = false" @mouseleave="isPressed = false">
    <!-- Imagen del producto -->
    <div class="relative overflow-hidden"
      :class="layout === 'grid' ? 'h-44 sm:h-48' : 'w-28 h-full min-h-[120px] shrink-0'">

      <!-- Imagen real -->
      <img v-if="product.imageUrl && !imgError" :src="product.imageUrl" :alt="product.name" class="w-full h-full object-cover transition-transform duration-500
               group-hover:scale-105" @error="imgError = true" />

      <!-- Placeholder neumórfico con emoji -->
      <div v-else class="w-full h-full flex items-center justify-center"
        style="background: linear-gradient(145deg, #F8F4EF, #E5E0D8);">
        <span class="text-5xl transition-transform duration-300
                     group-hover:scale-110 group-hover:rotate-3"
          :class="layout === 'list' ? 'text-4xl' : 'text-5xl sm:text-6xl'">
          {{ product.emoji || '💐' }}
        </span>
      </div>

      <!-- Overlay suave en hover -->
      <div class="absolute inset-0 bg-fire/0 group-hover:bg-fire/5
                  transition-all duration-300" />

      <!-- Badge popular -->
      <div v-if="product.popular" class="absolute top-3 left-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full
               text-[10px] font-black uppercase tracking-wide text-white" style="background: linear-gradient(135deg, #E8521A, #C03E0D);
               box-shadow: 2px 2px 8px rgba(232,82,26,0.4);">
        ⭐ Popular
      </div>

      <!-- Badge nuevo -->
      <div v-if="product.isNew" class="absolute top-3 right-3 px-2.5 py-1 rounded-full
               text-[10px] font-black uppercase tracking-wide" style="background: #EDE8E0; color: #E8521A;
               box-shadow: 2px 2px 6px #C8C3BB, -2px -2px 6px #F8F4EF;">
        ✨ Nuevo
      </div>

      <!-- Agotado overlay -->
      <div v-if="!product.available" class="absolute inset-0 flex items-center justify-center"
        style="background: rgba(237,232,224,0.75); backdrop-filter: blur(2px);">
        <span class="px-3 py-1.5 rounded-full text-[11px] font-black uppercase
                     tracking-wider text-ink-mid"
          style="box-shadow: inset 2px 2px 5px #C8C3BB, inset -2px -2px 5px #F8F4EF;">
          Agotado
        </span>
      </div>
    </div>

    <!-- Info del producto -->
    <div :class="layout === 'grid' ? 'p-4' : 'flex-1 flex items-center p-3 pl-4'">
      <div :class="layout === 'list' ? 'flex-1 min-w-0 mr-3' : ''">
        <h3 class="font-display font-black text-ink leading-tight m-0"
          :class="layout === 'grid' ? 'text-[16px] mb-1' : 'text-[14.5px]'">
          {{ product.name }}
        </h3>
        <p v-if="layout === 'grid'" class="text-[12.5px] text-ink-muted leading-snug m-0 mb-3 line-clamp-2">
          {{ product.description }}
        </p>
        <p v-else class="text-[11.5px] text-ink-muted leading-snug m-0 line-clamp-1">
          {{ product.description }}
        </p>
      </div>

      <div :class="layout === 'grid'
        ? 'flex items-center justify-between'
        : 'flex flex-col items-end gap-2 shrink-0'">

        <!-- Precio -->
        <div class="font-display font-black text-ink leading-none"
          :class="layout === 'grid' ? 'text-[20px]' : 'text-[18px]'">
          <sup class="font-body font-semibold text-ink-muted text-[11px] align-super mr-0.5">
            S/
          </sup>{{ product.price.toFixed(2) }}
        </div>

        <!-- Botón agregar neumórfico -->
        <button v-if="product.available" @click.stop="emit('add', product)" class="flex items-center justify-center rounded-full border-none cursor-pointer
                 text-white font-black text-xl leading-none transition-all duration-150
                 active:scale-95" :class="layout === 'grid' ? 'w-10 h-10' : 'w-9 h-9'"
          :style="isPressed
            ? 'background: #C03E0D; box-shadow: inset 2px 2px 5px rgba(0,0,0,0.3);'
            : 'background: linear-gradient(135deg, #FF6B35, #C03E0D); box-shadow: 4px 4px 10px rgba(232,82,26,0.4), -2px -2px 6px rgba(255,107,53,0.2);'"
          :aria-label="'Agregar ' + product.name">+</button>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { Product } from '@/types'

const props = withDefaults(defineProps<{
  product: Product
  layout?: 'grid' | 'list'
}>(), {
  layout: 'grid',
})

const emit = defineEmits<{ add: [product: Product] }>()
const imgError = ref(false)
const isPressed = ref(false)
</script>