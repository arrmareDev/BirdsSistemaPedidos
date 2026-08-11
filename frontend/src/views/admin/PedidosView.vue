<template>
  <div class="flex gap-4 h-[calc(100vh-110px)]">

    <!-- ══ CUSTOMIZER MODAL ══ -->
    <CustomizerModal ref="customizerRef" />

    <!-- ══ MODAL NUEVO PEDIDO ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-250"
        leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="showModal" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm
                 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeModal">
          <Transition enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200"
            enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
            leave-to-class="translate-y-4 opacity-0">
            <div v-if="showModal" class="w-full sm:max-w-5xl bg-white rounded-t-3xl sm:rounded-3xl
                     shadow-2xl border-0 sm:border sm:border-gray-100
                     flex flex-col overflow-hidden max-h-[95vh] sm:max-h-[90vh]">

              <!-- Header modal -->
              <div class="relative flex items-center justify-between
                          px-5 sm:px-6 py-4 border-b border-gray-100 shrink-0 bg-white">
                <div class="absolute top-2 left-1/2 -translate-x-1/2 w-10 h-1 rounded-full bg-gray-200 sm:hidden" />
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center shrink-0">
                    <ClipboardDocumentListIcon class="w-5 h-5 text-brand-red" />
                  </div>
                  <div>
                    <h2 class="font-black text-[17px] text-gray-900 m-0 leading-none"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      {{ isEditMode ? `Editar Pedido #${editingOrderId}` : 'Nuevo Pedido' }}
                    </h2>
                    <p class="text-[11px] text-gray-400 m-0 mt-0.5">
                      {{ cartItems.length > 0
                        ? `${cartItems.length} productos · S/ ${orderTotal.toFixed(2)}`
                        : 'Registrar pedido manual' }}
                    </p>
                  </div>
                </div>
                <button @click="closeModal" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
                         justify-center cursor-pointer border-none hover:bg-gray-200 transition-colors shrink-0">
                  <XMarkIcon class="w-4 h-4 text-gray-500" />
                </button>
              </div>

              <!-- Body modal -->
              <div class="flex flex-col lg:flex-row flex-1 min-h-0 overflow-hidden">

                <!-- Col izquierda — datos del pedido -->
                <div v-if="!isEditMode"
                  class="lg:w-72 xl:w-80 shrink-0 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-100 bg-white">
                  <div class="flex-1 overflow-y-auto p-4 sm:p-5 flex flex-col gap-4">

                    <!-- Tipo de pedido — solo recoger/delivery -->
                    <div>
                      <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-2">
                        Tipo de pedido
                      </label>
                      <div class="grid grid-cols-3 gap-2">
                        <button v-for="t in ORDER_TYPES" :key="t.id" @click="form.type = t.id as any" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl border-2
                                 text-[11.5px] font-bold cursor-pointer transition-all duration-150" :class="form.type === t.id
                                  ? 'border-brand-red bg-red-50 text-brand-red shadow-sm'
                                  : 'border-gray-100 bg-gray-50 text-gray-500 hover:border-red-200'">
                          <component :is="t.icon" class="w-5 h-5" />
                          {{ t.label }}
                        </button>
                      </div>
                    </div>

                    <!-- Nombre + Teléfono (teléfono no aplica a pedidos Local) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
                      <div>
                        <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                          Nombre *
                        </label>
                        <input v-model="form.client_name" placeholder="Nombre del cliente" class="modal-input" />
                      </div>
                      <div v-if="form.type !== 'local'">
                        <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                          Teléfono *
                        </label>
                        <input v-model="form.client_phone" placeholder="987 654 321" type="tel" class="modal-input" />
                      </div>
                    </div>

                    <!-- Campo mesa (solo local) -->
                    <Transition enter-active-class="transition-all duration-200"
                      enter-from-class="opacity-0 -translate-y-1" leave-active-class="transition-all duration-150"
                      leave-to-class="opacity-0">
                      <div v-if="form.type === 'local'">
                        <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                          Número de mesa *
                        </label>
                        <input v-model="form.mesa" placeholder="Ej: 4" class="modal-input" />
                      </div>
                    </Transition>

                    <!-- Campos delivery -->
                    <Transition enter-active-class="transition-all duration-200"
                      enter-from-class="opacity-0 -translate-y-1" leave-active-class="transition-all duration-150"
                      leave-to-class="opacity-0">
                      <div v-if="form.type === 'delivery'" class="flex flex-col gap-3">

                        <!-- Botón GPS -->
                        <button @click="usarGPS" :disabled="loadingGPS" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl
                                 border-2 border-brand-red/30 bg-brand-red/5 text-brand-red
                                 font-bold text-[12px] cursor-pointer transition-all duration-150
                                 hover:bg-brand-red/10 disabled:opacity-50 disabled:cursor-not-allowed">
                          <span v-if="loadingGPS"
                            class="w-3.5 h-3.5 border-2 border-brand-red/30 border-t-brand-red rounded-full animate-spin" />
                          <MapPinIcon v-else class="w-3.5 h-3.5" />
                          {{ loadingGPS ? 'Obteniendo ubicación...' : 'Usar mi ubicación (GPS)' }}
                        </button>

                        <div v-if="gpsError" class="flex items-center gap-1.5 px-2.5 py-2 rounded-xl
                                 bg-red-50 border border-red-200 text-[11px] text-red-600">
                          {{ gpsError }}
                        </div>

                        <!-- Búsqueda por texto -->
                        <div class="relative">
                          <input v-model="mapSearch" @input="debouncedMapSearch" placeholder="Buscar dirección..."
                            class="modal-input w-full pr-8" />
                          <div v-if="mapSearching" class="absolute right-2.5 top-1/2 -translate-y-1/2
                                   w-3.5 h-3.5 border-2 border-gray-200 border-t-brand-red rounded-full animate-spin" />
                        </div>

                        <div v-if="mapResults.length > 0"
                          class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden -mt-2">
                          <button v-for="r in mapResults" :key="r.place_id" @click="selectMapResult(r)" class="w-full text-left px-3 py-2 text-[11.5px] text-gray-700
                                   border-none bg-transparent cursor-pointer
                                   hover:bg-gray-50 border-b border-gray-100
                                   last:border-b-0 transition-colors duration-150">
                            {{ r.display_name }}
                          </button>
                        </div>

                        <!-- Mapa Leaflet -->
                        <div id="admin-delivery-map" class="w-full h-48 rounded-xl overflow-hidden border-2 border-gray-100" />

                        <div>
                          <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                            Dirección
                          </label>
                          <input v-model="form.address" placeholder="Se completa al marcar el mapa" class="modal-input" />
                        </div>
                        <div>
                          <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                            Referencia
                          </label>
                          <input v-model="form.reference" placeholder="Portón azul, frente al parque..."
                            class="modal-input" />
                        </div>

                        <!-- Zona detectada -->
                        <div v-if="detectingZone" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 border border-gray-200">
                          <div class="w-3.5 h-3.5 border-2 border-gray-300 border-t-brand-red rounded-full animate-spin" />
                          <span class="text-[11px] text-gray-500 font-medium">Detectando zona...</span>
                        </div>
                        <div v-else-if="selectedZone"
                          class="flex items-center justify-between px-3 py-2 rounded-xl bg-pink-50 border border-pink-200">
                          <span class="text-[11px] font-black text-pink-700">{{ selectedZone.nombre }}</span>
                          <span class="text-[13px] font-black text-pink-700">S/ {{ selectedZone.precio.toFixed(2) }}</span>
                        </div>
                        <div v-else-if="zoneNotFound" class="flex flex-col gap-1.5">
                          <div class="px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-700">
                            Fuera de cobertura — elige la tarifa más cercana
                          </div>
                          <select v-model="form.delivery_zone_id" @change="onManualZoneChange" class="modal-input">
                            <option value="">
                              {{ loadingZones ? 'Cargando...' : 'Seleccionar tarifa...' }}
                            </option>
                            <option v-for="z in zones" :key="z.id" :value="z.id">
                              {{ z.nombre }} — S/ {{ z.precio.toFixed(2) }}
                            </option>
                          </select>
                        </div>
                      </div>
                    </Transition>

                    <!-- Entrega programada -->
                    <div v-if="pedidoConfigStore.config.entrega_programada_activo" class="border border-gray-100 rounded-2xl p-3 flex flex-col gap-3">
                      <div class="flex items-center justify-between">
                        <label class="text-[10.5px] font-black uppercase tracking-widest text-gray-400">
                          {{ pedidoConfigStore.config.entrega_programada_label }}
                        </label>
                        <button @click="form.entrega_programada = !form.entrega_programada"
                          class="relative w-10 h-5 rounded-full transition-all duration-200 border-none cursor-pointer"
                          :class="form.entrega_programada ? 'bg-brand-red' : 'bg-gray-200'">
                          <span
                            class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-all duration-200"
                            :class="form.entrega_programada ? 'left-5' : 'left-0.5'" />
                        </button>
                      </div>
                      <Transition enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                        <div v-if="form.entrega_programada" class="flex flex-col gap-2">
                          <div>
                            <label
                              class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                              Fecha *
                            </label>
                            <input v-model="form.fecha_entrega" type="date" :min="fechaMinima" class="modal-input" />
                          </div>
                          <div>
                            <label
                              class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                              Hora
                            </label>
                            <select v-model="form.hora_entrega" class="modal-input">
                              <option value="">Cualquier hora</option>
                              <option v-for="h in HORARIOS" :key="h.value" :value="h.value">{{ h.label }}</option>
                            </select>
                          </div>
                        </div>
                      </Transition>
                    </div>

                    <!-- Mensaje personalizado -->
                    <div v-if="pedidoConfigStore.config.mensaje_activo">
                      <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                        {{ pedidoConfigStore.config.mensaje_label }}
                      </label>
                      <textarea v-model="form.mensaje_tarjeta" placeholder="Ej: ¡Feliz cumpleaños! Con cariño..."
                        rows="2" maxlength="300" class="modal-input resize-none" />
                    </div>

                    <!-- Nota -->
                    <div>
                      <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                        Nota adicional
                      </label>
                      <textarea v-model="form.note" placeholder="Indicaciones adicionales..." rows="2"
                        class="modal-input resize-none" />
                    </div>
                  </div>

                  <!-- Footer modal -->
                  <div class="p-4 sm:p-5 border-t border-gray-100 bg-white shrink-0">
                    <div v-if="cartItems.length > 0" class="bg-gray-50 rounded-2xl p-3.5 mb-3 border border-gray-100">
                      <div class="flex justify-between text-[12px] text-gray-400 mb-2">
                        <span>{{ cartItems.length }} producto{{ cartItems.length !== 1 ? 's' : '' }}</span>
                        <span>S/ {{ orderTotal.toFixed(2) }}</span>
                      </div>
                      <div v-if="form.type === 'delivery' && deliveryFeeAmount > 0" class="flex justify-between text-[12px] text-gray-400 mb-2">
                        <span>Delivery{{ selectedZone ? ` (${selectedZone.nombre})` : '' }}</span>
                        <span>+ S/ {{ deliveryFeeAmount.toFixed(2) }}</span>
                      </div>
                      <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="font-semibold text-[14px] text-gray-700">Total</span>
                        <div class="flex items-baseline gap-1">
                          <span class="text-[12px] font-semibold text-gray-400">S/</span>
                          <span class="font-black text-[24px] text-brand-red leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ totalConDelivery.toFixed(2) }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div v-else
                      class="flex items-center gap-2 px-3.5 py-3 rounded-2xl bg-amber-50 border border-amber-200 mb-3">
                      <ExclamationTriangleIcon class="w-4 h-4 text-amber-500 shrink-0" />
                      <p class="text-[12px] text-amber-700 m-0 font-medium">Agrega productos desde el catálogo</p>
                    </div>

                    <Transition enter-active-class="transition-all duration-150"
                      enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                      <div v-if="modalError" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                               text-[12px] text-red-600 mb-3 flex items-center gap-2">
                        <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
                        {{ modalError }}
                      </div>
                    </Transition>

                    <button @click="submitOrder" :disabled="!canSubmit || submitting" class="w-full py-4 rounded-2xl font-black text-[14px] text-white
                             border-none cursor-pointer transition-all duration-200 uppercase tracking-wide bg-brand-red
                             shadow-red-md hover:bg-red-700 hover:-translate-y-0.5
                             active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed
                             disabled:hover:translate-y-0 flex items-center justify-center gap-2"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      <span v-if="submitting"
                        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      <CheckCircleIcon v-else class="w-4 h-4" />
                      {{ submitting ? 'Registrando...' : `Confirmar · S/ ${totalConDelivery.toFixed(2)}` }}
                    </button>
                  </div>
                </div>
                <div v-if="isEditMode" class="lg:w-72 xl:w-80 shrink-0 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-100 bg-white">
                  <div class="flex-1 overflow-y-auto p-4 sm:p-5">
                    <p class="text-[13px] text-gray-500 leading-relaxed">
                      Estás editando los productos del pedido <strong class="text-gray-900">#{{ editingOrderId }}</strong>.
                      Los datos del cliente y entrega no se modifican aquí.
                    </p>
                  </div>
                  <div class="p-4 sm:p-5 border-t border-gray-100 bg-white shrink-0">
                    <div v-if="cartItems.length > 0" class="bg-gray-50 rounded-2xl p-3.5 mb-3 border border-gray-100">
                      <div class="flex justify-between items-center">
                        <span class="font-semibold text-[14px] text-gray-700">Nuevo total</span>
                        <div class="flex items-baseline gap-1">
                          <span class="text-[12px] font-semibold text-gray-400">S/</span>
                          <span class="font-black text-[24px] text-brand-red leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ orderTotal.toFixed(2) }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <Transition enter-active-class="transition-all duration-150"
                      enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                      <div v-if="modalError" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                               text-[12px] text-red-600 mb-3 flex items-center gap-2">
                        <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
                        {{ modalError }}
                      </div>
                    </Transition>
                    <button @click="submitOrder" :disabled="!canSubmit || submitting" class="w-full py-4 rounded-2xl font-black text-[14px] text-white
                             border-none cursor-pointer transition-all duration-200 uppercase tracking-wide bg-brand-red
                             shadow-red-md hover:bg-red-700 hover:-translate-y-0.5
                             active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed
                             disabled:hover:translate-y-0 flex items-center justify-center gap-2"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      <span v-if="submitting"
                        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      <CheckCircleIcon v-else class="w-4 h-4" />
                      {{ submitting ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                  </div>
                </div>

                <!-- Col derecha: catálogo + carrito -->
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden bg-gray-50/40">

                  <!-- Tabs -->
                  <div class="flex border-b border-gray-100 shrink-0 bg-white">
                    <button @click="rightTab = 'catalogo'" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-[13px] font-semibold
                             border-none cursor-pointer transition-all duration-150"
                      :class="rightTab === 'catalogo' ? 'bg-white text-brand-red border-b-2 border-brand-red' : 'bg-gray-50 text-gray-400 hover:text-gray-700'">
                      <TagIcon class="w-4 h-4" />
                      Catálogo
                    </button>
                    <button @click="rightTab = 'carrito'" class="flex-1 flex items-center justify-center gap-2 py-3.5 text-[13px] font-semibold
                             border-none cursor-pointer transition-all duration-150 relative"
                      :class="rightTab === 'carrito' ? 'bg-white text-brand-red border-b-2 border-brand-red' : 'bg-gray-50 text-gray-400 hover:text-gray-700'">
                      <ShoppingCartIcon class="w-4 h-4" />
                      Carrito
                      <span v-if="cartItems.length > 0"
                        class="inline-flex w-5 h-5 rounded-full bg-brand-red text-white text-[10px] font-black items-center justify-center shadow-sm">
                        {{ cartItems.length }}
                      </span>
                    </button>
                  </div>

                  <!-- CATÁLOGO -->
                  <div v-if="rightTab === 'catalogo'" class="flex-1 overflow-y-auto flex flex-col">
                    <div
                      class="flex gap-2 overflow-x-auto px-4 py-3 border-b border-gray-100 bg-white scrollbar-none shrink-0">
                      <button @click="activeCat = 'all'" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold
                               border whitespace-nowrap shrink-0 cursor-pointer transition-all duration-150" :class="activeCat === 'all'
                                ? 'bg-brand-red text-white border-brand-red shadow-sm'
                                : 'bg-white border-gray-200 text-gray-500 hover:border-red-300 hover:text-brand-red'">
                        <LayoutGrid :size="13" /> Todo
                      </button>
                      <button v-for="cat in productsStore.categories" :key="cat.id" @click="activeCat = cat.slug" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold
                               border whitespace-nowrap shrink-0 cursor-pointer transition-all duration-150" :class="activeCat === cat.slug
                                ? 'bg-brand-red text-white border-brand-red shadow-sm'
                                : 'bg-white border-gray-200 text-gray-500 hover:border-red-300 hover:text-brand-red'">
                        <AppIcon :name="cat.icon" :size="13" /> {{ cat.name }}
                      </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                      <div v-if="filteredCatalog.length === 0"
                        class="flex flex-col items-center py-16 text-gray-400 gap-2">
                        <PackageSearch :size="36" />
                        <p class="m-0 text-[13px]">Sin productos en esta categoría</p>
                      </div>
                      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        <div v-for="p in filteredCatalog" :key="p.id" @click="p.available && openProduct(p)"
                          class="bg-white rounded-2xl border overflow-hidden transition-all duration-200 group" :class="p.available
                            ? 'border-gray-100 cursor-pointer hover:border-red-200 hover:shadow-sm hover:-translate-y-0.5'
                            : 'border-gray-100 opacity-50 cursor-not-allowed'">
                          <div class="relative h-24 bg-gray-50 flex items-center justify-center overflow-hidden">
                            <img v-if="p.image_url" :src="p.image_url"
                              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <AppIcon v-else :name="p.icon" :size="36" class="text-gray-300" />
                            <div v-if="p.popular"
                              class="absolute top-2 right-0 bg-pink-400 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-l-md">
                              Popular
                            </div>
                            <div v-if="p.controla_stock && p.stock > 0 && p.stock <= 5"
                              class="absolute bottom-1 left-1 bg-amber-400 text-amber-900 text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md">
                              Últimos {{ p.stock }}
                            </div>
                            <div v-if="!p.available"
                              class="absolute inset-0 bg-white/70 flex items-center justify-center">
                              <span class="text-[10px] font-black text-gray-500 uppercase">Agotado</span>
                            </div>
                          </div>
                          <div class="p-3">
                            <p class="font-semibold text-[13px] text-gray-900 m-0 leading-snug mb-1">{{ p.name }}</p>
                            <div class="flex items-center justify-between">
                              <div class="flex items-baseline gap-0.5">
                                <span class="text-[11px] font-semibold text-gray-400">S/</span>
                                <span class="font-black text-[17px] text-brand-red leading-none"
                                  style="font-family:'Plus Jakarta Sans',sans-serif;">
                                  {{ p.price.toFixed(2) }}
                                </span>
                              </div>
                              <div v-if="p.available"
                                class="w-7 h-7 rounded-full bg-brand-red text-white text-xl flex items-center justify-center font-black shadow-sm hover:bg-red-700 transition-colors leading-none">
                                +
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- CARRITO -->
                  <div v-if="rightTab === 'carrito'" class="flex-1 overflow-y-auto flex flex-col">
                    <div v-if="cartItems.length === 0"
                      class="flex-1 flex flex-col items-center justify-center py-16 text-gray-400 gap-4">
                      <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <ShoppingCartIcon class="w-8 h-8 text-gray-300" />
                      </div>
                      <div class="text-center">
                        <p class="font-bold text-gray-600 text-[14px] m-0">Carrito vacío</p>
                        <p class="text-[12.5px] m-0 mt-1">Agrega productos del catálogo</p>
                      </div>
                      <button @click="rightTab = 'catalogo'" class="px-5 py-2.5 rounded-xl bg-brand-red text-white font-bold text-[13px]
                               border-none cursor-pointer hover:bg-red-700 shadow-sm transition-all duration-150">
                        Ver catálogo →
                      </button>
                    </div>

                    <div v-else class="p-4 flex flex-col gap-2.5">
                      <TransitionGroup name="cart-item">
                        <div v-for="item in cartItems" :key="item._uid"
                          class="flex items-start gap-3 p-3.5 rounded-2xl bg-white border border-gray-100 shadow-sm">
                          <div class="w-12 h-12 rounded-xl bg-gray-50 shrink-0 flex items-center justify-center
                                      text-2xl overflow-hidden border border-gray-100">
                            <img v-if="item.imageUrl" :src="item.imageUrl" class="w-full h-full object-cover" />
                            <AppIcon v-else :name="item.icon" :size="20" class="text-gray-300" />
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[13.5px] text-gray-900 m-0 leading-snug">{{ item.name }}</p>
                            <p v-if="item.customSummary" class="text-[11.5px] text-gray-400 mt-0.5 m-0 line-clamp-2">
                              {{ item.customSummary }}
                            </p>
                            <div class="flex items-center gap-2 mt-2.5">
                              <div class="flex items-center gap-1 bg-gray-50 rounded-xl border border-gray-100 p-0.5">
                                <button @click="cartStore.decrementQty(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-500
                                         cursor-pointer border-none bg-transparent hover:bg-white hover:text-brand-red
                                         transition-all duration-150 text-base font-bold">−</button>
                                <span class="text-[13px] font-black min-w-[20px] text-center text-gray-900">{{ item.qty
                                  }}</span>
                                <button @click="cartStore.incrementQty(item._uid)" class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-500
                                         cursor-pointer border-none bg-transparent hover:bg-white hover:text-brand-red
                                         transition-all duration-150 text-base font-bold">+</button>
                              </div>
                              <div class="flex items-baseline gap-0.5 ml-auto">
                                <span class="text-[11px] text-gray-400 font-semibold">S/</span>
                                <span class="font-black text-[15px] text-brand-red leading-none"
                                  style="font-family:'Plus Jakarta Sans',sans-serif;">
                                  {{ (item.price * item.qty).toFixed(2) }}
                                </span>
                              </div>
                              <button @click="cartStore.remove(item._uid)" class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                       cursor-pointer border-none bg-transparent hover:bg-red-50 hover:text-red-500
                                       transition-all duration-150">
                                <TrashIcon class="w-3.5 h-3.5" />
                              </button>
                            </div>
                          </div>
                        </div>
                      </TransitionGroup>

                      <div class="mt-1 p-4 rounded-2xl bg-white border border-gray-100">
                        <div class="flex justify-between text-[12.5px] text-gray-400 mb-2">
                          <span>{{ cartItems.length }} items</span>
                          <span>S/ {{ orderTotal.toFixed(2) }}</span>
                        </div>
                        <div v-if="!isEditMode && form.type === 'delivery' && deliveryFeeAmount > 0"
                          class="flex justify-between text-[12.5px] text-gray-400 mb-2">
                          <span>Delivery</span>
                          <span>+ S/ {{ deliveryFeeAmount.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2.5 border-t border-gray-100">
                          <span class="font-semibold text-[14px] text-gray-700">Total</span>
                          <div class="flex items-baseline gap-1">
                            <span class="text-[12px] text-gray-400 font-semibold">S/</span>
                            <span class="font-black text-[22px] text-brand-red leading-none"
                              style="font-family:'Plus Jakarta Sans',sans-serif;">
                              {{ isEditMode ? orderTotal.toFixed(2) : totalConDelivery.toFixed(2) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL CONFIRMAR CANCELAR/ELIMINAR/FORZAR ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="confirmModal.show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="confirmModal.show = false">
          <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="confirmModal.show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
              <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                :class="confirmModal.type === 'cancelar' ? 'bg-amber-50' : 'bg-red-50'">
                <component :is="confirmModal.type === 'cancelar' ? XCircleIcon : TrashIcon" class="w-7 h-7"
                  :class="confirmModal.type === 'cancelar' ? 'text-amber-500' : 'text-red-500'" />
              </div>
              <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ confirmModal.type === 'cancelar' ? '¿Cancelar pedido?'
                  : confirmModal.type === 'forzar' ? '¿Eliminar definitivamente?'
                  : '¿Eliminar pedido?' }}
              </h3>
              <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
                <template v-if="confirmModal.type === 'cancelar'">
                  El pedido <strong class="text-gray-700">#{{ confirmModal.order?.id }}</strong>
                  de <strong class="text-gray-700">{{ confirmModal.order?.client_name }}</strong>
                  será marcado como cancelado.
                </template>
                <template v-else-if="confirmModal.type === 'forzar'">
                  El pedido <strong class="text-gray-700">#{{ confirmModal.order?.id }}</strong>
                  se borrará <strong class="text-red-600">para siempre</strong> de la base de datos, junto con sus productos.
                  Esta acción <strong class="text-red-600">no se puede deshacer</strong>.
                </template>
                <template v-else>
                  El pedido <strong class="text-gray-700">#{{ confirmModal.order?.id }}</strong>
                  se moverá a la papelera. Podrás restaurarlo después desde "Eliminados".
                </template>
              </p>
              <div class="flex gap-3">
                <button @click="confirmModal.show = false"
                  class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                  No, volver
                </button>
                <button @click="executeConfirm" :disabled="confirmModal.loading"
                  class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer
                         border-none transition-all duration-150 disabled:opacity-50 flex items-center justify-center gap-2"
                  :class="confirmModal.type === 'cancelar' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-red-600 hover:bg-red-700'">
                  <span v-if="confirmModal.loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  {{ confirmModal.type === 'cancelar' ? 'Sí, cancelar'
                    : confirmModal.type === 'forzar' ? 'Sí, eliminar para siempre'
                    : 'Sí, eliminar' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL SOLICITAR REPARTIDOR ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="despachoModal.show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="despachoModal.show = false">
          <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="despachoModal.show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
              <div class="w-14 h-14 rounded-2xl bg-blue-50 mx-auto mb-5 flex items-center justify-center">
                <TruckIcon class="w-7 h-7 text-blue-500" />
              </div>
              <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Solicitar repartidor
              </h3>
              <p class="text-[13.5px] text-gray-400 m-0 mb-5 leading-relaxed">
                Se notificará a los repartidores disponibles. El primero en aceptar tomará el pedido.
              </p>
              <div class="bg-gray-50 rounded-2xl p-4 mb-6 text-left border border-gray-100 flex flex-col gap-2">
                <div class="flex justify-between text-[13px]">
                  <span class="text-gray-500">Pedido</span>
                  <span class="font-bold text-gray-700">#{{ despachoModal.order?.id }}</span>
                </div>
                <div class="flex justify-between text-[13px]">
                  <span class="text-gray-500">Cliente</span>
                  <span class="font-bold text-gray-700">{{ despachoModal.order?.client_name }}</span>
                </div>
                <div v-if="despachoModal.order?.address" class="flex justify-between text-[13px]">
                  <span class="text-gray-500">Dirección</span>
                  <span class="font-bold text-gray-700 text-right max-w-[160px]">
                    {{ despachoModal.order.address }}
                  </span>
                </div>
                <div v-if="despachoModal.order?.entrega_programada && despachoModal.order?.fecha_entrega"
                  class="flex justify-between text-[13px]">
                  <span class="text-gray-500">Entrega</span>
                  <span class="font-bold text-pink-700 inline-flex items-center gap-1">
                    <Calendar :size="12" /> {{ formatDate(despachoModal.order.fecha_entrega) }}
                  </span>
                </div>
                <div v-if="despachoModal.order?.metodo_pago" class="flex justify-between text-[13px]">
                  <span class="text-gray-500">Pago</span>
                  <span :class="metodoPagoCls(despachoModal.order.metodo_pago)"
                    class="font-bold px-2 py-0.5 rounded-full text-[11px] border">
                    {{ metodoPagoLabel(despachoModal.order.metodo_pago) }}
                  </span>
                </div>
                <div class="flex justify-between text-[13px] pt-2 border-t border-gray-200">
                  <span class="text-gray-500 font-semibold">Total</span>
                  <span class="font-black text-brand-red">
                    S/ {{ parseFloat(despachoModal.order?.total ?? 0).toFixed(2) }}
                  </span>
                </div>
              </div>
              <div class="flex gap-3">
                <button @click="despachoModal.show = false"
                  class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                  Cancelar
                </button>
                <button @click="confirmarDespacho" :disabled="despachoModal.loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                         bg-blue-600 hover:bg-blue-700 disabled:opacity-50 transition-all duration-150
                         flex items-center justify-center gap-2">
                  <span v-if="despachoModal.loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  {{ despachoModal.loading ? 'Solicitando...' : 'Solicitar' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL "YA TENGO REPARTIDOR" ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="yaTengoModal.show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
             flex items-center justify-center p-4" @click.self="yaTengoModal.show = false">
          <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="yaTengoModal.show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
              <div class="w-14 h-14 rounded-2xl bg-green-50 mx-auto mb-5 flex items-center justify-center">
                <CheckCircleIcon class="w-7 h-7 text-green-500" />
              </div>
              <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                ¿Confirmar con tu propio repartidor?
              </h3>
              <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
                El pedido <strong class="text-gray-700">#{{ yaTengoModal.order?.id }}</strong>
                de <strong class="text-gray-700">{{ yaTengoModal.order?.client_name }}</strong>
                pasará directamente a <strong class="text-gray-700">En camino</strong>.
              </p>
              <div class="flex gap-3">
                <button @click="yaTengoModal.show = false"
                  class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                  Cancelar
                </button>
                <button @click="confirmarYaTengo" :disabled="yaTengoModal.loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                         bg-green-600 hover:bg-green-700 disabled:opacity-50 transition-all duration-150
                         flex items-center justify-center gap-2">
                  <span v-if="yaTengoModal.loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  {{ yaTengoModal.loading ? 'Confirmando...' : 'Sí, confirmar' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ MODAL COBRAR (solo Local) ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="cobroModal.show" class="fixed inset-0 z-[400] bg-black/50 backdrop-blur-sm
             flex items-center justify-center p-4" @click.self="cobroModal.show = false">
          <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="cobroModal.show" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">
              <div class="w-14 h-14 rounded-2xl bg-green-50 mx-auto mb-5 flex items-center justify-center text-green-500">
                <Banknote :size="26" />
              </div>
              <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Cobrar pedido #{{ cobroModal.order?.id }}
              </h3>
              <p class="text-[13.5px] text-gray-400 m-0 mb-5 leading-relaxed">
                Selecciona el método de pago para completar el pedido de
                <strong class="text-gray-700">{{ cobroModal.order?.client_name }}</strong>.
              </p>

              <div class="grid grid-cols-3 gap-2 mb-6">
                <button v-for="mp in METODOS_PAGO_LOCAL" :key="mp.id" @click="cobroModal.metodoPago = mp.id" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl border-2
                         text-[12px] font-bold cursor-pointer transition-all duration-150" :class="cobroModal.metodoPago === mp.id
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
                  S/ {{ parseFloat(cobroModal.order?.total ?? 0).toFixed(2) }}
                </span>
              </div>

              <Transition enter-active-class="transition-all duration-150"
                enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                <div v-if="cobroModal.error" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                         text-[12px] text-red-600 mb-4 text-left">
                  {{ cobroModal.error }}
                </div>
              </Transition>

              <div class="flex gap-3">
                <button @click="cobroModal.show = false"
                  class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white hover:border-gray-300 transition-all duration-150">
                  Cancelar
                </button>
                <button @click="confirmarCobro" :disabled="!cobroModal.metodoPago || cobroModal.loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px] cursor-pointer border-none
                         bg-green-600 hover:bg-green-700 disabled:opacity-50 transition-all duration-150
                         flex items-center justify-center gap-2">
                  <span v-if="cobroModal.loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  {{ cobroModal.loading ? 'Cobrando...' : 'Confirmar cobro' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- ══ LISTA PEDIDOS ══ -->
    <div class="flex-1 overflow-y-auto flex flex-col min-w-0">

      <!-- Filtros -->
      <div class="flex items-center gap-2 mb-4 flex-wrap">
        <select v-model="filter" @change="setFilter(filter)" :disabled="showTrashed"
          class="px-3.5 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] text-gray-900
                 outline-none cursor-pointer focus:border-brand-red transition-all duration-200 font-semibold text-gray-600
                 disabled:opacity-40 disabled:cursor-not-allowed">
          <option v-for="s in STATUSES" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>

        <div class="flex items-center gap-1.5">
          <input v-model="dateFrom" type="date" @change="setFilter(filter)" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] outline-none
                   focus:border-brand-red transition-all duration-200 text-gray-600" />
          <span class="text-gray-400 text-[12px]">al</span>
          <input v-model="dateTo" type="date" @change="setFilter(filter)" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] outline-none
                   focus:border-brand-red transition-all duration-200 text-gray-600" />
        </div>

        <button v-if="filter || dateFrom || dateTo || search" @click="clearFilters" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[12px] font-semibold
                 text-gray-500 cursor-pointer hover:border-red-300 hover:text-brand-red
                 transition-all duration-150 flex items-center gap-1">
          <XMarkIcon class="w-3.5 h-3.5" />
          Limpiar
        </button>

        <div class="relative ml-auto">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
          <input v-model="search" @input="debouncedSearch" placeholder="Nombre, teléfono o #id..." class="pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] text-gray-900
                   outline-none w-52 focus:border-brand-red transition-all duration-200 placeholder:text-gray-300" />
        </div>

        <button v-if="can.delete" @click="toggleTrashed" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-[13px] font-semibold
                 border cursor-pointer transition-all duration-150"
          :class="showTrashed ? 'bg-gray-800 text-white border-gray-800' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300'">
          <TrashIcon class="w-3.5 h-3.5" />
          {{ showTrashed ? 'Ver activos' : 'Eliminados' }}
        </button>

        <button v-if="can.writeOrders && !showTrashed" @click="openModal" class="flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-brand-red text-white font-bold
                 text-[13px] border-none cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150">
          <PlusIcon class="w-3.5 h-3.5" />
          Nuevo
        </button>
      </div>

      <!-- Skeleton -->
      <div v-if="displayedLoading" class="flex flex-col gap-3">
        <div v-for="n in 4" :key="n" class="h-36 rounded-2xl bg-gray-100 animate-pulse" />
      </div>

      <!-- Empty -->
      <div v-else-if="displayedOrders.length === 0" class="flex flex-col items-center py-20 text-gray-400 gap-4">
        <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center">
          <ClipboardDocumentListIcon class="w-10 h-10 text-gray-300" />
        </div>
        <div class="text-center">
          <p class="m-0 text-[15px] font-bold text-gray-600">
            {{ showTrashed ? 'Papelera vacía' : search ? 'Sin resultados' : 'Sin pedidos' }}
          </p>
          <p class="m-0 text-[13px] mt-1">
            {{ showTrashed ? 'No hay pedidos eliminados' : search ? `No encontramos pedidos para "${search}"` : filter ? 'No hay pedidos con este estado' : 'Aún no hay pedidos registrados' }}
          </p>
        </div>
        <button v-if="!showTrashed && !search && !filter && can.writeOrders" @click="openModal" class="flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-brand-red text-white font-bold
                 text-[13px] border-none cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150">
          <PlusIcon class="w-4 h-4" />
          Registrar primer pedido
        </button>
      </div>

      <!-- Cards -->
      <div v-else class="flex flex-col gap-3">
        <div v-for="o in displayedOrders" :key="o.id"
          class="bg-white rounded-2xl border-2 cursor-pointer transition-all duration-150 shadow-sm overflow-hidden"
          :class="selected?.id === o.id ? 'border-brand-red shadow-[0_0_0_1px_rgba(var(--color-brand-primary-rgb,196,30,30),0.1)]' : 'border-gray-100 hover:border-red-200 hover:shadow-sm'"
          @click="selectOrder(o)">
          <div class="p-4">

            <!-- Header card -->
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="text-[11px] font-black px-2 py-0.5 rounded-lg shrink-0
                             bg-gray-100 text-gray-600 border border-gray-200 font-mono">
                  #{{ o.id }}
                </span>
                <span class="font-bold text-[14px] text-gray-900 truncate">{{ o.client_name }}</span>
                <span class="hidden sm:inline text-[10.5px] font-medium px-2 py-0.5 rounded-full
                             bg-gray-100 text-gray-500 shrink-0 border border-gray-200">
                  {{ typeLabel(o.type) }}
                </span>
                <span v-if="o.type === 'local' && o.mesa" class="hidden sm:inline text-[10.5px] font-bold px-2 py-0.5 rounded-full
                             bg-blue-50 text-blue-700 shrink-0 border border-blue-200">
                  Mesa {{ o.mesa }}
                </span>
                <span v-if="o.metodo_pago" :class="metodoPagoCls(o.metodo_pago)"
                  class="hidden sm:inline text-[10.5px] font-bold px-2 py-0.5 rounded-full shrink-0 border">
                  {{ metodoPagoLabel(o.metodo_pago) }}
                </span>
                <span v-if="o.entrega_programada && o.fecha_entrega" class="hidden sm:inline text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0
                         bg-pink-50 text-pink-700 border border-pink-200 items-center gap-1">
                  <Calendar :size="10" class="inline -mt-0.5" /> {{ o.fecha_entrega }}
                </span>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span :class="statusCls(o.status)">{{ statusLabel(o.status) }}</span>
                <div class="flex items-baseline gap-0.5">
                  <span class="text-[11px] font-semibold text-gray-400">S/</span>
                  <span class="font-black text-[15px] text-brand-red leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ parseFloat(o.total).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Timeline -->
            <div class="flex items-center mb-3.5">
              <div v-for="(step, i) in stepsFor(o.type)" :key="step.value"
                class="flex-1 flex flex-col items-center gap-1 relative text-[9.5px]"
                :class="getStepIdx(o.status, o.type) > i ? 'text-brand-red' : getStepIdx(o.status, o.type) === i ? 'text-brand-red font-bold' : 'text-gray-300'">
                <div v-if="i < stepsFor(o.type).length - 1"
                  class="absolute top-[11px] left-[calc(50%+11px)] h-0.5 w-[calc(100%-22px)] transition-colors duration-300"
                  :class="getStepIdx(o.status, o.type) > i ? 'bg-brand-red' : 'bg-gray-100'" />
                <div
                  class="w-[22px] h-[22px] rounded-full border-2 flex items-center justify-center text-[10px] z-10 transition-all duration-200"
                  :class="{
                    'bg-brand-red border-brand-red text-white': getStepIdx(o.status, o.type) > i,
                    'bg-red-50 border-brand-red text-brand-red': getStepIdx(o.status, o.type) === i,
                    'bg-white border-gray-200 text-gray-300': getStepIdx(o.status, o.type) < i,
                  }">
                  <CheckIcon v-if="getStepIdx(o.status, o.type) > i" class="w-3 h-3" />
                </div>
                <span class="text-center leading-tight hidden sm:block">{{ step.label }}</span>
              </div>
            </div>

            <!-- Footer card -->
            <div class="flex items-center gap-3">
              <span class="text-[12px] text-gray-400 flex items-center gap-1">
                <ClockIcon class="w-3.5 h-3.5" />
                {{ formatDate(o.created_at) }}
              </span>
              <span class="text-gray-200 hidden sm:inline">·</span>
              <span class="text-[12px] text-gray-400 hidden sm:inline">
                {{ o.items?.length ?? 0 }} items
              </span>
              <div class="flex gap-1.5 ml-auto">

                <template v-if="showTrashed">
                  <button @click.stop="handleRestore(o)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold border
                           cursor-pointer bg-green-50 border-green-300 text-green-700 hover:bg-green-100 transition-all duration-150">
                    <ArrowUturnLeftIcon class="w-3.5 h-3.5" />
                    Restaurar
                  </button>
                  <button v-if="can.delete" @click.stop="askForceDelete(o)" class="w-8 h-8 rounded-xl flex items-center justify-center border border-red-200
                           bg-white text-red-500 cursor-pointer hover:bg-red-50 transition-all duration-150" title="Eliminar definitivamente">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </template>

                <template v-else>
                  <template v-if="can.writeOrders">
                    <template v-if="o.type === 'delivery' && o.status === 'listo'">
                      <button @click.stop="abrirDespachoModal(o)"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold border
                               cursor-pointer bg-blue-50 border-blue-300 text-blue-700 hover:bg-blue-100 transition-all duration-150">
                        <TruckIcon class="w-3.5 h-3.5" />
                        Repartidor
                      </button>
                      <button @click.stop="askYaTengo(o)"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold border
                               cursor-pointer bg-green-50 border-green-300 text-green-700 hover:bg-green-100 transition-all duration-150">
                        <CheckCircleIcon class="w-3.5 h-3.5" />
                        Ya tengo →
                      </button>
                    </template>

                    <button v-else-if="o.type === 'local' && o.status === 'listo'" @click.stop="abrirCobroModal(o)"
                      class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold border
                             cursor-pointer bg-green-50 border-green-300 text-green-700 hover:bg-green-100 transition-all duration-150">
                      <Banknote :size="14" />
                      Cobrar
                    </button>

                    <button v-else-if="o.status !== 'entregado' && o.status !== 'cancelado'" @click.stop="advanceOrder(o)"
                      class="px-3 py-1.5 rounded-xl text-[12px] font-bold border border-gray-200 bg-white
                             text-gray-600 cursor-pointer hover:border-brand-red hover:text-brand-red transition-all duration-150">
                      {{ nextStatusLabel(o.status, o.type) }} →
                    </button>

                    <button v-if="o.status !== 'entregado' && o.status !== 'cancelado'" @click.stop="askCancel(o)" class="w-8 h-8 rounded-xl flex items-center justify-center border border-gray-200
                             bg-white text-gray-400 cursor-pointer hover:border-amber-300 hover:text-amber-500
                             transition-all duration-150" title="Cancelar pedido">
                      <XCircleIcon class="w-4 h-4" />
                    </button>
                  </template>

                  <button @click.stop="sendWA(o)" class="w-8 h-8 rounded-xl flex items-center justify-center bg-[#25D366] text-white
                           border-none cursor-pointer hover:bg-[#128C7E] transition-colors" title="WhatsApp">
                    <WhatsAppIcon :size="15" />
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="displayedMeta && displayedMeta.last_page > 1"
        class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
        <span class="text-[12.5px] text-gray-400">
          Página {{ displayedMeta.current_page }} de {{ displayedMeta.last_page }}
          · {{ displayedMeta.total }} pedidos
        </span>
        <div class="flex gap-2">
          <button @click="changePage(displayedMeta.current_page - 1)" :disabled="displayedMeta.current_page === 1"
            class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px] font-semibold text-gray-600
                   cursor-pointer bg-white hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
            ← Anterior
          </button>
          <button @click="changePage(displayedMeta.current_page + 1)"
            :disabled="displayedMeta.current_page === displayedMeta.last_page" class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px] font-semibold text-gray-600
                   cursor-pointer bg-white hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
            Siguiente →
          </button>
        </div>
      </div>
    </div>

    <!-- ══ PANEL DETALLE ══ -->
    <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-x-4"
      leave-active-class="transition-all duration-150" leave-to-class="opacity-0 translate-x-4">
      <div v-if="selected" class="fixed inset-0 z-[150] flex flex-col bg-white
           lg:static lg:z-auto lg:w-72 xl:w-80 lg:shrink-0 lg:rounded-2xl
           lg:border lg:border-gray-100 lg:shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100">
          <div class="flex items-start justify-between mb-3">
            <div>
              <p class="font-black text-[17px] text-gray-900 m-0 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Pedido #{{ selected.id }}
              </p>
              <p class="text-[12.5px] text-gray-600 mt-1 m-0 font-semibold">{{ selected.client_name }}</p>
            </div>
            <div class="flex flex-col items-end gap-2">
              <span :class="statusCls(selected.status)">{{ statusLabel(selected.status) }}</span>
              <button @click="selected = null" class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center
                       cursor-pointer border-none hover:bg-gray-200 transition-colors">
                <XMarkIcon class="w-3.5 h-3.5 text-gray-500" />
              </button>
            </div>
          </div>
          <div class="flex flex-col gap-1.5">
            <p class="text-[11.5px] text-gray-400 m-0 flex items-center gap-1.5">
              <PhoneIcon class="w-3.5 h-3.5 shrink-0" />
              {{ selected.client_phone }}
            </p>
            <p v-if="selected.address" class="text-[11.5px] text-gray-400 m-0 flex items-center gap-1.5">
              <MapPinIcon class="w-3.5 h-3.5 shrink-0" />
              {{ selected.address }}
              <span v-if="selected.district" class="text-gray-300">·</span>
              {{ selected.district }}
            </p>
            <p v-if="selected.type === 'local' && selected.mesa" class="text-[11.5px] text-blue-700 m-0 flex items-center gap-1.5 font-semibold">
              <BuildingStorefrontIcon class="w-3.5 h-3.5 shrink-0" />
              Mesa {{ selected.mesa }}
            </p>
            <div v-if="selected.entrega_programada && selected.fecha_entrega"
              class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-pink-50 border border-pink-200">
              <Calendar :size="14" class="text-pink-500 shrink-0" />
              <div>
                <p class="text-[10.5px] font-black text-pink-700 m-0">Entrega programada</p>
                <p class="text-[10px] text-pink-500 m-0">
                  {{ selected.fecha_entrega }}
                  <span v-if="selected.hora_entrega"> · {{ selected.hora_entrega }}</span>
                </p>
              </div>
            </div>
            <div v-if="selected.mensaje_tarjeta" class="px-2.5 py-1.5 rounded-xl bg-purple-50 border border-purple-200">
              <p class="text-[10px] font-black text-purple-600 m-0 mb-0.5 flex items-center gap-1">
                <Heart :size="11" /> {{ pedidoConfigStore.config.mensaje_label }}
              </p>
              <p class="text-[11px] text-purple-800 m-0 italic">"{{ selected.mensaje_tarjeta }}"</p>
            </div>
            <p v-if="selected.metodo_pago" class="text-[11.5px] m-0 mt-0.5">
              <span :class="metodoPagoCls(selected.metodo_pago)"
                class="font-bold px-2 py-0.5 rounded-full border text-[10.5px]">
                {{ metodoPagoLabel(selected.metodo_pago) }}
              </span>
            </p>
          </div>
        </div>

        <!-- Items -->
        <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
          <div v-if="!selected.items?.length" class="flex items-center justify-center py-8 text-gray-400 text-[13px]">
            Sin detalle disponible
          </div>
          <div v-for="item in selected.items" :key="item.id" class="flex items-start gap-3 px-5 py-3.5">
            <div class="w-7 h-7 rounded-lg bg-brand-red flex items-center justify-center
                        text-white text-[12px] font-black shrink-0 mt-0.5">
              {{ item.qty }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-[13px] text-gray-900 m-0">
                {{ item.product?.name ?? 'Producto' }}
              </p>
              <p v-if="item.custom_summary" class="text-[11.5px] text-gray-400 mt-0.5 m-0 line-clamp-2">
                {{ item.custom_summary }}
              </p>
            </div>
            <div class="flex items-baseline gap-0.5 shrink-0">
              <span class="text-[10px] text-gray-400 font-semibold">S/</span>
              <span class="font-black text-[14px] text-brand-red leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ parseFloat(item.subtotal ?? 0).toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="selected.note" class="mx-4 mb-3 px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-100 flex items-start gap-1.5">
          <StickyNote :size="13" class="text-amber-600 shrink-0 mt-0.5" />
          <p class="text-[12px] text-amber-800 m-0 font-medium">{{ selected.note }}</p>
        </div>

        <!-- Acciones -->
        <div class="p-4 border-t border-gray-100 flex flex-col gap-2">
          <div class="flex justify-between items-center mb-2">
            <span class="text-[13px] text-gray-500 font-medium">Total</span>
            <div class="flex items-baseline gap-1">
              <span class="text-[12px] text-gray-400 font-semibold">S/</span>
              <span class="font-black text-[22px] text-brand-red leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ parseFloat(selected.total).toFixed(2) }}
              </span>
            </div>
          </div>

          <!-- Modo papelera: solo Restaurar / Eliminar definitivo -->
          <template v-if="showTrashed">
            <button @click="handleRestore(selected)" class="w-full py-3 rounded-xl font-bold text-[13px] text-white bg-green-600 border-none
                     cursor-pointer shadow-sm hover:bg-green-700 transition-all duration-150
                     flex items-center justify-center gap-2">
              <ArrowUturnLeftIcon class="w-4 h-4" />
              Restaurar pedido
            </button>
            <button v-if="can.delete" @click="askForceDelete(selected)"
              class="w-full py-2.5 rounded-xl font-semibold text-[12px] text-red-600 bg-transparent
                     border border-red-200 cursor-pointer hover:bg-red-50 hover:border-red-300
                     transition-all duration-150 flex items-center justify-center gap-1.5">
              <TrashIcon class="w-3.5 h-3.5" />
              Eliminar definitivamente
            </button>
          </template>

          <!-- Modo normal -->
          <template v-else>
            <template v-if="can.writeOrders">
              <template v-if="selected.type === 'delivery' && selected.status === 'listo'">
                <button @click="abrirDespachoModal(selected)" class="w-full py-3 rounded-xl font-bold text-[13px] text-white bg-blue-600
                         border-none cursor-pointer hover:bg-blue-700 transition-all duration-150
                         flex items-center justify-center gap-2">
                  <TruckIcon class="w-4 h-4" />
                  Solicitar repartidor
                </button>
                <button @click="askYaTengo(selected)" class="w-full py-3 rounded-xl font-bold text-[13px] text-white bg-green-600
                         border-none cursor-pointer hover:bg-green-700 transition-all duration-150
                         flex items-center justify-center gap-2">
                  <CheckCircleIcon class="w-4 h-4" />
                  Ya tengo repartidor
                </button>
              </template>

              <button v-else-if="selected.type === 'local' && selected.status === 'listo'" @click="abrirCobroModal(selected)"
                class="w-full py-3 rounded-xl font-bold text-[13px] text-white bg-green-600 border-none
                       cursor-pointer shadow-sm hover:bg-green-700 transition-all duration-150
                       flex items-center justify-center gap-2">
                <Banknote :size="16" />
                Cobrar pedido
              </button>

              <button v-else @click="advanceOrder(selected)"
                :disabled="selected.status === 'entregado' || selected.status === 'cancelado'" class="w-full py-3 rounded-xl font-bold text-[13px] text-white bg-brand-red border-none
                       cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150
                       flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <ArrowRightCircleIcon class="w-4 h-4" />
                {{ nextStatusLabel(selected.status, selected.type) }}
              </button>
            </template>

            <template v-if="can.writeOrders">
              <button v-if="selected.status !== 'entregado' && selected.status !== 'cancelado' && selected.status !== 'en_camino'"
                @click="openEditItemsModal(selected)"
                class="w-full py-2.5 rounded-xl font-semibold text-[13px] text-gray-700 bg-white
                       border border-gray-200 cursor-pointer hover:border-brand-red hover:text-brand-red
                       transition-all duration-150 flex items-center justify-center gap-2">
                <TagIcon class="w-4 h-4" />
                Editar productos
              </button>
            </template>

            <button @click="sendWA(selected)" class="w-full py-2.5 rounded-xl font-semibold text-[13px] text-white bg-[#25D366]
                     border-none cursor-pointer hover:bg-[#128C7E] transition-all duration-150
                     flex items-center justify-center gap-2">
              <WhatsAppIcon :size="16" />
              WhatsApp al cliente
            </button>

            <template v-if="can.writeOrders">
              <button v-if="selected.status !== 'entregado' && selected.status !== 'cancelado'" @click="askCancel(selected)"
                class="w-full py-2 rounded-xl font-semibold text-[12px] text-amber-600 bg-transparent
                       border border-amber-200 cursor-pointer hover:bg-amber-50 hover:border-amber-300
                       transition-all duration-150 flex items-center justify-center gap-1.5">
                <XCircleIcon class="w-3.5 h-3.5" />
                Cancelar pedido
              </button>

              <button v-if="selected.status === 'cancelado' || selected.status === 'entregado'" @click="askDelete(selected)" class="w-full py-2 rounded-xl font-semibold text-[12px] text-gray-400 bg-transparent
                       border border-gray-100 cursor-pointer hover:bg-red-50 hover:text-red-500
                       hover:border-red-200 transition-all duration-150 flex items-center justify-center gap-1.5">
                <TrashIcon class="w-3.5 h-3.5" />
                Eliminar pedido
              </button>
            </template>
          </template>
        </div>
      </div>
    </Transition>

    <!-- Panel vacío desktop -->
    <div v-if="!selected" class="hidden lg:flex w-72 xl:w-80 shrink-0 bg-white rounded-2xl
             border border-gray-100 shadow-sm flex-col items-center justify-center gap-4 text-gray-400">
      <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
        <ClipboardDocumentListIcon class="w-8 h-8 text-gray-300" />
      </div>
      <div class="text-center">
        <p class="font-bold text-gray-600 text-[14px] m-0">Selecciona un pedido</p>
        <p class="text-[12px] m-0 mt-1">para ver el detalle completo</p>
      </div>
      <button v-if="can.writeOrders && !showTrashed" @click="openModal" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-red text-white font-bold
               text-[12.5px] border-none cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150">
        <PlusIcon class="w-3.5 h-3.5" />
        Nuevo pedido
      </button>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, reactive, inject, watch, nextTick } from 'vue'
import {
  PlusIcon, XMarkIcon, TrashIcon, CheckIcon,
  MagnifyingGlassIcon, ClockIcon, PhoneIcon,
  ExclamationTriangleIcon, ExclamationCircleIcon,
  CheckCircleIcon, ArrowRightCircleIcon, ArrowUturnLeftIcon,
  MapPinIcon, ClipboardDocumentListIcon, XCircleIcon,
  ShoppingCartIcon, TagIcon, TruckIcon,
  ShoppingBagIcon, BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline'
import WhatsAppIcon from '@/components/icons/WhatsAppIcon.vue'
import AppIcon from '@/components/AppIcon.vue'
import { LayoutGrid, PackageSearch, Calendar, Banknote, StickyNote, Heart } from 'lucide-vue-next'
import CustomizerModal from '@/components/catalog/CustomizerModal.vue'
import { useOrdersStore } from '@/stores/orders'
import { useProductsStore } from '@/stores/products'
import { usePedidoConfigStore } from '@/stores/pedidoConfig'
import { useCartStore } from '@/stores/cart'
import { useAdminStore } from '@/stores/admin'
import { storeToRefs } from 'pinia'

import api from '@/utils/api'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({ iconUrl: markerIcon, iconRetinaUrl: markerIcon2x, shadowUrl: markerShadow })

// ── Stores ────────────────────────────────────────────────
const ordersStore = useOrdersStore()
const productsStore = useProductsStore()
const pedidoConfigStore = usePedidoConfigStore()
const cartStore = useCartStore()
const adminStore = useAdminStore()
const { can } = storeToRefs(adminStore)

const customizerRef = ref<InstanceType<typeof CustomizerModal> | null>(null)
const registerCta = inject<(fn: () => void) => void>('registerCta')

// ── Estado ────────────────────────────────────────────────
const filter = ref('')
const search = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)
const selected = ref<any>(null)
const showModal = ref(false)
const submitting = ref(false)
const modalError = ref('')
const rightTab = ref<'catalogo' | 'carrito'>('catalogo')
const activeCat = ref('all')
const editingOrderId = ref<number | null>(null)
const isEditMode = computed(() => editingOrderId.value !== null)
const showTrashed = ref(false)

let searchTimer: ReturnType<typeof setTimeout> | null = null
let refreshInterval: ReturnType<typeof setInterval> | null = null

// ── Fecha mínima ──────────────────────────────────────────
const fechaMinima = computed(() => new Date().toISOString().split('T')[0])

// ── Modales ───────────────────────────────────────────────
const confirmModal = reactive({
  show: false, type: '' as 'cancelar' | 'eliminar' | 'forzar', order: null as any, loading: false,
})

const despachoModal = reactive({
  show: false, order: null as any, loading: false, error: '',
})

const yaTengoModal = reactive({
  show: false, order: null as any, loading: false,
})

const cobroModal = reactive({
  show: false, order: null as any, metodoPago: '', loading: false, error: '',
})

// ── Form nuevo pedido ──────────────────────────────────────
const form = reactive({
  client_name: '',
  client_phone: '',
  type: 'delivery' as 'local' | 'recoger' | 'delivery',
  mesa: '',
  address: '',
  reference: '',
  delivery_zone_id: 0,
  lat: null as number | null,
  lng: null as number | null,
  note: '',
  mensaje_tarjeta: '',
  fecha_entrega: '',
  hora_entrega: '',
  entrega_programada: false,
})

// ── Constantes ────────────────────────────────────────────
const STATUSES = [
  { value: '', label: 'Todos' },
  { value: 'nuevo', label: 'Nuevo' },
  { value: 'confirmado', label: 'Confirmado' },
  { value: 'preparando', label: 'Preparando' },
  { value: 'listo', label: 'Listo' },
  { value: 'en_camino', label: 'En camino' },
  { value: 'entregado', label: 'Entregado' },
  { value: 'cancelado', label: 'Cancelado' },
]

const STEPS = [
  { value: 'nuevo', label: 'Nuevo' },
  { value: 'confirmado', label: 'Confirm.' },
  { value: 'preparando', label: 'Preparan.' },
  { value: 'listo', label: 'Listo' },
  { value: 'en_camino', label: 'Camino' },
  { value: 'entregado', label: 'Entregado' },
]

const FLOW = ['nuevo', 'confirmado', 'preparando', 'listo', 'en_camino', 'entregado']

function flowFor(type: string): string[] {
  return type === 'delivery' ? FLOW : FLOW.filter(s => s !== 'en_camino')
}

function stepsFor(type: string) {
  return type === 'delivery' ? STEPS : STEPS.filter(s => s.value !== 'en_camino')
}

const ORDER_TYPES = [
  { id: 'local', icon: BuildingStorefrontIcon, label: 'Local' },
  { id: 'recoger', icon: ShoppingBagIcon, label: 'Recoger' },
  { id: 'delivery', icon: TruckIcon, label: 'Delivery' },
]

// ── Mapa / GPS / zona de delivery ──────────────────────────
interface DeliveryZone { id: number; nombre: string; precio: number }
const CHICLAYO_LAT = -6.7741
const CHICLAYO_LNG = -79.8409

let adminMap: L.Map | null = null
let adminMarker: L.Marker | null = null
const mapSearch = ref('')
const mapResults = ref<any[]>([])
const mapSearching = ref(false)
let mapSearchTimer: ReturnType<typeof setTimeout> | null = null

const loadingGPS = ref(false)
const gpsError = ref('')

const zones = ref<DeliveryZone[]>([])
const loadingZones = ref(false)
const detectedZone = ref<DeliveryZone | null>(null)
const detectingZone = ref(false)
const zoneNotFound = ref(false)

const selectedZone = computed<DeliveryZone | null>(() => {
  if (detectedZone.value) return detectedZone.value
  return zones.value.find(z => z.id === form.delivery_zone_id) ?? null
})
const deliveryFeeAmount = computed(() => selectedZone.value?.precio ?? 0)
const totalConDelivery = computed(() => orderTotal.value + deliveryFeeAmount.value)

const HORARIOS = [
  { value: '09:00', label: '9:00 AM - 10:00 AM' },
  { value: '10:00', label: '10:00 AM - 11:00 AM' },
  { value: '11:00', label: '11:00 AM - 12:00 PM' },
  { value: '12:00', label: '12:00 PM - 1:00 PM' },
  { value: '14:00', label: '2:00 PM - 3:00 PM' },
  { value: '15:00', label: '3:00 PM - 4:00 PM' },
  { value: '16:00', label: '4:00 PM - 5:00 PM' },
  { value: '17:00', label: '5:00 PM - 6:00 PM' },
]

const METODOS_PAGO_LOCAL = [
  { id: 'efectivo', icon: 'banknote', label: 'Efectivo' },
  { id: 'yape', icon: 'smartphone', label: 'Yape/Plin' },
  { id: 'tarjeta', icon: 'credit-card', label: 'Tarjeta' },
]

// ── Computed ──────────────────────────────────────────────
const cartItems = computed(() => cartStore.items)
const orderTotal = computed(() => cartItems.value.reduce((s, i) => s + i.price * i.qty, 0))

const canSubmit = computed(() => {
  if (isEditMode.value) return cartItems.value.length > 0
  return (
    form.client_name.trim() &&
    (form.type === 'local' || form.client_phone.trim()) &&
    cartItems.value.length > 0 &&
    (form.type !== 'local' || !!form.mesa.trim()) &&
    (form.type !== 'delivery' || (!!form.delivery_zone_id && !!form.address.trim())) &&
    (!form.entrega_programada || !!form.fecha_entrega)
  )
})

const filteredCatalog = computed(() => {
  const all = productsStore.products
  if (activeCat.value === 'all') return all
  return all.filter(p => p.category?.slug === activeCat.value)
})

// ── Papelera ──────────────────────────────────────────────
const displayedOrders = computed(() => showTrashed.value ? ordersStore.trashedOrders : ordersStore.orders)
const displayedLoading = computed(() => showTrashed.value ? ordersStore.loadingTrashed : ordersStore.loading)
const displayedMeta = computed(() => showTrashed.value ? ordersStore.trashedMeta : ordersStore.meta)

function toggleTrashed() {
  showTrashed.value = !showTrashed.value
  selected.value = null
  currentPage.value = 1
  if (showTrashed.value) {
    ordersStore.fetchTrashed({ search: search.value || undefined, page: 1 })
  } else {
    loadOrders(1)
  }
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => {
  if (can.value.writeOrders) registerCta?.(() => openModal())
  loadOrders()
  productsStore.fetchAdmin({ perPage: 200 })
  refreshInterval = setInterval(() => silentRefresh(), 20_000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
  if (adminMap) { adminMap.remove(); adminMap = null }
})

// ── Refresh silencioso ────────────────────────────────────
async function silentRefresh() {
  if (showTrashed.value) return
  try {
    const { data } = await api.get('/admin/orders', {
      params: {
        status: filter.value || undefined,
        search: search.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        page: currentPage.value,
      },
    })
    if (data.data?.data) {
      ordersStore.orders.splice(0, ordersStore.orders.length, ...data.data.data)
      ordersStore.meta = data.data.meta
    }
  } catch { }
}

// ── Carga y filtros ───────────────────────────────────────
function loadOrders(page = currentPage.value) {
  ordersStore.fetch({
    status: filter.value || undefined,
    search: search.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    page,
  })
}

function clearFilters() {
  filter.value = ''; search.value = ''; dateFrom.value = ''; dateTo.value = ''
  currentPage.value = 1
  if (showTrashed.value) ordersStore.fetchTrashed({ page: 1 })
  else loadOrders(1)
}

function changePage(page: number) {
  currentPage.value = page
  if (showTrashed.value) {
    ordersStore.fetchTrashed({ search: search.value || undefined, page })
  } else {
    loadOrders(page)
  }
}

function setFilter(value: string) {
  filter.value = value; currentPage.value = 1
  if (showTrashed.value) ordersStore.fetchTrashed({ search: search.value || undefined, page: 1 })
  else loadOrders(1)
}

function debouncedSearch() {
  clearTimeout(searchTimer!)
  searchTimer = setTimeout(() => {
    currentPage.value = 1
    if (showTrashed.value) ordersStore.fetchTrashed({ search: search.value || undefined, page: 1 })
    else loadOrders(1)
  }, 400)
}

function openEditItemsModal(order: any) {
  if (!can.value.writeOrders) return
  if (order.status === 'entregado' || order.status === 'cancelado' || order.status === 'en_camino') return

  editingOrderId.value = order.id

  const mapped = (order.items ?? []).map((i: any) => ({
    _uid: crypto.randomUUID(),
    productId: i.product_id,
    name: i.product?.name ?? 'Producto',
    icon: i.product?.icon ?? null,
    imageUrl: i.product?.image_url ?? null,
    rootCategorySlug: null,
    basePrice: parseFloat(i.unit_price),
    modifiersPrice: 0,
    extrasPrice: 0,
    price: parseFloat(i.unit_price),
    qty: i.qty,
    customization: i.customization ?? [],
    extras: i.extras ?? [],
    customSummary: i.custom_summary ?? '',
  }))

  cartStore.loadItems(mapped)
  showModal.value = true
  modalError.value = ''
  rightTab.value = 'carrito'
  activeCat.value = 'all'
}

// ── Modal nuevo pedido ────────────────────────────────────
function openModal() {
  if (!can.value.writeOrders) return
  cartStore.clear(); showModal.value = true; modalError.value = ''
  rightTab.value = 'catalogo'; activeCat.value = 'all'; resetForm()
}

function closeModal() {
  showModal.value = false; modalError.value = ''; cartStore.clear(); resetForm()
  editingOrderId.value = null
  if (adminMap) { adminMap.remove(); adminMap = null }
}

function resetForm() {
  Object.assign(form, {
    client_name: '', client_phone: '', type: 'delivery', mesa: '',
    address: '', reference: '', delivery_zone_id: 0, lat: null, lng: null, note: '',
    mensaje_tarjeta: '', fecha_entrega: '', hora_entrega: '', entrega_programada: false,
  })
  detectedZone.value = null
  zoneNotFound.value = false
  gpsError.value = ''
  mapSearch.value = ''
  mapResults.value = []
}

// ── Zonas de delivery ───────────────────────────────────────
async function fetchZones() {
  loadingZones.value = true
  try {
    const { data } = await api.get('/delivery-zones')
    zones.value = data.data
  } catch { }
  finally { loadingZones.value = false }
}

function onManualZoneChange() { }

async function detectarZona(lat: number, lng: number) {
  detectingZone.value = true
  zoneNotFound.value = false
  detectedZone.value = null
  form.delivery_zone_id = 0

  try {
    const { data } = await api.get('/delivery-zones/detectar', { params: { lat, lng } })
    detectedZone.value = data.data
    form.delivery_zone_id = data.data.id
  } catch {
    zoneNotFound.value = true
    await fetchZones()
  } finally {
    detectingZone.value = false
  }
}

// ── GPS ───────────────────────────────────────────────────
function usarGPS() {
  gpsError.value = ''
  if (!navigator.geolocation) {
    gpsError.value = 'Tu navegador no soporta geolocalización'
    return
  }
  loadingGPS.value = true
  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const lat = position.coords.latitude
      const lng = position.coords.longitude
      if (adminMap && adminMarker) { adminMap.setView([lat, lng], 17); adminMarker.setLatLng([lat, lng]) }
      form.lat = lat
      form.lng = lng
      await Promise.all([reverseGeocode(lat, lng), detectarZona(lat, lng)])
      loadingGPS.value = false
    },
    (error) => {
      loadingGPS.value = false
      const messages: Record<number, string> = {
        1: 'Permiso de ubicación denegado.',
        2: 'No se pudo obtener tu ubicación. Márcala en el mapa.',
        3: 'Tiempo de espera agotado. Intenta de nuevo.',
      }
      gpsError.value = messages[error.code] ?? 'Error al obtener ubicación.'
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

// ── Mapa Leaflet ──────────────────────────────────────────
function initAdminMap() {
  if (adminMap) return
  const el = document.getElementById('admin-delivery-map')
  if (!el) return

  adminMap = L.map('admin-delivery-map', { center: [CHICLAYO_LAT, CHICLAYO_LNG], zoom: 14 })
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19,
  }).addTo(adminMap)

  const redIcon = L.divIcon({
    className: '',
    html: `<div style="width:28px;height:28px;background:var(--color-brand-primary,#C41E1E);border:3px solid white;
      border-radius:50% 50% 50% 0;transform:rotate(-45deg);
      box-shadow:0 2px 8px rgba(var(--color-brand-primary-rgb,196,30,30),0.4);"></div>`,
    iconSize: [28, 28], iconAnchor: [14, 28],
  })

  adminMarker = L.marker([CHICLAYO_LAT, CHICLAYO_LNG], { draggable: true, icon: redIcon }).addTo(adminMap)
  adminMarker.on('dragend', () => {
    const pos = adminMarker!.getLatLng()
    form.lat = pos.lat; form.lng = pos.lng
    reverseGeocode(pos.lat, pos.lng); detectarZona(pos.lat, pos.lng)
  })
  adminMap.on('click', (e: L.LeafletMouseEvent) => {
    adminMarker!.setLatLng(e.latlng)
    form.lat = e.latlng.lat; form.lng = e.latlng.lng
    reverseGeocode(e.latlng.lat, e.latlng.lng); detectarZona(e.latlng.lat, e.latlng.lng)
  })
  form.lat = CHICLAYO_LAT; form.lng = CHICLAYO_LNG

  // El modal recién se hizo visible — Leaflet necesita recalcular el
  // tamaño del contenedor una vez que ya tiene dimensiones reales.
  setTimeout(() => adminMap?.invalidateSize(), 150)
}

async function reverseGeocode(lat: number, lng: number) {
  try {
    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
      { headers: { 'Accept-Language': 'es' } }
    )
    const data = await res.json()
    if (data.display_name) {
      form.address = data.display_name.split(',').slice(0, 3).join(',').trim()
    }
  } catch { }
}

function debouncedMapSearch() {
  clearTimeout(mapSearchTimer!)
  if (mapSearch.value.length < 3) { mapResults.value = []; return }
  mapSearchTimer = setTimeout(searchAddress, 500)
}

async function searchAddress() {
  mapSearching.value = true
  try {
    const query = encodeURIComponent(`${mapSearch.value}, Chiclayo, Peru`)
    const res = await fetch(
      `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=5`,
      { headers: { 'Accept-Language': 'es' } }
    )
    mapResults.value = await res.json()
  } catch { mapResults.value = [] }
  finally { mapSearching.value = false }
}

function selectMapResult(result: any) {
  const lat = parseFloat(result.lat)
  const lng = parseFloat(result.lon)
  if (adminMap && adminMarker) { adminMap.setView([lat, lng], 17); adminMarker.setLatLng([lat, lng]) }
  form.lat = lat; form.lng = lng
  form.address = result.display_name.split(',').slice(0, 3).join(',').trim()
  mapResults.value = []; mapSearch.value = ''
  detectarZona(lat, lng)
}

// Inicializa/destruye el mapa según el tipo de pedido elegido
watch(() => form.type, async (val) => {
  if (val === 'delivery' && showModal.value && !isEditMode.value) {
    await nextTick()
    initAdminMap()
  } else {
    if (adminMap) { adminMap.remove(); adminMap = null }
    detectedZone.value = null
    zoneNotFound.value = false
    form.delivery_zone_id = 0
    gpsError.value = ''
  }
})

// El modal se abre con type='delivery' por defecto — como el watch de
// arriba no dispara si el valor no cambia, hay que inicializar el mapa
// también cuando el modal mismo se hace visible.
watch(showModal, async (open) => {
  if (open && form.type === 'delivery' && !isEditMode.value) {
    await nextTick()
    initAdminMap()
  }
})

function openProduct(product: any) {
  customizerRef.value?.open(product)
  setTimeout(() => { if (cartItems.value.length > 0) rightTab.value = 'carrito' }, 800)
}

async function submitOrder() {
  if (!canSubmit.value || !can.value.writeOrders) return
  submitting.value = true; modalError.value = ''
  try {
    if (isEditMode.value) {
      const updated = await ordersStore.updateItems(editingOrderId.value!, cartItems.value.map(i => ({
        product_id: i.productId,
        qty: i.qty,
        unit_price: i.price,
        customization: i.customization ?? [],
        extras: i.extras ?? [],
        custom_summary: i.customSummary ?? null,
      })))
      if (selected.value?.id === editingOrderId.value && updated) {
        selected.value = updated
      }
      closeModal(); loadOrders()
      return
    }

    await api.post('/admin/orders', {
      client_name: form.client_name,
      client_phone: form.client_phone,
      type: form.type,
      mesa: form.mesa || null,
      address: form.address || null,
      reference: form.reference || null,
      delivery_zone_id: form.delivery_zone_id || null,
      delivery_fee: deliveryFeeAmount.value,
      lat: form.lat ?? null,
      lng: form.lng ?? null,
      note: form.note || null,
      mensaje_tarjeta: form.mensaje_tarjeta || null,
      fecha_entrega: form.fecha_entrega || null,
      hora_entrega: form.hora_entrega || null,
      entrega_programada: form.entrega_programada,
      total: totalConDelivery.value,
      items: cartItems.value.map(i => ({
        product_id: i.productId,
        qty: i.qty,
        unit_price: i.price,
        customization: i.customization ?? [],
        extras: i.extras ?? [],
        custom_summary: i.customSummary ?? null,
      })),
    })
    closeModal(); loadOrders()
  } catch (e: any) {
    modalError.value = e.response?.data?.message ?? (isEditMode.value ? 'Error al actualizar los items' : 'Error al registrar el pedido')
  } finally {
    submitting.value = false
  }
}

// ── Despacho ──────────────────────────────────────────────
function abrirDespachoModal(order: any) {
  if (!can.value.writeOrders) return
  despachoModal.order = order; despachoModal.show = true
  despachoModal.loading = false; despachoModal.error = ''
}

async function confirmarDespacho() {
  if (!despachoModal.order) return
  despachoModal.loading = true; despachoModal.error = ''
  try {
    await api.post('/admin/despachos/solicitar', { order_id: despachoModal.order.id })
    despachoModal.show = false
    const idx = ordersStore.orders.findIndex(o => o.id === despachoModal.order.id)
    if (idx !== -1) ordersStore.orders[idx] = { ...ordersStore.orders[idx], despacho_solicitado: true }
  } catch (e: any) {
    despachoModal.error = e.response?.data?.message ?? 'Error al solicitar repartidor'
  } finally {
    despachoModal.loading = false
  }
}

// ── Ya tengo repartidor ───────────────────────────────────
function askYaTengo(o: any) {
  if (!can.value.writeOrders) return
  yaTengoModal.order = o; yaTengoModal.show = true; yaTengoModal.loading = false
}

async function confirmarYaTengo() {
  if (!yaTengoModal.order) return
  yaTengoModal.loading = true
  try { await advanceOrder(yaTengoModal.order); yaTengoModal.show = false }
  finally { yaTengoModal.loading = false }
}

// ── Cobrar pedido Local ───────────────────────────────────
function abrirCobroModal(o: any) {
  if (!can.value.writeOrders) return
  cobroModal.order = o; cobroModal.metodoPago = ''
  cobroModal.show = true; cobroModal.loading = false; cobroModal.error = ''
}

async function confirmarCobro() {
  if (!cobroModal.order || !cobroModal.metodoPago) return
  cobroModal.loading = true; cobroModal.error = ''
  try {
    const updated = await ordersStore.cobrarLocal(cobroModal.order.id, cobroModal.metodoPago)
    if (selected.value?.id === cobroModal.order.id && updated) {
      selected.value = updated
    }
    cobroModal.show = false
    loadOrders()
  } catch (e: any) {
    cobroModal.error = e.response?.data?.message ?? 'Error al registrar el cobro'
  } finally {
    cobroModal.loading = false
  }
}

// ── Acciones pedidos ──────────────────────────────────────
async function selectOrder(o: any) {
  if (showTrashed.value) {
    // Ya viene con items cargados desde paginateTrashed()
    selected.value = o
    return
  }
  selected.value = await ordersStore.getOne(o.id)
}

async function advanceOrder(o: any) {
  if (!can.value.writeOrders) return
  const flow = flowFor(o.type)
  const idx = flow.indexOf(o.status)
  if (idx >= flow.length - 1) return
  const updated = await ordersStore.updateStatus(o.id, flow[idx + 1])
  if (selected.value?.id === o.id && updated) {
    selected.value = { ...selected.value, status: updated.status }
  }
}

function askCancel(o: any) {
  if (!can.value.writeOrders) return
  confirmModal.order = o; confirmModal.type = 'cancelar'
  confirmModal.show = true; confirmModal.loading = false
}

function askDelete(o: any) {
  if (!can.value.writeOrders) return
  confirmModal.order = o; confirmModal.type = 'eliminar'
  confirmModal.show = true; confirmModal.loading = false
}

function askForceDelete(o: any) {
  if (!can.value.delete) return
  confirmModal.order = o; confirmModal.type = 'forzar'
  confirmModal.show = true; confirmModal.loading = false
}

async function handleRestore(o: any) {
  const ok = await ordersStore.restoreOrder(o.id)
  if (ok) {
    if (selected.value?.id === o.id) selected.value = null
    ordersStore.fetchTrashed({ search: search.value || undefined, page: currentPage.value })
  }
}

async function executeConfirm() {
  if (!confirmModal.order) return
  confirmModal.loading = true
  try {
    if (confirmModal.type === 'cancelar') {
      await ordersStore.cancelOrder(confirmModal.order.id)
      if (selected.value?.id === confirmModal.order.id) {
        selected.value = { ...selected.value, status: 'cancelado' }
      }
    } else if (confirmModal.type === 'forzar') {
      await ordersStore.forceDeleteOrder(confirmModal.order.id)
      if (selected.value?.id === confirmModal.order.id) selected.value = null
      ordersStore.fetchTrashed({ search: search.value || undefined, page: currentPage.value })
    } else {
      await ordersStore.deleteOrder(confirmModal.order.id)
      if (selected.value?.id === confirmModal.order.id) selected.value = null
      loadOrders()
    }
  } finally {
    confirmModal.show = false; confirmModal.loading = false; confirmModal.order = null
  }
}

function sendWA(o: any) {
  const clientPhone = (o.client_phone ?? '').replace(/\D/g, '')
  const phone = clientPhone.startsWith('51') ? clientPhone : `51${clientPhone}`

  // Símbolos de texto plano, no emoji de color — wa.me tiene un bug
  // confirmado que corrompe el emoji de color al armar el mensaje
  // (llegan como � tanto en escritorio como en el celular).
  const lines = [
    `*Pedido #${o.id}*`,
    `${o.client_name} · ${typeLabel(o.type)}`,
    `Estado: *${statusLabel(o.status)}*`,
  ]
  if (o.entrega_programada && o.fecha_entrega) lines.push(`Entrega: ${o.fecha_entrega}${o.hora_entrega ? ' · ' + o.hora_entrega : ''}`)
  if (o.mensaje_tarjeta) lines.push(`Tarjeta: "${o.mensaje_tarjeta}"`)
  lines.push(`Total: *S/ ${parseFloat(o.total).toFixed(2)}*`, ``)
  lines.push(`Seguimiento: ${import.meta.env.VITE_APP_URL ?? ''}/seguimiento/${o.id}?tel=${clientPhone}`)

  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(lines.join('\n'))}`, '_blank')
}

// ── Helpers ───────────────────────────────────────────────
function getStepIdx(s: string, type: string = 'delivery'): number { return flowFor(type).indexOf(s) }

function nextStatusLabel(s: string, type: string = 'delivery'): string {
  const labels: Record<string, string> = {
    nuevo: 'Confirmar', confirmado: 'Preparando',
    preparando: 'Listo', listo: type === 'delivery' ? 'En camino' : 'Entregado',
    en_camino: 'Entregado',
  }
  return labels[s] ?? 'Completado'
}

function formatDate(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleString('es-PE', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })
}

function typeLabel(t: string): string {
  return { local: 'Local', recoger: 'Recoger', delivery: 'Delivery' }[t] ?? t
}

function statusLabel(s: string): string {
  const m: Record<string, string> = {
    nuevo: 'Nuevo', confirmado: 'Confirmado', preparando: 'Preparando',
    listo: 'Listo', en_camino: 'En camino', entregado: 'Entregado', cancelado: 'Cancelado',
  }
  return m[s] ?? s
}

function statusCls(s: string): string {
  const m: Record<string, string> = {
    nuevo: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50   text-blue-700   border border-blue-200',
    confirmado: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50  text-amber-700  border border-amber-200',
    preparando: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200',
    listo: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-green-50  text-green-700  border border-green-200',
    en_camino: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-pink-50   text-pink-700   border border-pink-200',
    entregado: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-green-100 text-green-800  border border-green-300',
    cancelado: 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-gray-100  text-gray-500   border border-gray-200',
  }
  return m[s] ?? m.cancelado
}

function metodoPagoLabel(m: string): string {
  return {
    anticipado: 'Pagado',
    contraentrega_efectivo: 'Efectivo',
    contraentrega_yape: 'Yape/Plin',
    efectivo: 'Efectivo',
    yape: 'Yape/Plin',
    tarjeta: 'Tarjeta',
  }[m] ?? m
}

function metodoPagoCls(m: string): string {
  return {
    anticipado: 'bg-green-50  text-green-700  border-green-200',
    contraentrega_efectivo: 'bg-amber-50  text-amber-700  border-amber-200',
    contraentrega_yape: 'bg-purple-50 text-purple-700 border-purple-200',
    efectivo: 'bg-amber-50  text-amber-700  border-amber-200',
    yape: 'bg-purple-50 text-purple-700 border-purple-200',
    tarjeta: 'bg-blue-50   text-blue-700   border-blue-200',
  }[m] ?? 'bg-gray-50 text-gray-600 border-gray-200'
}
</script>

<style scoped>
.modal-input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border-radius: 0.875rem;
  border: 2px solid #f3f4f6;
  background: #f9fafb;
  font-size: 13px;
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

.cart-item-enter-active {
  transition: all 0.2s ease;
}

.cart-item-leave-active {
  transition: all 0.15s ease;
}

.cart-item-enter-from {
  opacity: 0;
  transform: translateX(-8px);
}

.cart-item-leave-to {
  opacity: 0;
  transform: translateX(8px);
}
</style>