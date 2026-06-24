import type { CartItem, OrderForm } from '@/types'

const PHONE = import.meta.env.VITE_WA_PHONE ?? '51969943657'

/**
 * Genera el texto del mensaje de WhatsApp con el resumen del pedido
 */
export function buildWhatsAppMessage(
  items: CartItem[],
  form: OrderForm,
  total: number,
  orderNumber: string | number
): string {
  const lines: string[] = [
    `🍗 *El Brasero — Pedido #${orderNumber}*`,
    '',
  ]

  items.forEach((item) => {
    lines.push(`*${item.qty}× ${item.name}*`)
    if (item.customSummary) lines.push(`   ↳ ${item.customSummary}`)
  })

  const typeLabel: Record<string, string> = {
    llevar: '🛍️ Para llevar',
    local: '🪑 Consumo en local',
    delivery: '🛵 Delivery',
  }

  lines.push('')
  lines.push(`*Total: S/ ${total.toFixed(2)}*`)
  lines.push(`*Tipo: ${typeLabel[form.type] ?? form.type}*`)
  lines.push(`*Cliente: ${form.name}*`)
  lines.push(`*WhatsApp: ${form.phone}*`)
  if (form.type === 'delivery' && form.address) lines.push(`*Dirección: ${form.address}, ${form.district}*`)
  if (form.type === 'local' && form.mesa) lines.push(`*Mesa: ${form.mesa}*`)
  if (form.note) lines.push(`*Nota: ${form.note}*`)

  return lines.join('\n')
}

/**
 * Abre WhatsApp con el mensaje del pedido
 */
export function openWhatsApp(
  items: CartItem[],
  form: OrderForm,
  total: number,
  orderNumber: string | number
): void {
  const text = encodeURIComponent(
    buildWhatsAppMessage(items, form, total, orderNumber)
  )
  window.open(`https://wa.me/${PHONE}?text=${text}`, '_blank')
}
