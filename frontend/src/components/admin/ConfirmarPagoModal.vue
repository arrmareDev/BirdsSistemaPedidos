<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
           flex items-center justify-center p-4" @click.self="$emit('close')">
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          leave-to-class="opacity-0 scale-95">
          <div v-if="show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
            <div class="w-14 h-14 rounded-2xl bg-green-50 mx-auto mb-5 flex items-center justify-center text-green-500">
              <CheckCircle :size="26" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Confirmar pedido #{{ order?.codigo }}
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
              Al confirmar, se entiende que <strong class="text-gray-700">{{ order?.client_name }}</strong>
              ya realizó el pago (Yape/transferencia coordinado por WhatsApp). El pedido pasará a preparación.
            </p>

            <div class="flex gap-3">
              <button @click="$emit('close')"
                class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="$emit('confirm')" :disabled="loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                       bg-green-600 hover:bg-green-700 disabled:opacity-50 transition-all duration-150
                       flex items-center justify-center gap-2">
                <span v-if="loading"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ loading ? 'Confirmando...' : 'Ya pagó, confirmar' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { CheckCircle } from 'lucide-vue-next'
import type { AdminOrder } from '@/stores/orders'

defineProps<{
  show: boolean
  order: AdminOrder | null
  loading: boolean
}>()

defineEmits<{
  close: []
  confirm: []
}>()
</script>
