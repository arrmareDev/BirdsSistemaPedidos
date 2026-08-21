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
              <Banknote :size="26" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Cobrar pedido #{{ order?.codigo }}
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-5 leading-relaxed">
              Selecciona el método de pago para completar el pedido de
              <strong class="text-gray-700">{{ order?.client_name }}</strong>.
            </p>

            <div class="grid grid-cols-3 gap-2 mb-6">
              <button v-for="mp in METODOS_PAGO_LOCAL" :key="mp.id" @click="$emit('update:metodoPago', mp.id)" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl border-2
                       text-[12px] font-bold cursor-pointer transition-all duration-150" :class="metodoPago === mp.id
                        ? 'border-brand-red bg-red-50 text-brand-red shadow-sm'
                        : 'border-gray-100 bg-gray-50 text-gray-500 hover:border-red-200'">
                <AppIcon :name="mp.icon" :size="20" />
                {{ mp.label }}
              </button>
            </div>

            <div class="flex justify-between items-center px-1 mb-6">
              <span class="text-[13px] text-gray-500 font-medium">Total a cobrar</span>
              <span class="font-black text-[20px] text-brand-red leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                S/ {{ (order?.total ?? 0).toFixed(2) }}
              </span>
            </div>

            <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">
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
              <button @click="$emit('confirm')" :disabled="!metodoPago || loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                       bg-green-600 hover:bg-green-700 disabled:opacity-50 transition-all duration-150
                       flex items-center justify-center gap-2">
                <span v-if="loading"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ loading ? 'Cobrando...' : 'Confirmar cobro' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { Banknote } from 'lucide-vue-next'
import AppIcon from '@/components/AppIcon.vue'
import type { AdminOrder } from '@/stores/orders'

const METODOS_PAGO_LOCAL = [
  { id: 'efectivo', icon: 'banknote', label: 'Efectivo' },
  { id: 'yape', icon: 'smartphone', label: 'Yape/Plin' },
  { id: 'tarjeta', icon: 'credit-card', label: 'Tarjeta' },
]

defineProps<{
  show: boolean
  order: AdminOrder | null
  loading: boolean
  error: string
  metodoPago: string
}>()

defineEmits<{
  close: []
  confirm: []
  'update:metodoPago': [value: string]
}>()
</script>
