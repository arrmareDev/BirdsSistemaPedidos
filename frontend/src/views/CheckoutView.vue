<template>
  <div class="w-full max-w-2xl mx-auto px-5 md:px-8 pb-16">

    <!-- Header -->
    <div class="flex items-center gap-4 py-6 border-b border-surface-border mb-8">
      <RouterLink to="/" class="w-10 h-10 rounded-xl border-2 border-surface-border bg-white
               flex items-center justify-center text-lg text-ink-muted no-underline
               hover:border-brand-red/40 hover:text-brand-red
               transition-all duration-150 shrink-0">
        ←
      </RouterLink>
      <div>
        <h1 class="font-black text-[22px] text-ink leading-none m-0 uppercase tracking-wide">
          Tu Pedido
        </h1>
        <p class="text-[13px] text-ink-muted mt-1 m-0">Revisa y confirma</p>
      </div>
    </div>

    <!-- ══ TIPO DE PEDIDO ══ -->
    <div class="mb-6">
      <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-3">
        ¿Cómo lo recibes?
      </label>
      <div class="grid gap-3" :style="{ gridTemplateColumns: `repeat(${ORDER_TYPES.length}, minmax(0, 1fr))` }">
        <button v-for="t in ORDER_TYPES" :key="t.id" @click="form.type = t.id as any" class="flex flex-col items-center gap-1.5 py-4 rounded-2xl border-2
                 font-bold text-[12px] cursor-pointer transition-all duration-200 uppercase" :class="form.type === t.id
                  ? 'border-brand-red bg-brand-red/8 text-brand-red shadow-red-sm'
                  : 'border-surface-border bg-white text-ink-muted hover:border-brand-red/40 hover:text-brand-red'">
          <AppIcon :name="t.icon" :size="22" />
          {{ t.label }}
        </button>
      </div>
    </div>

    <!-- ══ DATOS DEL CLIENTE ══ -->
    <div class="bg-white rounded-3xl border border-surface-border shadow-card p-6 mb-6">
      <h2 class="font-black text-[15px] text-ink mb-5 m-0 uppercase tracking-wide">
        Tus datos
      </h2>
      <div class="flex flex-col gap-4">

        <!-- Nombre -->
        <div>
          <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
            Nombre *
          </label>
          <input v-model="form.name" placeholder="¿Cómo te llamamos?" class="checkout-input" />
        </div>

        <!-- WhatsApp -->
        <div>
          <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
            WhatsApp *
          </label>
          <input v-model="form.phone" type="tel" placeholder="987654321"
            @input="form.phone = form.phone.replace(/\D/g, '').slice(0, 9)" maxlength="9" class="checkout-input" />
        </div>

        <!-- ══ SECCIÓN LOCAL ══ -->
        <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
          leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
          <div v-if="form.type === 'local'" class="flex flex-col gap-3">
            <div class="px-4 py-3.5 rounded-2xl bg-blue-50 border border-blue-200 flex items-start gap-3">
              <Armchair :size="20" class="shrink-0 text-blue-600" />
              <div>
                <p class="font-bold text-[13px] text-blue-800 m-0">Consumo en el local</p>
                <p class="text-[11.5px] text-blue-600 m-0 mt-0.5">
                  Indícanos el número de tu mesa para llevarte el pedido.
                </p>
              </div>
            </div>
            <div>
              <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
                Número de mesa *
              </label>
              <input v-model="form.mesa" placeholder="Ej: 4" class="checkout-input" />
            </div>
          </div>
        </Transition>

        <!-- ══ SECCIÓN DELIVERY ══ -->
        <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
          leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
          <div v-if="form.type === 'delivery'" class="flex flex-col gap-5">

            <!-- 1. UBICACIÓN EN EL MAPA -->
            <div class="flex flex-col gap-3">
              <div>
                <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-1">
                  Tu ubicación de entrega *
                </label>
                <p class="text-[11.5px] text-ink-muted m-0">
                  Usa el GPS, busca tu dirección o mueve el pin en el mapa
                </p>
              </div>

              <!-- Botón GPS -->
              <button @click="usarGPS" :disabled="loadingGPS" class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl
                       border-2 border-brand-red/30 bg-brand-red/5 text-brand-red
                       font-bold text-[13px] cursor-pointer transition-all duration-150
                       hover:bg-brand-red/10 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="loadingGPS"
                  class="w-4 h-4 border-2 border-brand-red/30 border-t-brand-red rounded-full animate-spin" />
                <MapPin v-else :size="16" />
                {{ loadingGPS ? 'Obteniendo ubicación...' : 'Usar mi ubicación actual (GPS)' }}
              </button>

              <!-- Error GPS -->
              <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0">
                <div v-if="gpsError" class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl
                         bg-red-50 border border-red-200 text-[12px] text-red-600">
                  <TriangleAlert :size="14" class="shrink-0" /> {{ gpsError }}
                </div>
              </Transition>

              <!-- Búsqueda por texto -->
              <div class="relative">
                <input v-model="mapSearch" @input="debouncedMapSearch" placeholder="O busca tu dirección aquí..."
                  class="checkout-input pr-10" />
                <div v-if="mapSearching" class="absolute right-3 top-1/2 -translate-y-1/2
                         w-4 h-4 border-2 border-gray-200 border-t-brand-red rounded-full animate-spin" />
              </div>

              <!-- Resultados de búsqueda -->
              <div v-if="mapResults.length > 0"
                class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <button v-for="r in mapResults" :key="r.place_id" @click="selectMapResult(r)" class="w-full text-left px-4 py-3 text-[13px] text-gray-700
                         border-none bg-transparent cursor-pointer
                         hover:bg-gray-50 border-b border-gray-100
                         last:border-b-0 transition-colors duration-150">
                  {{ r.display_name }}
                </button>
              </div>

              <!-- Mapa Leaflet -->
              <div id="delivery-map" class="w-full h-64 rounded-2xl overflow-hidden border-2 border-surface-border" />

              <!-- Dirección exacta -->
              <div>
                <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
                  Dirección exacta *
                </label>
                <input v-model="form.address" placeholder="Se completa al marcar el mapa, puedes editarla"
                  class="checkout-input" />
              </div>

              <!-- Referencia -->
              <div>
                <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
                  Referencia *
                </label>
                <input v-model="form.reference" placeholder="Ej: Mz. C Lt. 14, portón negro, frente al parque..."
                  class="checkout-input" :class="!form.reference.trim() && form.address ? 'border-amber-300' : ''" />
                <p class="text-[11px] text-gray-400 mt-1 m-0">
                  Ayuda a nuestro repartidor a encontrarte
                </p>
              </div>
            </div>

            <!-- 2. ZONA DETECTADA -->
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
              leave-to-class="opacity-0">

              <div v-if="detectingZone"
                class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200">
                <div class="w-4 h-4 border-2 border-gray-300 border-t-brand-red rounded-full animate-spin" />
                <span class="text-[12.5px] text-ink-muted font-medium">Detectando tu zona de entrega...</span>
              </div>

              <div v-else-if="selectedZone"
                class="flex items-center justify-between px-4 py-3 rounded-2xl bg-pink-50 border border-pink-200">
                <div class="flex items-center gap-2">
                  <Truck :size="18" class="text-pink-600" />
                  <div>
                    <p class="text-[12px] font-black text-pink-700 m-0">{{ selectedZone.nombre }}</p>
                    <p class="text-[11px] text-pink-500 m-0">
                      {{ detectedZone ? 'Costo de delivery detectado' : 'Zona seleccionada' }}
                    </p>
                  </div>
                </div>
                <div class="flex items-baseline gap-0.5">
                  <span class="text-[11px] font-semibold text-pink-500">S/</span>
                  <span class="font-black text-[18px] text-pink-700 leading-none">
                    {{ selectedZone.precio.toFixed(2) }}
                  </span>
                </div>
              </div>

              <div v-else-if="zoneNotFound" class="flex flex-col gap-3">
                <div class="flex items-start gap-2.5 px-4 py-3 rounded-2xl bg-amber-50 border border-amber-200">
                  <TriangleAlert :size="14" class="text-amber-600 shrink-0 mt-0.5" />
                  <p class="text-[12px] text-amber-700 m-0 leading-relaxed">
                    Tu dirección está fuera de nuestra zona de cobertura. Puedes seleccionar la tarifa más cercana.
                  </p>
                </div>
                <div>
                  <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
                    Selecciona tu tarifa *
                  </label>
                  <select v-model="form.delivery_zone_id" @change="onManualZoneChange"
                    class="checkout-input cursor-pointer" :class="loadingZones ? 'opacity-50' : ''">
                    <option value="">
                      {{ loadingZones ? 'Cargando tarifas...' : 'Selecciona una tarifa...' }}
                    </option>
                    <option v-for="z in zones" :key="z.id" :value="z.id">
                      {{ z.nombre }} — S/ {{ z.precio.toFixed(2) }}
                    </option>
                  </select>
                </div>
              </div>
            </Transition>

          </div>
        </Transition>

        <!-- ══ SECCIÓN RECOGER EN TIENDA ══ -->
        <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
          leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
          <div v-if="form.type === 'recoger'"
            class="px-4 py-3.5 rounded-2xl bg-green-50 border border-green-200 flex items-start gap-3">
            <Store :size="20" class="shrink-0 text-green-600" />
            <div>
              <p class="font-bold text-[13px] text-green-800 m-0">Recoger en tienda</p>
              <p class="text-[11.5px] text-green-600 m-0 mt-0.5">
                Te avisaremos por WhatsApp cuando tu pedido esté listo para recoger.
              </p>
            </div>
          </div>
        </Transition>

      </div>
    </div>

    <!-- ══ ENTREGA PROGRAMADA ══ -->
    <div v-if="pedidoConfigStore.config.entrega_programada_activo"
      class="bg-white rounded-3xl border border-surface-border shadow-card p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="font-black text-[15px] text-ink m-0 uppercase tracking-wide">
            {{ pedidoConfigStore.config.entrega_programada_label }}
          </h2>
          <p class="text-[12px] text-ink-muted mt-0.5 m-0">
            Opcional — si no eliges fecha, lo prepararemos lo antes posible
          </p>
        </div>
        <!-- Toggle entrega programada -->
        <button @click="form.entrega_programada = !form.entrega_programada"
          class="relative w-12 h-6 rounded-full transition-all duration-200 border-none cursor-pointer"
          :class="form.entrega_programada ? 'bg-brand-red' : 'bg-gray-200'">
          <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all duration-200"
            :class="form.entrega_programada ? 'left-6' : 'left-0.5'" />
        </button>
      </div>

      <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
        leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
        <div v-if="form.entrega_programada" class="flex flex-col gap-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
                Fecha de entrega *
              </label>
              <input v-model="form.fecha_entrega" type="date" :min="fechaMinima" class="checkout-input" />
            </div>
            <div>
              <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
                Hora aproximada
              </label>
              <select v-model="form.hora_entrega" class="checkout-input cursor-pointer">
                <option value="">Cualquier hora</option>
                <option v-for="h in HORARIOS" :key="h.value" :value="h.value">
                  {{ h.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-2xl bg-pink-50 border border-pink-200">
            <Calendar :size="16" class="text-pink-500 shrink-0" />
            <p class="text-[12px] text-pink-700 m-0 leading-relaxed">
              Nos aseguraremos de que tu pedido esté listo para la fecha elegida.
            </p>
          </div>
        </div>
      </Transition>
    </div>

    <!-- ══ MENSAJE PERSONALIZADO ══ -->
    <div v-if="pedidoConfigStore.config.mensaje_activo"
      class="bg-white rounded-3xl border border-surface-border shadow-card p-6 mb-6">
      <h2 class="font-black text-[15px] text-ink mb-2 m-0 uppercase tracking-wide">
        {{ pedidoConfigStore.config.mensaje_label }}
      </h2>
      <p class="text-[12px] text-ink-muted mb-4 m-0">
        Opcional — lo incluiremos junto con tu pedido
      </p>
      <textarea v-model="form.mensaje_tarjeta" placeholder="Ej: ¡Feliz cumpleaños! Con todo mi cariño" rows="3"
        maxlength="300" class="w-full px-4 py-3 rounded-xl border-2 border-surface-border
               bg-white text-[14px] text-ink outline-none resize-none
               placeholder:text-ink-faint focus:border-brand-red transition-all duration-200" />
      <p class="text-[11px] text-gray-400 mt-1.5 m-0 text-right">
        {{ form.mensaje_tarjeta.length }}/300 caracteres
      </p>
    </div>

    <!-- ══ RESUMEN DEL CARRITO ══ -->
    <div class="bg-white rounded-3xl border border-surface-border shadow-card mb-6 overflow-hidden">
      <div class="px-6 py-4 border-b border-surface-border bg-surface-warm flex items-center gap-3">
        <div class="w-1 h-5 rounded-full bg-brand-red" />
        <h2 class="font-black text-[15px] text-ink m-0 uppercase tracking-wide">Resumen</h2>
      </div>

      <div class="divide-y divide-surface-border/60">
        <div v-for="item in cartStore.items" :key="item._uid" class="flex items-start gap-3 px-6 py-4">
          <div class="w-8 h-8 rounded-lg bg-brand-red flex items-center justify-center
                      text-white text-[12px] font-black shrink-0 mt-0.5">
            {{ item.qty }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-ink text-[14px] m-0">{{ item.name }}</p>
            <p v-if="item.customSummary" class="text-ink-muted text-[12px] mt-0.5 m-0">
              {{ item.customSummary }}
            </p>
          </div>
          <span class="font-black text-[15px] text-brand-red shrink-0">
            S/ {{ (item.price * item.qty).toFixed(2) }}
          </span>
        </div>
      </div>

      <!-- Totales -->
      <div class="px-6 py-4 border-t border-surface-border bg-surface-warm flex flex-col gap-2">
        <div class="flex items-center justify-between text-[13px]">
          <span class="text-ink-muted font-medium">Subtotal</span>
          <span class="font-bold text-ink">S/ {{ cartStore.total.toFixed(2) }}</span>
        </div>
        <div v-if="form.type === 'delivery' && selectedZone" class="flex items-center justify-between text-[13px]">
          <span class="text-ink-muted font-medium">Delivery ({{ selectedZone.nombre }})</span>
          <span class="font-bold text-pink-600">+ S/ {{ selectedZone.precio.toFixed(2) }}</span>
        </div>
        <div v-if="form.type === 'delivery' && !selectedZone" class="text-[12px] text-ink-muted italic">
          Marca tu ubicación en el mapa para ver el costo de delivery
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-surface-border">
          <span class="font-semibold text-[15px] text-ink">Total</span>
          <span class="font-black text-[30px] text-brand-red leading-none">
            S/ {{ totalConDelivery.toFixed(2) }}
          </span>
        </div>
      </div>
    </div>

    <!-- ══ NOTA ESPECIAL ══ -->
    <div class="mb-6">
      <label class="block text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-2">
        Nota adicional
      </label>
      <textarea v-model="form.note" placeholder="Indicaciones adicionales para tu pedido..." rows="2" class="w-full px-4 py-3 rounded-xl border-2 border-surface-border
               bg-white text-[14px] text-ink outline-none resize-none
               placeholder:text-ink-faint focus:border-brand-red transition-all duration-200" />
    </div>

    <!-- ══ BOTÓN FLOTANTE — AYUDA CON TU PEDIDO ══ -->
    <Teleport to="body">
      <div class="fixed bottom-5 right-5 z-50 flex items-center gap-3">

        <!-- Tooltip: visible por defecto en desktop -->
        <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 translate-x-2"
          leave-active-class="transition-all duration-100" leave-to-class="opacity-0 translate-x-2">
          <div v-if="showHelpTooltip" class="hidden sm:flex items-start gap-2 bg-white rounded-2xl border border-surface-border
                   shadow-lg px-4 py-3 max-w-[200px]">
            <div class="flex-1">
              <p class="font-bold text-[13px] text-ink m-0">¿Necesitas ayuda con tu pedido?</p>
              <p class="text-[11.5px] text-ink-muted m-0 mt-0.5">Escríbenos por WhatsApp</p>
            </div>
            <button @click="showHelpTooltip = false" class="w-4 h-4 rounded-full flex items-center justify-center
                     text-ink-faint hover:text-ink-muted border-none bg-transparent cursor-pointer shrink-0 mt-0.5"
              aria-label="Cerrar">
              <X :size="12" />
            </button>
          </div>
        </Transition>

        <!-- Botón -->
        <button @click="contactSoporte" class="relative w-14 h-14 rounded-full bg-[#25D366] border-none cursor-pointer
                 flex items-center justify-center shadow-[0_4px_20px_rgba(37,211,102,0.5)]
                 hover:bg-[#128C7E] hover:scale-105 active:scale-95
                 transition-all duration-200 shrink-0" aria-label="Ayuda por WhatsApp">
          <span class="absolute inset-0 rounded-full bg-[#25D366] animate-ping-slow opacity-75" />
          <WhatsAppIcon :size="24" class="relative text-white" />
        </button>
      </div>
    </Teleport>

    <!-- Error general -->
    <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
      leave-to-class="opacity-0">
      <div v-if="errorMsg"
        class="flex items-center gap-2.5 px-4 py-3.5 rounded-2xl bg-red-50 border border-red-200 mb-5">
        <TriangleAlert :size="16" class="text-red-500 shrink-0" />
        <p class="text-[12.5px] text-red-700 m-0">{{ errorMsg }}</p>
      </div>
    </Transition>

    <!-- CTA -->
    <button @click="handleOrder" :disabled="!canSubmit || orderStore.loading" class="w-full py-4 rounded-2xl font-black text-[16px] text-white
             border-none cursor-pointer bg-brand-red shadow-red-md
             uppercase tracking-wide
             hover:bg-brand-red2 hover:-translate-y-0.5 hover:shadow-red-lg
             active:scale-[0.98] transition-all duration-200
             disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0
             flex items-center justify-center gap-2">
      <span v-if="orderStore.loading"
        class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
      <WhatsAppIcon v-else :size="18" />
      {{ orderStore.loading ? 'Procesando...' : 'Confirmar y pedir por WhatsApp' }}
    </button>

    <p class="text-center text-[12px] text-ink-muted mt-3">
      Tu pedido se registra primero y luego te abrimos WhatsApp.
    </p>

  </div>
</template>

<script setup lang="ts">
import { reactive, computed, ref, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useOrderStore } from '@/stores/order'
import { usePedidoConfigStore } from '@/stores/pedidoConfig'
import AppIcon from '@/components/AppIcon.vue'
import WhatsAppIcon from '@/components/icons/WhatsAppIcon.vue'
import { Armchair, MapPin, TriangleAlert, Truck, Store, Calendar, X } from 'lucide-vue-next'
import api from '@/utils/api'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({ iconUrl: markerIcon, iconRetinaUrl: markerIcon2x, shadowUrl: markerShadow })

// ── Stores y router ───────────────────────────────────────
const cartStore = useCartStore()
const orderStore = useOrderStore()
const pedidoConfigStore = usePedidoConfigStore()
const router = useRouter()

// ── Tipos ─────────────────────────────────────────────────
interface DeliveryZone { id: number; nombre: string; precio: number }

// ── Constantes ────────────────────────────────────────────
const CHICLAYO_LAT = -6.7741
const CHICLAYO_LNG = -79.8409

// Orden: local, recoger, delivery — coincide con App\Enums\SaleChannel
const ORDER_TYPES = [
  // { id: 'local', icon: 'armchair', label: 'Consumo en local' },
  { id: 'recoger', icon: 'store', label: 'Para llevar' },
  { id: 'delivery', icon: 'truck', label: 'Delivery' },
] as const

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

// ── Estado del formulario ─────────────────────────────────
const form = reactive({
  name: '',
  phone: '',
  type: 'recoger' as 'local' | 'recoger' | 'delivery',
  mesa: '',
  address: '',
  reference: '',
  delivery_zone_id: 0,
  metodo_pago: 'anticipado' as 'anticipado',
  note: '',
  lat: null as number | null,
  lng: null as number | null,
  // ── Entrega programada / mensaje personalizado ─────────────
  mensaje_tarjeta: '',
  fecha_entrega: '',
  hora_entrega: '',
  entrega_programada: false,
})

// ── Estado general ────────────────────────────────────────
const errorMsg = ref('')
const showHelpTooltip = ref(true) // visible por defecto, se puede cerrar con el botón X

// ── Zonas ────────────────────────────────────────────────
const zones = ref<DeliveryZone[]>([])
const loadingZones = ref(false)
const detectedZone = ref<DeliveryZone | null>(null)
const detectingZone = ref(false)
const zoneNotFound = ref(false)

// ── GPS ───────────────────────────────────────────────────
const loadingGPS = ref(false)
const gpsError = ref('')

// ── Mapa ─────────────────────────────────────────────────
let map: L.Map | null = null
let marker: L.Marker | null = null
const mapSearch = ref('')
const mapResults = ref<any[]>([])
const mapSearching = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | null = null

// ── Fecha mínima (hoy) ───────────────────────────────────
const fechaMinima = computed(() => new Date().toISOString().split('T')[0])

// ── Computados ────────────────────────────────────────────
const selectedZone = computed<DeliveryZone | null>(() => {
  if (detectedZone.value) return detectedZone.value
  return zones.value.find(z => z.id === form.delivery_zone_id) ?? null
})

const totalConDelivery = computed(() => {
  const delivery = form.type === 'delivery' && selectedZone.value
    ? selectedZone.value.precio
    : 0
  return cartStore.total + delivery
})

const canSubmit = computed(() => {
  if (!form.name.trim() || !form.phone.trim() || cartStore.isEmpty) return false

  if (form.type === 'local') {
    return !!form.mesa.trim()
  }

  if (form.type === 'delivery') {
    return !!form.delivery_zone_id
      && !!form.address.trim()
      && !!form.reference.trim()
  }

  // recoger
  if (form.entrega_programada && !form.fecha_entrega) return false
  return true
})

// ── Lifecycle ─────────────────────────────────────────────
onUnmounted(() => { if (map) { map.remove(); map = null } })

watch(() => form.type, async (val) => {
  if (val === 'delivery') {
    await nextTick()
    initMap()
  } else {
    if (map) { map.remove(); map = null }
    detectedZone.value = null
    zoneNotFound.value = false
    form.delivery_zone_id = 0
    gpsError.value = ''
  }

  if (val !== 'local') {
    form.mesa = ''
  }
})

watch(() => form.entrega_programada, (val) => {
  if (!val) {
    form.fecha_entrega = ''
    form.hora_entrega = ''
  }
})

// ── Zonas ────────────────────────────────────────────────
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
      if (map && marker) { map.setView([lat, lng], 17); marker.setLatLng([lat, lng]) }
      form.lat = lat
      form.lng = lng
      await Promise.all([reverseGeocode(lat, lng), detectarZona(lat, lng)])
      loadingGPS.value = false
    },
    (error) => {
      loadingGPS.value = false
      const messages: Record<number, string> = {
        1: 'Permiso de ubicación denegado. Actívalo en la configuración de tu navegador.',
        2: 'No se pudo obtener tu ubicación. Intenta marcarla manualmente en el mapa.',
        3: 'Tiempo de espera agotado. Intenta de nuevo.',
      }
      gpsError.value = messages[error.code] ?? 'Error al obtener ubicación.'
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

// ── Mapa Leaflet ──────────────────────────────────────────
function initMap() {
  if (map) return
  map = L.map('delivery-map', { center: [CHICLAYO_LAT, CHICLAYO_LNG], zoom: 14 })
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19,
  }).addTo(map)

  const redIcon = L.divIcon({
    className: '',
    html: `<div style="width:32px;height:32px;background:var(--color-brand-primary,#C41E1E);border:3px solid white;
      border-radius:50% 50% 50% 0;transform:rotate(-45deg);
      box-shadow:0 2px 8px rgba(var(--color-brand-primary-rgb,196,30,30),0.4);"></div>`,
    iconSize: [32, 32], iconAnchor: [16, 32],
  })

  marker = L.marker([CHICLAYO_LAT, CHICLAYO_LNG], { draggable: true, icon: redIcon }).addTo(map)
  marker.on('dragend', () => {
    const pos = marker!.getLatLng()
    form.lat = pos.lat; form.lng = pos.lng
    reverseGeocode(pos.lat, pos.lng); detectarZona(pos.lat, pos.lng)
  })
  map.on('click', (e: L.LeafletMouseEvent) => {
    marker!.setLatLng(e.latlng)
    form.lat = e.latlng.lat; form.lng = e.latlng.lng
    reverseGeocode(e.latlng.lat, e.latlng.lng); detectarZona(e.latlng.lat, e.latlng.lng)
  })
  form.lat = CHICLAYO_LAT; form.lng = CHICLAYO_LNG
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
  clearTimeout(searchTimer!)
  if (mapSearch.value.length < 3) { mapResults.value = []; return }
  searchTimer = setTimeout(searchAddress, 500)
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
  if (map && marker) { map.setView([lat, lng], 17); marker.setLatLng([lat, lng]) }
  form.lat = lat; form.lng = lng
  form.address = result.display_name.split(',').slice(0, 3).join(',').trim()
  mapResults.value = []; mapSearch.value = ''
  detectarZona(lat, lng)
}

// ── Ayuda con el pedido ────────────────────────────────────
function contactSoporte() {
  const phone = (import.meta.env.VITE_WA_PHONE ?? '51984199340').replace(/\D/g, '')
  const msg = 'Hola, tengo una consulta sobre mi pedido en la web.'
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank')
}

// ── Submit ────────────────────────────────────────────────
async function handleOrder() {
  errorMsg.value = ''

  if (cartStore.isEmpty) { errorMsg.value = 'Tu carrito está vacío'; return }

  if (form.type === 'local' && !form.mesa.trim()) {
    errorMsg.value = 'Indica el número de mesa'
    return
  }

  if (form.type === 'delivery') {
    if (!form.delivery_zone_id) {
      errorMsg.value = 'Marca tu ubicación en el mapa para detectar el costo de delivery'
      return
    }
    if (!form.address.trim()) { errorMsg.value = 'Ingresa tu dirección de entrega'; return }
    if (!form.reference.trim()) { errorMsg.value = 'La referencia es obligatoria'; return }
  }

  if (form.entrega_programada && !form.fecha_entrega) {
    errorMsg.value = 'Selecciona una fecha de entrega'
    return
  }

  const ok = await orderStore.placeOrder({
    client_name: form.name.trim(),
    client_phone: form.phone.trim(),
    type: form.type,
    mesa: form.mesa || undefined,
    address: form.address || undefined,
    reference: form.reference || undefined,
    delivery_zone_id: form.delivery_zone_id || undefined,
    delivery_fee: selectedZone.value?.precio ?? 0,
    metodo_pago: form.metodo_pago || undefined,
    note: form.note || undefined,
    lat: form.lat ?? undefined,
    lng: form.lng ?? undefined,
    // ── Entrega programada / mensaje personalizado ─────────────
    mensaje_tarjeta: form.mensaje_tarjeta || undefined,
    fecha_entrega: form.fecha_entrega || undefined,
    hora_entrega: form.hora_entrega || undefined,
    entrega_programada: form.entrega_programada,
  })

  if (ok) {
    router.push('/confirmado')
  } else {
    errorMsg.value = 'Hubo un error al procesar tu pedido. Intenta de nuevo.'
  }
}
</script>

<style scoped>
.checkout-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 2px solid var(--color-surface-border, #e5e7eb);
  background: var(--color-surface-warm, #f9fafb);
  font-size: 14px;
  color: var(--color-ink, #111827);
  outline: none;
  transition: all 0.2s;
  font-family: inherit;
}

.checkout-input::placeholder {
  color: var(--color-ink-faint, #d1d5db);
}

.checkout-input:focus {
  border-color: var(--color-brand-primary, #C41E1E);
  background: white;
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-brand-primary, #C41E1E) 8%, transparent);
}

/* Pulso lento y sutil del botón flotante de WhatsApp */
@keyframes ping-slow {
  0% {
    transform: scale(1);
    opacity: 0.5;
  }

  75%,
  100% {
    transform: scale(1.6);
    opacity: 0;
  }
}

.animate-ping-slow {
  animation: ping-slow 2.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>