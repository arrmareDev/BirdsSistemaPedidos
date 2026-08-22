<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6">
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-100
                          flex items-center justify-center">
                <CheckCircleIcon class="w-5 h-5 text-purple-600" />
              </div>
              <h3 class="font-black text-[17px] text-gray-900 m-0"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ editingSeccionTipo ? 'Editar tipo de sección' : 'Nuevo tipo de sección' }}
              </h3>
            </div>
            <button @click="$emit('close')" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
                     justify-center cursor-pointer border-none hover:bg-gray-200 transition-colors">
              <XMarkIcon class="w-4 h-4 text-gray-500" />
            </button>
          </div>

          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-[1fr_auto] gap-3">
              <div class="flex flex-col gap-1.5">
                <label class="field-label">Nombre *</label>
                <input v-model="seccionTipoForm.nombre" placeholder="Ej: Término de cocción"
                  class="modal-input w-full" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="field-label">Ícono</label>
                <div class="flex items-center gap-2">
                  <div
                    class="w-11 h-11 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0 text-gray-500">
                    <AppIcon :name="seccionTipoForm.icono" :size="20" />
                  </div>
                  <input v-model="seccionTipoForm.icono" placeholder="sparkles"
                    class="modal-input w-24 text-center text-[12px]" />
                </div>
              </div>
            </div>

            <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">
              <div v-if="seccionTipoError" class="flex items-center gap-2.5 px-4 py-3 rounded-2xl
                       bg-red-50 border border-red-200">
                <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                <p class="text-[12.5px] text-red-700 m-0">{{ seccionTipoError }}</p>
              </div>
            </Transition>
          </div>

          <div class="flex gap-3 mt-5">
            <button @click="$emit('close')" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                     font-semibold text-[13.5px] cursor-pointer bg-white
                     hover:border-gray-300 transition-all duration-150">
              Cancelar
            </button>
            <button @click="$emit('save')" :disabled="savingSeccionTipo" class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold
                     text-[13.5px] cursor-pointer border-none hover:bg-red-700
                     transition-all duration-150 disabled:opacity-50
                     flex items-center justify-center gap-2">
              <span v-if="savingSeccionTipo" class="w-4 h-4 border-2 border-white/30 border-t-white
                       rounded-full animate-spin" />
              <CheckCircleIcon v-else class="w-4 h-4" />
              {{ savingSeccionTipo ? 'Guardando...' : (editingSeccionTipo ? 'Guardar' : 'Crear') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { CheckCircleIcon, XMarkIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import AppIcon from '@/components/AppIcon.vue'
import type { SeccionTipo, SeccionTipoForm } from '@/types/product-form'

defineProps<{
  show: boolean
  editingSeccionTipo: SeccionTipo | null
  seccionTipoForm: SeccionTipoForm
  seccionTipoError: string
  savingSeccionTipo: boolean
}>()

defineEmits<{
  close: []
  save: []
}>()
</script>
