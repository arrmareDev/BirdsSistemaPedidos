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
                      Nuevo Pedido
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
                <div
                  class="lg:w-72 xl:w-80 shrink-0 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-100 bg-white">
                  <div class="flex-1 overflow-y-auto p-4 sm:p-5 flex flex-col gap-4">

                    <!-- Tipo de pedido — solo recoger/delivery -->
                    <div>
                      <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-2">
                        Tipo de pedido
                      </label>
                      <div class="grid grid-cols-2 gap-2">
                        <button v-for="t in ORDER_TYPES" :key="t.id" @click="form.type = t.id as any" class="flex flex-col items-center gap-1.5 py-3 rounded-2xl border-2
                                 text-[11.5px] font-bold cursor-pointer transition-all duration-150" :class="form.type === t.id
                                  ? 'border-brand-red bg-red-50 text-brand-red shadow-sm'
                                  : 'border-gray-100 bg-gray-50 text-gray-500 hover:border-red-200'">
                          <component :is="t.icon" class="w-5 h-5" />
                          {{ t.label }}
                        </button>
                      </div>
                    </div>

                    <!-- Nombre + Teléfono -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
                      <div>
                        <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                          Nombre *
                        </label>
                        <input v-model="form.client_name" placeholder="Nombre del cliente" class="modal-input" />
                      </div>
                      <div>
                        <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                          Teléfono *
                        </label>
                        <input v-model="form.client_phone" placeholder="987 654 321" type="tel" class="modal-input" />
                      </div>
                    </div>

                    <!-- Campos delivery -->
                    <Transition enter-active-class="transition-all duration-200"
                      enter-from-class="opacity-0 -translate-y-1" leave-active-class="transition-all duration-150"
                      leave-to-class="opacity-0">
                      <div v-if="form.type === 'delivery'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
                        <div>
                          <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                            Dirección
                          </label>
                          <input v-model="form.address" placeholder="Calle, número, referencia" class="modal-input" />
                        </div>
                        <div>
                          <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                            Referencia
                          </label>
                          <input v-model="form.reference" placeholder="Portón azul, frente al parque..."
                            class="modal-input" />
                        </div>
                        <div>
                          <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                            Distrito
                          </label>
                          <select v-model="form.district" class="modal-input">
                            <option value="">Seleccionar...</option>
                            <option v-for="d in DISTRICTS" :key="d">{{ d }}</option>
                          </select>
                        </div>
                      </div>
                    </Transition>

                    <!-- Entrega programada -->
                    <div class="border border-gray-100 rounded-2xl p-3 flex flex-col gap-3">
                      <div class="flex items-center justify-between">
                        <label class="text-[10.5px] font-black uppercase tracking-widest text-gray-400">
                          Entrega programada
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

                    <!-- Mensaje tarjeta -->
                    <div>
                      <label class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                        Mensaje para tarjeta
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
                      <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="font-semibold text-[14px] text-gray-700">Total</span>
                        <div class="flex items-baseline gap-1">
                          <span class="text-[12px] font-semibold text-gray-400">S/</span>
                          <span class="font-black text-[24px] text-brand-red leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ orderTotal.toFixed(2) }}
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
                             shadow-[0_4px_20px_rgba(196,30,30,0.3)] hover:bg-red-700 hover:-translate-y-0.5
                             active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed
                             disabled:hover:translate-y-0 flex items-center justify-center gap-2"
                      style="font-family:'Plus Jakarta Sans',sans-serif;">
                      <span v-if="submitting"
                        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      <CheckCircleIcon v-else class="w-4 h-4" />
                      {{ submitting ? 'Registrando...' : `Confirmar · S/ ${orderTotal.toFixed(2)}` }}
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
                      <button v-for="cat in CATS" :key="cat.id" @click="activeCat = cat.id" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold
                               border whitespace-nowrap shrink-0 cursor-pointer transition-all duration-150" :class="activeCat === cat.id
                                ? 'bg-brand-red text-white border-brand-red shadow-sm'
                                : 'bg-white border-gray-200 text-gray-500 hover:border-red-300 hover:text-brand-red'">
                        {{ cat.icon }} {{ cat.label }}
                      </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                      <div v-if="filteredCatalog.length === 0"
                        class="flex flex-col items-center py-16 text-gray-400 gap-2">
                        <span class="text-4xl">🌸</span>
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
                            <span v-else class="text-4xl">{{ p.emoji }}</span>
                            <div v-if="p.popular"
                              class="absolute top-2 right-0 bg-pink-400 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-l-md">
                              Popular
                            </div>
                            <!-- Badge stock bajo -->
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
                            <!-- Tags florería -->
                            <div v-if="p.ocasion || p.color || p.tamano" class="flex flex-wrap gap-1 mb-2">
                              <span v-if="p.tamano"
                                class="text-[9px] px-1.5 py-0.5 rounded-full bg-pink-50 text-pink-600 border border-pink-100 font-medium">
                                {{ p.tamano }}
                              </span>
                              <span v-if="p.color"
                                class="text-[9px] px-1.5 py-0.5 rounded-full bg-purple-50 text-purple-600 border border-purple-100 font-medium">
                                {{ p.color }}
                              </span>
                              <span v-if="p.ocasion"
                                class="text-[9px] px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-100 font-medium">
                                {{ p.ocasion }}
                              </span>
                            </div>
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
                            <span v-else>{{ item.emoji }}</span>
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
                        <div class="flex justify-between items-center pt-2.5 border-t border-gray-100">
                          <span class="font-semibold text-[14px] text-gray-700">Total</span>
                          <div class="flex items-baseline gap-1">
                            <span class="text-[12px] text-gray-400 font-semibold">S/</span>
                            <span class="font-black text-[22px] text-brand-red leading-none"
                              style="font-family:'Plus Jakarta Sans',sans-serif;">
                              {{ orderTotal.toFixed(2) }}
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

    <!-- ══ MODAL CONFIRMAR CANCELAR/ELIMINAR ══ -->
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
                {{ confirmModal.type === 'cancelar' ? '¿Cancelar pedido?' : '¿Eliminar pedido?' }}
              </h3>
              <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
                <template v-if="confirmModal.type === 'cancelar'">
                  El pedido <strong class="text-gray-700">#{{ confirmModal.order?.id }}</strong>
                  de <strong class="text-gray-700">{{ confirmModal.order?.client_name }}</strong>
                  será marcado como cancelado.
                </template>
                <template v-else>
                  El pedido <strong class="text-gray-700">#{{ confirmModal.order?.id }}</strong>
                  será eliminado permanentemente.
                  Esta acción <strong class="text-red-600">no se puede deshacer</strong>.
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
                  {{ confirmModal.type === 'cancelar' ? 'Sí, cancelar' : 'Sí, eliminar' }}
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
                <!-- Entrega programada en modal despacho -->
                <div v-if="despachoModal.order?.entrega_programada && despachoModal.order?.fecha_entrega"
                  class="flex justify-between text-[13px]">
                  <span class="text-gray-500">Entrega</span>
                  <span class="font-bold text-pink-700">
                    📅 {{ formatDate(despachoModal.order.fecha_entrega) }}
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

    <!-- ══ LISTA PEDIDOS ══ -->
    <div class="flex-1 overflow-y-auto flex flex-col min-w-0">

      <!-- Filtros -->
      <div class="flex items-center gap-2 mb-4 flex-wrap">
        <select v-model="filter" @change="setFilter(filter)"
          class="px-3.5 py-1.5 rounded-xl border border-gray-200 bg-white text-[13px] text-gray-900
                 outline-none cursor-pointer focus:border-brand-red transition-all duration-200 font-semibold text-gray-600">
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

        <button @click="openModal" class="flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-brand-red text-white font-bold
                 text-[13px] border-none cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150">
          <PlusIcon class="w-3.5 h-3.5" />
          Nuevo
        </button>
      </div>

      <!-- Skeleton -->
      <div v-if="ordersStore.loading" class="flex flex-col gap-3">
        <div v-for="n in 4" :key="n" class="h-36 rounded-2xl bg-gray-100 animate-pulse" />
      </div>

      <!-- Empty -->
      <div v-else-if="ordersStore.orders.length === 0" class="flex flex-col items-center py-20 text-gray-400 gap-4">
        <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center">
          <ClipboardDocumentListIcon class="w-10 h-10 text-gray-300" />
        </div>
        <div class="text-center">
          <p class="m-0 text-[15px] font-bold text-gray-600">
            {{ search ? 'Sin resultados' : 'Sin pedidos' }}
          </p>
          <p class="m-0 text-[13px] mt-1">
            {{ search ? `No encontramos pedidos para "${search}"` : filter ? 'No hay pedidos con este estado' : 'Aún no hay pedidos registrados' }}
          </p>
        </div>
        <button v-if="!search && !filter" @click="openModal" class="flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-brand-red text-white font-bold
                 text-[13px] border-none cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150">
          <PlusIcon class="w-4 h-4" />
          Registrar primer pedido
        </button>
      </div>

      <!-- Cards -->
      <div v-else class="flex flex-col gap-3">
        <div v-for="o in ordersStore.orders" :key="o.id"
          class="bg-white rounded-2xl border-2 cursor-pointer transition-all duration-150 shadow-sm overflow-hidden"
          :class="selected?.id === o.id ? 'border-brand-red shadow-[0_0_0_1px_rgba(196,30,30,0.1)]' : 'border-gray-100 hover:border-red-200 hover:shadow-sm'"
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
                <span v-if="o.metodo_pago" :class="metodoPagoCls(o.metodo_pago)"
                  class="hidden sm:inline text-[10.5px] font-bold px-2 py-0.5 rounded-full shrink-0 border">
                  {{ metodoPagoLabel(o.metodo_pago) }}
                </span>
                <!-- Badge entrega programada -->
                <span v-if="o.entrega_programada && o.fecha_entrega" class="hidden sm:inline text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0
                         bg-pink-50 text-pink-700 border border-pink-200">
                  📅 {{ o.fecha_entrega }}
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
              <div v-for="(step, i) in STEPS" :key="step.value"
                class="flex-1 flex flex-col items-center gap-1 relative text-[9.5px]"
                :class="getStepIdx(o.status) > i ? 'text-brand-red' : getStepIdx(o.status) === i ? 'text-brand-red font-bold' : 'text-gray-300'">
                <div v-if="i < STEPS.length - 1"
                  class="absolute top-[11px] left-[calc(50%+11px)] h-0.5 w-[calc(100%-22px)] transition-colors duration-300"
                  :class="getStepIdx(o.status) > i ? 'bg-brand-red' : 'bg-gray-100'" />
                <div
                  class="w-[22px] h-[22px] rounded-full border-2 flex items-center justify-center text-[10px] z-10 transition-all duration-200"
                  :class="{
                    'bg-brand-red border-brand-red text-white': getStepIdx(o.status) > i,
                    'bg-red-50 border-brand-red text-brand-red': getStepIdx(o.status) === i,
                    'bg-white border-gray-200 text-gray-300': getStepIdx(o.status) < i,
                  }">
                  <CheckIcon v-if="getStepIdx(o.status) > i" class="w-3 h-3" />
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

                <!-- Delivery listo: repartidor propio o delivery central -->
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

                <!-- Avanzar normal -->
                <button v-else-if="o.status !== 'entregado' && o.status !== 'cancelado'" @click.stop="advanceOrder(o)"
                  class="px-3 py-1.5 rounded-xl text-[12px] font-bold border border-gray-200 bg-white
                         text-gray-600 cursor-pointer hover:border-brand-red hover:text-brand-red transition-all duration-150">
                  {{ nextStatusLabel(o.status) }} →
                </button>

                <!-- Cancelar -->
                <button v-if="o.status !== 'entregado' && o.status !== 'cancelado'" @click.stop="askCancel(o)" class="w-8 h-8 rounded-xl flex items-center justify-center border border-gray-200
                         bg-white text-gray-400 cursor-pointer hover:border-amber-300 hover:text-amber-500
                         transition-all duration-150" title="Cancelar pedido">
                  <XCircleIcon class="w-4 h-4" />
                </button>

                <!-- WhatsApp -->
                <button @click.stop="sendWA(o)" class="w-8 h-8 rounded-xl flex items-center justify-center bg-[#25D366] text-white
                         border-none cursor-pointer hover:bg-[#128C7E] transition-colors" title="WhatsApp">
                  <WhatsAppIcon :size="15" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="ordersStore.meta && ordersStore.meta.last_page > 1"
        class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
        <span class="text-[12.5px] text-gray-400">
          Página {{ ordersStore.meta.current_page }} de {{ ordersStore.meta.last_page }}
          · {{ ordersStore.meta.total }} pedidos
        </span>
        <div class="flex gap-2">
          <button @click="changePage(ordersStore.meta.current_page - 1)" :disabled="ordersStore.meta.current_page === 1"
            class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px] font-semibold text-gray-600
                   cursor-pointer bg-white hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
            ← Anterior
          </button>
          <button @click="changePage(ordersStore.meta.current_page + 1)"
            :disabled="ordersStore.meta.current_page === ordersStore.meta.last_page" class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px] font-semibold text-gray-600
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
      <div v-if="selected" class="hidden lg:flex w-72 xl:w-80 shrink-0 bg-white rounded-2xl
               border border-gray-100 shadow-sm flex-col overflow-hidden">

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
            <!-- Entrega programada en detalle -->
            <div v-if="selected.entrega_programada && selected.fecha_entrega"
              class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-pink-50 border border-pink-200">
              <span class="text-sm">📅</span>
              <div>
                <p class="text-[10.5px] font-black text-pink-700 m-0">Entrega programada</p>
                <p class="text-[10px] text-pink-500 m-0">
                  {{ selected.fecha_entrega }}
                  <span v-if="selected.hora_entrega"> · {{ selected.hora_entrega }}</span>
                </p>
              </div>
            </div>
            <!-- Mensaje tarjeta en detalle -->
            <div v-if="selected.mensaje_tarjeta" class="px-2.5 py-1.5 rounded-xl bg-purple-50 border border-purple-200">
              <p class="text-[10px] font-black text-purple-600 m-0 mb-0.5">💌 Mensaje tarjeta</p>
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
              <!-- Tags del producto florería -->
              <div v-if="item.product?.tamano || item.product?.color" class="flex gap-1 mt-0.5">
                <span v-if="item.product?.tamano"
                  class="text-[9px] px-1.5 py-0.5 rounded-full bg-pink-50 text-pink-600 border border-pink-100">
                  {{ item.product.tamano }}
                </span>
                <span v-if="item.product?.color"
                  class="text-[9px] px-1.5 py-0.5 rounded-full bg-purple-50 text-purple-600 border border-purple-100">
                  {{ item.product.color }}
                </span>
              </div>
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

        <div v-if="selected.note" class="mx-4 mb-3 px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-100">
          <p class="text-[12px] text-amber-800 m-0 font-medium">📝 {{ selected.note }}</p>
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

          <button v-else @click="advanceOrder(selected)"
            :disabled="selected.status === 'entregado' || selected.status === 'cancelado'" class="w-full py-3 rounded-xl font-bold text-[13px] text-white bg-brand-red border-none
                   cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150
                   flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
            <ArrowRightCircleIcon class="w-4 h-4" />
            {{ nextStatusLabel(selected.status) }}
          </button>

          <button @click="sendWA(selected)" class="w-full py-2.5 rounded-xl font-semibold text-[13px] text-white bg-[#25D366]
                   border-none cursor-pointer hover:bg-[#128C7E] transition-all duration-150
                   flex items-center justify-center gap-2">
            <WhatsAppIcon :size="16" />
            WhatsApp al cliente
          </button>

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
      <button @click="openModal" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-red text-white font-bold
               text-[12.5px] border-none cursor-pointer shadow-sm hover:bg-red-700 transition-all duration-150">
        <PlusIcon class="w-3.5 h-3.5" />
        Nuevo pedido
      </button>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, reactive, inject } from 'vue'
import {
  PlusIcon, XMarkIcon, TrashIcon, CheckIcon,
  MagnifyingGlassIcon, ClockIcon, PhoneIcon,
  ExclamationTriangleIcon, ExclamationCircleIcon,
  CheckCircleIcon, ArrowRightCircleIcon,
  MapPinIcon, ClipboardDocumentListIcon, XCircleIcon,
  ShoppingCartIcon, TagIcon, TruckIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline'
import WhatsAppIcon from '@/components/icons/WhatsAppIcon.vue'
import CustomizerModal from '@/components/catalog/CustomizerModal.vue'
import { useOrdersStore } from '@/stores/orders'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import api from '@/utils/api'

// ── Stores ────────────────────────────────────────────────
const ordersStore = useOrdersStore()
const productsStore = useProductsStore()
const cartStore = useCartStore()

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

let searchTimer: ReturnType<typeof setTimeout> | null = null
let refreshInterval: ReturnType<typeof setInterval> | null = null

// ── Fecha mínima ──────────────────────────────────────────
const fechaMinima = computed(() => new Date().toISOString().split('T')[0])

// ── Modales ───────────────────────────────────────────────
const confirmModal = reactive({
  show: false, type: '' as 'cancelar' | 'eliminar', order: null as any, loading: false,
})

const despachoModal = reactive({
  show: false, order: null as any, loading: false, error: '',
})

const yaTengoModal = reactive({
  show: false, order: null as any, loading: false,
})

// ── Form nuevo pedido — florería ──────────────────────────
const form = reactive({
  client_name: '',
  client_phone: '',
  type: 'delivery' as 'recoger' | 'delivery',
  address: '',
  reference: '',
  district: '',
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

const ORDER_TYPES = [
  { id: 'recoger', icon: ShoppingBagIcon, label: 'Recoger' },
  { id: 'delivery', icon: TruckIcon, label: 'Delivery' },
]

const DISTRICTS = [
  'Chiclayo', 'José Leonardo Ortiz', 'La Victoria', 'Pimentel', 'San José',
]

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

// Categorías de florería
const CATS = [
  { id: 'all', icon: '💐', label: 'Todo' },
  { id: 'ramos', icon: '🌹', label: 'Ramos' },
  { id: 'arreglos', icon: '🌸', label: 'Arreglos' },
  { id: 'plantas', icon: '🪴', label: 'Plantas' },
  { id: 'coronas', icon: '🌿', label: 'Coronas' },
  { id: 'regalos', icon: '🎁', label: 'Regalos' },
  { id: 'globos', icon: '🎈', label: 'Globos' },
]

// ── Computed ──────────────────────────────────────────────
const cartItems = computed(() => cartStore.items)
const orderTotal = computed(() => cartItems.value.reduce((s, i) => s + i.price * i.qty, 0))

const canSubmit = computed(() =>
  form.client_name.trim() &&
  form.client_phone.trim() &&
  cartItems.value.length > 0 &&
  (!form.entrega_programada || !!form.fecha_entrega)
)

const filteredCatalog = computed(() => {
  const all = productsStore.products
  if (activeCat.value === 'all') return all
  return all.filter(p => p.category?.slug === activeCat.value)
})

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => {
  registerCta?.(() => openModal())
  loadOrders()
  productsStore.fetchAdmin()
  refreshInterval = setInterval(() => silentRefresh(), 20_000)
})

onUnmounted(() => { if (refreshInterval) clearInterval(refreshInterval) })

// ── Refresh silencioso ────────────────────────────────────
async function silentRefresh() {
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
  currentPage.value = 1; loadOrders(1)
}

function changePage(page: number) { currentPage.value = page; loadOrders(page) }

function setFilter(value: string) { filter.value = value; currentPage.value = 1; loadOrders(1) }

function debouncedSearch() {
  clearTimeout(searchTimer!)
  searchTimer = setTimeout(() => { currentPage.value = 1; loadOrders(1) }, 400)
}

// ── Modal nuevo pedido ────────────────────────────────────
function openModal() {
  cartStore.clear(); showModal.value = true; modalError.value = ''
  rightTab.value = 'catalogo'; activeCat.value = 'all'; resetForm()
}

function closeModal() {
  showModal.value = false; modalError.value = ''; cartStore.clear(); resetForm()
}

function resetForm() {
  Object.assign(form, {
    client_name: '', client_phone: '', type: 'delivery',
    address: '', reference: '', district: '', note: '',
    mensaje_tarjeta: '', fecha_entrega: '', hora_entrega: '', entrega_programada: false,
  })
}

function openProduct(product: any) {
  customizerRef.value?.open(product)
  setTimeout(() => { if (cartItems.value.length > 0) rightTab.value = 'carrito' }, 800)
}

async function submitOrder() {
  if (!canSubmit.value) return
  submitting.value = true; modalError.value = ''
  try {
    await api.post('/admin/orders', {
      client_name: form.client_name,
      client_phone: form.client_phone,
      type: form.type,
      address: form.address || null,
      reference: form.reference || null,
      district: form.district || null,
      note: form.note || null,
      mensaje_tarjeta: form.mensaje_tarjeta || null,
      fecha_entrega: form.fecha_entrega || null,
      hora_entrega: form.hora_entrega || null,
      entrega_programada: form.entrega_programada,
      total: orderTotal.value,
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
    modalError.value = e.response?.data?.message ?? 'Error al registrar el pedido'
  } finally {
    submitting.value = false
  }
}

// ── Despacho ──────────────────────────────────────────────
function abrirDespachoModal(order: any) {
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
  yaTengoModal.order = o; yaTengoModal.show = true; yaTengoModal.loading = false
}

async function confirmarYaTengo() {
  if (!yaTengoModal.order) return
  yaTengoModal.loading = true
  try { await advanceOrder(yaTengoModal.order); yaTengoModal.show = false }
  finally { yaTengoModal.loading = false }
}

// ── Acciones pedidos ──────────────────────────────────────
async function selectOrder(o: any) { selected.value = await ordersStore.getOne(o.id) }

async function advanceOrder(o: any) {
  const idx = FLOW.indexOf(o.status)
  if (idx >= FLOW.length - 1) return
  const updated = await ordersStore.updateStatus(o.id, FLOW[idx + 1])
  if (selected.value?.id === o.id && updated) {
    selected.value = { ...selected.value, status: updated.status }
  }
}

function askCancel(o: any) {
  confirmModal.order = o; confirmModal.type = 'cancelar'
  confirmModal.show = true; confirmModal.loading = false
}

function askDelete(o: any) {
  confirmModal.order = o; confirmModal.type = 'eliminar'
  confirmModal.show = true; confirmModal.loading = false
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

  const lines = [
    `💐 *Florería — Pedido #${o.id}*`,
    `👤 ${o.client_name} · ${typeLabel(o.type)}`,
    `📊 Estado: *${statusLabel(o.status)}*`,
  ]
  if (o.entrega_programada && o.fecha_entrega) lines.push(`📅 Entrega: ${o.fecha_entrega}${o.hora_entrega ? ' · ' + o.hora_entrega : ''}`)
  if (o.mensaje_tarjeta) lines.push(`💌 Tarjeta: "${o.mensaje_tarjeta}"`)
  lines.push(`💰 Total: *S/ ${parseFloat(o.total).toFixed(2)}*`, ``)
  lines.push(`📦 Seguimiento: ${import.meta.env.VITE_APP_URL ?? ''}/seguimiento/${o.id}?tel=${clientPhone}`)

  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(lines.join('\n'))}`, '_blank')
}

// ── Helpers ───────────────────────────────────────────────
function getStepIdx(s: string): number { return FLOW.indexOf(s) }

function nextStatusLabel(s: string): string {
  const labels: Record<string, string> = {
    nuevo: 'Confirmar', confirmado: 'Preparando',
    preparando: 'Listo', listo: 'En camino', en_camino: 'Entregado',
  }
  return labels[s] ?? 'Completado'
}

function formatDate(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleString('es-PE', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })
}

function typeLabel(t: string): string {
  return { recoger: '🏪 Recoger', delivery: '🚚 Delivery' }[t] ?? t
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
  return { anticipado: '💳 Pagado', contraentrega_efectivo: '💵 Efectivo', contraentrega_yape: '📱 Yape/Plin' }[m] ?? m
}

function metodoPagoCls(m: string): string {
  return {
    anticipado: 'bg-green-50  text-green-700  border-green-200',
    contraentrega_efectivo: 'bg-amber-50  text-amber-700  border-amber-200',
    contraentrega_yape: 'bg-purple-50 text-purple-700 border-purple-200',
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
  border-color: #C41E1E;
  background: white;
  box-shadow: 0 0 0 3px rgba(196, 30, 30, 0.08);
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