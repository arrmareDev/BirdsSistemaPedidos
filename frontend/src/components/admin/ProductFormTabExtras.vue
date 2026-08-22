<template>
  <div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <p class="text-[13px] text-gray-500 m-0">
        Productos adicionales que el cliente puede agregar con costo
      </p>
      <button @click="$emit('addExtra')" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-red text-white
               font-bold text-[12.5px] border-none cursor-pointer hover:bg-red-700
               transition-all duration-150">
        <PlusIcon class="w-3.5 h-3.5" />
        Agregar extra
      </button>
    </div>

    <div v-if="form.extras.length === 0"
      class="flex items-center gap-2 px-4 py-3.5 rounded-2xl bg-gray-50 border border-dashed border-gray-200">
      <p class="text-[13px] text-gray-400 m-0">Sin extras — ej: Peluche, Chocolates, Globo metálico</p>
    </div>

    <div v-for="(extra, i) in form.extras" :key="i"
      class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-100">
      <input v-model="extra.name" placeholder="Ej: Caja de chocolates"
        class="modal-input flex-1 font-semibold" />
      <div class="flex flex-col gap-0.5 shrink-0 w-32">
        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Precio S/</span>
        <input v-model.number="extra.price" type="number" step="0.50" min="0" placeholder="0.00"
          class="modal-input font-bold py-2 w-full" />
      </div>
      <button @click="$emit('removeExtra', i)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400
               cursor-pointer border-none bg-white hover:bg-red-50 hover:text-red-500
               transition-all duration-150 shrink-0 mt-4">
        <TrashIcon class="w-4 h-4" />
      </button>
    </div>

    <p v-if="form.extras.length > 0" class="text-[11px] text-gray-400 m-0">
      El cliente verá estos extras en el modal y puede agregarlos al pedido.
    </p>

    <div class="flex flex-col gap-2 mt-2 pt-4 border-t border-gray-100">
      <div class="flex items-center justify-between">
        <p class="field-label m-0">Extras compartidos (reutilizables entre productos)</p>
        <button type="button" @click="$emit('openExtrasManager')" class="flex items-center gap-1.5 text-[11.5px] font-bold text-brand-red
                 cursor-pointer border-none bg-transparent hover:underline">
          <PencilSquareIcon class="w-3.5 h-3.5" />
          Gestionar
        </button>
      </div>
      <div v-if="availableExtras.length === 0" class="text-[12px] text-gray-400">
        Sin extras compartidos creados aún.
      </div>
      <div v-else class="grid grid-cols-2 gap-2">
        <label v-for="extra in availableExtras" :key="extra.id"
          class="flex items-center gap-2 px-3 py-2 rounded-xl border-2 cursor-pointer transition-all"
          :class="form.extra_ids.includes(extra.id) ? 'border-brand-red bg-red-50' : 'border-gray-200'">
          <input type="checkbox" :value="extra.id" v-model="form.extra_ids" class="accent-brand-red" />
          <span class="text-[12.5px] font-semibold text-gray-700 truncate">{{ extra.name }}</span>
          <span class="text-[11px] text-green-600 font-bold ml-auto shrink-0">
            +S/{{ extra.price.toFixed(2) }}
          </span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PlusIcon, TrashIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'
import type { ProductForm, AvailableExtra } from '@/types/product-form'

defineProps<{
  form: ProductForm
  availableExtras: AvailableExtra[]
}>()

defineEmits<{
  addExtra: []
  removeExtra: [index: number]
  openExtrasManager: []
}>()
</script>
