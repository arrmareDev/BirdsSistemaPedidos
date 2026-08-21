<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="$emit('close')">
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          leave-to-class="opacity-0 scale-95">
          <div v-if="show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
              :class="type === 'cancelar' ? 'bg-amber-50' : 'bg-red-50'">
              <component :is="type === 'cancelar' ? XCircleIcon : TrashIcon" class="w-7 h-7"
                :class="type === 'cancelar' ? 'text-amber-500' : 'text-red-500'" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ type === 'cancelar' ? '¿Cancelar pedido?'
                : type === 'forzar' ? '¿Eliminar definitivamente?'
                  : '¿Eliminar pedido?' }}
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
              <template v-if="type === 'cancelar'">
                El pedido <strong class="text-gray-700">#{{ order?.codigo }}</strong>
                de <strong class="text-gray-700">{{ order?.client_name }}</strong>
                será marcado como cancelado.
              </template>
              <template v-else-if="type === 'forzar'">
                El pedido <strong class="text-gray-700">#{{ order?.codigo }}</strong>
                se borrará <strong class="text-red-600">para siempre</strong> de la base de datos, junto con sus
                productos.
                Esta acción <strong class="text-red-600">no se puede deshacer</strong>.
              </template>
              <template v-else>
                El pedido <strong class="text-gray-700">#{{ order?.codigo }}</strong>
                se moverá a la papelera. Podrás restaurarlo después desde "Eliminados".
              </template>
            </p>
            <div class="flex gap-3">
              <button @click="$emit('close')"
                class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                No, volver
              </button>
              <button @click="$emit('confirm')" :disabled="loading"
                class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer
                       border-none transition-all duration-150 disabled:opacity-50 flex items-center justify-center gap-2"
                :class="type === 'cancelar' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-red-600 hover:bg-red-700'">
                <span v-if="loading"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ type === 'cancelar' ? 'Sí, cancelar'
                  : type === 'forzar' ? 'Sí, eliminar para siempre'
                    : 'Sí, eliminar' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { XCircleIcon, TrashIcon } from '@heroicons/vue/24/outline'
import type { AdminOrder } from '@/stores/orders'

defineProps<{
  show: boolean
  type: 'cancelar' | 'eliminar' | 'forzar'
  order: AdminOrder | null
  loading: boolean
}>()

defineEmits<{
  close: []
  confirm: []
}>()
</script>
