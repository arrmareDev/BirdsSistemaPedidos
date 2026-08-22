<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="$emit('close')">
        <Transition appear enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
          leave-active-class="transition-all duration-150" leave-to-class="opacity-0 scale-95">
          <div v-if="show" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh]
                   flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 shrink-0">
              <div>
                <h2 class="font-black text-[17px] text-gray-900 m-0">Extras compartidos</h2>
                <p class="text-[12px] text-gray-400 m-0 mt-0.5">Crea, edita o elimina extras reutilizables</p>
              </div>
              <button @click="$emit('close')" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400
                       cursor-pointer border-none bg-gray-50 hover:bg-gray-100 transition-all">
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>

            <!-- Nuevo extra -->
            <div class="px-6 py-4 border-b border-gray-100 shrink-0 bg-gray-50/50">
              <div class="grid grid-cols-[1fr_110px_auto] gap-2 items-end">
                <div>
                  <label class="field-label">Nombre</label>
                  <input v-model="newExtra.name" placeholder="Ej: Leche de almendra" class="modal-input w-full" />
                </div>
                <div>
                  <label class="field-label">Precio</label>
                  <input v-model.number="newExtra.price" type="number" step="0.5" placeholder="0.00"
                    class="modal-input w-full" />
                </div>
                <button @click="$emit('createExtra')" :disabled="!newExtra.name.trim() || savingExtra" class="h-[42px] px-4 rounded-xl bg-brand-red text-white font-bold text-[13px]
                         cursor-pointer border-none hover:bg-red-700 transition-all
                         disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5">
                  <PlusIcon class="w-4 h-4" />
                  Crear
                </button>
              </div>
              <p v-if="extrasError" class="text-[11.5px] text-red-600 mt-2 m-0">{{ extrasError }}</p>
            </div>

            <!-- Lista editable -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
              <div v-if="availableExtras.length === 0" class="text-center py-10 text-[13px] text-gray-400">
                Aún no has creado ningún extra compartido.
              </div>
              <div v-else class="flex flex-col gap-2.5">
                <div v-for="extra in availableExtras" :key="extra.id" class="grid grid-cols-[1fr_110px_auto] gap-2 items-center
                         px-3 py-2.5 rounded-xl border border-gray-100 bg-white">
                  <input v-model="extra.name" class="modal-input w-full py-1.5" />
                  <input v-model.number="extra.price" type="number" step="0.5" class="modal-input w-full py-1.5" />
                  <div class="flex items-center gap-1.5">
                    <button @click="$emit('saveExtra', extra)" title="Guardar cambios" class="w-8 h-8 rounded-lg flex items-center justify-center text-green-600
                             cursor-pointer border-none bg-green-50 hover:bg-green-100 transition-all shrink-0">
                      <CheckIcon class="w-4 h-4" />
                    </button>
                    <button @click="$emit('deleteExtra', extra.id)" title="Eliminar" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500
                             cursor-pointer border-none bg-red-50 hover:bg-red-100 transition-all shrink-0">
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { XMarkIcon, PlusIcon, CheckIcon, TrashIcon } from '@heroicons/vue/24/outline'
import type { AvailableExtra } from '@/types/product-form'

defineProps<{
  show: boolean
  availableExtras: AvailableExtra[]
  newExtra: { name: string; price: number }
  extrasError: string
  savingExtra: boolean
}>()

defineEmits<{
  close: []
  createExtra: []
  saveExtra: [extra: AvailableExtra]
  deleteExtra: [id: number]
}>()
</script>
