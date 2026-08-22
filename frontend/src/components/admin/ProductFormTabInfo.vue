<template>
  <div class="flex flex-col gap-4">
    <div class="grid grid-cols-[1fr_auto] gap-3">
      <div class="flex flex-col gap-1.5">
        <label class="field-label">Nombre *</label>
        <input v-model="form.name" placeholder="Ej: Ramo de 12 Rosas Rojas" class="modal-input w-full" />
      </div>
      <div class="flex flex-col gap-1.5">
        <label class="field-label">Ícono</label>
        <div class="flex items-center gap-1.5">
          <div
            class="w-9 h-9 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0 text-gray-500">
            <AppIcon :name="form.icon" :size="16" />
          </div>
          <input v-model="form.icon" placeholder="flower-2"
            class="modal-input w-24 text-center text-[11px]" />
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="field-label">Descripción</label>
      <textarea v-model="form.description" placeholder="Breve descripción..." rows="2"
        class="modal-input w-full resize-none" />
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="field-label">Categoría *</label>
      <select v-model="form.category_id" class="modal-input w-full">
        <option value="">Seleccionar categoría...</option>
        <option v-for="cat in categoryOptionsTree" :key="cat.id" :value="cat.id">
          {{ cat.parent_id ? '— ' : '' }}{{ cat.name }}
        </option>
      </select>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="field-label">Precio (S/) *</label>
      <input v-model.number="form.price" type="number" step="0.50" min="0" placeholder="0.00"
        class="modal-input w-full font-bold" />
    </div>

    <div class="flex flex-col gap-2 p-4 rounded-2xl bg-gray-50 border border-gray-200">
      <button type="button" @click="form.tieneDescuento = !form.tieneDescuento"
        class="flex items-center justify-between cursor-pointer border-none bg-transparent p-0 w-full text-left">
        <div class="flex items-center gap-2">
          <TagIcon class="w-4 h-4 text-gray-500" />
          <span class="text-[13px] font-semibold text-gray-700">Producto en promoción</span>
        </div>
        <div class="w-10 h-6 rounded-full transition-colors duration-200 relative shrink-0"
          :class="form.tieneDescuento ? 'bg-brand-red' : 'bg-gray-300'">
          <div
            class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform duration-200 shadow-sm"
            :class="form.tieneDescuento ? 'translate-x-[18px]' : 'translate-x-0.5'" />
        </div>
      </button>
      <Transition enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
        <div v-if="form.tieneDescuento" class="flex flex-col gap-3 pt-1">

          <div class="flex gap-2">
            <button type="button" @click="form.descuento_tipo = 'porcentaje'"
              class="flex-1 py-2 rounded-xl text-[12.5px] font-bold border-2 cursor-pointer transition-all duration-150"
              :class="form.descuento_tipo === 'porcentaje'
                ? 'border-brand-red bg-red-50 text-brand-red'
                : 'border-gray-200 bg-white text-gray-500'">
              % Porcentaje
            </button>
            <button type="button" @click="form.descuento_tipo = 'monto_fijo'"
              class="flex-1 py-2 rounded-xl text-[12.5px] font-bold border-2 cursor-pointer transition-all duration-150"
              :class="form.descuento_tipo === 'monto_fijo'
                ? 'border-brand-red bg-red-50 text-brand-red'
                : 'border-gray-200 bg-white text-gray-500'">
              S/ Monto fijo
            </button>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="field-label">
              {{ form.descuento_tipo === 'monto_fijo' ? 'Descuento (S/)' : 'Descuento (%)' }}
            </label>
            <input v-model.number="form.descuento_valor" type="number" min="0"
              :max="form.descuento_tipo === 'porcentaje' ? 100 : undefined" step="0.50" placeholder="0"
              class="modal-input font-bold w-32" />
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div class="flex flex-col gap-1.5">
              <label class="field-label">Desde (opcional)</label>
              <input v-model="form.descuento_desde" type="date" class="modal-input" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="field-label">Hasta (opcional)</label>
              <input v-model="form.descuento_hasta" type="date" class="modal-input" />
            </div>
          </div>
          <p class="text-[11px] text-gray-400 m-0">
            Sin fechas, la promoción queda activa hasta que la apagues a mano.
            {{ previewPrecioFinal }}
          </p>
        </div>
      </Transition>
    </div>

    <div class="flex flex-col gap-2 p-4 rounded-2xl bg-gray-50 border border-gray-200">
      <button type="button" @click="form.controla_stock = !form.controla_stock"
        class="flex items-center justify-between cursor-pointer border-none bg-transparent p-0 w-full text-left">
        <div class="flex items-center gap-2">
          <ArchiveBoxIcon class="w-4 h-4 text-gray-500" />
          <span class="text-[13px] font-semibold text-gray-700">Controlar inventario</span>
        </div>
        <div class="w-10 h-6 rounded-full transition-colors duration-200 relative shrink-0"
          :class="form.controla_stock ? 'bg-brand-red' : 'bg-gray-300'">
          <div
            class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform duration-200 shadow-sm"
            :class="form.controla_stock ? 'translate-x-[18px]' : 'translate-x-0.5'" />
        </div>
      </button>
      <Transition enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
        <div v-if="form.controla_stock" class="flex flex-col gap-3 pt-1">
          <div class="flex flex-col gap-1.5">
            <label class="field-label">Stock disponible</label>
            <input v-model.number="form.stock" type="number" min="0" step="1" placeholder="0"
              class="modal-input font-bold w-32" />
            <p class="text-[11px] text-gray-400 m-0">
              Cuando llegue a 0, el producto se mostrará como agotado.
            </p>
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="field-label">Avisarme cuando quede (opcional)</label>
            <input v-model.number="form.stock_minimo" type="number" min="0" step="1" placeholder="Ej: 3"
              class="modal-input font-bold w-32" />
            <p class="text-[11px] text-gray-400 m-0">
              Aparece marcado como "stock bajo" en Inventario a partir de este número.
            </p>
          </div>
        </div>
      </Transition>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="field-label">Imagen</label>
      <label class="flex items-center gap-3 px-4 py-3 rounded-2xl border-2 border-dashed
                    border-gray-200 cursor-pointer hover:border-red-300 hover:bg-red-50/20
                    transition-all duration-150">
        <PhotoIcon class="w-5 h-5 text-gray-400 shrink-0" />
        <span class="text-[13px] text-gray-500">
          {{ imageFile ? imageFile.name : 'Seleccionar imagen...' }}
        </span>
        <input type="file" accept="image/*" class="hidden" @change="$emit('imageChange', $event)" />
      </label>
      <img v-if="imagePreview || editingProduct?.image_url"
        :src="imagePreview ?? editingProduct?.image_url ?? ''"
        class="h-24 rounded-2xl object-cover border border-gray-100" />
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="field-label">Galería de fotos (opcional)</label>
      <p v-if="!editingProduct" class="text-[11.5px] text-gray-400 m-0">
        Guarda el producto primero para poder agregarle fotos a la galería
      </p>
      <div v-else class="flex flex-wrap gap-2">
        <div v-for="img in galleryImages" :key="img.id"
          class="relative w-16 h-16 rounded-xl overflow-hidden border border-gray-200 group shrink-0">
          <img :src="img.image_url" class="w-full h-full object-cover" />
          <button @click="$emit('deleteGalleryImage', img.id)" type="button" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
                   flex items-center justify-center border-none cursor-pointer
                   transition-opacity duration-150">
            <TrashIcon class="w-4 h-4 text-white" />
          </button>
        </div>
        <label class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-300 shrink-0
                 flex items-center justify-center cursor-pointer
                 hover:border-brand-red/40 transition-all duration-150 relative">
          <span v-if="uploadingGallery"
            class="w-4 h-4 border-2 border-gray-300 border-t-brand-red rounded-full animate-spin" />
          <PlusIcon v-else class="w-5 h-5 text-gray-400" />
          <input type="file" accept="image/*" multiple class="hidden" @change="$emit('galleryFilesSelected', $event)" />
        </label>
      </div>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="field-label">Etiquetas</label>
      <div class="grid grid-cols-2 gap-2">
        <button type="button" @click="form.available = !form.available" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border-2 cursor-pointer
                 text-[13px] font-semibold transition-all duration-150" :class="form.available
                  ? 'border-green-400 bg-green-50 text-green-700'
                  : 'border-gray-200 bg-gray-50 text-gray-400'">
          <div class="w-4 h-4 rounded-full flex items-center justify-center"
            :class="form.available ? 'bg-green-500' : 'bg-gray-300'">
            <CheckIcon v-if="form.available" class="w-2.5 h-2.5 text-white" />
          </div>
          Disponible
        </button>
        <button type="button" @click="form.popular = !form.popular" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border-2 cursor-pointer
                 text-[13px] font-semibold transition-all duration-150" :class="form.popular
                  ? 'border-yellow-400 bg-yellow-50 text-yellow-700'
                  : 'border-gray-200 bg-gray-50 text-gray-400'">
          <Star :size="14" :fill="form.popular ? 'currentColor' : 'none'" />
          Popular
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { TagIcon, ArchiveBoxIcon, PhotoIcon, PlusIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { TrashIcon } from '@heroicons/vue/24/outline'
import { Star } from 'lucide-vue-next'
import AppIcon from '@/components/AppIcon.vue'
import type { Product, Category } from '@/stores/products'
import type { ProductForm, GalleryImage } from '@/types/product-form'

defineProps<{
  form: ProductForm
  categoryOptionsTree: Category[]
  editingProduct: Product | null
  imageFile: File | null
  imagePreview: string | null
  galleryImages: GalleryImage[]
  uploadingGallery: boolean
  previewPrecioFinal: string
}>()

defineEmits<{
  imageChange: [event: Event]
  galleryFilesSelected: [event: Event]
  deleteGalleryImage: [id: number]
}>()
</script>
