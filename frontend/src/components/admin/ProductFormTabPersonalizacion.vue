<template>
  <div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
      <p class="text-[13px] text-gray-500 m-0">Preferencias del cliente — sin costo adicional</p>
      <button @click="$emit('update:showAddSection', !showAddSection)" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-red text-white
               font-bold text-[12.5px] border-none cursor-pointer hover:bg-red-700
               transition-all duration-150">
        <PlusIcon class="w-3.5 h-3.5" />
        Agregar
      </button>
    </div>

    <Transition enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 -translate-y-2" leave-to-class="opacity-0">
      <div v-if="showAddSection"
        class="grid grid-cols-2 gap-2 p-4 rounded-2xl bg-gray-50 border border-gray-200">
        <button v-for="tipo in seccionTiposActivos" :key="tipo.id" @click="$emit('addSection', tipo)"
          :disabled="form.sections.some(s => s.seccion === tipo.nombre)" class="flex items-center gap-2.5 px-3.5 py-3 rounded-xl border-2 cursor-pointer
                 text-[13px] font-semibold transition-all duration-150"
          :class="form.sections.some(s => s.seccion === tipo.nombre)
            ? 'border-gray-100 bg-gray-100 text-gray-400 cursor-not-allowed'
            : 'border-gray-200 bg-white text-gray-700 hover:border-brand-red hover:text-brand-red'">
          <AppIcon :name="tipo.icono" :size="18" />
          {{ tipo.nombre }}
          <CheckIcon v-if="form.sections.some(s => s.seccion === tipo.nombre)"
            class="w-3.5 h-3.5 ml-auto text-gray-400" />
        </button>
        <p v-if="seccionTiposActivos.length === 0"
          class="col-span-2 text-[12.5px] text-gray-400 text-center py-2 m-0">
          No hay tipos de sección activos — créalos en el panel de arriba
        </p>
      </div>
    </Transition>

    <div v-if="form.sections.length === 0"
      class="flex items-center gap-2 px-4 py-3.5 rounded-2xl bg-gray-50 border border-dashed border-gray-200">
      <p class="text-[13px] text-gray-400 m-0">Sin secciones de personalización</p>
    </div>

    <div v-for="(section, si) in form.sections" :key="si"
      class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-white">
        <div class="flex items-center gap-2">
          <AppIcon :name="seccionTipos.find(t => t.nombre === section.seccion)?.icono ?? 'sparkles'"
            :size="18" />
          <div>
            <input v-model="section.label"
              class="font-bold text-[14px] text-gray-900 bg-transparent border-none outline-none p-0 w-full" />
            <div class="flex items-center gap-3 mt-0.5">
              <label class="flex items-center gap-1.5 text-[11px] text-gray-500 cursor-pointer">
                <input type="checkbox" v-model="section.required" />
                Requerido
              </label>
              <label class="flex items-center gap-1.5 text-[11px] text-gray-500 cursor-pointer">
                <input type="checkbox" v-model="section.multiple" />
                Múltiple
              </label>
            </div>
          </div>
        </div>
        <button @click="$emit('removeSection', si)" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                 cursor-pointer border-none bg-transparent hover:bg-red-50 hover:text-red-500
                 transition-all duration-150">
          <TrashIcon class="w-3.5 h-3.5" />
        </button>
      </div>
      <div class="p-4 flex flex-col gap-2">
        <div v-for="(opt, oi) in section.options" :key="oi" class="flex items-center gap-2">
          <button v-if="opt.id" @click="$emit('openOptionImagePicker', opt)" type="button"
            class="w-9 h-9 rounded-lg overflow-hidden border border-gray-200 shrink-0 cursor-pointer
                   bg-white flex items-center justify-center text-gray-300 hover:border-brand-red/40 relative group" title="Subir/cambiar foto">
            <img v-if="opt.image_url" :src="opt.image_url" class="w-full h-full object-cover" />
            <PhotoIcon v-else class="w-4 h-4" />
            <span v-if="uploadingOptionId === opt.id"
              class="absolute inset-0 bg-white/70 flex items-center justify-center">
              <span
                class="w-3.5 h-3.5 border-2 border-gray-300 border-t-brand-red rounded-full animate-spin" />
            </span>
          </button>
          <div v-else class="w-9 h-9 rounded-lg border border-dashed border-gray-200 shrink-0
                   flex items-center justify-center text-gray-300"
            title="Guarda el producto primero para poder subirle foto">
            <PhotoIcon class="w-4 h-4" />
          </div>
          <input v-model="opt.name" placeholder="Ej: Grande" class="modal-input flex-1 py-2" />
          <input v-model.number="opt.price_modifier" type="number" step="0.5" placeholder="0.00"
            title="Modificador de precio (+/-)" class="modal-input w-24 py-2 text-right" />
          <button @click="$emit('removeOption', si, oi)" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                   cursor-pointer border-none bg-white hover:bg-red-50 hover:text-red-500
                   transition-all duration-150 shrink-0">
            <XMarkIcon class="w-3.5 h-3.5" />
          </button>
        </div>
        <button @click="$emit('addOption', si)" class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-dashed border-gray-300
                 bg-white text-[12px] font-semibold text-gray-500 cursor-pointer
                 hover:border-brand-red hover:text-brand-red transition-all duration-150 w-fit">
          <PlusIcon class="w-3.5 h-3.5" />
          Agregar opción
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PlusIcon, CheckIcon, TrashIcon, PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import AppIcon from '@/components/AppIcon.vue'
import type { ProductForm, SeccionTipo, FormOption } from '@/types/product-form'

defineProps<{
  form: ProductForm
  seccionTipos: SeccionTipo[]
  seccionTiposActivos: SeccionTipo[]
  showAddSection: boolean
  uploadingOptionId: number | null
}>()

defineEmits<{
  'update:showAddSection': [value: boolean]
  addSection: [tipo: SeccionTipo]
  removeSection: [index: number]
  addOption: [sectionIndex: number]
  removeOption: [sectionIndex: number, optionIndex: number]
  openOptionImagePicker: [opt: FormOption]
}>()
</script>
