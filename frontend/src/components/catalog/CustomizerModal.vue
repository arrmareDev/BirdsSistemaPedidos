<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-250"
      leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="isOpen" class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm
               flex items-end sm:items-center justify-center sm:p-4" @click.self="close">

        <Transition enter-active-class="transition-all duration-300 ease-out"
          leave-active-class="transition-all duration-200"
          enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
          leave-to-class="translate-y-4 opacity-0">
          <div v-if="isOpen && product" class="w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-3xl
                   shadow-2xl flex flex-col overflow-hidden max-h-[92vh]">

            <!-- Imagen -->
            <div class="relative h-48 sm:h-56 bg-gray-50 shrink-0 overflow-hidden">
              <img v-if="displayImageUrl" :src="displayImageUrl" :alt="product.name"
                class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center
                       bg-gradient-to-br from-rose-50 via-pink-50 to-emerald-50 text-rose-400">
                <AppIcon :name="product.icon" :size="72" :stroke-width="1.5" />
              </div>

              <!-- Badge modo edición -->
              <div v-if="editingUid" class="absolute top-4 left-4 flex items-center gap-1.5
                       px-3 py-1.5 rounded-full bg-amber-500 text-white
                       text-[11px] font-black uppercase tracking-wide">
                <PencilIcon class="w-3 h-3" />
                Editando
              </div>

              <!-- Badge stock -->
              <div v-else-if="controlaStock" class="absolute top-4 left-4 flex items-center gap-1.5
                       px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wide" :class="agotado
                        ? 'bg-gray-900/80 text-white'
                        : stockBajo
                          ? 'bg-amber-500 text-white'
                          : 'bg-emerald-500 text-white'">
                <component :is="agotado ? XCircleIcon : ArchiveBoxIcon" class="w-3 h-3" />
                {{ agotado ? 'Agotado' : stockBajo ? `Quedan ${stockDisponible}` : 'Disponible' }}
              </div>

              <div class="absolute top-3 left-1/2 -translate-x-1/2
                          w-10 h-1 rounded-full bg-white/60 sm:hidden" />
              <button @click="close" class="absolute top-4 right-4 w-9 h-9 rounded-full
                       bg-black/30 backdrop-blur-sm flex items-center
                       justify-center border-none cursor-pointer
                       hover:bg-black/50 transition-colors">
                <XMarkIcon class="w-4 h-4 text-white" />
              </button>
            </div>

            <!-- Miniaturas (imagen principal + galería + variantes con foto) -->
            <div v-if="galleryThumbs.length > 1" class="flex gap-2 px-5 pt-3 shrink-0 overflow-x-auto">
              <button v-for="thumb in galleryThumbs" :key="thumb.key" @click="manualImage = thumb.url" class="w-11 h-11 rounded-xl overflow-hidden border-2 cursor-pointer shrink-0
                       transition-all duration-150" :class="displayImageUrl === thumb.url
                        ? 'border-brand-red' : 'border-gray-200 hover:border-gray-300'">
                <img :src="thumb.url" class="w-full h-full object-cover" />
              </button>
            </div>

            <!-- Info -->
            <div class="px-5 pt-5 pb-3 shrink-0">
              <h2 class="font-black text-[20px] text-gray-900 m-0 mb-1 leading-tight"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ product.name }}
              </h2>
              <p v-if="product.description" class="text-[13.5px] text-gray-400 m-0 leading-relaxed">
                {{ product.description }}
              </p>

              <div class="flex items-baseline gap-1 mt-3">
                <span class="text-[13px] font-semibold text-gray-400">S/</span>
                <span class="font-black text-[28px] text-brand-red leading-none"
                  style="font-family:'Plus Jakarta Sans',sans-serif;">
                  {{ totalPrice.toFixed(2) }}
                </span>
                <span v-if="extrasTotal > 0" class="text-[12px] text-green-600 font-semibold ml-1">
                  +S/{{ extrasTotal.toFixed(2) }} extras
                </span>
              </div>
            </div>

            <!-- Scroll -->
            <div class="flex-1 overflow-y-auto px-5 pb-3">

              <!-- Personalización -->
              <div v-if="product.customization_sections.length > 0">
                <div v-for="section in product.customization_sections" :key="section.id" class="mb-5">

                  <div class="flex items-center gap-2 mb-3">
                    <h3 class="font-black text-[14px] text-gray-900 m-0">
                      {{ section.label }}
                    </h3>
                    <span v-if="section.required" class="text-[10px] font-bold px-2 py-0.5 rounded-full
                             bg-red-50 text-brand-red border border-red-200">
                      Requerido
                    </span>
                    <span v-if="section.multiple" class="text-[10px] font-medium px-2 py-0.5 rounded-full
                             bg-gray-100 text-gray-500">
                      Varios
                    </span>
                  </div>

                  <!-- Múltiple → chips -->
                  <div v-if="section.multiple" class="flex flex-wrap gap-2">
                    <button v-for="opt in section.options" :key="opt.id" @click="toggleMultiple(section.id, opt)" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl
                             border-2 text-[13px] font-semibold cursor-pointer
                             transition-all duration-150" :class="isSelected(section.id, opt.id)
                              ? 'border-brand-red bg-red-50 text-brand-red'
                              : 'border-gray-200 bg-white text-gray-600 hover:border-red-200'">
                      <div class="w-4 h-4 rounded-md border-2 flex items-center
                                  justify-center shrink-0" :class="isSelected(section.id, opt.id)
                                    ? 'border-brand-red bg-brand-red'
                                    : 'border-gray-300'">
                        <CheckIcon v-if="isSelected(section.id, opt.id)" class="w-2.5 h-2.5 text-white" />
                      </div>
                      <img v-if="opt.image_url" :src="opt.image_url" class="w-5 h-5 rounded-md object-cover shrink-0" />
                      {{ opt.name }}
                    </button>
                  </div>

                  <!-- Único → radio -->
                  <div v-else class="flex flex-col gap-2">
                    <button v-for="opt in section.options" :key="opt.id" @click="selectSingle(section.id, opt)" class="flex items-center gap-3 px-4 py-3 rounded-2xl
                             border-2 cursor-pointer transition-all duration-150
                             text-left" :class="isSelected(section.id, opt.id)
                              ? 'border-brand-red bg-red-50'
                              : 'border-gray-100 bg-gray-50 hover:border-red-200'">
                      <div class="w-5 h-5 rounded-full border-2 flex items-center
                                  justify-center shrink-0" :class="isSelected(section.id, opt.id)
                                    ? 'border-brand-red bg-brand-red'
                                    : 'border-gray-300'">
                        <div v-if="isSelected(section.id, opt.id)" class="w-2 h-2 rounded-full bg-white" />
                      </div>
                      <img v-if="opt.image_url" :src="opt.image_url" class="w-9 h-9 rounded-lg object-cover shrink-0" />
                      <span class="font-medium text-[13.5px] text-gray-900">
                        {{ opt.name }}
                      </span>
                    </button>
                  </div>

                  <Transition enter-active-class="transition-all duration-150"
                    enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                    <p v-if="errors[section.id]" class="text-[11.5px] text-red-500 font-semibold mt-2 m-0
                             flex items-center gap-1">
                      <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0" />
                      {{ errors[section.id] }}
                    </p>
                  </Transition>
                </div>

                <div v-if="product.extras.length > 0" class="border-t border-gray-100 mb-5" />
              </div>

              <!-- Extras -->
              <div v-if="product.extras.length > 0" class="mb-3">
                <h3 class="font-black text-[14px] text-gray-900 m-0 mb-3">
                  ¿Deseas agregar algo más?
                  <span class="text-[11px] font-medium text-gray-400 ml-1">
                    opcional
                  </span>
                </h3>

                <div class="flex flex-col gap-2.5">
                  <div v-for="extra in product.extras" :key="extra.id" class="flex items-center justify-between px-4 py-3
                           rounded-2xl border-2 transition-all duration-150" :class="getExtraQty(extra.id) > 0
                            ? 'border-brand-red bg-red-50'
                            : 'border-gray-100 bg-gray-50'">

                    <div class="flex flex-col">
                      <span class="font-semibold text-[13.5px] text-gray-900">
                        {{ extra.name }}
                      </span>
                      <span class="text-[12px] font-bold text-green-600">
                        +S/ {{ extra.price.toFixed(2) }}
                      </span>
                    </div>

                    <div class="flex items-center gap-2">
                      <button v-if="getExtraQty(extra.id) > 0" @click="decrementExtra(extra.id)" class="w-8 h-8 rounded-xl flex items-center justify-center
                               border-2 border-brand-red text-brand-red
                               font-bold text-lg cursor-pointer bg-white
                               hover:bg-red-50 transition-all duration-150">
                        −
                      </button>
                      <span v-if="getExtraQty(extra.id) > 0" class="font-black text-[15px] text-gray-900
                               min-w-[20px] text-center">
                        {{ getExtraQty(extra.id) }}
                      </span>
                      <button @click="incrementExtra(extra)" class="w-8 h-8 rounded-xl flex items-center justify-center
                               border-2 font-bold text-lg cursor-pointer
                               transition-all duration-150"
                        :class="getExtraQty(extra.id) > 0
                          ? 'border-brand-red bg-brand-red text-white hover:bg-red-700'
                          : 'border-gray-300 text-gray-500 bg-white hover:border-brand-red hover:text-brand-red'">
                        +
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-4 border-t border-gray-100 bg-white shrink-0">
              <div v-if="summaryText" class="text-[12px] text-gray-400 mb-3 line-clamp-2">
                {{ summaryText }}
              </div>

              <div class="flex items-center gap-3">
                <!-- Qty -->
                <div class="flex items-center gap-2 bg-gray-50 rounded-2xl
                            border border-gray-100 p-1 shrink-0">
                  <button @click="decrementQty" :disabled="agotado" class="w-9 h-9 rounded-xl flex items-center justify-center
                           cursor-pointer border-none bg-white text-gray-600
                           font-bold text-xl hover:text-brand-red
                           shadow-sm transition-all duration-150
                           disabled:opacity-40 disabled:cursor-not-allowed">
                    −
                  </button>
                  <span class="font-black text-[16px] text-gray-900
                               min-w-[24px] text-center">
                    {{ qty }}
                  </span>
                  <button @click="incrementQty" :disabled="agotado || qty >= maxQty" class="w-9 h-9 rounded-xl flex items-center justify-center
                           cursor-pointer border-none bg-white text-gray-600
                           font-bold text-xl hover:text-brand-red
                           shadow-sm transition-all duration-150
                           disabled:opacity-40 disabled:cursor-not-allowed">
                    +
                  </button>
                </div>

                <!-- Botón — cambia según modo/stock -->
                <button @click="confirm" :disabled="agotado" class="flex-1 py-3.5 rounded-2xl font-black text-[14px]
                         text-white border-none cursor-pointer uppercase
                         tracking-wide transition-all duration-200
                         hover:-translate-y-0.5 active:scale-[0.98]
                         disabled:opacity-50 disabled:cursor-not-allowed
                         disabled:hover:translate-y-0
                         flex items-center justify-center gap-2" :class="agotado
                          ? 'bg-gray-400 shadow-none'
                          : editingUid
                            ? 'bg-amber-500 hover:bg-amber-600 shadow-[0_4px_20px_rgba(245,158,11,0.35)]'
                            : 'bg-brand-red hover:bg-red-700 shadow-red-md'"
                  style="font-family:'Plus Jakarta Sans',sans-serif;">
                  <component :is="agotado ? XCircleIcon : editingUid ? CheckCircleIcon : ShoppingCartIcon"
                    class="w-4 h-4" />
                  {{ confirmLabel }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import AppIcon from '@/components/AppIcon.vue'
import {
  XMarkIcon, CheckIcon, ShoppingCartIcon,
  ExclamationCircleIcon, PencilIcon, CheckCircleIcon,
  ArchiveBoxIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'
import { useCartStore } from '@/stores/cart'
import type {
  Product, CustomizationOption, ProductExtra,
} from '@/stores/products'
import type { CartCustomization, CartExtra } from '@/stores/cart'

const cartStore = useCartStore()

// ── Estado ────────────────────────────────────────────────
const isOpen = ref(false)
const product = ref<Product | null>(null)
const editingUid = ref<string | null>(null) // uid del item que se está editando
const selections = ref<Map<number, CartCustomization>>(new Map())
const extrasMap = ref<Map<number, CartExtra>>(new Map())
const errors = ref<Record<number, string>>({})
const qty = ref(1)

// Si el cliente eligió una opción con foto propia (ej. un color), se
// muestra esa en vez de la foto genérica del producto — salvo que haya
// tocado una miniatura de la galería a mano, eso manda primero.
const manualImage = ref<string | null>(null)
const displayImageUrl = computed(() => {
  if (manualImage.value) return manualImage.value
  if (!product.value) return null
  for (const section of product.value.customization_sections) {
    if (section.multiple) continue
    const sel = selections.value.get(section.id)
    if (!sel || sel.selections.length === 0) continue
    const opt = section.options.find(o => o.id === sel.selections[0].option_id)
    if (opt?.image_url) return opt.image_url
  }
  return product.value.image_url
})

// Todas las fotos disponibles para la tira de miniaturas: principal +
// galería general + variantes con foto propia (sin duplicados).
const galleryThumbs = computed(() => {
  if (!product.value) return []
  const seen = new Set<string>()
  const thumbs: { key: string; url: string }[] = []

  const add = (key: string, url: string | null | undefined) => {
    if (!url || seen.has(url)) return
    seen.add(url)
    thumbs.push({ key, url })
  }

  add('main', product.value.image_url)
  product.value.images?.forEach(img => add(`gallery-${img.id}`, img.image_url))
  product.value.customization_sections.forEach(section => {
    if (section.multiple) return
    section.options.forEach(opt => add(`opt-${opt.id}`, opt.image_url))
  })

  return thumbs
})

// ── Expose ────────────────────────────────────────────────
defineExpose({
  // Abrir para agregar nuevo
  open(p: Product) {
    product.value = p
    editingUid.value = null
    selections.value = new Map()
    extrasMap.value = new Map()
    errors.value = {}
    qty.value = 1
    manualImage.value = null
    isOpen.value = true
  },

  // Abrir para editar item existente del carrito
  openEdit(p: Product, item: ReturnType<typeof cartStore.items>[number]) {
    product.value = p
    editingUid.value = item._uid
    errors.value = {}
    qty.value = item.qty
    manualImage.value = null

    // Precargar personalización
    const selMap = new Map<number, CartCustomization>()
    item.customization.forEach(c => selMap.set(c.section_id, { ...c, selections: [...c.selections] }))
    selections.value = selMap

    // Precargar extras
    const extMap = new Map<number, CartExtra>()
    item.extras.forEach(e => extMap.set(e.extra_id, { ...e }))
    extrasMap.value = extMap

    isOpen.value = true
  },

  close,
})

function close() {
  isOpen.value = false
  product.value = null
  editingUid.value = null
}

// ── Control de stock ──────────────────────────────────────
const controlaStock = computed(() => product.value?.controla_stock ?? false)
const stockDisponible = computed(() => product.value?.stock ?? 0)
const agotado = computed(() => controlaStock.value && stockDisponible.value <= 0)
const stockBajo = computed(() =>
  controlaStock.value && stockDisponible.value > 0 && stockDisponible.value <= 5
)
const maxQty = computed(() =>
  controlaStock.value ? Math.max(1, stockDisponible.value) : 99
)

function incrementQty() {
  if (agotado.value) return
  qty.value = Math.min(maxQty.value, qty.value + 1)
}

function decrementQty() {
  qty.value = Math.max(1, qty.value - 1)
}

// ── Precios ───────────────────────────────────────────────
const extrasTotal = computed(() => {
  let sum = 0
  extrasMap.value.forEach(e => { sum += e.price * e.qty })
  return sum
})

const totalPrice = computed(() =>
  ((product.value?.price ?? 0) + extrasTotal.value) * qty.value
)

const summaryText = computed(() => {
  const parts: string[] = []
  selections.value.forEach(sec =>
    sec.selections.forEach(s => parts.push(s.name))
  )
  extrasMap.value.forEach(e => {
    if (e.qty > 0) parts.push(e.qty > 1 ? `${e.name} ×${e.qty}` : `+ ${e.name}`)
  })
  return parts.join(' · ')
})

const confirmLabel = computed(() => {
  if (agotado.value) return 'Agotado'
  return editingUid.value
    ? `Guardar cambios · S/ ${totalPrice.value.toFixed(2)}`
    : `Agregar · S/ ${totalPrice.value.toFixed(2)}`
})

// ── Personalización ───────────────────────────────────────
function toggleMultiple(sectionId: number, opt: CustomizationOption) {
  errors.value[sectionId] = ''
  const section = product.value?.customization_sections.find(s => s.id === sectionId)
  if (!section) return

  const current = selections.value.get(sectionId)
  if (current) {
    const idx = current.selections.findIndex(s => s.option_id === opt.id)
    if (idx !== -1) {
      current.selections.splice(idx, 1)
      if (current.selections.length === 0) selections.value.delete(sectionId)
    } else {
      current.selections.push({ option_id: opt.id, name: opt.name })
    }
  } else {
    selections.value.set(sectionId, {
      section_id: sectionId,
      seccion: section.seccion,
      label: section.label,
      selections: [{ option_id: opt.id, name: opt.name }],
    })
  }
  selections.value = new Map(selections.value)
}

function selectSingle(sectionId: number, opt: CustomizationOption) {
  errors.value[sectionId] = ''
  const section = product.value?.customization_sections.find(s => s.id === sectionId)
  if (!section) return

  const current = selections.value.get(sectionId)
  if (current?.selections[0]?.option_id === opt.id) {
    selections.value.delete(sectionId)
  } else {
    selections.value.set(sectionId, {
      section_id: sectionId,
      seccion: section.seccion,
      label: section.label,
      selections: [{ option_id: opt.id, name: opt.name }],
    })
    manualImage.value = null
  }
  selections.value = new Map(selections.value)
}

function isSelected(sectionId: number, optionId: number): boolean {
  return selections.value.get(sectionId)?.selections
    .some(s => s.option_id === optionId) ?? false
}

// ── Extras ────────────────────────────────────────────────
function getExtraQty(extraId: number): number {
  return extrasMap.value.get(extraId)?.qty ?? 0
}

function incrementExtra(extra: ProductExtra) {
  const current = extrasMap.value.get(extra.id)
  if (current) {
    current.qty++
  } else {
    extrasMap.value.set(extra.id, {
      extra_id: extra.id,
      name: extra.name,
      price: extra.price,
      qty: 1,
    })
  }
  extrasMap.value = new Map(extrasMap.value)
}

function decrementExtra(extraId: number) {
  const current = extrasMap.value.get(extraId)
  if (!current) return
  if (current.qty <= 1) extrasMap.value.delete(extraId)
  else current.qty--
  extrasMap.value = new Map(extrasMap.value)
}

// ── Confirmar (agregar o guardar edición) ─────────────────
function confirm() {
  if (!product.value || agotado.value) return
  errors.value = {}
  let valid = true

  product.value.customization_sections.forEach(sec => {
    if (sec.required && !selections.value.has(sec.id)) {
      errors.value[sec.id] = 'Selecciona una opción'
      valid = false
    }
  })

  if (!valid) return

  const customization = Array.from(selections.value.values())
  const extras = Array.from(extrasMap.value.values()).filter(e => e.qty > 0)

  if (editingUid.value) {
    // ── Modo edición — actualiza item existente ──
    cartStore.updateItem(editingUid.value, customization, extras, qty.value)
  } else {
    // ── Modo agregar — crea items nuevos ──
    for (let i = 0; i < qty.value; i++) {
      cartStore.add(product.value, customization, extras)
    }
  }

  close()
}

watch(isOpen, val => {
  if (!val) {
    selections.value = new Map()
    extrasMap.value = new Map()
    errors.value = {}
    qty.value = 1
    editingUid.value = null
  }
})
</script>