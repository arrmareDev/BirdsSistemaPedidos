<template>
  <div class="flex flex-col gap-5">

    <!-- ══ KPIs ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="kpi in KPIS" :key="kpi.label" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4
               flex flex-col gap-3">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">
            {{ kpi.label }}
          </span>
          <div class="w-8 h-8 rounded-xl flex items-center justify-center" :class="kpi.bgIcon">
            <component :is="kpi.icon" class="w-4 h-4" :class="kpi.iconColor" />
          </div>
        </div>
        <div>
          <p class="font-black text-[26px] text-gray-900 leading-none m-0"
            style="font-family:'Plus Jakarta Sans',sans-serif;">
            {{ kpi.value }}
          </p>
          <p class="text-[11.5px] text-gray-400 m-0 mt-1">{{ kpi.sub }}</p>
        </div>
      </div>
    </div>

    <!-- ══ FILTROS + BÚSQUEDA ══ -->
    <div class="flex flex-wrap items-center gap-3">

      <!-- Segmentos -->
      <div class="flex gap-1.5 flex-wrap">
        <button v-for="seg in SEGMENTOS" :key="seg.value" @click="segmento = seg.value" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full
                 text-[12.5px] font-semibold border cursor-pointer
                 transition-all duration-150" :class="segmento === seg.value
                  ? seg.activeClass
                  : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'">
          <component :is="seg.icon" class="w-3.5 h-3.5" />
          {{ seg.label }}
          <span class="text-[10px] font-black opacity-70">
            ({{ seg.count }})
          </span>
        </button>
      </div>

      <!-- Búsqueda -->
      <div class="relative ml-auto">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input v-model="search" placeholder="Buscar por nombre, teléfono o distrito..." class="pl-9 pr-4 py-2 rounded-xl border border-gray-200 bg-white
                 text-[13px] text-gray-900 outline-none w-64 max-w-full
                 focus:border-brand-red focus:ring-2 focus:ring-red-100
                 transition-all duration-200 placeholder:text-gray-300" />
      </div>

      <span class="text-[12.5px] text-gray-400 font-medium shrink-0">
        {{ filtered.length }} clientes
      </span>
    </div>

    <!-- ══ TABLA ══ -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

      <!-- Skeleton -->
      <div v-if="store.loading" class="p-4 flex flex-col gap-3">
        <div v-for="n in 6" :key="n" class="h-14 rounded-xl bg-gray-100 animate-pulse" />
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th v-for="h in HEADERS" :key="h.key" class="text-left text-[10.5px] font-black uppercase tracking-widest
                       text-gray-400 px-5 py-3.5 whitespace-nowrap" :class="h.class ?? ''">
                {{ h.label }}
              </th>
            </tr>
          </thead>
          <tbody>

            <!-- Empty -->
            <tr v-if="filtered.length === 0">
              <td :colspan="HEADERS.length" class="text-center py-16 text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <UsersIcon class="w-10 h-10 text-gray-200" />
                  <p class="m-0 text-[13px]">
                    {{ search ? 'Sin resultados para tu búsqueda' : 'Sin clientes aún' }}
                  </p>
                </div>
              </td>
            </tr>

            <!-- Filas -->
            <tr v-for="c in paginated" :key="c.id" @click="openDetail(c)" class="border-b border-gray-50 last:border-0 cursor-pointer
                     hover:bg-gray-50/60 transition-colors duration-100 group">

              <!-- Cliente -->
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center
                              text-[12px] font-black shrink-0 leading-none" :class="segmentoBadgeClass(c).avatar">
                    {{ initials(c.name) }}
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-1.5 mb-0.5">
                      <p class="font-semibold text-[13.5px] text-gray-900 m-0 leading-none">
                        {{ c.name }}
                      </p>
                      <span class="text-[9px] font-black uppercase px-1.5 py-0.5
                                   rounded-full leading-none" :class="segmentoBadgeClass(c).badge">
                        {{ segmentoBadgeClass(c).label }}
                      </span>
                    </div>
                    <p v-if="c.address" class="text-[11.5px] text-gray-400 m-0 truncate max-w-[180px]">
                      {{ c.address }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Teléfono -->
              <td class="px-5 py-4">
                <a :href="`tel:${c.phone}`" @click.stop class="flex items-center gap-1.5 text-[13px] text-gray-700
                         font-medium no-underline hover:text-brand-red
                         transition-colors group/tel">
                  <PhoneIcon class="w-3.5 h-3.5 text-gray-400
                                    group-hover/tel:text-brand-red" />
                  {{ c.phone }}
                </a>
              </td>

              <!-- Distrito -->
              <td class="px-5 py-4">
                <span v-if="c.district" class="text-[11.5px] font-medium px-2.5 py-1 rounded-full
                         bg-gray-100 text-gray-600 border border-gray-200">
                  {{ c.district }}
                </span>
                <span v-else class="text-gray-300 text-[13px]">—</span>
              </td>

              <!-- Pedidos + gasto -->
              <td class="px-5 py-4">
                <div class="flex flex-col gap-0.5">
                  <p class="font-black text-[14px] text-gray-900 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ c.orders_count }}
                    <span class="font-normal text-[11px] text-gray-400">pedidos</span>
                  </p>
                  <p v-if="c.total_spent > 0" class="text-[11.5px] text-green-600 font-semibold m-0">
                    S/ {{ formatMonto(c.total_spent) }} total
                  </p>
                </div>
              </td>

              <!-- Último pedido -->
              <td class="px-5 py-4">
                <p class="text-[12.5px] text-gray-500 m-0">
                  {{ c.last_order_at ? formatFecha(c.last_order_at) : '—' }}
                </p>
                <p v-if="c.last_order_at && diasDesde(c.last_order_at) > 30"
                  class="text-[11px] text-amber-500 font-semibold m-0 mt-0.5">
                  {{ diasDesde(c.last_order_at) }}d sin pedir
                </p>
              </td>

              <!-- Preferencias -->
              <td class="px-5 py-4">
                <div v-if="c.preferences?.salsas?.length" class="flex gap-1 flex-wrap max-w-[160px]">
                  <span v-for="s in c.preferences.salsas.slice(0, 3)" :key="s" class="text-[10.5px] font-medium px-2 py-0.5 rounded-full
                           bg-orange-50 text-orange-700 border border-orange-100">
                    {{ s }}
                  </span>
                  <span v-if="(c.preferences.salsas.length ?? 0) > 3" class="text-[10.5px] font-bold text-gray-400">
                    +{{ c.preferences.salsas.length - 3 }}
                  </span>
                </div>
                <span v-else class="text-[12px] text-gray-300">Sin datos</span>
              </td>

              <!-- Acción -->
              <td class="px-5 py-4">
                <button @click.stop="openDetail(c)" class="flex items-center gap-1 px-3 py-1.5 rounded-xl border
                         border-gray-200 bg-white text-[12px] font-semibold
                         text-gray-500 cursor-pointer
                         hover:border-brand-red hover:text-brand-red
                         transition-all duration-150">
                  Ver
                  <ChevronRightIcon class="w-3 h-3" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación simple -->
      <div v-if="totalPages > 1" class="flex items-center justify-between px-5 py-3
               border-t border-gray-100 bg-gray-50/50">
        <span class="text-[12.5px] text-gray-400">
          Página {{ page }} de {{ totalPages }}
        </span>
        <div class="flex gap-2">
          <button @click="page--" :disabled="page === 1" class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px]
                   font-semibold text-gray-600 cursor-pointer bg-white
                   hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
            ← Anterior
          </button>
          <button @click="page++" :disabled="page === totalPages" class="px-3 py-1.5 rounded-xl border border-gray-200 text-[12px]
                   font-semibold text-gray-600 cursor-pointer bg-white
                   hover:border-gray-300 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all duration-150">
            Siguiente →
          </button>
        </div>
      </div>
    </div>

    <!-- ══ MODAL DETALLE CLIENTE ══ -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="selected" class="fixed inset-0 z-[300] bg-black/40 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="selected = null">

          <Transition enter-active-class="transition-all duration-250 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-2" leave-to-class="opacity-0 scale-95">
            <div v-if="selected" class="w-full max-w-lg bg-white rounded-3xl shadow-2xl
                     overflow-hidden flex flex-col max-h-[88vh]">

              <!-- Header modal -->
              <div class="flex items-start justify-between px-6 py-5
                          border-b border-gray-100">
                <div class="flex items-center gap-4">
                  <div class="w-14 h-14 rounded-2xl flex items-center justify-center
                              text-[18px] font-black shrink-0" :class="segmentoBadgeClass(selected).avatar">
                    {{ initials(selected.name) }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <h2 class="font-black text-[19px] text-gray-900 m-0 leading-none"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ selected.name }}
                      </h2>
                      <span class="text-[10px] font-black uppercase px-2 py-0.5
                                   rounded-full" :class="segmentoBadgeClass(selected).badge">
                        {{ segmentoBadgeClass(selected).label }}
                      </span>
                    </div>
                    <a :href="`tel:${selected.phone}`" class="flex items-center gap-1 text-[13px] text-gray-500
                             no-underline hover:text-brand-red transition-colors">
                      <PhoneIcon class="w-3.5 h-3.5" />
                      {{ selected.phone }}
                    </a>
                  </div>
                </div>
                <button @click="selected = null" class="w-8 h-8 rounded-full bg-gray-100 flex items-center
                         justify-center cursor-pointer border-none
                         hover:bg-gray-200 transition-colors shrink-0">
                  <XMarkIcon class="w-4 h-4 text-gray-500" />
                </button>
              </div>

              <!-- Body modal -->
              <div class="overflow-y-auto flex-1 p-6 flex flex-col gap-5">

                <!-- Stats rápidos -->
                <div class="grid grid-cols-3 gap-3">
                  <div v-for="s in clientStats" :key="s.label" class="text-center bg-gray-50 rounded-2xl p-3.5
                           border border-gray-100">
                    <p class="font-black text-[20px] leading-none m-0 mb-1"
                      style="font-family:'Plus Jakarta Sans',sans-serif;" :class="s.color">
                      {{ s.value }}
                    </p>
                    <p class="text-[10.5px] text-gray-400 font-medium m-0">
                      {{ s.label }}
                    </p>
                  </div>
                </div>

                <!-- Info contacto -->
                <div class="flex flex-col gap-3">
                  <h3 class="text-[11px] font-black uppercase tracking-widest
                             text-gray-400 m-0">
                    Información
                  </h3>
                  <div class="grid grid-cols-1 gap-2">
                    <div v-for="info in clientInfo" :key="info.label" class="flex items-center gap-3 px-4 py-3 rounded-xl
                             bg-gray-50 border border-gray-100">
                      <component :is="info.icon" class="w-4 h-4 text-gray-400 shrink-0" />
                      <div class="flex-1 min-w-0">
                        <p class="text-[10.5px] text-gray-400 m-0 font-medium">
                          {{ info.label }}
                        </p>
                        <p class="text-[13px] text-gray-800 font-semibold m-0 truncate">
                          {{ info.value || '—' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Preferencias -->
                <div v-if="selected.preferences">
                  <h3 class="text-[11px] font-black uppercase tracking-widest
                             text-gray-400 m-0 mb-3">
                    Preferencias detectadas
                  </h3>
                  <div class="flex flex-col gap-2.5">

                    <div v-if="selected.preferences.salsas?.length" class="flex items-start gap-3 px-4 py-3 rounded-xl
                             bg-orange-50 border border-orange-100">
                      <span class="text-lg shrink-0">🫙</span>
                      <div>
                        <p class="text-[11px] text-orange-600 font-bold m-0 mb-1.5">
                          Cremas favoritas
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                          <span v-for="(s, i) in selected.preferences.salsas" :key="s"
                            class="text-[11px] font-semibold px-2.5 py-1 rounded-full" :class="i === 0
                              ? 'bg-orange-200 text-orange-800'
                              : 'bg-orange-100 text-orange-700'">
                            {{ i === 0 ? '⭐ ' : '' }}{{ s }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <div v-if="selected.preferences.ensalada" class="flex items-center gap-3 px-4 py-3 rounded-xl
                             bg-green-50 border border-green-100">
                      <span class="text-lg shrink-0">🥗</span>
                      <div>
                        <p class="text-[11px] text-green-600 font-bold m-0">Ensalada preferida</p>
                        <p class="text-[13px] text-green-800 font-semibold m-0">
                          {{ selected.preferences.ensalada }}
                        </p>
                      </div>
                    </div>

                    <div v-if="selected.preferences.papas" class="flex items-center gap-3 px-4 py-3 rounded-xl
                             bg-yellow-50 border border-yellow-100">
                      <span class="text-lg shrink-0">🍟</span>
                      <div>
                        <p class="text-[11px] text-yellow-600 font-bold m-0">Papas preferidas</p>
                        <p class="text-[13px] text-yellow-800 font-semibold m-0">
                          {{ selected.preferences.papas }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Fidelización -->
                <div>
                  <h3 class="text-[11px] font-black uppercase tracking-widest
                             text-gray-400 m-0 mb-3">
                    Fidelización
                  </h3>
                  <div class="flex flex-col gap-2">

                    <!-- Barra de progreso VIP -->
                    <div class="px-4 py-3.5 rounded-xl bg-gray-50 border border-gray-100">
                      <div class="flex justify-between items-center mb-2">
                        <span class="text-[12px] font-semibold text-gray-600">
                          Progreso VIP
                        </span>
                        <span class="text-[11px] font-bold text-gray-400">
                          {{ selected.orders_count }} / 10 pedidos
                        </span>
                      </div>
                      <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" :class="selected.orders_count >= 10
                          ? 'bg-yellow-400'
                          : selected.orders_count >= 5
                            ? 'bg-brand-red'
                            : 'bg-gray-400'" :style="`width: ${Math.min(selected.orders_count * 10, 100)}%`" />
                      </div>
                      <p class="text-[11px] text-gray-400 m-0 mt-1.5">
                        <template v-if="selected.orders_count >= 10">
                          🏆 Cliente VIP — ¡merece atención especial!
                        </template>
                        <template v-else-if="selected.orders_count >= 5">
                          🔥 Recurrente — faltan {{ 10 - selected.orders_count }} para VIP
                        </template>
                        <template v-else>
                          Nuevo cliente — faltan {{ 10 - selected.orders_count }} para VIP
                        </template>
                      </p>
                    </div>

                    <!-- Acciones fidelización -->
                    <div class="grid grid-cols-2 gap-2">
                      <a :href="waLink(selected, 'saludo')" target="_blank" class="flex items-center gap-2 px-3.5 py-3 rounded-xl
                               bg-[#25D366]/10 border border-[#25D366]/30
                               text-[12.5px] font-semibold text-[#128C7E]
                               no-underline hover:bg-[#25D366]/20
                               transition-all duration-150 cursor-pointer">
                        <ChatBubbleLeftIcon class="w-4 h-4 shrink-0" />
                        Saludar por WA
                      </a>
                      <a :href="waLink(selected, 'promo')" target="_blank" class="flex items-center gap-2 px-3.5 py-3 rounded-xl
                               bg-brand-red/10 border border-brand-red/20
                               text-[12.5px] font-semibold text-brand-red
                               no-underline hover:bg-brand-red/20
                               transition-all duration-150 cursor-pointer">
                        <GiftIcon class="w-4 h-4 shrink-0" />
                        Enviar promo
                      </a>
                      <a :href="waLink(selected, 'reactivar')" target="_blank" class="flex items-center gap-2 px-3.5 py-3 rounded-xl
                               bg-amber-50 border border-amber-200
                               text-[12.5px] font-semibold text-amber-700
                               no-underline hover:bg-amber-100
                               transition-all duration-150 cursor-pointer" :class="diasDesde(selected.last_order_at ?? '') < 15
                                ? 'opacity-40 pointer-events-none' : ''">
                        <ArrowPathIcon class="w-4 h-4 shrink-0" />
                        Reactivar cliente
                      </a>
                      <a :href="waLink(selected, 'cumple')" target="_blank" class="flex items-center gap-2 px-3.5 py-3 rounded-xl
                               bg-purple-50 border border-purple-200
                               text-[12.5px] font-semibold text-purple-700
                               no-underline hover:bg-purple-100
                               transition-all duration-150 cursor-pointer">
                        <StarIcon class="w-4 h-4 shrink-0" />
                        Mensaje especial
                      </a>
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
import { ref, computed, onMounted } from 'vue'
import { useClientsStore, type Client } from '@/stores/clients'
import {
  MagnifyingGlassIcon,
  PhoneIcon,
  ChevronRightIcon,
  XMarkIcon,
  UsersIcon,
  StarIcon,
  SparklesIcon,
  UserPlusIcon,
  CurrencyDollarIcon,
  MapPinIcon,
  ClockIcon,
  ChatBubbleLeftIcon,
  GiftIcon,
  ArrowPathIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline'

// ── Store ─────────────────────────────────────────────────
const store = useClientsStore()

// ── Estado ────────────────────────────────────────────────
const search = ref('')
const segmento = ref('todos')
const selected = ref<Client | null>(null)
const page = ref(1)
const PER_PAGE = 10

// ── Constantes ────────────────────────────────────────────
const HEADERS = [
  { key: 'cliente', label: 'Cliente' },
  { key: 'telefono', label: 'Teléfono' },
  { key: 'distrito', label: 'Distrito' },
  { key: 'pedidos', label: 'Pedidos' },
  { key: 'ultimo', label: 'Último pedido' },
  { key: 'prefs', label: 'Preferencias' },
  { key: 'acciones', label: '', class: 'w-10' },
]

const SEGMENTOS = computed(() => [
  {
    value: 'todos',
    label: 'Todos',
    icon: UsersIcon,
    count: store.clients.length,
    activeClass: 'bg-gray-900 border-gray-900 text-white',
  },
  {
    value: 'vip',
    label: 'VIP',
    icon: StarIcon,
    count: store.vips,
    activeClass: 'bg-yellow-400 border-yellow-400 text-yellow-900',
  },
  {
    value: 'recurrentes',
    label: 'Recurrentes',
    icon: ShoppingBagIcon,
    count: store.recurrentes,
    activeClass: 'bg-brand-red border-brand-red text-white',
  },
  {
    value: 'nuevos',
    label: 'Nuevos',
    icon: UserPlusIcon,
    count: store.nuevos,
    activeClass: 'bg-blue-500 border-blue-500 text-white',
  },
  {
    value: 'inactivos',
    label: 'Inactivos',
    icon: ClockIcon,
    count: store.clients.filter(c =>
      c.last_order_at && diasDesde(c.last_order_at) > 30
    ).length,
    activeClass: 'bg-amber-500 border-amber-500 text-white',
  },
])

const KPIS = computed(() => [
  {
    label: 'Total clientes',
    value: store.clients.length,
    sub: 'registrados en el sistema',
    icon: UsersIcon,
    bgIcon: 'bg-gray-100',
    iconColor: 'text-gray-500',
  },
  {
    label: 'Clientes VIP',
    value: store.vips,
    sub: '5+ pedidos realizados',
    icon: StarIcon,
    bgIcon: 'bg-yellow-50',
    iconColor: 'text-yellow-500',
  },
  {
    label: 'Nuevos este mes',
    value: store.nuevos,
    sub: 'primer pedido reciente',
    icon: UserPlusIcon,
    bgIcon: 'bg-blue-50',
    iconColor: 'text-blue-500',
  },
  {
    label: 'Ingresos totales',
    value: `S/ ${formatMonto(store.totalGastado)}`,
    sub: 'suma de todos los clientes',
    icon: CurrencyDollarIcon,
    bgIcon: 'bg-green-50',
    iconColor: 'text-green-500',
  },
])

// ── Computed filtros ──────────────────────────────────────
const bySegmento = computed(() => {
  switch (segmento.value) {
    case 'vip':
      return store.clients.filter(c => c.orders_count >= 5)
    case 'recurrentes':
      return store.clients.filter(c => c.orders_count >= 2 && c.orders_count < 5)
    case 'nuevos':
      return store.clients.filter(c => c.orders_count === 1)
    case 'inactivos':
      return store.clients.filter(c =>
        c.last_order_at && diasDesde(c.last_order_at) > 30
      )
    default:
      return store.clients
  }
})

const filtered = computed(() => {
  if (!search.value.trim()) return bySegmento.value
  const q = search.value.toLowerCase()
  return bySegmento.value.filter(c =>
    c.name.toLowerCase().includes(q) ||
    c.phone.includes(q) ||
    (c.district?.toLowerCase().includes(q) ?? false)
  )
})

const totalPages = computed(() => Math.ceil(filtered.value.length / PER_PAGE))

const paginated = computed(() => {
  const start = (page.value - 1) * PER_PAGE
  return filtered.value.slice(start, start + PER_PAGE)
})

// ── Computed detalle cliente ──────────────────────────────
const clientStats = computed(() => {
  if (!selected.value) return []
  return [
    {
      label: 'Pedidos',
      value: selected.value.orders_count,
      color: 'text-gray-900',
    },
    {
      label: 'Total gastado',
      value: `S/${formatMonto(selected.value.total_spent ?? 0)}`,
      color: 'text-green-600',
    },
    {
      label: 'Días inactivo',
      value: selected.value.last_order_at
        ? diasDesde(selected.value.last_order_at)
        : '—',
      color: diasDesde(selected.value.last_order_at ?? '') > 30
        ? 'text-amber-500'
        : 'text-gray-900',
    },
  ]
})

const clientInfo = computed(() => {
  if (!selected.value) return []
  return [
    {
      label: 'Teléfono',
      value: selected.value.phone,
      icon: PhoneIcon,
    },
    {
      label: 'Dirección',
      value: selected.value.address ?? '—',
      icon: MapPinIcon,
    },
    {
      label: 'Distrito',
      value: selected.value.district ?? '—',
      icon: MapPinIcon,
    },
    {
      label: 'Último pedido',
      value: selected.value.last_order_at
        ? formatFecha(selected.value.last_order_at)
        : 'Sin pedidos',
      icon: ClockIcon,
    },
  ]
})

// ── Helpers ───────────────────────────────────────────────
function initials(name: string): string {
  return name
    .split(' ')
    .map(w => w[0] ?? '')
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

function formatMonto(n: number): string {
  return n.toLocaleString('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function formatFecha(d: string): string {
  return new Date(d).toLocaleDateString('es-PE', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function diasDesde(d: string): number {
  if (!d) return 0
  return Math.floor(
    (Date.now() - new Date(d).getTime()) / (1000 * 60 * 60 * 24)
  )
}

function segmentoBadgeClass(c: Client) {
  if (c.orders_count >= 10)
    return {
      avatar: 'bg-yellow-100 text-yellow-700',
      badge: 'bg-yellow-100 text-yellow-700',
      label: 'VIP ⭐',
    }
  if (c.orders_count >= 5)
    return {
      avatar: 'bg-red-100 text-red-700',
      badge: 'bg-red-100 text-red-700',
      label: 'Fiel 🔥',
    }
  if (c.orders_count >= 2)
    return {
      avatar: 'bg-blue-100 text-blue-700',
      badge: 'bg-blue-100 text-blue-700',
      label: 'Regular',
    }
  return {
    avatar: 'bg-gray-100 text-gray-600',
    badge: 'bg-gray-100 text-gray-500',
    label: 'Nuevo',
  }
}

function waLink(c: Client, tipo: 'saludo' | 'promo' | 'reactivar' | 'cumple'): string {
  const phone = import.meta.env.VITE_WA_PHONE ?? '51984199340'
  const nombre = c.name.split(' ')[0]
  const crema = c.preferences?.salsas?.[0] ?? 'crema especial'

  const mensajes: Record<string, string> = {
    saludo: `Hola ${nombre} ¡Gracias por ser cliente de Birds! ¿Cómo estás?`,
    promo: `Hola ${nombre} ¡Tenemos una oferta especial para ti! Esta semana 10% de descuento en tu próximo pedido. ¿Te animás? `,
    reactivar: `Hola ${nombre} ¡Te extrañamos en Birds! Han pasado unos días y queremos invitarte a volver. Recuerda que nos puedes pedir a domicilio. ¡Te esperamos!`,
    cumple: `Hola ${nombre} Desde Birds te deseamos un día increíble. Sabemos que te gusta la ${crema} ¡así que hoy es el mejor día para unas deliciosas alas de pollo!`,
  }

  return `https://wa.me/${c.phone.replace(/\D/g, '')}?text=${encodeURIComponent(mensajes[tipo])}`
}

// ── Acciones ──────────────────────────────────────────────
function openDetail(c: Client) {
  selected.value = c
}

// ── Lifecycle ─────────────────────────────────────────────
onMounted(() => store.fetch())
</script>