<template>
  <div class="flex flex-col gap-5">

    <!-- ══ MODAL CATEGORÍA ══ -->
    <CategoriaModal :show="showCatModal" :editing-cat="editingCat" :cat-form="catForm" :cat-error="catError"
      :saving-cat="savingCat" :root-categories="rootCategories" @close="closeCatModal" @save="saveCat" />

    <!-- ══ MODAL ELIMINAR CATEGORÍA ══ -->
    <ConfirmModal :model-value="!!deleteCatTarget" @update:model-value="deleteCatTarget = null"
      title="¿Eliminar categoría?" :message="`«${deleteCatTarget?.name}» será eliminada permanentemente.`"
      variant="danger" confirm-label="Sí, eliminar" loading-label="Eliminando..." :loading="deletingCat"
      :error="deleteCatError" @confirm="confirmDeleteCat" />

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
                <ProductFormTabInfo v-show="modalTab === 'info'" :form="form"
                  :category-options-tree="categoryOptionsTree" :editing-product="editingProduct" :image-file="imageFile"
                  :image-preview="imagePreview" :gallery-images="galleryImages" :uploading-gallery="uploadingGallery"
                  :preview-precio-final="previewPrecioFinal" @image-change="handleImageChange"
                  @gallery-files-selected="onGalleryFilesSelected" @delete-gallery-image="deleteGalleryImage" />

                <!-- ── Tab Personalización ── -->
                <ProductFormTabPersonalizacion v-show="modalTab === 'personalizacion'" :form="form"
                  :seccion-tipos="seccionTipos" :seccion-tipos-activos="seccionTiposActivos"
                  :show-add-section="showAddSection" :uploading-option-id="uploadingOptionId"
                  @update:show-add-section="showAddSection = $event" @add-section="addSection"
                  @remove-section="removeSection" @add-option="addOption" @remove-option="removeOption"
                  @open-option-image-picker="openOptionImagePicker" />

                <input ref="optionImageInputRef" type="file" accept="image/*" class="hidden"
                  @change="onOptionImageSelected" />

                <!-- ── Tab Extras ── -->
                <ProductFormTabExtras v-show="modalTab === 'extras'" :form="form" :available-extras="availableExtras"
                  @add-extra="addExtra" @remove-extra="removeExtra" @open-extras-manager="openExtrasManager" />

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
    <ConfirmModal :model-value="!!deleteTarget" @update:model-value="deleteTarget = null" title="¿Eliminar producto?"
      :message="`«${deleteTarget?.name}» será eliminado permanentemente.`" variant="danger" confirm-label="Sí, eliminar"
      loading-label="Eliminando..." :loading="deleting" :error="deleteError" @confirm="confirmDelete" />

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
    <SeccionTipoModal :show="showSeccionTipoModal" :editing-seccion-tipo="editingSeccionTipo"
      :seccion-tipo-form="seccionTipoForm" :seccion-tipo-error="seccionTipoError"
      :saving-seccion-tipo="savingSeccionTipo" @close="closeSeccionTipoModal" @save="saveSeccionTipo" />

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
              :class="(product.stock ?? 0) <= 0
                ? 'bg-gray-200 text-gray-600'
                : product.stock_bajo
                  ? 'bg-amber-100 text-amber-700'
                  : 'bg-emerald-100 text-emerald-700'">
              {{ (product.stock ?? 0) <= 0 ? 'Sin stock' : `Stock ${product.stock}` }} </span>
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
          <button @click="deleteTarget = product; deleteError = ''" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[12px] font-semibold
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
    <ExtrasManagerModal :show="showExtrasManager" :available-extras="availableExtras" :new-extra="newExtra"
      :extras-error="extrasError" :saving-extra="savingExtra" @close="showExtrasManager = false"
      @create-extra="createExtra" @save-extra="saveExtra" @delete-extra="deleteExtra" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import {
  PlusIcon, PencilIcon, TrashIcon, XMarkIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon, ExclamationCircleIcon, TagIcon,
  PlusCircleIcon, FolderIcon, ChevronDownIcon,
} from '@heroicons/vue/24/outline'
import { Star, Check, X } from 'lucide-vue-next'
import AppIcon from '@/components/AppIcon.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import { useProductsStore } from '@/stores/products'
import type { Product, Category } from '@/stores/products'
import type {
  FormOption, FormSection, FormExtra, AvailableExtra, SeccionTipo, GalleryImage, ProductForm,
  CategoriaForm, SeccionTipoForm,
} from '@/types/product-form'
import ProductFormTabInfo from '@/components/admin/ProductFormTabInfo.vue'
import ProductFormTabPersonalizacion from '@/components/admin/ProductFormTabPersonalizacion.vue'
import ProductFormTabExtras from '@/components/admin/ProductFormTabExtras.vue'
import ExtrasManagerModal from '@/components/admin/ExtrasManagerModal.vue'
import CategoriaModal from '@/components/admin/CategoriaModal.vue'
import SeccionTipoModal from '@/components/admin/SeccionTipoModal.vue'
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
const deleteError = ref('')
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

const catForm = reactive<CategoriaForm>({
  name: '',
  icon: '',
  parent_id: null,
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
const seccionTipoForm = reactive<SeccionTipoForm>({ nombre: '', icono: 'sparkles' })

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
const form = reactive<ProductForm>({
  name: '', description: '', icon: '',
  category_id: '' as number | '',
  price: 0,
  stock: 0, controla_stock: false, stock_minimo: null as number | null,
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
    stock: 0, controla_stock: false, stock_minimo: null, available: true, popular: false,
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
    stock_minimo: p.stock_minimo ?? null,
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
    if (form.controla_stock && form.stock_minimo !== null) {
      fd.append('stock_minimo', String(form.stock_minimo))
    }
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
  deleteError.value = ''
  try {
    await api.delete(`/admin/products/${deleteTarget.value.id}`)
    await refreshCurrentPage()
    deleteTarget.value = null
  } catch (e: any) {
    deleteError.value = e.response?.data?.message ?? 'No se pudo eliminar el producto'
  } finally {
    deleting.value = false
  }
}
</script>
