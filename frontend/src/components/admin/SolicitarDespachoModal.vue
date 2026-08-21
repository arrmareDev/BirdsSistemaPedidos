<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="$emit('close')">
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
          leave-to-class="opacity-0 scale-95">
          <div v-if="show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 mx-auto mb-5 flex items-center justify-center">
              <TruckIcon class="w-7 h-7 text-blue-500" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Solicitar repartidor
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-5 leading-relaxed">
              Se notificará a los repartidores disponibles. El primero en aceptar tomará el pedido.
            </p>
            <div class="bg-gray-50 rounded-2xl p-4 mb-6 text-left border border-gray-100 flex flex-col gap-2">
              <div class="flex justify-between text-[13px]">
                <span class="text-gray-500">Pedido</span>
                <span class="font-bold text-gray-700">#{{ order?.codigo }}</span>
              </div>
              <div class="flex justify-between text-[13px]">
                <span class="text-gray-500">Cliente</span>
                <span class="font-bold text-gray-700">{{ order?.client_name }}</span>
              </div>
              <div v-if="order?.address" class="flex justify-between text-[13px]">
                <span class="text-gray-500">Dirección</span>
                <span class="font-bold text-gray-700 text-right max-w-[160px]">
                  {{ order.address }}
                </span>
              </div>
              <div v-if="order?.entrega_programada && order?.fecha_entrega"
                class="flex justify-between text-[13px]">
                <span class="text-gray-500">Entrega</span>
                <span class="font-bold text-pink-700 inline-flex items-center gap-1">
                  <Calendar :size="12" /> {{ formatDate(order.fecha_entrega) }}
                </span>
              </div>
              <div v-if="order?.metodo_pago" class="flex justify-between text-[13px]">
                <span class="text-gray-500">Pago</span>
                <span :class="metodoPagoCls(order.metodo_pago)"
                  class="font-bold px-2 py-0.5 rounded-full text-[11px] border">
                  {{ metodoPagoLabel(order.metodo_pago) }}
                </span>
              </div>
              <div class="flex justify-between text-[13px] pt-2 border-t border-gray-200">
                <span class="text-gray-500 font-semibold">Total</span>
                <span class="font-black text-brand-red">
                  S/ {{ (order?.total ?? 0).toFixed(2) }}
                </span>
              </div>
            </div>
            <div class="flex gap-3">
              <button @click="$emit('close')"
                class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="$emit('confirm')" :disabled="loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                       bg-blue-600 hover:bg-blue-700 disabled:opacity-50 transition-all duration-150
                       flex items-center justify-center gap-2">
                <span v-if="loading"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ loading ? 'Solicitando...' : 'Solicitar' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { TruckIcon } from '@heroicons/vue/24/outline'
import { Calendar } from 'lucide-vue-next'
import type { AdminOrder } from '@/stores/orders'
import { formatDate, metodoPagoCls, metodoPagoLabel } from '@/utils/orderFormatting'

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
