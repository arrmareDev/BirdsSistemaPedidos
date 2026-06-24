<template>
  <div class="flex flex-col gap-5">

    <!-- ══ MODAL PRODUCTO ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-250"
        leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showProductModal" class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm
                 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeProductModal">
          <Transition enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-4 opacity-0 sm:scale-95" leave-to-class="translate-y-4 opacity-0">
            <div v-if="showProductModal" class="w-full sm:max-w-2xl bg-white rounded-t-3xl sm:rounded-3xl
                     shadow-2xl flex flex-col overflow-hidden
                     max-h-[95vh] sm:max-h-[90vh]">

              <!-- Header -->
              <div class="flex items-center justify-between px-6 py-4
                          border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-red-50 border border-red-100
                              flex items-center justify-center">
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
                         justify-center cursor-pointer border-none
                         hover:bg-gray-200 transition-colors">
                  <XMarkIcon class="w-4 h-4 text-gray-500" />
                </button>
              </div>

              <!-- Body -->
              <div class="flex-1 overflow-y-auto px-6 py-5 flex flex-col gap-5">

                <!-- Tabs -->
                <div class="flex gap-1 bg-gray-100 p-1 rounded-xl">
                  <button v-for="t in MODAL_TABS" :key="t.value" @click="modalTab = t.value" class="flex-1 flex items-center justify-center gap-1.5
                           py-2 rounded-lg text-[12.5px] font-semibold
                           transition-all duration-150 border-none cursor-pointer" :class="modalTab === t.value
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
                      <input v-model="form.name" placeholder="Ej: Ramo de 12 Rosas Rojas" class="modal-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                      <label class="field-label">Emoji</label>
                      <input v-model="form.emoji" placeholder="🌹" class="modal-input w-20 text-center text-[20px]" />
                    </div>
                  </div>

                  <div class="flex flex-col gap-1.5">
                    <label class="field-label">Descripción</label>
                    <textarea v-model="form.description" placeholder="Breve descripción..." rows="2"
                      class="modal-input resize-none" />
                  </div>

                  <div class="flex flex-col gap-1.5">
                    <label class="field-label">Categoría *</label>
                    <select v-model="form.category_id" class="modal-input">
                      <option value="">Seleccionar categoría...</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.emoji }} {{ cat.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Precio -->
                  <div class="flex flex-col gap-1.5">
                    <label class="field-label">Precio (S/) *</label>
                    <input v-model.number="form.price" type="number" step="0.50" min="0" placeholder="0.00"
                      class="modal-input font-bold" />
                  </div>

                  <!-- ── Atributos florería ── -->
                  <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                      <label class="field-label">Ocasión</label>
                      <input v-model="form.ocasion" list="ocasiones-list" placeholder="Ej: Cumpleaños"
                        class="modal-input" />
                      <datalist id="ocasiones-list">
                        <option v-for="o in OCASIONES_SUGERIDAS" :key="o" :value="o" />
                      </datalist>
                    </div>
                    <div class="flex flex-col gap-1.5">
                      <label class="field-label">Tamaño</label>
                      <input v-model="form.tamano" list="tamanos-list" placeholder="Ej: Mediano" class="modal-input" />
                      <datalist id="tamanos-list">
                        <option v-for="t in TAMANOS_SUGERIDOS" :key="t" :value="t" />
                      </datalist>
                    </div>
                  </div>

                  <!-- Color con swatch -->
                  <div class="flex flex-col gap-1.5">
                    <label class="field-label">Color predominante</label>
                    <div class="flex items-center gap-2">
                      <span v-if="form.color" class="w-9 h-9 rounded-xl shrink-0 border border-black/10"
                        :style="{ background: colorHex(form.color) }" />
                      <input v-model="form.color" list="colores-list" placeholder="Ej: Rojo"
                        class="modal-input flex-1" />
                      <datalist id="colores-list">
                        <option v-for="c in Object.keys(COLOR_MAP)" :key="c" :value="capitalize(c)" />
                      </datalist>
                    </div>
                  </div>

                  <!-- ── Inventario ── -->
                  <div class="flex flex-col gap-2 p-4 rounded-2xl bg-gray-50 border border-gray-200">
                    <button type="button" @click="form.controla_stock = !form.controla_stock" class="flex items-center justify-between cursor-pointer
                             border-none bg-transparent p-0 w-full text-left">
                      <div class="flex items-center gap-2">
                        <ArchiveBoxIcon class="w-4 h-4 text-gray-500" />
                        <span class="text-[13px] font-semibold text-gray-700">
                          Controlar inventario
                        </span>
                      </div>
                      <div class="w-10 h-6 rounded-full transition-colors duration-200 relative shrink-0"
                        :class="form.controla_stock ? 'bg-brand-red' : 'bg-gray-300'">
                        <div class="w-5 h-5 rounded-full bg-white absolute top-0.5
                                    transition-transform duration-200 shadow-sm"
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

                  <!-- Imagen -->
                  <div class="flex flex-col gap-1.5">
                    <label class="field-label">Imagen</label>
                    <label class="flex items-center gap-3 px-4 py-3 rounded-2xl
                                  border-2 border-dashed border-gray-200 cursor-pointer
                                  hover:border-red-300 hover:bg-red-50/20
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

                  <!-- Flags -->
                  <div class="flex flex-col gap-1.5">
                    <label class="field-label">Etiquetas</label>
                    <div class="grid grid-cols-2 gap-2">
                      <button type="button" @click="form.available = !form.available" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl
                               border-2 cursor-pointer text-[13px] font-semibold
                               transition-all duration-150" :class="form.available
                                ? 'border-green-400 bg-green-50 text-green-700'
                                : 'border-gray-200 bg-gray-50 text-gray-400'">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center"
                          :class="form.available ? 'bg-green-500' : 'bg-gray-300'">
                          <CheckIcon v-if="form.available" class="w-2.5 h-2.5 text-white" />
                        </div>
                        Disponible
                      </button>
                      <button type="button" @click="form.popular = !form.popular" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl
                               border-2 cursor-pointer text-[13px] font-semibold
                               transition-all duration-150" :class="form.popular
                                ? 'border-yellow-400 bg-yellow-50 text-yellow-700'
                                : 'border-gray-200 bg-gray-50 text-gray-400'">
                        <span>⭐</span>
                        Popular
                      </button>
                    </div>
                  </div>
                </div>

                <!-- ── Tab Personalización ── -->
                <div v-show="modalTab === 'personalizacion'" class="flex flex-col gap-4">

                  <div class="flex items-center justify-between">
                    <p class="text-[13px] text-gray-500 m-0">
                      Preferencias del cliente — sin costo adicional
                    </p>
                    <button @click="showAddSection = !showAddSection" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl
                             bg-brand-red text-white font-bold text-[12.5px]
                             border-none cursor-pointer hover:bg-red-700
                             transition-all duration-150">
                      <PlusIcon class="w-3.5 h-3.5" />
                      Agregar
                    </button>
                  </div>

                  <Transition enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-2" leave-to-class="opacity-0">
                    <div v-if="showAddSection" class="grid grid-cols-2 gap-2 p-4 rounded-2xl
                             bg-gray-50 border border-gray-200">
                      <button v-for="sec in SECCIONES_DISPONIBLES" :key="sec.value" @click="addSection(sec)"
                        :disabled="form.sections.some(s => s.seccion === sec.value)" class="flex items-center gap-2.5 px-3.5 py-3 rounded-xl
                               border-2 cursor-pointer text-[13px] font-semibold
                               transition-all duration-150" :class="form.sections.some(s => s.seccion === sec.value)
                                ? 'border-gray-100 bg-gray-100 text-gray-400 cursor-not-allowed'
                                : 'border-gray-200 bg-white text-gray-700 hover:border-brand-red hover:text-brand-red'">
                        <span class="text-[18px]">{{ sec.emoji }}</span>
                        {{ sec.label }}
                        <CheckIcon v-if="form.sections.some(s => s.seccion === sec.value)"
                          class="w-3.5 h-3.5 ml-auto text-gray-400" />
                      </button>
                    </div>
                  </Transition>

                  <div v-if="form.sections.length === 0" class="flex items-center gap-2 px-4 py-3.5 rounded-2xl
                           bg-gray-50 border border-dashed border-gray-200">
                    <p class="text-[13px] text-gray-400 m-0">
                      Sin secciones de personalización
                    </p>
                  </div>

                  <div v-for="(section, si) in form.sections" :key="si"
                    class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden">

                    <div class="flex items-center justify-between px-4 py-3
                                border-b border-gray-200 bg-white">
                      <div class="flex items-center gap-2">
                        <span class="text-[18px]">
                          {{SECCIONES_DISPONIBLES.find(s => s.value === section.seccion)?.emoji ?? '🌸'}}
                        </span>
                        <div>
                          <input v-model="section.label" class="font-bold text-[14px] text-gray-900 bg-transparent
                                   border-none outline-none p-0 w-full" />
                          <div class="flex items-center gap-3 mt-0.5">
                            <label class="flex items-center gap-1.5 text-[11px]
                                          text-gray-500 cursor-pointer">
                              <input type="checkbox" v-model="section.required" />
                              Requerido
                            </label>
                            <label class="flex items-center gap-1.5 text-[11px]
                                          text-gray-500 cursor-pointer">
                              <input type="checkbox" v-model="section.multiple" />
                              Múltiple
                            </label>
                          </div>
                        </div>
                      </div>
                      <button @click="removeSection(si)" class="w-7 h-7 rounded-lg flex items-center justify-center
                               text-gray-400 cursor-pointer border-none bg-transparent
                               hover:bg-red-50 hover:text-red-500
                               transition-all duration-150">
                        <TrashIcon class="w-3.5 h-3.5" />
                      </button>
                    </div>

                    <!-- Opciones — solo nombre, sin precio -->
                    <div class="p-4 flex flex-col gap-2">
                      <div v-for="(opt, oi) in section.options" :key="oi" class="flex items-center gap-2">
                        <input v-model="opt.name" placeholder="Ej: Papel kraft" class="modal-input flex-1 py-2" />
                        <button @click="removeOption(si, oi)" class="w-7 h-7 rounded-lg flex items-center justify-center
                                 text-gray-400 cursor-pointer border-none bg-white
                                 hover:bg-red-50 hover:text-red-500
                                 transition-all duration-150 shrink-0">
                          <XMarkIcon class="w-3.5 h-3.5" />
                        </button>
                      </div>
                      <button @click="addOption(si)" class="flex items-center gap-1.5 px-3 py-2 rounded-xl
                               border border-dashed border-gray-300 bg-white
                               text-[12px] font-semibold text-gray-500 cursor-pointer
                               hover:border-brand-red hover:text-brand-red
                               transition-all duration-150 w-fit">
                        <PlusIcon class="w-3.5 h-3.5" />
                        Agregar opción
                      </button>
                    </div>
                  </div>
                </div>

                <!-- ── Tab Extras ── -->
                <div v-show="modalTab === 'extras'" class="flex flex-col gap-4">

                  <div class="flex items-center justify-between">
                    <p class="text-[13px] text-gray-500 m-0">
                      Productos adicionales que el cliente puede agregar con costo
                    </p>
                    <button @click="addExtra" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl
                             bg-brand-red text-white font-bold text-[12.5px]
                             border-none cursor-pointer hover:bg-red-700
                             transition-all duration-150">
                      <PlusIcon class="w-3.5 h-3.5" />
                      Agregar extra
                    </button>
                  </div>

                  <div v-if="form.extras.length === 0" class="flex items-center gap-2 px-4 py-3.5 rounded-2xl
                           bg-gray-50 border border-dashed border-gray-200">
                    <p class="text-[13px] text-gray-400 m-0">
                      Sin extras — ej: Peluche, Chocolates, Globo metálico
                    </p>
                  </div>

                  <div v-for="(extra, i) in form.extras" :key="i" class="flex items-center gap-3 p-3 rounded-2xl
                           bg-gray-50 border border-gray-100">

                    <!-- Nombre del extra -->
                    <input v-model="extra.name" placeholder="Ej: Caja de chocolates"
                      class="modal-input flex-1 font-semibold" />

                    <!-- Precio del extra -->
                    <div class="flex flex-col gap-0.5 shrink-0 w-32">
                      <span class="text-[10px] font-black uppercase tracking-wider
                                   text-gray-400">
                        Precio S/
                      </span>
                      <input v-model.number="extra.price" type="number" step="0.50" min="0" placeholder="0.00"
                        class="modal-input font-bold py-2 w-full" />
                    </div>

                    <!-- Eliminar -->
                    <button @click="removeExtra(i)" class="w-8 h-8 rounded-xl flex items-center justify-center
                             text-gray-400 cursor-pointer border-none bg-white
                             hover:bg-red-50 hover:text-red-500
                             transition-all duration-150 shrink-0 mt-4">
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>

                  <p v-if="form.extras.length > 0" class="text-[11px] text-gray-400 m-0">
                    El cliente verá estos extras en el modal y puede agregarlos al pedido.
                  </p>
                </div>

                <!-- Error -->
                <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                  leave-to-class="opacity-0">
                  <div v-if="formError" class="flex items-center gap-2.5 px-4 py-3 rounded-2xl
                           bg-red-50 border border-red-200">
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
                         text-[13.5px] cursor-pointer border-none
                         hover:bg-red-700 transition-all duration-150
                         disabled:opacity-50 flex items-center justify-center gap-2">
                  <span v-if="saving" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                  <CheckCircleIcon v-else class="w-4 h-4" />
                  {{ saving ? 'Guardando...' : (editingProduct ? 'Guardar' : 'Crear') }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL ELIMINAR ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="deleteTarget" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="deleteTarget = null">
          <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-50 mx-auto mb-5
                        flex items-center justify-center">
              <TrashIcon class="w-7 h-7 text-red-500" />
            </div>
            <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              ¿Eliminar producto?
            </h3>
            <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
              <strong class="text-gray-700">{{ deleteTarget.name }}</strong>
              será eliminado permanentemente.
            </p>
            <div class="flex gap-3">
              <button @click="deleteTarget = null" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white
                       hover:border-gray-300 transition-all duration-150">
                Cancelar
              </button>
              <button @click="confirmDelete" :disabled="deleting" class="flex-1 py-3 rounded-2xl bg-red-600 text-white font-bold
                       text-[13.5px] cursor-pointer border-none
                       hover:bg-red-700 transition-all duration-150
                       disabled:opacity-50 flex items-center justify-center gap-2">
                <span v-if="deleting" class="w-4 h-4 border-2 border-white/30 border-t-white
                         rounded-full animate-spin" />
                {{ deleting ? 'Eliminando...' : 'Sí, eliminar' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ HEADER ══ -->
    <div class="flex items-center justify-between flex-wrap gap-3">
      <p class="text-[13px] text-gray-400 m-0">
        {{ productsStore.products.length }} productos ·
        {{ categories.length }} categorías
      </p>

      <div class="flex items-center gap-2 flex-wrap">
        <button @click="activeCat = ''" class="px-3.5 py-1.5 rounded-full text-[12.5px] font-semibold
                 border transition-all duration-150 cursor-pointer" :class="activeCat === ''
                  ? 'bg-brand-red text-white border-brand-red'
                  : 'bg-white border-gray-200 text-gray-600 hover:border-red-300'">
          Todos
        </button>
        <button v-for="cat in categories" :key="cat.id" @click="activeCat = String(cat.id)" class="px-3.5 py-1.5 rounded-full text-[12.5px] font-semibold
                 border transition-all duration-150 cursor-pointer" :class="activeCat === String(cat.id)
                  ? 'bg-brand-red text-white border-brand-red'
                  : 'bg-white border-gray-200 text-gray-600 hover:border-red-300'">
          {{ cat.emoji }} {{ cat.name }}
        </button>

        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
          <input v-model="search" placeholder="Buscar..." class="pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 bg-white
                   text-[13px] text-gray-900 outline-none w-40
                   focus:border-brand-red transition-all duration-200
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

      <div v-for="product in filteredProducts" :key="product.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden
               flex flex-col transition-all duration-200
               hover:shadow-md hover:-translate-y-0.5" :class="!product.available ? 'opacity-60' : ''">

        <!-- Imagen -->
        <div class="relative h-40 bg-gradient-to-br from-rose-50 via-pink-50 to-emerald-50
                    flex items-center justify-center overflow-hidden">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
            class="w-full h-full object-cover" />
          <span v-else class="text-5xl">{{ product.emoji || '💐' }}</span>

          <div class="absolute top-2 left-2 flex flex-col gap-1">
            <span v-if="product.popular" class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full
                     bg-yellow-400 text-yellow-900 w-fit">
              ⭐ Popular
            </span>
            <span v-if="product.controla_stock" class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full w-fit"
              :class="(product.stock ?? 0) > 0
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-gray-200 text-gray-600'">
              {{ (product.stock ?? 0) > 0 ? `Stock ${product.stock}` : 'Sin stock' }}
            </span>
          </div>

          <button @click="toggleAvailable(product)" class="absolute top-2 right-2 px-2 py-1 rounded-lg text-[10px]
                   font-bold border cursor-pointer transition-all duration-150" :class="product.available
                    ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                    : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'">
            {{ product.available ? '✓' : '✗' }}
          </button>

          <div v-if="!product.available" class="absolute inset-0 bg-gray-900/30 flex items-center justify-center">
            <span class="bg-white text-gray-700 text-[11px] font-black uppercase
                         px-3 py-1 rounded-full">Agotado</span>
          </div>
        </div>

        <!-- Info -->
        <div class="p-3.5 flex flex-col gap-1 flex-1">
          <p class="font-bold text-[13.5px] text-gray-900 m-0 leading-snug line-clamp-2">
            {{ product.name }}
          </p>
          <p class="text-[11px] text-gray-400 m-0 line-clamp-1">
            {{ product.category?.name ?? '—' }}
          </p>

          <!-- Badges -->
          <div class="flex flex-wrap gap-1 mt-1">
            <span v-if="product.ocasion" class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full
                     bg-rose-50 text-rose-600 border border-rose-100">
              {{ product.ocasion }}
            </span>
            <span v-if="product.color" class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full
                     bg-gray-50 text-gray-600 border border-gray-100 inline-flex items-center gap-1">
              <span class="w-2 h-2 rounded-full border border-black/10"
                :style="{ background: colorHex(product.color) }" />
              {{ product.color }}
            </span>
            <span v-if="product.tamano" class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full
                     bg-blue-50 text-blue-600 border border-blue-100">
              {{ product.tamano }}
            </span>
            <span v-if="product.customization_sections?.length" class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full
                     bg-purple-50 text-purple-600 border border-purple-100">
              {{ product.customization_sections.length }} secciones
            </span>
            <span v-if="product.extras?.length" class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full
                     bg-green-50 text-green-600 border border-green-100">
              {{ product.extras.length }} extras
            </span>
          </div>

          <!-- Precio -->
          <div class="flex items-baseline gap-0.5 mt-2">
            <span class="text-[10px] font-bold text-gray-400">S/</span>
            <span class="font-black text-[20px] text-brand-red leading-none"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              {{ product.price.toFixed(2) }}
            </span>
          </div>
        </div>

        <!-- Acciones -->
        <div class="flex border-t border-gray-100">
          <button @click="openEdit(product)" class="flex-1 flex items-center justify-center gap-1.5 py-2.5
                   text-[12px] font-semibold text-gray-600 cursor-pointer
                   border-none bg-transparent hover:bg-gray-50
                   hover:text-brand-red transition-all duration-150">
            <PencilIcon class="w-3.5 h-3.5" />
            Editar
          </button>
          <div class="w-px bg-gray-100" />
          <button @click="deleteTarget = product" class="flex-1 flex items-center justify-center gap-1.5 py-2.5
                   text-[12px] font-semibold text-gray-400 cursor-pointer
                   border-none bg-transparent hover:bg-red-50
                   hover:text-red-600 transition-all duration-150">
            <TrashIcon class="w-3.5 h-3.5" />
            Eliminar
          </button>
        </div>
      </div>

      <!-- Card nuevo -->
      <button @click="openCreate" class="h-64 rounded-2xl border-2 border-dashed border-gray-200
               flex flex-col items-center justify-center gap-3 cursor-pointer
               bg-transparent hover:border-red-300 hover:bg-red-50/30
               transition-all duration-200 group">
        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center
                    group-hover:bg-red-100 transition-colors">
          <PlusIcon class="w-6 h-6 text-gray-400 group-hover:text-red-500" />
        </div>
        <span class="text-[13px] font-semibold text-gray-400
                     group-hover:text-red-500 transition-colors">
          Nuevo Producto
        </span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import {
  PlusIcon, PencilIcon, TrashIcon, XMarkIcon,
  MagnifyingGlassIcon, PhotoIcon, CheckIcon,
  CheckCircleIcon, ExclamationCircleIcon, TagIcon,
  PlusCircleIcon, ArchiveBoxIcon,
} from '@heroicons/vue/24/outline'
import { useProductsStore } from '@/stores/products'
import type { Product, Category } from '@/stores/products'
import api from '@/utils/api'

// ── Store ─────────────────────────────────────────────────
const productsStore = useProductsStore()

// ── Estado ────────────────────────────────────────────────
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

// ── Constantes ────────────────────────────────────────────
const MODAL_TABS = [
  { value: 'info', label: 'Info', icon: TagIcon },
  { value: 'personalizacion', label: 'Personalización', icon: CheckCircleIcon },
  { value: 'extras', label: 'Extras', icon: PlusCircleIcon },
]

// Secciones de personalización para florería
const SECCIONES_DISPONIBLES = [
  { value: 'envoltura', label: 'Envoltura', emoji: '🎁' },
  { value: 'lazo', label: 'Lazo / Cinta', emoji: '🎀' },
  { value: 'follaje', label: 'Follaje', emoji: '🌿' },
  { value: 'dedicatoria', label: 'Dedicatoria', emoji: '✍️' },
  { value: 'presentacion', label: 'Presentación', emoji: '🪴' },
  { value: 'complemento', label: 'Complemento', emoji: '🧸' },
]

// Sugerencias (datalist) — no restrictivas
const OCASIONES_SUGERIDAS = [
  'Cumpleaños', 'Aniversario', 'Amor', 'Condolencias',
  'Graduación', 'Día de la Madre', 'San Valentín',
  'Agradecimiento', 'Felicitaciones', 'Recuperación',
]
const TAMANOS_SUGERIDOS = ['Pequeño', 'Mediano', 'Grande', 'Premium']

// Mapa de colores → hex para swatches
const COLOR_MAP: Record<string, string> = {
  rojo: '#DC2626', rosa: '#EC4899', rosado: '#F472B6', blanco: '#F9FAFB',
  amarillo: '#FACC15', naranja: '#FB923C', morado: '#9333EA', lila: '#C084FC',
  azul: '#3B82F6', celeste: '#7DD3FC', verde: '#22C55E', fucsia: '#D946EF',
  durazno: '#FDBA74', coral: '#FF7F6B', crema: '#FEF3C7', vino: '#7F1D1D',
}

function colorHex(name: string): string {
  return COLOR_MAP[name.trim().toLowerCase()] ?? '#D1D5DB'
}

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

// ── Form ──────────────────────────────────────────────────
interface FormOption {
  name: string
}

interface FormSection {
  seccion: string
  label: string
  required: boolean
  multiple: boolean
  sort_order: number
  options: FormOption[]
}

interface FormExtra {
  name: string
  price: number
}

const form = reactive({
  name: '',
  description: '',
  emoji: '',
  category_id: '' as number | '',
  price: 0,
  ocasion: '',
  color: '',
  tamano: '',
  stock: 0,
  controla_stock: false,
  available: true,
  popular: false,
  sections: [] as FormSection[],
  extras: [] as FormExtra[],
})

// ── Computed ──────────────────────────────────────────────
const filteredProducts = computed(() => {
  let list = productsStore.products

  if (activeCat.value) {
    list = list.filter(p => String(p.category?.id) === activeCat.value)
  }
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(p =>
      p.name.toLowerCase().includes(q) ||
      (p.description ?? '').toLowerCase().includes(q) ||
      (p.ocasion ?? '').toLowerCase().includes(q)
    )
  }
  return list
})

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  await productsStore.fetchAdmin()
  const { data } = await api.get('/admin/categories')
  categories.value = data.data
})

// ── Modal crear ───────────────────────────────────────────
function openCreate() {
  editingProduct.value = null
  imageFile.value = null
  imagePreview.value = null
  formError.value = ''
  showAddSection.value = false
  modalTab.value = 'info'
  Object.assign(form, {
    name: '',
    description: '',
    emoji: '',
    category_id: '',
    price: 0,
    ocasion: '',
    color: '',
    tamano: '',
    stock: 0,
    controla_stock: false,
    available: true,
    popular: false,
    sections: [],
    extras: [],
  })
  showProductModal.value = true
}

// ── Modal editar ──────────────────────────────────────────
function openEdit(p: Product) {
  editingProduct.value = p
  imageFile.value = null
  imagePreview.value = null
  formError.value = ''
  showAddSection.value = false
  modalTab.value = 'info'
  Object.assign(form, {
    name: p.name,
    description: p.description ?? '',
    emoji: p.emoji ?? '',
    category_id: p.category?.id ?? '',
    price: p.price,
    ocasion: p.ocasion ?? '',
    color: p.color ?? '',
    tamano: p.tamano ?? '',
    stock: p.stock ?? 0,
    controla_stock: p.controla_stock ?? false,
    available: p.available,
    popular: p.popular,
    sections: (p.customization_sections ?? []).map(s => ({
      seccion: s.seccion,
      label: s.label,
      required: s.required,
      multiple: s.multiple,
      sort_order: 0,
      options: s.options.map(o => ({
        name: o.name,
      })),
    })),
    extras: (p.extras ?? []).map(e => ({
      name: e.name,
      price: e.price,
    })),
  })
  showProductModal.value = true
}

function closeProductModal() {
  showProductModal.value = false
  editingProduct.value = null
  formError.value = ''
}

// ── Imagen ────────────────────────────────────────────────
function handleImageChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = ev => { imagePreview.value = ev.target?.result as string }
  reader.readAsDataURL(file)
}

// ── Secciones ─────────────────────────────────────────────
function addSection(sec: { value: string; label: string }) {
  form.sections.push({
    seccion: sec.value,
    label: sec.label,
    required: false,
    multiple: sec.value === 'follaje' || sec.value === 'complemento',
    sort_order: form.sections.length,
    options: [],
  })
  showAddSection.value = false
}

function removeSection(i: number) { form.sections.splice(i, 1) }

function addOption(si: number) {
  form.sections[si].options.push({ name: '' })
}

function removeOption(si: number, oi: number) {
  form.sections[si].options.splice(oi, 1)
}

// ── Extras ────────────────────────────────────────────────
function addExtra() {
  form.extras.push({ name: '', price: 0 })
}

function removeExtra(i: number) {
  form.extras.splice(i, 1)
}

// ── Guardar ───────────────────────────────────────────────
async function saveProduct() {
  formError.value = ''

  if (!form.name.trim()) {
    formError.value = 'El nombre es requerido'
    modalTab.value = 'info'
    return
  }
  if (!form.category_id) {
    formError.value = 'Selecciona una categoría'
    modalTab.value = 'info'
    return
  }
  if (!form.price || form.price <= 0) {
    formError.value = 'Ingresa un precio válido'
    modalTab.value = 'info'
    return
  }

  saving.value = true
  try {
    const fd = new FormData()
    fd.append('name', form.name.trim())
    fd.append('description', form.description)
    fd.append('emoji', form.emoji)
    fd.append('category_id', String(form.category_id))
    fd.append('price', String(form.price))
    fd.append('available', form.available ? '1' : '0')
    fd.append('popular', form.popular ? '1' : '0')

    // ── Campos florería ──
    fd.append('ocasion', form.ocasion.trim())
    fd.append('color', form.color.trim())
    fd.append('tamano', form.tamano.trim())
    fd.append('controla_stock', form.controla_stock ? '1' : '0')
    fd.append('stock', String(form.controla_stock ? form.stock : 0))

    if (form.sections.length > 0) {
      fd.append('sections', JSON.stringify(
        form.sections.map((s, i) => ({
          seccion: s.seccion,
          label: s.label,
          required: s.required,
          multiple: s.multiple,
          sort_order: i,
          options: s.options.map((o, j) => ({
            name: o.name,
            sort_order: j,
          })),
        }))
      ))
    }

    fd.append('extras', JSON.stringify(
      form.extras
        .filter(e => e.name.trim())
        .map((e, i) => ({
          name: e.name.trim(),
          price: e.price,
          sort_order: i,
        }))
    ))

    if (imageFile.value) fd.append('image', imageFile.value)

    const url = editingProduct.value
      ? `/admin/products/${editingProduct.value.id}/update`
      : '/admin/products'

    await api.post(url, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    await productsStore.fetchAdmin()
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

// ── Toggle disponible ─────────────────────────────────────
async function toggleAvailable(product: Product) {
  try {
    await api.post(`/admin/products/${product.id}/toggle`)
    await productsStore.fetchAdmin()
  } catch { }
}

// ── Eliminar ──────────────────────────────────────────────
async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/admin/products/${deleteTarget.value.id}`)
    await productsStore.fetchAdmin()
    deleteTarget.value = null
  } catch {
    deleteTarget.value = null
  } finally {
    deleting.value = false
  }
}
</script>

<style scoped>
.modal-input {
  width: 100%;
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
  border-color: #C41E1E;
  background: white;
  box-shadow: 0 0 0 3px rgba(196, 30, 30, 0.08);
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