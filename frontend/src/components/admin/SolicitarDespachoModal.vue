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

            <!-- Verificando si ya hay un despacho activo -->
            <div v-if="checkingEstado" class="flex items-center justify-center gap-2 py-6 text-gray-400 text-[13px]">
              <span class="w-4 h-4 border-2 border-gray-300 border-t-gray-500 rounded-full animate-spin" />
              Verificando...
            </div>

            <!-- Ya hay un despacho activo — no se puede volver a solicitar -->
            <div v-else-if="estadoActual" class="mb-4">
              <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 text-left">
                <p class="text-[12.5px] font-bold text-amber-800 m-0 mb-1">
                  Ya hay un despacho solicitado para este pedido
                </p>
                <p class="text-[12px] text-amber-700 m-0">
                  Estado: {{ estadoActual.estado ?? estadoActual.status ?? 'en curso' }}
                </p>
              </div>
              <Transition enter-active-class="transition-all duration-150"
                enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                <div v-if="error" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                         text-[12px] text-red-600 mb-4 text-left">
                  {{ error }}
                </div>
              </Transition>
              <div class="flex gap-3">
                <button @click="$emit('close')"
                  class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                  Cerrar
                </button>
                <button @click="$emit('cancelarDespacho')" :disabled="loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                         bg-red-600 hover:bg-red-700 disabled:opacity-50 transition-all duration-150
                         flex items-center justify-center gap-2">
                  <span v-if="loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  {{ loading ? 'Cancelando...' : 'Cancelar despacho' }}
                </button>
              </div>
            </div>

            <!-- Sin despacho activo — formulario normal -->
            <template v-else>
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
              <Transition enter-active-class="transition-all duration-150"
                enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                <div v-if="error" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                         text-[12px] text-red-600 mb-4 text-left">
                  {{ error }}
                </div>
              </Transition>
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
            </template>
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
  error: string
  checkingEstado: boolean
  // Shape real de DeliveryCentral no verificable desde este repo (es un
  // servicio externo) — se lee de forma defensiva (?? fallback) en vez
  // de asumir un contrato estricto que no podemos confirmar.
  estadoActual: Record<string, any> | null
}>()

defineEmits<{
  close: []
  confirm: []
  cancelarDespacho: []
}>()
</script>
