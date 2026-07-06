<template>
    <div class="min-h-screen bg-white">

        <!-- ══ NAVBAR ══ -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-4">
                <button @click="router.back()" class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-500
                           hover:text-gray-900 hover:bg-gray-100 cursor-pointer border-none
                           bg-transparent font-semibold text-[13px] transition-all duration-150">
                    <ChevronLeftIcon class="w-4 h-4" />
                    <span>Volver</span>
                </button>

                <div class="flex items-center gap-2 text-[12.5px] text-gray-400 min-w-0">
                    <span class="cursor-pointer hover:text-brand-red transition-colors truncate"
                        @click="router.push('/')">
                        Catálogo
                    </span>
                    <ChevronRightIcon class="w-3 h-3 shrink-0" />
                    <span v-if="product?.category" class="truncate">{{ product.category.name }}</span>
                    <ChevronRightIcon v-if="product?.category" class="w-3 h-3 shrink-0" />
                    <span class="text-gray-700 font-semibold truncate">{{ product?.name }}</span>
                </div>
            </div>
        </header>

        <!-- ══ LOADING ══ -->
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">
                <div class="rounded-3xl bg-gray-100 animate-pulse" style="aspect-ratio:1;" />
                <div class="flex flex-col gap-4 pt-4">
                    <div class="h-5 w-28 rounded-full bg-gray-100 animate-pulse" />
                    <div class="h-9 w-3/4 rounded-2xl bg-gray-100 animate-pulse" />
                    <div class="h-4 w-full rounded-xl bg-gray-100 animate-pulse" />
                    <div class="h-4 w-2/3 rounded-xl bg-gray-100 animate-pulse" />
                    <div class="h-12 rounded-2xl bg-gray-100 animate-pulse mt-6" />
                    <div class="h-12 rounded-2xl bg-gray-100 animate-pulse" />
                </div>
            </div>
        </div>

        <!-- ══ NOT FOUND ══ -->
        <div v-else-if="!product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32
                   flex flex-col items-center gap-5 text-center">
            <div class="w-24 h-24 rounded-3xl bg-gray-50 flex items-center
                        justify-center text-5xl border border-gray-100">
                🌷
            </div>
            <div>
                <p class="font-black text-gray-900 text-[20px] m-0">Producto no encontrado</p>
                <p class="text-gray-400 text-[14px] mt-1 m-0">
                    El producto que buscas no existe o fue removido.
                </p>
            </div>
            <button @click="router.push('/')" class="px-6 py-3 rounded-2xl bg-brand-red text-white font-bold text-[13.5px]
                       border-none cursor-pointer hover:bg-red-700 transition-colors">
                Ver catálogo
            </button>
        </div>

        <!-- ══ CONTENIDO ══ -->
        <main v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10 pb-36 lg:pb-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-start">

                <!-- ── COLUMNA IZQUIERDA ── -->
                <div class="flex flex-col gap-4 lg:sticky lg:top-20">

                    <!-- Imagen principal -->
                    <div class="relative rounded-3xl overflow-hidden border border-gray-100
                                bg-gradient-to-br from-rose-50 via-pink-50 to-emerald-50" style="aspect-ratio:1;">
                        <img v-if="selectedImage" :src="selectedImage" :alt="product.name"
                            class="w-full h-full object-cover transition-all duration-500"
                            :class="imgTransition ? 'opacity-0 scale-105' : 'opacity-100 scale-100'" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-[110px] leading-none drop-shadow-sm
                                         animate-[float_3s_ease-in-out_infinite]">
                                {{ product.emoji || '💐' }}
                            </span>
                        </div>

                        <!-- Badges -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            <span v-if="product.popular" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                       text-[10.5px] font-black uppercase tracking-wide text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);
                                       box-shadow:0 4px 12px rgba(245,158,11,0.35);">
                                ⭐ Popular
                            </span>
                            <span v-if="agotado" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                       text-[10.5px] font-black uppercase tracking-wide text-white bg-gray-800">
                                Agotado
                            </span>
                            <span v-else-if="stockBajo" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                       text-[10.5px] font-black uppercase tracking-wide text-white bg-amber-500">
                                ⚡ Últimos {{ product.stock }}
                            </span>
                        </div>

                        <!-- Overlay agotado -->
                        <div v-if="agotado" class="absolute inset-0 bg-white/60 backdrop-blur-[2px]
                                   flex items-center justify-center">
                            <span class="bg-white text-gray-600 font-black text-[12px] uppercase
                                         tracking-widest px-5 py-2.5 rounded-full shadow-lg border border-gray-100">
                                Agotado
                            </span>
                        </div>
                    </div>

                    <!-- Miniaturas -->
                    <div v-if="product.image_url" class="flex gap-2.5">
                        <button @click="selectImage(product.image_url!)" class="w-16 h-16 rounded-2xl overflow-hidden border-2 cursor-pointer
                                   transition-all duration-150 shrink-0" :class="selectedImage === product.image_url
                                    ? 'border-brand-red shadow-md shadow-red-100'
                                    : 'border-gray-200 hover:border-gray-300'">
                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
                        </button>
                    </div>

                    <!-- Info delivery — solo desktop -->
                    <div class="hidden lg:flex flex-col gap-2.5 mt-2">
                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center
                                        justify-center shrink-0 border border-green-100">
                                <TruckIcon class="w-4 h-4 text-green-600" />
                            </div>
                            <div>
                                <p class="font-semibold text-[13px] text-gray-800 m-0">Delivery en Chiclayo</p>
                                <p class="text-[11.5px] text-gray-400 m-0">Envío disponible a domicilio</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center
                                        justify-center shrink-0 border border-blue-100">
                                <SparklesIcon class="w-4 h-4 text-blue-500" />
                            </div>
                            <div>
                                <p class="font-semibold text-[13px] text-gray-800 m-0">Flores frescas del día</p>
                                <p class="text-[11.5px] text-gray-400 m-0">Arreglo personalizado al momento</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── COLUMNA DERECHA ── -->
                <div class="flex flex-col gap-7">

                    <!-- Tags -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span v-if="product.category" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                   text-[11.5px] font-bold" style="background:rgba(196,30,30,0.07);color:#C41E1E;
                                   border:1.5px solid rgba(196,30,30,0.15);">
                            {{ product.category.emoji }} {{ product.category.name }}
                        </span>
                        <span v-if="product.ocasion" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                   text-[11.5px] font-bold bg-rose-50 text-rose-600 border border-rose-100">
                            <SparklesIcon class="w-3.5 h-3.5" />
                            {{ product.ocasion }}
                        </span>
                    </div>

                    <!-- Nombre + descripción -->
                    <div class="flex flex-col gap-3">
                        <h1 class="font-black text-[28px] sm:text-[34px] text-gray-900 m-0
                                   leading-[1.15] tracking-tight" style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ product.name }}
                        </h1>
                        <p v-if="product.description" class="text-[14.5px] text-gray-500 m-0 leading-relaxed">
                            {{ product.description }}
                        </p>
                    </div>

                    <!-- Atributos -->
                    <div v-if="product.color || product.tamano" class="flex flex-wrap gap-2">
                        <div v-if="product.color"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-50 border border-gray-200">
                            <span class="w-3.5 h-3.5 rounded-full border border-black/10 shrink-0"
                                :style="{ background: colorHex(product.color) }" />
                            <span class="text-[12.5px] font-semibold text-gray-700 capitalize">
                                {{ product.color }}
                            </span>
                        </div>
                        <div v-if="product.tamano"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-50 border border-gray-200">
                            <ArrowsPointingOutIcon class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                            <span class="text-[12.5px] font-semibold text-gray-700">{{ product.tamano }}</span>
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="flex items-baseline gap-2">
                        <span class="text-[15px] font-bold text-gray-400">S/</span>
                        <span class="font-black text-[46px] text-gray-900 leading-none"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ totalPrice.toFixed(2) }}
                        </span>
                        <span v-if="extrasTotal > 0" class="text-[13px] text-green-600 font-semibold">
                            +S/ {{ extrasTotal.toFixed(2) }} extras
                        </span>
                    </div>

                    <div class="h-px bg-gray-100" />

                    <!-- ── PERSONALIZACIÓN ── -->
                    <div v-if="product.customization_sections.length > 0" class="flex flex-col gap-6">
                        <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest m-0">
                            Personalización
                        </p>

                        <div v-for="section in product.customization_sections" :key="section.id"
                            class="flex flex-col gap-3">

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[17px] leading-none">{{ sectionEmoji(section.seccion) }}</span>
                                <span class="font-bold text-[14px] text-gray-900">{{ section.label }}</span>
                                <span v-if="section.required" class="text-[9.5px] font-black uppercase px-2 py-0.5 rounded-full
                                           bg-red-50 text-brand-red border border-red-200 tracking-wide">
                                    Requerido
                                </span>
                                <span v-if="section.multiple"
                                    class="text-[9.5px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                    Múltiple
                                </span>
                            </div>

                            <!-- Múltiple -->
                            <div v-if="section.multiple" class="flex flex-wrap gap-2">
                                <button v-for="opt in section.options" :key="opt.id"
                                    @click="toggleMultiple(section.id, opt)" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border-2
                                           text-[13px] font-semibold cursor-pointer transition-all duration-150"
                                    :class="isSelected(section.id, opt.id)
                                        ? 'border-brand-red bg-red-50 text-brand-red'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'">
                                    <div class="w-4 h-4 rounded-md border-2 flex items-center
                                                justify-center shrink-0 transition-all" :class="isSelected(section.id, opt.id)
                                                    ? 'border-brand-red bg-brand-red' : 'border-gray-300'">
                                        <CheckIcon v-if="isSelected(section.id, opt.id)"
                                            class="w-2.5 h-2.5 text-white" />
                                    </div>
                                    {{ opt.name }}
                                </button>
                            </div>

                            <!-- Único -->
                            <div v-else class="flex flex-wrap gap-2">
                                <button v-for="opt in section.options" :key="opt.id"
                                    @click="selectSingle(section.id, opt)" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl border-2
                                           text-[13px] font-semibold cursor-pointer transition-all duration-150"
                                    :class="isSelected(section.id, opt.id)
                                        ? 'border-brand-red bg-red-50 text-brand-red'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center
                                                justify-center shrink-0 transition-all" :class="isSelected(section.id, opt.id)
                                                    ? 'border-brand-red bg-brand-red' : 'border-gray-300'">
                                        <div v-if="isSelected(section.id, opt.id)"
                                            class="w-2 h-2 rounded-full bg-white" />
                                    </div>
                                    {{ opt.name }}
                                </button>
                            </div>

                            <!-- Error -->
                            <Transition enter-active-class="transition-all duration-150"
                                enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                                <div v-if="errors[section.id]" class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl
                                           bg-red-50 border border-red-200">
                                    <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
                                    <p class="text-[12px] text-red-600 font-semibold m-0">
                                        {{ errors[section.id] }}
                                    </p>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- ── EXTRAS COMPARTIDOS (cafetería/menú) ── -->
                    <div v-if="product.extras_compartidos.length > 0" class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest m-0">
                                Extras
                            </p>
                            <span class="text-[10.5px] text-gray-300 font-medium">opcional</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div v-for="extra in product.extras_compartidos" :key="'shared-' + extra.id" class="flex items-center justify-between px-4 py-3.5 rounded-2xl
                                       border-2 transition-all duration-150" :class="getSharedExtraQty(extra.id) > 0
                                        ? 'border-brand-red bg-red-50/60'
                                        : 'border-gray-100 bg-gray-50'">
                                <div class="min-w-0 mr-3">
                                    <p class="font-semibold text-[13.5px] text-gray-900 m-0 truncate">
                                        {{ extra.name }}
                                    </p>
                                    <p class="text-[12px] font-black text-green-600 m-0 mt-0.5">
                                        +S/ {{ extra.price.toFixed(2) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button v-if="getSharedExtraQty(extra.id) > 0"
                                        @click="decrementSharedExtra(extra.id)" class="w-8 h-8 rounded-xl flex items-center justify-center
                                               border-2 border-brand-red text-brand-red font-bold
                                               cursor-pointer bg-white hover:bg-red-50 transition-all">
                                        <MinusIcon class="w-3.5 h-3.5" />
                                    </button>
                                    <span v-if="getSharedExtraQty(extra.id) > 0"
                                        class="w-5 text-center font-black text-[13px]">
                                        {{ getSharedExtraQty(extra.id) }}
                                    </span>
                                    <button @click="incrementSharedExtra(extra)" class="w-8 h-8 rounded-xl flex items-center justify-center
                                               border-2 border-brand-red bg-brand-red text-white font-bold
                                               cursor-pointer hover:bg-red-700 transition-all">
                                        <PlusIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── EXTRAS ── -->
                    <div v-if="product.extras.length > 0" class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest m-0">
                                ¿Deseas agregar algo más?
                            </p>
                            <span class="text-[10.5px] text-gray-300 font-medium">opcional</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div v-for="extra in product.extras" :key="extra.id" class="flex items-center justify-between px-4 py-3.5 rounded-2xl
                                       border-2 transition-all duration-150" :class="getExtraQty(extra.id) > 0
                                        ? 'border-brand-red bg-red-50/60'
                                        : 'border-gray-100 bg-gray-50'">
                                <div class="min-w-0 mr-3">
                                    <p class="font-semibold text-[13.5px] text-gray-900 m-0 truncate">
                                        {{ extra.name }}
                                    </p>
                                    <p class="text-[12px] font-black text-green-600 m-0 mt-0.5">
                                        +S/ {{ extra.price.toFixed(2) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button v-if="getExtraQty(extra.id) > 0" @click="decrementExtra(extra.id)" class="w-8 h-8 rounded-xl flex items-center justify-center
                                               border-2 border-brand-red text-brand-red font-bold
                                               cursor-pointer bg-white hover:bg-red-50 transition-all">
                                        −
                                    </button>
                                    <span v-if="getExtraQty(extra.id) > 0"
                                        class="font-black text-[14px] text-gray-900 min-w-[20px] text-center">
                                        {{ getExtraQty(extra.id) }}
                                    </span>
                                    <button @click="incrementExtra(extra)" class="w-8 h-8 rounded-xl flex items-center justify-center
                                               border-2 font-bold cursor-pointer transition-all"
                                        :class="getExtraQty(extra.id) > 0
                                            ? 'border-brand-red bg-brand-red text-white hover:bg-red-700'
                                            : 'border-gray-300 text-gray-500 bg-white hover:border-brand-red hover:text-brand-red'">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── RESUMEN ── -->
                    <Transition enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                        <div v-if="summaryText"
                            class="flex items-start gap-3 px-4 py-3.5 rounded-2xl bg-gray-50 border border-gray-200">
                            <CheckCircleIcon class="w-4 h-4 text-green-500 shrink-0 mt-0.5" />
                            <p class="text-[12.5px] text-gray-600 m-0 leading-relaxed">
                                <span class="font-bold text-gray-800">Tu selección: </span>
                                {{ summaryText }}
                            </p>
                        </div>
                    </Transition>

                    <div class="h-px bg-gray-100" />

                    <!-- ── CANTIDAD + CTA — solo desktop ── -->
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">

                            <!-- Qty -->
                            <div class="flex items-center gap-1 bg-gray-50 border-2 border-gray-200
                                        rounded-2xl p-1 shrink-0">
                                <button @click="decrementQty" :disabled="agotado || qty <= 1" class="w-10 h-10 rounded-xl flex items-center justify-center
                                           cursor-pointer border-none bg-white text-gray-600
                                           font-bold hover:text-brand-red hover:bg-red-50
                                           shadow-sm transition-all duration-150
                                           disabled:opacity-30 disabled:cursor-not-allowed">
                                    <MinusIcon class="w-4 h-4" />
                                </button>
                                <span class="font-black text-[17px] text-gray-900 min-w-[36px] text-center">
                                    {{ qty }}
                                </span>
                                <button @click="incrementQty" :disabled="agotado || qty >= maxQty" class="w-10 h-10 rounded-xl flex items-center justify-center
                                           cursor-pointer border-none bg-white text-gray-600
                                           font-bold hover:text-brand-red hover:bg-red-50
                                           shadow-sm transition-all duration-150
                                           disabled:opacity-30 disabled:cursor-not-allowed">
                                    <PlusIcon class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Botón agregar — SOLO DESKTOP -->
                            <button @click="addToCart" :disabled="agotado" class="flex-1 py-4 rounded-2xl font-black text-[14.5px] text-white
                                       border-none cursor-pointer uppercase tracking-wide
                                       transition-all duration-200 hover:-translate-y-0.5
                                       active:scale-[0.98] disabled:opacity-50
                                       disabled:cursor-not-allowed disabled:hover:translate-y-0
                                       hidden lg:flex items-center justify-center gap-2.5" :class="agotado
                                        ? 'bg-gray-400'
                                        : added
                                            ? 'bg-green-600 shadow-[0_6px_20px_rgba(22,163,74,0.3)]'
                                            : 'bg-brand-red hover:bg-red-700 shadow-[0_6px_20px_rgba(196,30,30,0.3)]'"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                <Transition mode="out-in" enter-active-class="transition-all duration-200"
                                    enter-from-class="opacity-0 scale-50"
                                    leave-active-class="transition-all duration-150"
                                    leave-to-class="opacity-0 scale-50">
                                    <CheckIcon v-if="added" key="check" class="w-5 h-5" />
                                    <ShoppingCartIcon v-else key="cart" class="w-5 h-5" />
                                </Transition>
                                <span>
                                    {{ agotado ? 'Agotado'
                                        : added ? '¡Agregado!'
                                            : `Agregar · S/ ${totalPrice.toFixed(2)}` }}
                                </span>
                            </button>
                        </div>

                        <!-- Link ver carrito — solo desktop -->
                        <Transition enter-active-class="transition-all duration-300"
                            enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0">
                            <RouterLink v-if="added" to="/checkout" class="hidden lg:flex items-center justify-center gap-2 py-3.5 rounded-2xl
                                       border-2 border-brand-red text-brand-red font-bold text-[13.5px]
                                       no-underline hover:bg-red-50 transition-all duration-150">
                                <span>Ver carrito y confirmar pedido</span>
                                <ArrowRightIcon class="w-4 h-4" />
                            </RouterLink>
                        </Transition>
                    </div>

                    <!-- Info delivery — solo mobile -->
                    <div class="flex lg:hidden flex-col gap-2.5 pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-3 text-[13px] text-gray-500">
                            <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center
                                        justify-center shrink-0 border border-green-100">
                                <TruckIcon class="w-4 h-4 text-green-600" />
                            </div>
                            <span>Delivery disponible en Chiclayo y alrededores</span>
                        </div>
                        <div class="flex items-center gap-3 text-[13px] text-gray-500">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center
                                        justify-center shrink-0 border border-blue-100">
                                <SparklesIcon class="w-4 h-4 text-blue-500" />
                            </div>
                            <span>Flores frescas del día — arreglo personalizado</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- ══ BOTTOM BAR MOBILE — único botón en mobile ══ -->
        <div v-if="product && !loading" class="fixed bottom-0 inset-x-0 z-50 lg:hidden bg-white border-t border-gray-100
                   px-4 py-3 flex items-center gap-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">

            <!-- Precio + nombre -->
            <div class="flex flex-col min-w-0 shrink-0">
                <span class="text-[11px] text-gray-400 font-medium truncate max-w-[120px]">
                    {{ product.name }}
                </span>
                <div class="flex items-baseline gap-0.5">
                    <span class="text-[11px] text-gray-400 font-semibold">S/</span>
                    <span class="font-black text-[20px] text-gray-900 leading-none"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ totalPrice.toFixed(2) }}
                    </span>
                </div>
            </div>

            <!-- Botón agregar -->
            <button @click="addToCart" :disabled="agotado" class="flex-1 py-3 rounded-2xl font-black text-[13.5px] text-white
                       border-none cursor-pointer uppercase tracking-wide
                       transition-all duration-150 active:scale-[0.98]
                       disabled:opacity-50 flex items-center justify-center gap-2" :class="agotado ? 'bg-gray-400'
                        : added ? 'bg-green-600'
                            : 'bg-brand-red hover:bg-red-700'" style="font-family:'Plus Jakarta Sans',sans-serif;">
                <CheckIcon v-if="added" class="w-5 h-5 shrink-0" />
                <ShoppingCartIcon v-else class="w-5 h-5 shrink-0" />
                <span>{{ agotado ? 'Agotado' : added ? '¡Agregado!' : 'Agregar al carrito' }}</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import type { Product, CustomizationOption, ProductExtra } from '@/stores/products'
import type { CartCustomization, CartExtra } from '@/stores/cart'
import {
    ChevronLeftIcon, ChevronRightIcon, ArrowRightIcon,
    SparklesIcon, ArrowsPointingOutIcon, CheckIcon,
    ExclamationCircleIcon, ShoppingCartIcon, CheckCircleIcon,
    TruckIcon, MinusIcon, PlusIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const productsStore = useProductsStore()
const cartStore = useCartStore()

const loading = ref(false)
const product = ref<Product | null>(null)
const selectedImage = ref<string | null>(null)
const imgTransition = ref(false)
const added = ref(false)
const qty = ref(1)
const selections = ref<Map<number, CartCustomization>>(new Map())
const extrasMap = ref<Map<number, CartExtra>>(new Map())
const sharedExtrasMap = ref<Map<number, CartExtra>>(new Map()) // ← extras_compartidos
const errors = ref<Record<number, string>>({})

onMounted(async () => {
    const slug = route.params.slug as string
    let found = productsStore.products.find(p => p.slug === slug)
    if (!found) {
        loading.value = true
        await productsStore.fetch()
        found = productsStore.products.find(p => p.slug === slug)
        loading.value = false
    }
    product.value = found ?? null
    if (product.value?.image_url) selectedImage.value = product.value.image_url
})

// ── Computed ──────────────────────────────────────────────
const agotado = computed(() =>
    (product.value?.controla_stock ?? false) && (product.value?.stock ?? 0) <= 0
)
const stockBajo = computed(() =>
    (product.value?.controla_stock ?? false) &&
    (product.value?.stock ?? 0) > 0 &&
    (product.value?.stock ?? 0) <= 5
)
const maxQty = computed(() =>
    product.value?.controla_stock ? Math.max(1, product.value.stock ?? 1) : 99
)
const modifiersTotal = computed(() => {
    let sum = 0
    selections.value.forEach(sec =>
        sec.selections.forEach(s => { sum += s.price_modifier ?? 0 })
    )
    return sum
})
const extrasTotal = computed(() => {
    let sum = 0
    extrasMap.value.forEach(e => { sum += e.price * e.qty })
    sharedExtrasMap.value.forEach(e => { sum += e.price * e.qty })
    return sum
})
const totalPrice = computed(() =>
    ((product.value?.price ?? 0) + modifiersTotal.value + extrasTotal.value) * qty.value
)
const summaryText = computed(() => {
    const parts: string[] = []
    selections.value.forEach(sec => sec.selections.forEach(s => parts.push(s.name)))
    extrasMap.value.forEach(e => {
        if (e.qty > 0) parts.push(e.qty > 1 ? `${e.name} ×${e.qty}` : `+ ${e.name}`)
    })
    sharedExtrasMap.value.forEach(e => {
        if (e.qty > 0) parts.push(e.qty > 1 ? `${e.name} ×${e.qty}` : `+ ${e.name}`)
    })
    return parts.join(' · ')
})

// ── Imagen ────────────────────────────────────────────────
function selectImage(url: string) {
    if (url === selectedImage.value) return
    imgTransition.value = true
    setTimeout(() => { selectedImage.value = url; imgTransition.value = false }, 200)
}

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
            current.selections.push({ option_id: opt.id, name: opt.name, price_modifier: opt.price_modifier ?? 0 })
        }
    } else {
        selections.value.set(sectionId, {
            section_id: sectionId, seccion: section.seccion, label: section.label,
            selections: [{ option_id: opt.id, name: opt.name, price_modifier: opt.price_modifier ?? 0 }],
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
            section_id: sectionId, seccion: section.seccion, label: section.label,
            selections: [{ option_id: opt.id, name: opt.name, price_modifier: opt.price_modifier ?? 0 }],
        })
    }
    selections.value = new Map(selections.value)
}

function isSelected(sectionId: number, optionId: number): boolean {
    return selections.value.get(sectionId)?.selections.some(s => s.option_id === optionId) ?? false
}

// ── Extras ────────────────────────────────────────────────
function getExtraQty(extraId: number): number {
    return extrasMap.value.get(extraId)?.qty ?? 0
}

function incrementExtra(extra: ProductExtra) {
    const current = extrasMap.value.get(extra.id)
    if (current) { current.qty++ }
    else {
        extrasMap.value.set(extra.id, {
            extra_id: extra.id, name: extra.name, price: extra.price, qty: 1,
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

// ── Extras compartidos (cafetería/menú) ──────────────────
function getSharedExtraQty(extraId: number): number {
    return sharedExtrasMap.value.get(extraId)?.qty ?? 0
}

function incrementSharedExtra(extra: ProductExtra) {
    const current = sharedExtrasMap.value.get(extra.id)
    if (current) { current.qty++ }
    else {
        sharedExtrasMap.value.set(extra.id, {
            extra_id: extra.id, name: extra.name, price: extra.price, qty: 1,
        })
    }
    sharedExtrasMap.value = new Map(sharedExtrasMap.value)
}

function decrementSharedExtra(extraId: number) {
    const current = sharedExtrasMap.value.get(extraId)
    if (!current) return
    if (current.qty <= 1) sharedExtrasMap.value.delete(extraId)
    else current.qty--
    sharedExtrasMap.value = new Map(sharedExtrasMap.value)
}

// ── Cantidad ──────────────────────────────────────────────
function incrementQty() { qty.value = Math.min(maxQty.value, qty.value + 1) }
function decrementQty() { qty.value = Math.max(1, qty.value - 1) }

// ── Agregar al carrito ────────────────────────────────────
function addToCart() {
    if (!product.value || agotado.value) return

    errors.value = {}
    let valid = true

    product.value.customization_sections.forEach(sec => {
        if (sec.required && !selections.value.has(sec.id)) {
            errors.value[sec.id] = 'Selecciona una opción para continuar'
            valid = false
        }
    })

    if (!valid) return

    const customization = Array.from(selections.value.values())
    const extras = [
        ...Array.from(extrasMap.value.values()),
        ...Array.from(sharedExtrasMap.value.values()),
    ].filter(e => e.qty > 0)

    for (let i = 0; i < qty.value; i++) {
        cartStore.add(product.value, customization, extras)
    }

    added.value = true

    // Feedback breve y luego volver al catálogo
    setTimeout(() => {
        router.push('/')
    }, 800)
}

// ── Helpers ───────────────────────────────────────────────
const COLOR_MAP: Record<string, string> = {
    rojo: '#DC2626', rosa: '#EC4899', rosado: '#F472B6', blanco: '#F9FAFB',
    amarillo: '#FACC15', naranja: '#FB923C', morado: '#9333EA', lila: '#C084FC',
    azul: '#3B82F6', celeste: '#7DD3FC', verde: '#22C55E', fucsia: '#D946EF',
    durazno: '#FDBA74', coral: '#FF7F6B', crema: '#FEF3C7', vino: '#7F1D1D',
    multicolor: 'linear-gradient(135deg,#EC4899,#FACC15,#22C55E,#3B82F6)',
}

function colorHex(name: string): string {
    return COLOR_MAP[name.trim().toLowerCase()] ?? '#D1D5DB'
}

function sectionEmoji(seccion: string): string {
    const map: Record<string, string> = {
        envoltura: '🎁', lazo: '🎀', follaje: '🌿',
        dedicatoria: '✍️', presentacion: '🪴', complemento: '🧸',
    }
    return map[seccion] ?? '🌸'
}
</script>

<style scoped>
@keyframes float {

    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }

    33% {
        transform: translateY(-10px) rotate(-3deg);
    }

    66% {
        transform: translateY(-5px) rotate(2deg);
    }
}
</style>