<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6">
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-red-50 border border-red-100
                          flex items-center justify-center">
                <FolderIcon class="w-5 h-5 text-brand-red" />
              </div>
              <h3 class="font-black text-[17px] text-gray-900 m-0"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ editingCat ? 'Editar categoría' : 'Nueva categoría' }}
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
                <input v-model="catForm.name" placeholder="Ej: Ramos" class="modal-input w-full" />
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="field-label">Ícono</label>
                <div class="flex items-center gap-2">
                  <div
                    class="w-11 h-11 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0 text-gray-500">
                    <AppIcon :name="catForm.icon" :size="20" />
                  </div>
                  <input v-model="catForm.icon" placeholder="flower-2"
                    class="modal-input w-24 text-center text-[12px]" />
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="field-label">Categoría padre</label>
              <select v-model="catForm.parent_id" class="modal-input w-full cursor-pointer">
                <option :value="null">Ninguna — es una categoría principal</option>
                <option v-for="root in rootCategories" :key="root.id" :value="root.id"
                  :disabled="editingCat?.id === root.id">
                  {{ root.name }}
                </option>
              </select>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="field-label">Orden</label>
              <input v-model.number="catForm.sort_order" type="number" min="0" step="1" placeholder="0"
                class="modal-input w-28 font-bold" />
            </div>

            <button type="button" @click="catForm.active = !catForm.active" class="flex items-center justify-between p-3.5 rounded-2xl
                     bg-gray-50 border border-gray-200 cursor-pointer">
              <div class="flex items-center gap-2">
                <CheckCircleIcon class="w-4 h-4 text-gray-500" />
                <span class="text-[13px] font-semibold text-gray-700">Categoría activa</span>
              </div>
              <div class="w-10 h-6 rounded-full transition-colors duration-200 relative shrink-0"
                :class="catForm.active ? 'bg-brand-red' : 'bg-gray-300'">
                <div class="w-5 h-5 rounded-full bg-white absolute top-0.5
                            transition-transform duration-200 shadow-sm"
                  :class="catForm.active ? 'translate-x-[18px]' : 'translate-x-0.5'" />
              </div>
            </button>

            <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">
              <div v-if="catError" class="flex items-center gap-2.5 px-4 py-3 rounded-2xl
                       bg-red-50 border border-red-200">
                <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                <p class="text-[12.5px] text-red-700 m-0">{{ catError }}</p>
              </div>
            </Transition>
          </div>

          <div class="flex gap-3 mt-5">
            <button @click="$emit('close')" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                     font-semibold text-[13.5px] cursor-pointer bg-white
                     hover:border-gray-300 transition-all duration-150">
              Cancelar
            </button>
            <button @click="$emit('save')" :disabled="savingCat" class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold
                     text-[13.5px] cursor-pointer border-none hover:bg-red-700
                     transition-all duration-150 disabled:opacity-50
                     flex items-center justify-center gap-2">
              <span v-if="savingCat" class="w-4 h-4 border-2 border-white/30 border-t-white
                       rounded-full animate-spin" />
              <CheckCircleIcon v-else class="w-4 h-4" />
              {{ savingCat ? 'Guardando...' : (editingCat ? 'Guardar' : 'Crear') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { FolderIcon, XMarkIcon, CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import AppIcon from '@/components/AppIcon.vue'
import type { Category } from '@/stores/products'
import type { CategoriaForm } from '@/types/product-form'

defineProps<{
  show: boolean
  editingCat: Category | null
  catForm: CategoriaForm
  catError: string
  savingCat: boolean
  rootCategories: Category[]
}>()

defineEmits<{
  close: []
  save: []
}>()
</script>
