<template>
  <div class="min-h-screen" style="background: #FFFAF5;">

    <!-- Header -->
    <div class="sticky top-0 z-10 bg-white/90 backdrop-blur-md border-b border-gray-100">
      <div class="w-full max-w-lg mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
        <RouterLink to="/" class="w-9 h-9 rounded-xl border border-gray-200 bg-white
                 flex items-center justify-center text-gray-500 no-underline
                 hover:border-red-400 hover:text-red-600
                 transition-all duration-150 shrink-0 shadow-sm">
          <ArrowLeftIcon class="w-4 h-4" />
        </RouterLink>
        <div class="flex-1 min-w-0">
          <h1 class="font-black text-[18px] text-gray-900 leading-none m-0 truncate"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            Seguimiento
          </h1>
          <p class="text-[11.5px] text-gray-400 mt-0.5 m-0">
            Rastrea tu pedido en tiempo real
          </p>
        </div>
        <div class="w-16 h-16 shrink-0 flex items-center justify-center">
          <img src="/images/logobirds.png" alt="Birds" class="w-full h-full object-contain"
            @error="($event.target as HTMLImageElement).style.display = 'none'" />
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="w-full max-w-lg mx-auto px-4 sm:px-6 py-6 pb-24">

      <!-- Buscador -->
      <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-3"
        leave-active-class="transition-all duration-200" leave-to-class="opacity-0 translate-y-2">
        <div v-if="!orderId && !loading">
          <div class="text-center mb-7">
            <div class="w-20 h-20 rounded-3xl bg-red-50 border border-red-100
                        flex items-center justify-center mx-auto mb-4">
              <MagnifyingGlassIcon class="w-10 h-10 text-brand-red" />
            </div>
            <h2 class="font-black text-[22px] text-gray-900 m-0 mb-1"
              style="font-family:'Plus Jakarta Sans',sans-serif;">
              Busca tu pedido
            </h2>
            <p class="text-[13.5px] text-gray-400 m-0">
              Ingresa tu número de pedido y teléfono
            </p>
          </div>

          <div class="bg-white rounded-3xl border border-gray-100
                      shadow-[0_4px_24px_rgba(0,0,0,0.06)] p-6">
            <div class="flex flex-col gap-4">

              <div class="flex flex-col gap-1.5">
                <label class="text-[10.5px] font-black uppercase tracking-widest
                               text-brand-red">
                  Número de pedido
                </label>
                <div class="relative">
                  <HashtagIcon class="absolute left-4 top-1/2 -translate-y-1/2
                                      w-4 h-4 text-gray-400 pointer-events-none" />
                  <input v-model="searchForm.orderId" type="number" placeholder="Ej: 27" @keyup.enter="doSearch" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2
                           border-gray-100 bg-gray-50 text-[14px] text-gray-900
                           outline-none placeholder:text-gray-300
                           focus:border-brand-red focus:bg-white
                           focus:shadow-[0_0_0_4px_rgba(var(--color-brand-primary-rgb,196,30,30),0.08)]
                           transition-all duration-200" />
                </div>
              </div>

              <div class="flex flex-col gap-1.5">
                <label class="text-[10.5px] font-black uppercase tracking-widest
                               text-brand-red">
                  Tu teléfono WhatsApp
                </label>
                <div class="relative">
                  <PhoneIcon class="absolute left-4 top-1/2 -translate-y-1/2
                                    w-4 h-4 text-gray-400 pointer-events-none" />
                  <input v-model="searchForm.phone" type="tel" placeholder="987 654 321" @keyup.enter="doSearch" class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2
                           border-gray-100 bg-gray-50 text-[14px] text-gray-900
                           outline-none placeholder:text-gray-300
                           focus:border-brand-red focus:bg-white
                           focus:shadow-[0_0_0_4px_rgba(var(--color-brand-primary-rgb,196,30,30),0.08)]
                           transition-all duration-200" />
                </div>
              </div>

              <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-y-1"
                leave-to-class="opacity-0">
                <div v-if="searchError" class="flex items-start gap-2.5 px-4 py-3 rounded-2xl
                         bg-red-50 border border-red-200">
                  <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
                  <p class="text-[12.5px] text-red-700 m-0">{{ searchError }}</p>
                </div>
              </Transition>

              <button @click="doSearch" :disabled="searching || !searchForm.orderId || !searchForm.phone" class="w-full py-4 rounded-2xl font-black text-[15px] text-white
                       border-none cursor-pointer transition-all duration-200
                       uppercase tracking-wide bg-brand-red
                       shadow-[0_6px_24px_rgba(var(--color-brand-primary-rgb,196,30,30),0.3)]
                       hover:bg-red-700 hover:-translate-y-0.5
                       active:scale-[0.98]
                       disabled:opacity-40 disabled:cursor-not-allowed
                       disabled:hover:translate-y-0
                       flex items-center justify-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
                <span v-if="searching" class="w-4 h-4 border-2 border-white/30 border-t-white
                         rounded-full animate-spin" />
                <MagnifyingGlassIcon v-else class="w-4 h-4" />
                {{ searching ? 'Buscando...' : 'Buscar mi pedido' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Skeleton -->
      <div v-if="loading" class="flex flex-col gap-4">
        <div class="h-44 rounded-3xl bg-gray-100 animate-pulse" />
        <div class="h-16 rounded-2xl bg-gray-100 animate-pulse" />
        <div class="h-72 rounded-3xl bg-gray-100 animate-pulse" />
        <div class="h-28 rounded-3xl bg-gray-100 animate-pulse" />
      </div>

      <!-- Error -->
      <div v-else-if="error && orderId" class="bg-white rounded-3xl border border-gray-100
               shadow-[0_4px_24px_rgba(0,0,0,0.06)] p-10 text-center">
        <div class="w-20 h-20 rounded-full bg-red-50 flex items-center
                    justify-center mx-auto mb-5 text-red-300">
          <SearchX :size="40" :stroke-width="1.5" />
        </div>
        <h2 class="font-black text-[20px] text-gray-900 m-0 mb-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
          No encontramos tu pedido
        </h2>
        <p class="text-gray-400 text-[13.5px] m-0 mb-7 leading-relaxed">
          {{ error }}
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="orderId = ''; error = ''" class="flex items-center justify-center gap-2 px-6 py-3 rounded-2xl
                   border-2 border-gray-200 text-gray-600 font-semibold
                   text-[13.5px] cursor-pointer bg-white
                   hover:border-red-400 hover:text-brand-red
                   transition-all duration-150">
            <ArrowLeftIcon class="w-4 h-4" />
            Buscar otro
          </button>
          <RouterLink to="/" class="flex items-center justify-center gap-2 px-6 py-3
                   rounded-2xl bg-brand-red text-white font-bold
                   text-[13.5px] no-underline
                   hover:bg-red-700 transition-all duration-150">
            <Store :size="16" /> Ir al catálogo
          </RouterLink>
        </div>
      </div>

      <!-- Pedido encontrado -->
      <template v-else-if="order">

        <!-- Hero card -->
        <div class="bg-white rounded-3xl border border-gray-100
                    shadow-[0_4px_24px_rgba(0,0,0,0.06)] overflow-hidden mb-4">
          <div class="h-1.5 w-full" :class="{
            'bg-green-400': order.status === 'entregado',
            'bg-red-300': order.status === 'cancelado',
            'bg-brand-red': !['entregado', 'cancelado'].includes(order.status),
          }" />

          <div class="p-5">
            <div class="flex items-start justify-between mb-4 gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <h2 class="font-black text-[26px] text-gray-900 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Pedido #{{ order.id }}
                  </h2>
                  <span :class="statusCls(order.status)">
                    {{ statusLabel(order.status) }}
                  </span>
                </div>
                <p class="text-[13px] text-gray-500 m-0 font-medium">
                  {{ order.client_name }}
                  <span class="text-gray-300 mx-1">·</span>
                  {{ typeLabel(order.type) }}
                </p>
                <div class="flex flex-col gap-0.5 mt-1.5">
                  <p v-if="order.address" class="text-[12px] text-gray-400 m-0 flex items-center gap-1">
                    <MapPinIcon class="w-3.5 h-3.5 shrink-0" />
                    {{ order.address }}
                    <span v-if="order.district">· {{ order.district }}</span>
                  </p>
                  <p v-if="order.mesa" class="text-[12px] text-gray-400 m-0 flex items-center gap-1">
                    <TableCellsIcon class="w-3.5 h-3.5 shrink-0" />
                    {{ order.mesa }}
                  </p>
                </div>
              </div>
              <div class="text-right shrink-0">
                <div class="flex items-baseline gap-0.5 justify-end">
                  <span class="text-[13px] font-semibold text-gray-400">S/</span>
                  <span class="font-black text-[28px] text-brand-red leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ parseFloat(order.total).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Banner estado -->
            <div v-if="order.status === 'entregado'" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl
                     bg-green-50 border border-green-200">
              <div class="w-9 h-9 rounded-full bg-green-500 flex items-center
                          justify-center text-white shrink-0">
                <CheckCircleIcon class="w-5 h-5" />
              </div>
              <div>
                <p class="text-[13.5px] text-green-800 font-bold m-0">
                  ¡Pedido entregado!
                </p>
                <p class="text-[12px] text-green-600 m-0">
                  Gracias por elegir Birds. Esperamos que disfrutes tu pedido.
                </p>
              </div>
            </div>

            <div v-else-if="order.status === 'cancelado'" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl
                     bg-red-50 border border-red-200">
              <XCircleIcon class="w-6 h-6 text-red-400 shrink-0" />
              <p class="text-[13px] text-red-700 font-medium m-0">
                Este pedido fue cancelado. Contáctanos si tienes dudas.
              </p>
            </div>

            <div v-else class="flex items-center gap-3 px-4 py-3.5 rounded-2xl
                     bg-amber-50 border border-amber-200">
              <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse shrink-0" />
              <p class="text-[13px] text-amber-800 font-medium m-0">
                {{ tiempoEstimado }}
              </p>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-3xl border border-gray-100
                    shadow-[0_4px_24px_rgba(0,0,0,0.06)] p-5 mb-4">
          <div class="flex items-center gap-2 mb-5">
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">
              <ClockIcon class="w-4 h-4 text-brand-red" />
            </div>
            <h3 class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
              Estado del pedido
            </h3>
          </div>

          <div class="flex flex-col">
            <div v-for="(step, i) in order.status_history" :key="step.status"
              class="flex items-start gap-4 pb-6 last:pb-0 relative">

              <div v-if="i < order.status_history.length - 1" class="absolute left-[17px] top-9 w-0.5 bottom-0
                       transition-colors duration-500" :class="{
                        'bg-green-400': step.state === 'done' && step.status === 'entregado',
                        'bg-brand-red': step.state === 'done' && step.status !== 'entregado',
                        'bg-gray-100': step.state !== 'done',
                      }" />

              <div class="relative z-10 w-9 h-9 rounded-full border-2 flex items-center
                          justify-center shrink-0 transition-all duration-300" :class="{
                            'bg-green-500 border-green-500 text-white shadow-[0_0_0_4px_rgba(34,197,94,0.15)]':
                              step.state === 'done' || (step.state === 'active' && step.status === 'entregado'),
                            'bg-red-50 border-brand-red shadow-[0_0_0_4px_rgba(var(--color-brand-primary-rgb,196,30,30),0.12)]':
                              step.state === 'active' && step.status !== 'entregado',
                            'bg-white border-gray-200 text-gray-400':
                              step.state === 'pending',
                          }">
                <CheckIcon v-if="step.state === 'done' || (step.state === 'active' && step.status === 'entregado')"
                  class="w-4 h-4 text-white" />
                <AppIcon v-else :name="step.icon" :size="16" />
              </div>

              <div class="pt-1.5 flex-1 min-w-0">
                <p class="font-bold text-[14px] m-0 transition-colors duration-300" :class="{
                  'text-green-600': step.state === 'active' && step.status === 'entregado',
                  'text-brand-red': step.state === 'active' && step.status !== 'entregado',
                  'text-gray-800': step.state === 'done',
                  'text-gray-300': step.state === 'pending',
                }">
                  {{ step.label }}
                </p>
                <p class="text-[12px] mt-0.5 m-0" :class="{
                  'text-green-500': step.state === 'active' && step.status === 'entregado',
                  'text-gray-500': step.state === 'active' && step.status !== 'entregado',
                  'text-gray-400': step.state === 'done',
                  'text-gray-200': step.state === 'pending',
                }">
                  {{ stepSubtitle(step) }}
                </p>
              </div>

              <div v-if="step.state === 'active' && step.status !== 'entregado'" class="shrink-0 mt-1.5 px-2.5 py-1 rounded-full
                       bg-amber-50 border border-amber-200">
                <span class="text-[11px] font-bold text-amber-600">En curso</span>
              </div>
              <div v-if="step.state === 'active' && step.status === 'entregado'" class="shrink-0 mt-1.5 px-2.5 py-1 rounded-full
                       bg-green-50 border border-green-200">
                <span class="text-[11px] font-bold text-green-600 inline-flex items-center gap-1">
                  <Check :size="11" :stroke-width="3" /> Completado
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Detalle del pedido -->
        <div class="bg-white rounded-3xl border border-gray-100
                    shadow-[0_4px_24px_rgba(0,0,0,0.06)] overflow-hidden mb-4">
          <div class="flex items-center justify-between px-5 py-4
                      border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-2">
              <ClipboardDocumentListIcon class="w-4 h-4 text-gray-400" />
              <h3 class="font-black text-[14px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                Detalle del pedido
              </h3>
            </div>
            <span class="text-[11.5px] font-bold text-gray-400 bg-gray-100
                         px-2.5 py-1 rounded-full">
              {{ order.items?.length ?? 0 }} productos
            </span>
          </div>

          <div class="divide-y divide-gray-50">
            <div v-for="(item, i) in order.items" :key="i" class="flex items-center gap-3.5 px-5 py-4">
              <div class="w-11 h-11 rounded-2xl bg-orange-50 border border-orange-100
                          flex items-center justify-center text-orange-400 shrink-0">
                <AppIcon :name="item.icon" :size="20" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-[13.5px] text-gray-900 m-0 leading-snug">
                  {{ item.qty }}× {{ item.name }}
                </p>
                <p v-if="item.custom_summary" class="text-[12px] text-gray-400 mt-0.5 m-0 truncate">
                  {{ item.custom_summary }}
                </p>
              </div>
            </div>
          </div>

          <div v-if="order.note" class="flex items-start gap-2.5 px-5 py-3.5
                   bg-amber-50 border-t border-amber-100">
            <ChatBubbleBottomCenterTextIcon class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
            <p class="text-[12.5px] text-amber-800 m-0">{{ order.note }}</p>
          </div>

          <div class="flex items-center justify-between px-5 py-4
                      border-t border-gray-100 bg-gray-50/50">
            <span class="font-semibold text-[14px] text-gray-600">Total pagado</span>
            <div class="flex items-baseline gap-1">
              <span class="text-[13px] font-semibold text-gray-400">S/</span>
              <span class="font-black text-[24px] text-brand-red leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                {{ parseFloat(order.total).toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Auto-refresh indicator -->
        <div class="flex items-center justify-center gap-2 mb-5">
          <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse" />
          <p class="text-[11.5px] text-gray-400 m-0">
            Actualizando automáticamente cada 20 segundos
          </p>
        </div>

        <!-- Botones -->
        <div class="flex flex-col gap-3">
          <button @click="refreshOrder" :disabled="refreshing" class="w-full py-3.5 rounded-2xl border-2 border-gray-200 bg-white
                   text-gray-700 font-semibold text-[14px] cursor-pointer
                   hover:border-brand-red hover:text-brand-red
                   disabled:opacity-50 disabled:cursor-not-allowed
                   transition-all duration-200
                   flex items-center justify-center gap-2">
            <ArrowPathIcon class="w-4 h-4" :class="refreshing ? 'animate-spin' : ''" />
            {{ refreshing ? 'Actualizando...' : 'Actualizar estado' }}
          </button>

          <RouterLink to="/" class="flex items-center justify-center gap-2 w-full py-4
                   rounded-2xl no-underline bg-brand-red text-white
                   font-black text-[15px] uppercase tracking-wide
                   shadow-[0_6px_24px_rgba(var(--color-brand-primary-rgb,196,30,30),0.3)]
                   hover:bg-red-700 hover:-translate-y-0.5
                   active:scale-[0.98] transition-all duration-200"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            Hacer otro pedido
          </RouterLink>

          <a :href="waConsultaLink" target="_blank" class="flex items-center justify-center gap-2.5 w-full py-3.5
                   rounded-2xl no-underline bg-[#25D366] text-white font-bold
                   text-[14px] hover:bg-[#128C7E] active:scale-[0.98]
                   transition-all duration-200">
            <WhatsAppIcon :size="18" />
            Consultar al local
          </a>

          <button @click="resetSearch" class="w-full py-3 rounded-2xl border border-gray-200 bg-transparent
                   text-gray-500 font-medium text-[13px] cursor-pointer
                   hover:border-gray-300 hover:text-gray-700
                   transition-all duration-150
                   flex items-center justify-center gap-1.5">
            <MagnifyingGlassIcon class="w-3.5 h-3.5" />
            Buscar otro pedido
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/utils/api'
import WhatsAppIcon from '@/components/icons/WhatsAppIcon.vue'
import AppIcon from '@/components/AppIcon.vue'
import {
  ArrowLeftIcon, ArrowPathIcon, MagnifyingGlassIcon,
  PhoneIcon, CheckIcon, CheckCircleIcon, XCircleIcon,
  ClockIcon, MapPinIcon, TableCellsIcon,
  ClipboardDocumentListIcon, ChatBubbleBottomCenterTextIcon,
  ExclamationCircleIcon, HashtagIcon,
} from '@heroicons/vue/24/outline'
import { SearchX, Store, Check } from 'lucide-vue-next'

const route = useRoute()

// ── Estado ────────────────────────────────────────────────
const order = ref<any>(null)
const loading = ref(false)
const error = ref('')
const refreshing = ref(false)
const searching = ref(false)
const searchError = ref('')

const searchForm = ref({ orderId: '', phone: '' })
const orderId = ref<string>((route.params.id as string) ?? '')
const phone = ref<string>((route.query.tel as string) ?? '')

const waPhone = (import.meta.env.VITE_WA_PHONE ?? '51984199340').replace(/\D/g, '')

let refreshInterval: ReturnType<typeof setInterval> | null = null

// ── Computed ──────────────────────────────────────────────
const waConsultaLink = computed(() => {
  const texto = `Hola tengo una consulta sobre mi pedido #${order.value?.id ?? ''}`
  return `https://wa.me/${waPhone}?text=${encodeURIComponent(texto)}`
})

const tiempoEstimado = computed(() => {
  const m: Record<string, string> = {
    nuevo: 'Esperando confirmación del local...',
    confirmado: 'Confirmado · Preparación en ~20 minutos',
    preparando: 'Alistando tu pedido · ~15 minutos',
    listo: '¡Tu pedido está listo para entregar!',
    en_camino: 'En camino a tu dirección · ~10 minutos',
    entregado: 'Pedido entregado',
  }
  return m[order.value?.status] ?? ''
})

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  if (orderId.value && phone.value) {
    await loadOrder()
    startRefresh()
  }
})

onUnmounted(() => stopRefresh())

// ── API ───────────────────────────────────────────────────
async function loadOrder() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get(
      `/orders/${orderId.value}/track`,
      { params: { phone: phone.value } }
    )
    order.value = data.data
  } catch (e: any) {
    error.value = e.response?.data?.message
      ?? 'No pudimos encontrar tu pedido'
  } finally {
    loading.value = false
  }
}

async function refreshOrder() {
  if (!orderId.value || !phone.value) return
  refreshing.value = true
  try {
    const { data } = await api.get(
      `/orders/${orderId.value}/track`,
      { params: { phone: phone.value } }
    )
    order.value = data.data
  } catch { }
  finally { refreshing.value = false }
}

async function doSearch() {
  if (!searchForm.value.orderId || !searchForm.value.phone) return
  searching.value = true
  searchError.value = ''
  try {
    const { data } = await api.post('/orders/search', {
      order_id: parseInt(searchForm.value.orderId),
      phone: searchForm.value.phone.replace(/\s/g, ''),
    })
    orderId.value = String(data.data.id)
    phone.value = searchForm.value.phone.replace(/\s/g, '')
    await loadOrder()
    startRefresh()
  } catch (e: any) {
    searchError.value = e.response?.data?.message
      ?? 'No encontramos un pedido con esos datos'
  } finally {
    searching.value = false
  }
}

function resetSearch() {
  orderId.value = ''
  phone.value = ''
  order.value = null
  error.value = ''
  searchForm.value = { orderId: '', phone: '' }
  stopRefresh()
}

function startRefresh() {
  stopRefresh()
  refreshInterval = setInterval(refreshOrder, 20_000)
}

function stopRefresh() {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
}

// ── Helpers ───────────────────────────────────────────────
function stepSubtitle(step: any): string {
  if (step.state === 'done') return 'Completado'
  if (step.state === 'active') {
    const m: Record<string, string> = {
      nuevo: 'Notificando al local...',
      confirmado: 'El local revisó tu pedido',
      preparando: 'Alistando con amor! ~15 min',
      listo: '¡Listo para entregar!',
      en_camino: 'En camino a tu dirección',
      entregado: '¡Que lo disfrutes!',
    }
    return m[step.status] ?? 'En proceso...'
  }
  return 'Pendiente'
}

function typeLabel(t: string): string {
  const m: Record<string, string> = {
    recoger: 'Para llevar', local: 'En local', delivery: 'Delivery',
  }
  return m[t] ?? t
}

function statusLabel(s: string): string {
  const m: Record<string, string> = {
    nuevo: 'Nuevo', confirmado: 'Confirmado', preparando: 'Alistando',
    listo: 'Listo', en_camino: 'En camino', entregado: 'Entregado',
    cancelado: 'Cancelado',
  }
  return m[s] ?? s
}

function statusCls(s: string): string {
  const base = 'text-[11px] font-bold px-3 py-1 rounded-full border'
  const m: Record<string, string> = {
    nuevo: `${base} bg-blue-50   text-blue-700   border-blue-200`,
    confirmado: `${base} bg-amber-50  text-amber-700  border-amber-200`,
    preparando: `${base} bg-orange-50 text-orange-700 border-orange-200`,
    listo: `${base} bg-green-50  text-green-700  border-green-200`,
    en_camino: `${base} bg-red-50    text-red-700    border-red-200`,
    entregado: `${base} bg-green-100 text-green-800  border-green-300`,
    cancelado: `${base} bg-gray-100  text-gray-600   border-gray-200`,
  }
  return m[s] ?? m.cancelado
}
</script>