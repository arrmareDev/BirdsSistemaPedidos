<template>
  <div class="flex flex-col gap-5">

    <!-- ══ MODAL CATEGORÍA ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showCatModal" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="closeCatModal">
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
              <button @click="closeCatModal" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
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
              <button @click="closeCatModal" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white
                       hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="saveCat" :disabled="savingCat" class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold
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

    <!-- ══ MODAL ELIMINAR CATEGORÍA ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="deleteCatTarget" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="deleteCatTarget = null">
          <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-50 mx-auto mb-5 flex items-center justify-center">
              <TrashIcon class="w-7 h-7 text-red-500" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              ¿Eliminar categoría?
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
              <strong class="text-gray-700">{{ deleteCatTarget.name }}</strong>
              será eliminada permanentemente.
            </p>
            <div v-if="deleteCatError" class="flex items-center gap-2 px-3.5 py-3 rounded-2xl bg-red-50
                     border border-red-200 text-[12.5px] text-red-600 mb-4 text-left">
              <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
              {{ deleteCatError }}
            </div>
            <div class="flex gap-3">
              <button @click="deleteCatTarget = null" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white
                       hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="confirmDeleteCat" :disabled="deletingCat" class="flex-1 py-3 rounded-2xl bg-red-600 text-white font-bold
                       text-[13.5px] cursor-pointer border-none hover:bg-red-700
                       transition-all duration-150 disabled:opacity-50
                       flex items-center justify-center gap-2">
                <span v-if="deletingCat" class="w-4 h-4 border-2 border-white/30 border-t-white
                         rounded-full animate-spin" />
                {{ deletingCat ? 'Eliminando...' : 'Sí, eliminar' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL PRODUCTO ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-250"
        leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showProductModal" class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm
                 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeProductModal">
          <Transition enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-4 opacity-0 sm:scale-95" leave-to-class="translate-y-4 opacity-0">
            <div v-if="showProductModal" class="w-full sm:max-w-2xl bg-white rounded-t-3xl sm:rounded-3xl
                     shadow-2xl flex flex-col overflow-hidden max-h-[95vh] sm:max-h-[90vh]">

              <!-- Header -->
              <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center">
                    <TagIcon class="w-5 h-5 text-brand-red" />
                  </div>
                  <div>
                    <h2 class="font-black text-[17px] text-gray-900 m-0 leading-none"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ editingProduct ? 'Editar producto' : 'Nuevo producto' }}
                    </h2>
                    <p class="text-[11px] text-gray-400 m-0 mt-0.5">
                      {{ editingProduct?.name ?? 'Datos base del producto' }}
                    </p>
                  </div>
                </div>
                <button @click="closeProductModal" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
                         justify-center cursor-pointer border-none hover:bg-gray-200 transition-colors">
                  <XMarkIcon class="w-4 h-4 text-gray-500" />
                </button>
              </div>

              <!-- Body -->
              <div class="flex-1 overflow-y-auto px-6 py-5 flex flex-col gap-5">

                <!-- Tabs -->
                <div class="flex gap-1 bg-gray-100 p-1 rounded-xl">
                  <button v-for="t in MODAL_TABS" :key="t.value" @click="modalTab = t.value" class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg
                           text-[12.5px] font-semibold transition-all duration-150 border-none cursor-pointer" :class="modalTab === t.value
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'bg-transparent text-gray-500 hover:text-gray-700'">
                    <component :is="t.icon" class="w-3.5 h-3.5" />
                    {{ t.label }}
                  </button>
                </div>

                <!-- ── Tab Info ── -->
                <div v-show="modalTab === 'info'" class="flex flex-col gap-4">
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
                      <div v-if="form.controla_stock" class="flex flex-col gap-1.5 pt-1">
                        <label class="field-label">Stock disponible</label>
                        <input v-model.number="form.stock" type="number" min="0" step="1" placeholder="0"
                          class="modal-input font-bold w-32" />
                        <p class="text-[11px] text-gray-400 m-0">
                          Cuando llegue a 0, el producto se mostrará como agotado.
                        </p>
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
                      <input type="file" accept="image/*" class="hidden" @change="handleImageChange" />
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
                        <button @click="deleteGalleryImage(img.id)" type="button" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
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
                        <input type="file" accept="image/*" multiple class="hidden" @change="onGalleryFilesSelected" />
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

                <!-- ── Tab Personalización ── -->
                <div v-show="modalTab === 'personalizacion'" class="flex flex-col gap-4">
                  <div class="flex items-center justify-between">
                    <p class="text-[13px] text-gray-500 m-0">Preferencias del cliente — sin costo adicional</p>
                    <button @click="showAddSection = !showAddSection" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-red text-white
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
                      <button v-for="tipo in seccionTiposActivos" :key="tipo.id" @click="addSection(tipo)"
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
                      <button @click="removeSection(si)" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                               cursor-pointer border-none bg-transparent hover:bg-red-50 hover:text-red-500
                               transition-all duration-150">
                        <TrashIcon class="w-3.5 h-3.5" />
                      </button>
                    </div>
                    <div class="p-4 flex flex-col gap-2">
                      <div v-for="(opt, oi) in section.options" :key="oi" class="flex items-center gap-2">
                        <button v-if="opt.id" @click="openOptionImagePicker(opt)" type="button"
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
                        <button @click="removeOption(si, oi)" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                 cursor-pointer border-none bg-white hover:bg-red-50 hover:text-red-500
                                 transition-all duration-150 shrink-0">
                          <XMarkIcon class="w-3.5 h-3.5" />
                        </button>
                      </div>
                      <button @click="addOption(si)" class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-dashed border-gray-300
                               bg-white text-[12px] font-semibold text-gray-500 cursor-pointer
                               hover:border-brand-red hover:text-brand-red transition-all duration-150 w-fit">
                        <PlusIcon class="w-3.5 h-3.5" />
                        Agregar opción
                      </button>
                    </div>
                  </div>
                </div>

                <input ref="optionImageInputRef" type="file" accept="image/*" class="hidden"
                  @change="onOptionImageSelected" />

                <!-- ── Tab Extras ── -->
                <div v-show="modalTab === 'extras'" class="flex flex-col gap-4">
                  <div class="flex items-center justify-between">
                    <p class="text-[13px] text-gray-500 m-0">
                      Productos adicionales que el cliente puede agregar con costo
                    </p>
                    <button @click="addExtra" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-red text-white
                             font-bold text-[12.5px] border-none cursor-pointer hover:bg-red-700
                             transition-all duration-150">
                      <PlusIcon class="w-3.5 h-3.5" />
                      Agregar extra
                    </button>
                  </div>

                  <div v-if="form.extras.length === 0"
                    class="flex items-center gap-2 px-4 py-3.5 rounded-2xl bg-gray-50 border border-dashed border-gray-200">
                    <p class="text-[13px] text-gray-400 m-0">Sin extras — ej: Peluche, Chocolates, Globo metálico</p>
                  </div>

                  <div v-for="(extra, i) in form.extras" :key="i"
                    class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-100">
                    <input v-model="extra.name" placeholder="Ej: Caja de chocolates"
                      class="modal-input flex-1 font-semibold" />
                    <div class="flex flex-col gap-0.5 shrink-0 w-32">
                      <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Precio S/</span>
                      <input v-model.number="extra.price" type="number" step="0.50" min="0" placeholder="0.00"
                        class="modal-input font-bold py-2 w-full" />
                    </div>
                    <button @click="removeExtra(i)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400
                             cursor-pointer border-none bg-white hover:bg-red-50 hover:text-red-500
                             transition-all duration-150 shrink-0 mt-4">
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>

                  <p v-if="form.extras.length > 0" class="text-[11px] text-gray-400 m-0">
                    El cliente verá estos extras en el modal y puede agregarlos al pedido.
                  </p>

                  <div class="flex flex-col gap-2 mt-2 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                      <p class="field-label m-0">Extras compartidos (reutilizables entre productos)</p>
                      <button type="button" @click="openExtrasManager" class="flex items-center gap-1.5 text-[11.5px] font-bold text-brand-red
                               cursor-pointer border-none bg-transparent hover:underline">
                        <PencilSquareIcon class="w-3.5 h-3.5" />
                        Gestionar
                      </button>
                    </div>
                    <div v-if="availableExtras.length === 0" class="text-[12px] text-gray-400">
                      Sin extras compartidos creados aún.
                    </div>
                    <div v-else class="grid grid-cols-2 gap-2">
                      <label v-for="extra in availableExtras" :key="extra.id"
                        class="flex items-center gap-2 px-3 py-2 rounded-xl border-2 cursor-pointer transition-all"
                        :class="form.extra_ids.includes(extra.id) ? 'border-brand-red bg-red-50' : 'border-gray-200'">
                        <input type="checkbox" :value="extra.id" v-model="form.extra_ids" class="accent-brand-red" />
                        <span class="text-[12.5px] font-semibold text-gray-700 truncate">{{ extra.name }}</span>
                        <span class="text-[11px] text-green-600 font-bold ml-auto shrink-0">
                          +S/{{ extra.price.toFixed(2) }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Error -->
                <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                  leave-to-class="opacity-0">
                  <div v-if="formError"
                    class="flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-red-50 border border-red-200">
                    <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                    <p class="text-[12.5px] text-red-700 m-0">{{ formError }}</p>
                  </div>
                </Transition>
              </div>

              <!-- Footer -->
              <div class="px-6 py-4 border-t border-gray-100 flex gap-3 shrink-0">
                <button @click="closeProductModal" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white
                         hover:border-gray-300 transition-all duration-150">
                  Cancelar
                </button>
                <button @click="saveProduct" :disabled="saving" class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold
                         text-[13.5px] cursor-pointer border-none hover:bg-red-700
                         transition-all duration-150 disabled:opacity-50
                         flex items-center justify-center gap-2">
                  <span v-if="saving"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  <CheckCircleIcon v-else class="w-4 h-4" />
                  {{ saving ? 'Guardando...' : (editingProduct ? 'Guardar' : 'Crear') }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL ELIMINAR PRODUCTO ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="deleteTarget" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="deleteTarget = null">
          <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-50 mx-auto mb-5 flex items-center justify-center">
              <TrashIcon class="w-7 h-7 text-red-500" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              ¿Eliminar producto?
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
              <strong class="text-gray-700">{{ deleteTarget.name }}</strong> será eliminado permanentemente.
            </p>
            <div class="flex gap-3">
              <button @click="deleteTarget = null"
                class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="confirmDelete" :disabled="deleting" class="flex-1 py-3 rounded-2xl bg-red-600 text-white font-bold
                       text-[13.5px] cursor-pointer border-none hover:bg-red-700
                       transition-all duration-150 disabled:opacity-50 flex items-center justify-center gap-2">
                <span v-if="deleting"
                  class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ deleting ? 'Eliminando...' : 'Sí, eliminar' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ PANEL CATEGORÍAS ══ -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <button @click="showCatPanel = !showCatPanel" class="w-full flex items-center justify-between px-5 py-4 cursor-pointer
               border-none bg-transparent hover:bg-gray-50 transition-colors">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
            <FolderIcon class="w-4 h-4 text-brand-red" />
          </div>
          <div class="text-left">
            <p class="font-black text-[14px] text-gray-900 m-0">Categorías</p>
            <p class="text-[11px] text-gray-400 m-0">{{ categories.length }} registradas</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click.stop="openCreateCat" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-red text-white
                   font-bold text-[12px] border-none cursor-pointer hover:bg-red-700
                   transition-all duration-150">
            <PlusIcon class="w-3 h-3" />
            Nueva
          </button>
          <ChevronDownIcon class="w-4 h-4 text-gray-400 transition-transform duration-200"
            :class="showCatPanel ? 'rotate-180' : ''" />
        </div>
      </button>

      <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
        leave-to-class="opacity-0">
        <div v-if="showCatPanel" class="border-t border-gray-100">
          <div v-if="categories.length === 0" class="flex items-center justify-center py-10 text-gray-400 text-[13px]">
            Sin categorías — crea la primera
          </div>
          <div v-else class="flex flex-col">
            <div v-for="group in categoriesGrouped" :key="group.id">

              <!-- Header de la categoría principal -->
              <button @click="toggleGroupExpanded(group.id)" class="w-full flex items-center gap-2 px-5 py-2.5 bg-gray-50/70 border-y border-gray-100
                       cursor-pointer border-none text-left hover:bg-gray-100/70 transition-colors">
                <ChevronDownIcon class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0"
                  :class="isGroupExpanded(group.id) ? 'rotate-180' : ''" />
                <AppIcon :name="group.icon" :size="15" class="text-gray-500" />
                <p class="font-black text-[11px] uppercase tracking-wider text-gray-500 m-0">
                  {{ group.label }}
                </p>
                <span class="text-[10.5px] font-bold text-gray-400">
                  {{ group.categories.length }} subcategoría{{ group.categories.length !== 1 ? 's' : '' }}
                  <template v-if="(group.root.products_count ?? 0) > 0">
                    · {{ group.root.products_count }} producto{{ group.root.products_count !== 1 ? 's' : '' }} propio{{
                      group.root.products_count !== 1 ? 's' : '' }}
                  </template>
                </span>
                <span @click.stop="openEditCat(group.root)" title="Editar esta categoría principal" class="ml-auto w-6 h-6 rounded-lg flex items-center justify-center text-gray-400
                         cursor-pointer hover:bg-white hover:text-brand-red transition-all">
                  <PencilIcon class="w-3 h-3" />
                </span>
                <span @click.stop="askDeleteCat(group.root)" :title="group.categories.length > 0
                  ? 'Tiene subcategorías — no se puede eliminar'
                  : (group.root.products_count ?? 0) > 0
                    ? 'Tiene productos — no se puede eliminar'
                    : 'Eliminar categoría principal'"
                  class="w-6 h-6 rounded-lg flex items-center justify-center transition-all" :class="group.categories.length > 0 || (group.root.products_count ?? 0) > 0
                    ? 'text-gray-200 cursor-not-allowed'
                    : 'text-gray-400 cursor-pointer hover:bg-white hover:text-red-500'">
                  <TrashIcon class="w-3 h-3" />
                </span>
              </button>

              <template v-if="isGroupExpanded(group.id)">
                <!-- Vacío dentro del grupo -->
                <div v-if="group.categories.length === 0"
                  class="flex items-center gap-2 px-5 py-4 text-[12.5px] text-gray-300 italic">
                  Sin subcategorías en {{ group.label.toLowerCase() }} todavía
                </div>

                <!-- Categorías de esta línea -->
                <div v-else class="divide-y divide-gray-50">
                  <div v-for="cat in group.categories" :key="cat.id"
                    class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">

                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center
                    text-gray-500 shrink-0">
                      <AppIcon :name="cat.icon" :size="18" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <p class="font-bold text-[13.5px] text-gray-900 m-0 truncate">{{ cat.name }}</p>
                        <span v-if="!cat.active" class="text-[9.5px] font-black uppercase px-1.5 py-0.5 rounded-full
                     bg-gray-100 text-gray-500 shrink-0">
                          Inactiva
                        </span>
                      </div>
                      <p class="text-[11px] text-gray-400 m-0">
                        {{ cat.products_count ?? 0 }} producto{{ (cat.products_count ?? 0) !== 1 ? 's' : '' }}
                        · orden {{ cat.sort_order }}
                      </p>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                      <button @click="toggleCat(cat)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border cursor-pointer
                   transition-all duration-150" :class="cat.active
                    ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                    : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'">
                        {{ cat.active ? 'Activa' : 'Inactiva' }}
                      </button>
                      <button @click="openEditCat(cat)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-500
                   cursor-pointer border-none bg-gray-100 hover:bg-red-50 hover:text-brand-red
                   transition-all duration-150">
                        <PencilIcon class="w-3.5 h-3.5" />
                      </button>
                      <button @click="askDeleteCat(cat)" :disabled="(cat.products_count ?? 0) > 0" class="w-8 h-8 rounded-xl flex items-center justify-center cursor-pointer
                   border-none transition-all duration-150" :class="(cat.products_count ?? 0) > 0
                    ? 'bg-gray-50 text-gray-300 cursor-not-allowed'
                    : 'bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500'"
                        :title="(cat.products_count ?? 0) > 0 ? 'Tiene productos — no se puede eliminar' : 'Eliminar'">
                        <TrashIcon class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- ══ PANEL TIPOS DE SECCIÓN DE PERSONALIZACIÓN ══ -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <button @click="showSeccionTiposPanel = !showSeccionTiposPanel" class="w-full flex items-center justify-between px-5 py-4 cursor-pointer
               border-none bg-transparent hover:bg-gray-50 transition-colors">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
            <CheckCircleIcon class="w-4 h-4 text-purple-600" />
          </div>
          <div class="text-left">
            <p class="font-black text-[14px] text-gray-900 m-0">Tipos de sección de personalización</p>
            <p class="text-[11px] text-gray-400 m-0">{{ seccionTipos.length }} registrados — aparecen al armar
              "Personalización" en un producto</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click.stop="openCreateSeccionTipo" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-red text-white
                   font-bold text-[12px] border-none cursor-pointer hover:bg-red-700
                   transition-all duration-150">
            <PlusIcon class="w-3 h-3" />
            Nuevo
          </button>
          <ChevronDownIcon class="w-4 h-4 text-gray-400 transition-transform duration-200"
            :class="showSeccionTiposPanel ? 'rotate-180' : ''" />
        </div>
      </button>

      <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2"
        leave-to-class="opacity-0">
        <div v-if="showSeccionTiposPanel" class="border-t border-gray-100">
          <div v-if="seccionTipos.length === 0"
            class="flex items-center justify-center py-10 text-gray-400 text-[13px]">
            Sin tipos de sección — crea el primero
          </div>
          <div v-else class="divide-y divide-gray-50">
            <div v-for="tipo in seccionTipos" :key="tipo.id"
              class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">

              <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center
                    text-gray-500 shrink-0">
                <AppIcon :name="tipo.icono" :size="18" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <p class="font-bold text-[13.5px] text-gray-900 m-0 truncate">{{ tipo.nombre }}</p>
                  <span v-if="!tipo.activo" class="text-[9.5px] font-black uppercase px-1.5 py-0.5 rounded-full
                     bg-gray-100 text-gray-500 shrink-0">
                    Inactivo
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-1.5 shrink-0">
                <button @click="toggleSeccionTipo(tipo)" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border cursor-pointer
                   transition-all duration-150" :class="tipo.activo
                    ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                    : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'">
                  {{ tipo.activo ? 'Activo' : 'Inactivo' }}
                </button>
                <button @click="openEditSeccionTipo(tipo)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-500
                   cursor-pointer border-none bg-gray-100 hover:bg-red-50 hover:text-brand-red
                   transition-all duration-150">
                  <PencilIcon class="w-3.5 h-3.5" />
                </button>
                <button @click="askDeleteSeccionTipo(tipo)" class="w-8 h-8 rounded-xl flex items-center justify-center cursor-pointer
                   border-none bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500
                   transition-all duration-150">
                  <TrashIcon class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- ══ MODAL TIPO DE SECCIÓN ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showSeccionTipoModal" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="closeSeccionTipoModal">
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
              <button @click="closeSeccionTipoModal" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
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
              <button @click="closeSeccionTipoModal" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white
                       hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="saveSeccionTipo" :disabled="savingSeccionTipo" class="flex-1 py-3 rounded-2xl bg-brand-red text-white font-bold
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

    <!-- ══ CONFIRMAR ELIMINAR TIPO DE SECCIÓN ══ -->
    <ConfirmModal v-model="deleteSeccionTipoConfirm.show" title="¿Eliminar este tipo de sección?"
      :message="`«${deleteSeccionTipoConfirm.target?.nombre}» ya no aparecerá como opción al agregar personalización a un producto.`"
      variant="danger" confirm-label="Sí, eliminar" :loading="deleteSeccionTipoConfirm.loading"
      @confirm="confirmDeleteSeccionTipo" />

    <!-- ══ HEADER PRODUCTOS ══ -->
    <div class="flex items-center justify-between flex-wrap gap-3">
      <p class="text-[13px] text-gray-400 m-0">
        {{ productsStore.meta?.total ?? productsStore.products.length }} productos · {{ categories.length }} categorías
      </p>
      <div class="flex items-center gap-2 flex-wrap">
        <button @click="filterByCategory('')" class="px-3.5 py-1.5 rounded-full text-[12.5px] font-semibold
                 border transition-all duration-150 cursor-pointer" :class="activeCat === ''
                  ? 'bg-brand-red text-white border-brand-red'
                  : 'bg-white border-gray-200 text-gray-600 hover:border-red-300'">
          Todos
        </button>
        <button v-for="cat in categories" :key="cat.id" @click="filterByCategory(String(cat.id))"
          class="px-3.5 py-1.5 rounded-full text-[12.5px] font-semibold border transition-all duration-150 cursor-pointer inline-flex items-center gap-1.5"
          :class="activeCat === String(cat.id)
            ? 'bg-brand-red text-white border-brand-red'
            : 'bg-white border-gray-200 text-gray-600 hover:border-red-300'">
          <AppIcon :name="cat.icon" :size="13" /> {{ cat.name }}
        </button>

        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
          <input v-model="search" @input="onSearchInput" placeholder="Buscar..." class="pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px]
                   text-gray-900 outline-none w-60 focus:border-brand-red transition-all duration-200
                   placeholder:text-gray-300" />
        </div>

        <button @click="openCreate" class="flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-brand-red
                 text-white font-bold text-[13px] border-none cursor-pointer
                 shadow-sm hover:bg-red-700 transition-all duration-150">
          <PlusIcon class="w-3.5 h-3.5" />
          Nuevo producto
        </button>
      </div>
    </div>

    <!-- ══ SKELETON ══ -->
    <div v-if="productsStore.loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <div v-for="n in 8" :key="n" class="h-64 rounded-2xl bg-gray-100 animate-pulse" />
    </div>

    <!-- ══ GRID PRODUCTOS ══ -->
    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <div v-for="product in productsStore.products" :key="product.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col
               transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
        :class="!product.available ? 'opacity-60' : ''">

        <div class="relative h-40 bg-gradient-to-br from-rose-50 via-pink-50 to-emerald-50
                    flex items-center justify-center overflow-hidden">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
            class="w-full h-full object-cover" />
          <AppIcon v-else :name="product.icon" :size="40" class="text-gray-300" />

          <div class="absolute top-2 left-2 flex flex-col gap-1">
            <span v-if="product.popular"
              class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-yellow-400 text-yellow-900 w-fit inline-flex items-center gap-1">
              <Star :size="9" fill="currentColor" /> Popular
            </span>
            <span v-if="product.descuento"
              class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-brand-red text-white w-fit inline-flex items-center gap-1">
              <TagIcon class="w-2.5 h-2.5" /> -{{ product.descuento.porcentaje }}%
            </span>
            <span v-if="product.controla_stock" class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full w-fit"
              :class="(product.stock ?? 0) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600'">
              {{ (product.stock ?? 0) > 0 ? `Stock ${product.stock}` : 'Sin stock' }}
            </span>
          </div>

          <button @click="toggleAvailable(product)" class="absolute top-2 right-2 px-2 py-1 rounded-lg text-[10px] font-bold
                   border cursor-pointer transition-all duration-150" :class="product.available
                    ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                    : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'">
            <Check v-if="product.available" :size="12" :stroke-width="3" />
            <X v-else :size="12" :stroke-width="3" />
          </button>

          <div v-if="!product.available" class="absolute inset-0 bg-gray-900/30 flex items-center justify-center">
            <span class="bg-white text-gray-700 text-[11px] font-black uppercase px-3 py-1 rounded-full">
              Agotado
            </span>
          </div>
        </div>

        <div class="p-3.5 flex flex-col gap-1 flex-1">
          <p class="font-bold text-[13.5px] text-gray-900 m-0 leading-snug line-clamp-2">{{ product.name }}</p>
          <p class="text-[11px] text-gray-400 m-0 line-clamp-1">
            {{ product.category?.name ?? '—' }}
            <span v-if="parentCategoryName(product.category?.id)" class="text-brand-red font-semibold">
              · {{ parentCategoryName(product.category?.id) }}
            </span>
          </p>

          <div class="flex flex-wrap gap-1 mt-1">
            <span v-if="product.customization_sections?.length"
              class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full bg-purple-50 text-purple-600 border border-purple-100">
              {{ product.customization_sections.length }} secciones
            </span>
            <span v-if="product.extras?.length"
              class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-100">
              {{ product.extras.length }} extras
            </span>
          </div>

          <div v-if="product.descuento" class="flex items-center gap-2 mt-2">
            <span class="text-[11px] font-semibold text-gray-400 line-through">S/{{ product.price.toFixed(2) }}</span>
            <div class="flex items-baseline gap-0.5">
              <span class="text-[10px] font-bold text-brand-red">S/</span>
              <span class="font-black text-[20px] text-brand-red leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ product.precio_final.toFixed(2) }}
              </span>
            </div>
          </div>
          <div v-else class="flex items-baseline gap-0.5 mt-2">
            <span class="text-[10px] font-bold text-gray-400">S/</span>
            <span class="font-black text-[20px] text-brand-red leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ product.price.toFixed(2) }}
            </span>
          </div>
        </div>

        <div class="flex border-t border-gray-100">
          <button @click="openEdit(product)" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[12px] font-semibold
                   text-gray-600 cursor-pointer border-none bg-transparent hover:bg-gray-50
                   hover:text-brand-red transition-all duration-150">
            <PencilIcon class="w-3.5 h-3.5" />
            Editar
          </button>
          <div class="w-px bg-gray-100" />
          <button @click="deleteTarget = product" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[12px] font-semibold
                   text-gray-400 cursor-pointer border-none bg-transparent hover:bg-red-50
                   hover:text-red-600 transition-all duration-150">
            <TrashIcon class="w-3.5 h-3.5" />
            Eliminar
          </button>
        </div>
      </div>

      <!-- Card nuevo -->
      <button @click="openCreate" class="h-64 rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center
               justify-center gap-3 cursor-pointer bg-transparent hover:border-red-300 hover:bg-red-50/30
               transition-all duration-200 group">
        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center
                    group-hover:bg-red-100 transition-colors">
          <PlusIcon class="w-6 h-6 text-gray-400 group-hover:text-red-500" />
        </div>
        <span class="text-[13px] font-semibold text-gray-400 group-hover:text-red-500 transition-colors">
          Nuevo Producto
        </span>
      </button>
    </div>

    <!-- ══ CARGAR MÁS ══ -->
    <div v-if="productsStore.hasMore" class="flex justify-center pt-2">
      <button @click="loadMoreProducts" :disabled="productsStore.loadingMore" class="flex items-center gap-2 px-5 py-2.5 rounded-2xl border-2 border-gray-200
               bg-white text-[13px] font-bold text-gray-600 cursor-pointer
               hover:border-brand-red/40 hover:text-brand-red transition-all duration-150
               disabled:opacity-50 disabled:cursor-wait">
        <span v-if="productsStore.loadingMore"
          class="w-4 h-4 border-2 border-gray-300 border-t-brand-red rounded-full animate-spin" />
        {{ productsStore.loadingMore ? 'Cargando...' : `Cargar más (${productsStore.products.length} de
        ${productsStore.meta?.total ?? 0})` }}
      </button>
    </div>

    <!-- ══ MODAL: GESTIONAR EXTRAS COMPARTIDOS ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showExtrasManager" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="showExtrasManager = false">
          <Transition appear enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
            leave-active-class="transition-all duration-150" leave-to-class="opacity-0 scale-95">
            <div v-if="showExtrasManager" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh]
                     flex flex-col overflow-hidden">

              <!-- Header -->
              <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 shrink-0">
                <div>
                  <h2 class="font-black text-[17px] text-gray-900 m-0">Extras compartidos</h2>
                  <p class="text-[12px] text-gray-400 m-0 mt-0.5">Crea, edita o elimina extras reutilizables</p>
                </div>
                <button @click="showExtrasManager = false" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400
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
                  <button @click="createExtra" :disabled="!newExtra.name.trim() || savingExtra" class="h-[42px] px-4 rounded-xl bg-brand-red text-white font-bold text-[13px]
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
                      <button @click="saveExtra(extra)" title="Guardar cambios" class="w-8 h-8 rounded-lg flex items-center justify-center text-green-600
                               cursor-pointer border-none bg-green-50 hover:bg-green-100 transition-all shrink-0">
                        <CheckIcon class="w-4 h-4" />
                      </button>
                      <button @click="deleteExtra(extra.id)" title="Eliminar" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import {
  PlusIcon, PencilIcon, TrashIcon, XMarkIcon,
  MagnifyingGlassIcon, PhotoIcon, CheckIcon,
  CheckCircleIcon, ExclamationCircleIcon, TagIcon,
  PlusCircleIcon, ArchiveBoxIcon, FolderIcon, ChevronDownIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline'
import { Star, Check, X } from 'lucide-vue-next'
import AppIcon from '@/components/AppIcon.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import { useProductsStore } from '@/stores/products'
import type { Product, Category } from '@/stores/products'
import api from '@/utils/api'

// ── Store ─────────────────────────────────────────────────
const productsStore = useProductsStore()

// ── Estado productos ──────────────────────────────────────
const activeCat = ref('')
const search = ref('')
const categories = ref<Category[]>([])
const showProductModal = ref(false)
const showAddSection = ref(false)
const modalTab = ref('info')
const editingProduct = ref<Product | null>(null)
const deleteTarget = ref<Product | null>(null)
const saving = ref(false)
const deleting = ref(false)
const formError = ref('')
const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

// ── Estado categorías ─────────────────────────────────────
const showCatPanel = ref(false)
const expandedGroups = ref<Set<number>>(new Set())

function isGroupExpanded(groupId: number): boolean {
  return expandedGroups.value.has(groupId)
}

function toggleGroupExpanded(groupId: number) {
  const next = new Set(expandedGroups.value)
  if (next.has(groupId)) next.delete(groupId)
  else next.add(groupId)
  expandedGroups.value = next
}
const showCatModal = ref(false)
const editingCat = ref<Category | null>(null)
const deleteCatTarget = ref<Category | null>(null)
const deleteCatError = ref('')
const savingCat = ref(false)
const deletingCat = ref(false)
const catError = ref('')

const catForm = reactive({
  name: '',
  icon: '',
  parent_id: null as number | null,
  sort_order: 0,
  active: true,
})

// ── Constantes ────────────────────────────────────────────
const MODAL_TABS = [
  { value: 'info', label: 'Info', icon: TagIcon },
  { value: 'personalizacion', label: 'Personalización', icon: CheckCircleIcon },
  { value: 'extras', label: 'Extras', icon: PlusCircleIcon },
]

// ── Tipos de sección de personalización (lista real, con CRUD) ──
interface SeccionTipo { id: number; nombre: string; icono: string; activo: boolean; sort_order: number }
const seccionTipos = ref<SeccionTipo[]>([])
const seccionTiposActivos = computed(() => seccionTipos.value.filter(t => t.activo))
const showSeccionTiposPanel = ref(false)

async function loadSeccionTipos() {
  try {
    const { data } = await api.get('/admin/seccion-tipos')
    seccionTipos.value = data.data
  } catch { }
}

const showSeccionTipoModal = ref(false)
const editingSeccionTipo = ref<SeccionTipo | null>(null)
const savingSeccionTipo = ref(false)
const seccionTipoError = ref('')
const seccionTipoForm = reactive({ nombre: '', icono: 'sparkles' })

function openCreateSeccionTipo() {
  editingSeccionTipo.value = null
  seccionTipoError.value = ''
  Object.assign(seccionTipoForm, { nombre: '', icono: 'sparkles' })
  showSeccionTipoModal.value = true
}

function openEditSeccionTipo(tipo: SeccionTipo) {
  editingSeccionTipo.value = tipo
  seccionTipoError.value = ''
  Object.assign(seccionTipoForm, { nombre: tipo.nombre, icono: tipo.icono })
  showSeccionTipoModal.value = true
}

function closeSeccionTipoModal() {
  showSeccionTipoModal.value = false
  editingSeccionTipo.value = null
  seccionTipoError.value = ''
}

async function saveSeccionTipo() {
  seccionTipoError.value = ''
  if (!seccionTipoForm.nombre.trim()) {
    seccionTipoError.value = 'El nombre es requerido'
    return
  }
  savingSeccionTipo.value = true
  try {
    if (editingSeccionTipo.value) {
      await api.put(`/admin/seccion-tipos/${editingSeccionTipo.value.id}`, seccionTipoForm)
    } else {
      await api.post('/admin/seccion-tipos', seccionTipoForm)
    }
    await loadSeccionTipos()
    closeSeccionTipoModal()
  } catch (e: any) {
    seccionTipoError.value = e.response?.data?.message ?? 'Error al guardar'
  } finally {
    savingSeccionTipo.value = false
  }
}

async function toggleSeccionTipo(tipo: SeccionTipo) {
  try {
    await api.put(`/admin/seccion-tipos/${tipo.id}`, { activo: !tipo.activo })
    await loadSeccionTipos()
  } catch { }
}

const deleteSeccionTipoConfirm = reactive({ show: false, loading: false, target: null as SeccionTipo | null })

function askDeleteSeccionTipo(tipo: SeccionTipo) {
  deleteSeccionTipoConfirm.target = tipo
  deleteSeccionTipoConfirm.show = true
}

async function confirmDeleteSeccionTipo() {
  if (!deleteSeccionTipoConfirm.target) return
  deleteSeccionTipoConfirm.loading = true
  try {
    await api.delete(`/admin/seccion-tipos/${deleteSeccionTipoConfirm.target.id}`)
    await loadSeccionTipos()
    deleteSeccionTipoConfirm.show = false
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'No se pudo eliminar este tipo de sección')
  } finally {
    deleteSeccionTipoConfirm.loading = false
  }
}

// ── Form producto ─────────────────────────────────────────
interface FormOption { id?: number; name: string; price_modifier: number; image_url?: string }
interface FormSection {
  id?: number; seccion: string; label: string; required: boolean
  multiple: boolean; sort_order: number; options: FormOption[]
}
interface FormExtra { name: string; price: number }
interface AvailableExtra { id: number; name: string; price: number }

const form = reactive({
  name: '', description: '', icon: '',
  category_id: '' as number | '',
  price: 0,
  stock: 0, controla_stock: false,
  available: true, popular: false,
  tieneDescuento: false,
  descuento_tipo: 'porcentaje' as 'porcentaje' | 'monto_fijo',
  descuento_valor: 0,
  descuento_desde: '',
  descuento_hasta: '',
  sections: [] as FormSection[],
  extras: [] as FormExtra[],
  extra_ids: [] as number[], // ← extras compartidos seleccionados
})

const availableExtras = ref<AvailableExtra[]>([])

// ── Computed ──────────────────────────────────────────────
// Vista previa en vivo mientras se configura el descuento —
// ayuda a confirmar que el número tiene sentido antes de guardar.
const previewPrecioFinal = computed(() => {
  if (!form.tieneDescuento || !form.descuento_valor || form.price <= 0) return ''
  const final = form.descuento_tipo === 'porcentaje'
    ? form.price - (form.price * form.descuento_valor / 100)
    : form.price - form.descuento_valor
  return `Precio final: S/ ${Math.max(0, final).toFixed(2)}`
})

const categoriesSorted = computed(() =>
  [...categories.value].sort((a, b) => a.sort_order - b.sort_order)
)

// Categorías principales (raíz) — para el selector de "categoría padre"
const rootCategories = computed(() =>
  categoriesSorted.value.filter(c => c.parent_id === null)
)

// Agrupa: cada categoría principal real, con sus subcategorías debajo
const categoriesGrouped = computed(() =>
  rootCategories.value.map(root => ({
    id: root.id,
    root,
    label: root.name,
    icon: root.icon,
    categories: categoriesSorted.value.filter(c => c.parent_id === root.id),
  }))
)

// Lista plana para selects: cada raíz seguida de sus subcategorías (en ese orden)
const categoryOptionsTree = computed(() =>
  rootCategories.value.flatMap(root => [
    root,
    ...categoriesSorted.value.filter(c => c.parent_id === root.id),
  ])
)

// Nombre de la categoría padre de un producto, si su categoría es una subcategoría
function parentCategoryName(catId?: number | null): string | null {
  if (!catId) return null
  const cat = categories.value.find(c => c.id === catId)
  if (!cat?.parent_id) return null
  return categories.value.find(c => c.id === cat.parent_id)?.name ?? null
}

// Filtro/búsqueda ahora van al servidor — el catálogo completo ya no
// vive en memoria una vez que hay paginación real.
function currentFilters() {
  return {
    categoryId: activeCat.value || undefined,
    q: search.value.trim() || undefined,
  }
}

function filterByCategory(catId: string) {
  activeCat.value = catId
  productsStore.fetchAdmin(currentFilters())
}

let searchDebounce: ReturnType<typeof setTimeout> | null = null
function onSearchInput() {
  clearTimeout(searchDebounce!)
  searchDebounce = setTimeout(() => {
    productsStore.fetchAdmin(currentFilters())
  }, 400)
}

function loadMoreProducts() {
  productsStore.loadMoreAdmin()
}

// Recarga la página actual de resultados respetando los filtros activos
// (se usa después de crear/editar/borrar un producto).
function refreshCurrentPage() {
  return productsStore.fetchAdmin(currentFilters())
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  await productsStore.fetchAdmin()
  await loadCategories()
  await loadAvailableExtras()
  await loadSeccionTipos()
})

async function loadCategories() {
  const { data } = await api.get('/admin/categories')
  categories.value = data.data
}

async function loadAvailableExtras() {
  try {
    const { data } = await api.get('/admin/extras')

    availableExtras.value = (data.data ?? []).map((extra: any) => ({
      ...extra,
      id: Number(extra.id),
      price: Number(extra.price),
    }))
  } catch (error) {
    console.error(error)
    availableExtras.value = []
  }
}

// ══ GESTIÓN DE EXTRAS COMPARTIDOS ═══════════════════════════

const showExtrasManager = ref(false)
const extrasError = ref('')
const savingExtra = ref(false)
const newExtra = reactive({
  name: '',
  price: 0,
})

function openExtrasManager() {
  extrasError.value = ''
  showExtrasManager.value = true
}

async function createExtra() {
  if (!newExtra.name.trim()) return
  extrasError.value = ''
  savingExtra.value = true
  try {
    const { data } = await api.post('/admin/extras', {
      name: newExtra.name.trim(),
      price: newExtra.price || 0,
    })
    availableExtras.value.push(data.data)
    newExtra.name = ''
    newExtra.price = 0
  } catch (e: any) {
    extrasError.value = e?.response?.data?.message ?? 'No se pudo crear el extra'
  } finally {
    savingExtra.value = false
  }
}

async function saveExtra(extra: AvailableExtra) {
  extrasError.value = ''
  try {
    await api.put(`/admin/extras/${extra.id}`, {
      name: extra.name,
      price: extra.price,
    })
  } catch (e: any) {
    extrasError.value = e?.response?.data?.message ?? 'No se pudo guardar el extra'
    await loadAvailableExtras() // revertir a lo que hay en el servidor
  }
}

async function deleteExtra(id: number) {
  if (!confirm('¿Eliminar este extra? Se quitará de todos los productos que lo tengan.')) return
  extrasError.value = ''
  try {
    await api.delete(`/admin/extras/${id}`)
    availableExtras.value = availableExtras.value.filter(e => e.id !== id)
    form.extra_ids = form.extra_ids.filter(id2 => id2 !== id)
  } catch (e: any) {
    extrasError.value = e?.response?.data?.message ?? 'No se pudo eliminar el extra'
  }
}

// ══ CRUD CATEGORÍAS ═══════════════════════════════════════

function openCreateCat() {
  editingCat.value = null
  catError.value = ''
  Object.assign(catForm, {
    name: '', icon: '', parent_id: null,
    sort_order: categories.value.length, active: true,
  })
  showCatModal.value = true
}

function openEditCat(cat: Category) {
  editingCat.value = cat
  catError.value = ''
  Object.assign(catForm, {
    name: cat.name,
    icon: cat.icon ?? '',
    parent_id: cat.parent_id,
    sort_order: cat.sort_order,
    active: cat.active,
  })
  showCatModal.value = true
}

function closeCatModal() {
  showCatModal.value = false
  editingCat.value = null
  catError.value = ''
}

async function saveCat() {
  catError.value = ''
  if (!catForm.name.trim()) {
    catError.value = 'El nombre es requerido'
    return
  }
  savingCat.value = true
  try {
    if (editingCat.value) {
      await api.put(`/admin/categories/${editingCat.value.id}`, catForm)
    } else {
      await api.post('/admin/categories', catForm)
    }
    await loadCategories()
    closeCatModal()
  } catch (e: any) {
    catError.value = e.response?.data?.message ?? 'Error al guardar la categoría'
  } finally {
    savingCat.value = false
  }
}

async function toggleCat(cat: Category) {
  try {
    await api.put(`/admin/categories/${cat.id}`, { active: !cat.active })
    await loadCategories()
  } catch { }
}

function askDeleteCat(cat: Category) {
  if ((cat.products_count ?? 0) > 0) return
  if (categories.value.some(c => c.parent_id === cat.id)) return
  deleteCatError.value = ''
  deleteCatTarget.value = cat
}

async function confirmDeleteCat() {
  if (!deleteCatTarget.value) return
  deletingCat.value = true
  deleteCatError.value = ''
  try {
    await api.delete(`/admin/categories/${deleteCatTarget.value.id}`)
    await loadCategories()
    deleteCatTarget.value = null
  } catch (e: any) {
    deleteCatError.value = e.response?.data?.message ?? 'No se pudo eliminar la categoría'
  } finally {
    deletingCat.value = false
  }
}

// ══ CRUD PRODUCTOS ════════════════════════════════════════

function openCreate() {
  editingProduct.value = null
  imageFile.value = null
  imagePreview.value = null
  formError.value = ''
  showAddSection.value = false
  modalTab.value = 'info'
  galleryImages.value = []
  Object.assign(form, {
    name: '', description: '', icon: '', category_id: '',
    price: 0,
    stock: 0, controla_stock: false, available: true, popular: false,
    tieneDescuento: false, descuento_tipo: 'porcentaje', descuento_valor: 0,
    descuento_desde: '', descuento_hasta: '',
    sections: [], extras: [], extra_ids: [],
  })
  showProductModal.value = true
}

function openEdit(p: Product) {
  editingProduct.value = p
  imageFile.value = null
  imagePreview.value = null
  formError.value = ''
  showAddSection.value = false
  modalTab.value = 'info'
  galleryImages.value = (p.images ?? []).map(img => ({ ...img }))
  Object.assign(form, {
    name: p.name, description: p.description ?? '', icon: p.icon ?? '',
    category_id: p.category?.id ?? '', price: p.price,
    stock: p.stock ?? 0, controla_stock: p.controla_stock ?? false,
    available: p.available, popular: p.popular,
    tieneDescuento: !!p.descuento_config?.tipo,
    descuento_tipo: (p.descuento_config?.tipo as 'porcentaje' | 'monto_fijo') ?? 'porcentaje',
    descuento_valor: p.descuento_config?.valor ?? 0,
    descuento_desde: p.descuento_config?.desde ?? '',
    descuento_hasta: p.descuento_config?.hasta ?? '',
    sections: (p.customization_sections ?? []).map(s => ({
      id: s.id, seccion: s.seccion, label: s.label, required: s.required,
      multiple: s.multiple, sort_order: 0,
      options: s.options.map(o => ({
        id: o.id, name: o.name, price_modifier: o.price_modifier ?? 0, image_url: o.image_url,
      })),
    })),
    extras: (p.extras ?? []).map(e => ({ name: e.name, price: e.price })),
    extra_ids: (p.extras_compartidos ?? []).map(e => e.id),
  })
  showProductModal.value = true
}

function closeProductModal() {
  showProductModal.value = false
  editingProduct.value = null
  formError.value = ''
}

function handleImageChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = ev => { imagePreview.value = ev.target?.result as string }
  reader.readAsDataURL(file)
}

function addSection(tipo: SeccionTipo) {
  form.sections.push({
    seccion: tipo.nombre, label: tipo.nombre, required: false,
    multiple: false,
    sort_order: form.sections.length, options: [],
  })
  showAddSection.value = false
}

function removeSection(i: number) { form.sections.splice(i, 1) }
function addOption(si: number) { form.sections[si].options.push({ name: '', price_modifier: 0 }) }
function removeOption(si: number, oi: number) { form.sections[si].options.splice(oi, 1) }

// ── Imagen por opción de personalización ───────────────────
const optionImageInputRef = ref<HTMLInputElement | null>(null)
const uploadingOptionId = ref<number | null>(null)

// ── Galería de fotos generales del producto ────────────────
interface GalleryImage { id: number; image_url: string; sort_order: number }
const galleryImages = ref<GalleryImage[]>([])
const uploadingGallery = ref(false)

async function onGalleryFilesSelected(e: Event) {
  const files = (e.target as HTMLInputElement).files
  if (!files || files.length === 0 || !editingProduct.value) return

  uploadingGallery.value = true
  try {
    const fd = new FormData()
    Array.from(files).forEach(f => fd.append('images[]', f))
    const { data } = await api.post(
      `/admin/products/${editingProduct.value.id}/images`,
      fd,
      { headers: { 'Content-Type': undefined } },
    )
    galleryImages.value = data.data
    await refreshCurrentPage()
  } catch {
    formError.value = 'No se pudieron subir las fotos'
  } finally {
    uploadingGallery.value = false
      ; (e.target as HTMLInputElement).value = ''
  }
}

async function deleteGalleryImage(imageId: number) {
  if (!editingProduct.value) return
  try {
    await api.delete(`/admin/products/${editingProduct.value.id}/images/${imageId}`)
    galleryImages.value = galleryImages.value.filter(img => img.id !== imageId)
    await refreshCurrentPage()
  } catch {
    formError.value = 'No se pudo eliminar la foto'
  }
}
let targetOption: { id?: number; image_url?: string } | null = null

function openOptionImagePicker(opt: { id?: number; image_url?: string }) {
  if (!opt.id) return // sin id (opción recién agregada, sin guardar) no se puede subir foto todavía
  targetOption = opt
  optionImageInputRef.value?.click()
}

async function onOptionImageSelected(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  const opt = targetOption
  if (!file || !opt?.id || !editingProduct.value) return

  uploadingOptionId.value = opt.id
  try {
    const fd = new FormData()
    fd.append('image', file)
    const { data } = await api.post(
      `/admin/products/${editingProduct.value.id}/options/${opt.id}/image`,
      fd,
      { headers: { 'Content-Type': undefined } },
    )
    opt.image_url = data.data.image_url
    await refreshCurrentPage() // refresca la miniatura en la tarjeta del catálogo también
  } catch {
    formError.value = 'No se pudo subir la imagen'
  } finally {
    uploadingOptionId.value = null
    if (optionImageInputRef.value) optionImageInputRef.value.value = ''
  }
}
function addExtra() { form.extras.push({ name: '', price: 0 }) }
function removeExtra(i: number) { form.extras.splice(i, 1) }

async function saveProduct() {
  formError.value = ''
  if (!form.name.trim()) { formError.value = 'El nombre es requerido'; modalTab.value = 'info'; return }
  if (!form.category_id) { formError.value = 'Selecciona una categoría'; modalTab.value = 'info'; return }
  if (!form.price || form.price <= 0) { formError.value = 'Ingresa un precio válido'; modalTab.value = 'info'; return }

  saving.value = true
  try {
    const fd = new FormData()
    fd.append('name', form.name.trim())
    fd.append('description', form.description)
    fd.append('icon', form.icon)
    fd.append('category_id', String(form.category_id))
    fd.append('price', String(form.price))
    fd.append('available', form.available ? '1' : '0')
    fd.append('popular', form.popular ? '1' : '0')
    fd.append('controla_stock', form.controla_stock ? '1' : '0')
    fd.append('stock', String(form.controla_stock ? form.stock : 0))
    if (form.tieneDescuento) {
      fd.append('descuento_tipo', form.descuento_tipo)
      fd.append('descuento_valor', String(form.descuento_valor))
      if (form.descuento_desde) fd.append('descuento_desde', form.descuento_desde)
      if (form.descuento_hasta) fd.append('descuento_hasta', form.descuento_hasta)
    }
    // Si tieneDescuento es false, simplemente no se manda el campo —
    // catalogAttributes() en el backend interpreta su ausencia como
    // "apagar el descuento", sin depender de cómo Laravel trate un
    // string vacío contra la regla in:porcentaje,monto_fijo.

    if (form.sections.length > 0) {
      fd.append('sections', JSON.stringify(
        form.sections.map((s, i) => ({
          id: s.id, seccion: s.seccion, label: s.label, required: s.required,
          multiple: s.multiple, sort_order: i,
          options: s.options.map((o, j) => ({
            id: o.id, name: o.name, price_modifier: o.price_modifier || 0, sort_order: j,
          })),
        }))
      ))
    }
    fd.append('extras', JSON.stringify(
      form.extras.filter(e => e.name.trim()).map((e, i) => ({
        name: e.name.trim(), price: e.price, sort_order: i,
      }))
    ))
    fd.append('extra_ids', JSON.stringify(form.extra_ids))
    if (imageFile.value) fd.append('image', imageFile.value)

    const url = editingProduct.value
      ? `/admin/products/${editingProduct.value.id}/update`
      : '/admin/products'

    await api.post(url, fd, { headers: { 'Content-Type': undefined } })
    await refreshCurrentPage()
    closeProductModal()
  } catch (e: any) {
    const errors = e.response?.data?.errors
    formError.value = errors
      ? Object.values(errors).flat().join(' · ')
      : (e.response?.data?.message ?? 'Error al guardar')
  } finally {
    saving.value = false
  }
}

async function toggleAvailable(product: Product) {
  try {
    await api.post(`/admin/products/${product.id}/toggle`)
    await refreshCurrentPage()
  } catch { }
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/admin/products/${deleteTarget.value.id}`)
    await refreshCurrentPage()
    deleteTarget.value = null
  } catch { deleteTarget.value = null } finally { deleting.value = false }
}
</script>

<style scoped>
.modal-input {
  padding: 0.55rem 0.875rem;
  border-radius: 0.75rem;
  border: 2px solid #f3f4f6;
  background: #f9fafb;
  font-size: 13.5px;
  color: #111827;
  outline: none;
  transition: all 0.2s;
}

.modal-input::placeholder {
  color: #d1d5db;
}

.modal-input:focus {
  border-color: var(--color-brand-primary, #C41E1E);
  background: white;
  box-shadow: 0 0 0 3px rgba(var(--color-brand-primary-rgb, 196, 30, 30), 0.08);
}

.field-label {
  display: block;
  font-size: 10.5px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6b7280;
  margin-bottom: 6px;
}
</style>