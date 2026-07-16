<template>
  <div class="w-full max-w-lg mx-auto px-6 py-16 flex flex-col items-center text-center">

    <!-- Icono -->
    <div class="w-28 h-28 rounded-full bg-pink-50 border-4 border-pink-200
                flex items-center justify-center mb-6">
      <span class="text-6xl">{{ EMOJI.flor }}</span>
    </div>

    <h1 class="font-black text-[32px] text-ink leading-tight m-0 mb-3
               uppercase tracking-wide">
      ¡Pedido Recibido!
    </h1>
    <p class="text-ink-muted text-[15px] leading-relaxed max-w-xs m-0 mb-6">
      Hemos registrado tu pedido. Ahora envíanos el resumen por WhatsApp
      para confirmarlo.
    </p>

    <!-- Número -->
    <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full
                bg-pink-50 border border-pink-200 mb-4">
      <span class="text-[14px] font-black text-brand-dark">
        # Pedido {{ orderStore.orderNumber || '—' }}
      </span>
    </div>

    <!-- Banner entrega programada -->
    <div v-if="order?.entrega_programada && order?.fecha_entrega" class="w-full max-w-xs mb-6 px-4 py-3.5 rounded-2xl bg-pink-50
             border border-pink-200 flex items-start gap-3 text-left">
      <span class="text-xl shrink-0">{{ EMOJI.calendario }}</span>
      <div>
        <p class="font-black text-[13px] text-pink-800 m-0">Entrega programada</p>
        <p class="text-[12px] text-pink-600 m-0 mt-0.5">
          {{ formatFechaEntrega(order.fecha_entrega) }}
          <span v-if="order.hora_entrega"> · {{ formatHora(order.hora_entrega) }}</span>
        </p>
      </div>
    </div>

    <!-- WhatsApp -->
    <button @click="sendWA" class="flex items-center gap-3 px-8 py-4 rounded-2xl border-none
             cursor-pointer bg-[#25D366] text-white font-black text-[15px]
             mb-3 w-full max-w-xs
             shadow-[0_4px_20px_rgba(37,211,102,0.3)] uppercase tracking-wide
             hover:-translate-y-0.5 hover:shadow-[0_6px_28px_rgba(37,211,102,0.4)]
             transition-all duration-200 justify-center">
      <span class="text-xl">{{ EMOJI.chat }}</span>
      Enviar por WhatsApp
    </button>

<RouterLink :to="seguimientoLink" class="flex items-center justify-center gap-2 px-6 py-3 rounded-2xl
         no-underline border-2 border-surface-border text-ink-muted
         font-bold text-[14px] w-full max-w-xs uppercase tracking-wide
         hover:border-brand-red/40 hover:text-brand-red
         transition-all duration-200">
  Ver estado del pedido →
</RouterLink>

    <!-- Pasos -->
    <div class="mt-10 w-full max-w-xs bg-white rounded-3xl border
                border-surface-border shadow-card p-5 text-left">
      <p class="text-[10.5px] font-black uppercase tracking-wider text-brand-red mb-4 m-0">
        ¿Qué pasa ahora?
      </p>
      <div v-for="(step, i) in STEPS" :key="i" class="flex items-center gap-3 mb-3 last:mb-0">
        <div class="w-6 h-6 rounded-full bg-brand-red text-white
                    flex items-center justify-center shrink-0">
          <span class="text-[11px] font-black">{{ i + 1 }}</span>
        </div>
        <span class="text-[13.5px] text-ink-mid">{{ step }}</span>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useOrderStore } from '@/stores/order'

// ── Emojis para la UI (esto NO va al mensaje de WhatsApp) ──
// Estos sí se pueden usar tranquilos: se renderizan en tu propia
// página, no pasan por el bug de decodificación de wa.me.
const EMOJI = {
  flor: '\u{1F490}',        // 💐
  calendario: '\u{1F4C5}',  // 📅
  chat: '\u{1F4AC}',        // 💬
}

// ── Símbolos para el mensaje de WhatsApp ───────────────────
// wa.me / api.whatsapp.com tiene un bug confirmado que corrompe
// los emoji de color al armar el compositor de "click to chat"
// (llegan como � tanto en escritorio como en el celular).
// Se usan solo símbolos de texto plano, que sí renderizan bien
// (confirmado con pruebas manuales).
const SYM = {
  bullet: '▸',
  arrow: '➤',
  plus: '+',
  dash: '-----------------------------------',
}

const seguimientoLink = computed(() => {
  const numero = orderStore.orderNumber
  const clientPhone = order.value?.client_phone
  if (!numero || !clientPhone) return '/seguimiento' // fallback: sin datos, formulario manual

  return {
    path: `/seguimiento/${numero}`,
    query: { tel: String(clientPhone).replace(/\D/g, '') },
  }
})

const orderStore = useOrderStore()
const order = computed(() => orderStore.currentOrder)

// ── Número de Yape de la florería ─────────────────────────
// Si está definido en .env (VITE_YAPE_PHONE) se usa ese;
// si no, cae al número de WhatsApp por defecto.
const YAPE_PHONE = import.meta.env.VITE_YAPE_PHONE ?? '51984199340'
const YAPE_TITULAR = import.meta.env.VITE_YAPE_TITULAR ?? 'Florería Birds'

// ── Constantes ────────────────────────────────────────────
const STEPS = [
  'Enviamos el resumen por WhatsApp',
  'La florería confirma tu pedido',
  'Preparamos tu arreglo con amor',
  order.value?.entrega_programada
    ? '¡Entrega en la fecha elegida!'
    : '¡Lo recibes pronto!',
]

const METODO_PAGO_LABELS: Record<string, string> = {
  anticipado: 'Pago anticipado (transferencia / coordinado)',
  contraentrega_efectivo: 'Pago contraentrega en efectivo',
  contraentrega_yape: 'Pago contraentrega por Yape/Plin',
}

const TIPO_LABELS: Record<string, string> = {
  local: 'Consumo en local',
  recoger: 'Recoger en tienda',
  delivery: 'Delivery a domicilio',
}

const HORARIO_LABELS: Record<string, string> = {
  '09:00': '9:00 AM - 10:00 AM',
  '10:00': '10:00 AM - 11:00 AM',
  '11:00': '11:00 AM - 12:00 PM',
  '12:00': '12:00 PM - 1:00 PM',
  '14:00': '2:00 PM - 3:00 PM',
  '15:00': '3:00 PM - 4:00 PM',
  '16:00': '4:00 PM - 5:00 PM',
  '17:00': '5:00 PM - 6:00 PM',
}

// ── Helpers ───────────────────────────────────────────────
function formatFechaEntrega(fecha: string): string {
  const d = new Date(fecha + 'T00:00:00')
  return d.toLocaleDateString('es-PE', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
}

function formatHora(hora: string): string {
  return HORARIO_LABELS[hora] ?? hora
}

// ── WhatsApp ──────────────────────────────────────────────
function sendWA() {
  const rawPhone = import.meta.env.VITE_WA_PHONE ?? '51984199340'
  const phone = rawPhone.replace(/\D/g, '')
  const numero = orderStore.orderNumber || '—'
  const o = order.value
  const clientName = o?.client_name ?? 'Cliente'

  const L: string[] = []

  // ── Saludo Inicial ────────────────────────────────────────
  L.push(`Hola *Florería Birds*`)
  L.push(`Soy *${clientName}*, aquí está el detalle de mi pedido:`)
  L.push(``)
  L.push(`*PEDIDO #${numero}*`)
  L.push(SYM.dash)

  if (o?.items?.length) {

    // ── Resumen de Productos ────────────────────────────────
    o.items.forEach((item: any) => {
      const nombre = item.name ?? item.product?.name ?? 'Producto'
      const basePrice = Number(item.unit_price ?? 0)
      const extras: any[] = item.extras ?? []

      L.push(`${SYM.bullet} *${item.qty}x ${nombre}*`)
      L.push(`   _S/ ${basePrice.toFixed(2)}_`)

      // Personalización (Solo si existe)
      if (item.custom_summary) {
        L.push(`   ▸ ${item.custom_summary}`)
      }

      // Extras
      extras.forEach((e: any) => {
        if ((e.qty ?? 0) > 0) {
          const extraLabel = e.qty > 1 ? `${e.name} (x${e.qty})` : e.name
          L.push(`   ${SYM.plus} ${extraLabel} [+S/ ${(e.price * e.qty).toFixed(2)}]`)
        }
      })

      L.push(``)
    })

    L.push(SYM.dash)

    // ── Opciones de Entrega ─────────────────────────────────
    L.push(`*DATOS DE ENTREGA*`)
    L.push(`▸ Modalidad: _${TIPO_LABELS[o.type] ?? o.type}_`)

    if (o.type === 'local' && o.mesa) {
      L.push(`▸ Mesa: ${o.mesa}`)
    }

    if (o.type === 'delivery') {
      if (o.address) L.push(`▸ Dirección: ${o.address}`)
      if (o.reference) L.push(`▸ Referencia: ${o.reference}`)
    }

    if (o.entrega_programada && o.fecha_entrega) {
      L.push(`▸ ¿Cuándo?: ${formatFechaEntrega(o.fecha_entrega)}`)
      if (o.hora_entrega) L.push(`▸ Hora: ${formatHora(o.hora_entrega)}`)
    } else {
      L.push(`▸ ¿Cuándo?: _Lo antes posible_`)
    }
    L.push(``)

    // ── Extras Especiales ───────────────────────────────────
    if (o.mensaje_tarjeta || o.note) {
      L.push(`*DETALLES ADICIONALES*`)
      if (o.mensaje_tarjeta) {
        L.push(`Mensaje para la tarjeta:`)
        L.push(`_"${o.mensaje_tarjeta}"_`)
        L.push(``)
      }
      if (o.note) {
        L.push(`Nota especial: _${o.note}_`)
        L.push(``)
      }
    }

    // ── Resumen de Pago ─────────────────────────────────────
    L.push(SYM.dash)
    L.push(`*RESUMEN DE PAGO*`)

    const subtotalItems = o.items.reduce((s: number, i: any) => s + Number(i.subtotal ?? 0), 0)
    L.push(`▸ Subtotal: S/ ${subtotalItems.toFixed(2)}`)

    if (o.type === 'delivery' && o.delivery_fee > 0) {
      L.push(`▸ Delivery: S/ ${Number(o.delivery_fee).toFixed(2)}`)
    }

    L.push(`*${SYM.arrow} TOTAL A PAGAR: S/ ${Number(o.total).toFixed(2)}*`)

    if (o.type === 'delivery' && o.metodo_pago) {
      L.push(`(Método: ${METODO_PAGO_LABELS[o.metodo_pago] ?? o.metodo_pago})`)
    }

  } else {
    L.push(`Hola, acabo de realizar el pedido *#${numero}* en la web.`)
  }

  // ── Info Final (Pago) ────────────────────────────────────
  L.push(``)

  if (o?.metodo_pago === 'contraentrega_yape') {
    L.push(`*DATOS PARA YAPE / PLIN*`)
    L.push(`Número: *${YAPE_PHONE}*`)
    L.push(`Titular: ${YAPE_TITULAR}`)
    L.push(`_Por favor, te envío mi captura por aquí para confirmar mi pedido. Gracias._`)
  } else if (o?.metodo_pago === 'contraentrega_efectivo') {
    L.push(`*Pagaré en efectivo al repartidor al momento de la entrega.*`)
  } else if (o?.metodo_pago === 'anticipado') {
    L.push(`*Quedo atento a las indicaciones para el pago anticipado.*`)
  }

  // ── Link de seguimiento ───────────────────────────────────
  // Mismos datos que usa el botón "Ver estado del pedido →" (seguimientoLink):
  // el número de pedido y el teléfono del cliente, ya validados en `order`.
  const clientPhone = o?.client_phone
  if (clientPhone) {
    const tel = String(clientPhone).replace(/\D/g, '')
    L.push(``)
    L.push(`${SYM.arrow} Sigue tu pedido aquí: ${window.location.origin}/seguimiento/${numero}?tel=${tel}`)
  }

  window.open(
    `https://wa.me/${phone}?text=${encodeURIComponent(L.join('\n'))}`,
    '_blank',
  )
}
</script>