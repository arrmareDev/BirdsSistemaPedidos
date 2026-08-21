// Funciones puras de formato/etiquetas para pedidos — sin estado, sin
// dependencias de componente. Extraídas de PedidosView.vue para poder
// reusarlas también en los componentes de modal (SolicitarDespachoModal,
// CobrarPedidoModal, etc.) sin duplicar la lógica.

export const FLOW = ['nuevo', 'confirmado', 'preparando', 'listo', 'en_camino', 'entregado']

export function flowFor(type: string): string[] {
  return type === 'delivery' ? FLOW : FLOW.filter(s => s !== 'en_camino')
}

export const STEPS = [
  { value: 'nuevo', label: 'Nuevo' },
  { value: 'confirmado', label: 'Confirm.' },
  { value: 'preparando', label: 'Preparan.' },
  { value: 'listo', label: 'Listo' },
  { value: 'en_camino', label: 'Camino' },
  { value: 'entregado', label: 'Entregado' },
]

export function stepsFor(type: string) {
  return type === 'delivery' ? STEPS : STEPS.filter(s => s.value !== 'en_camino')
}

export function getStepIdx(s: string, type: string = 'delivery'): number {
  return flowFor(type).indexOf(s)
}

export function nextStatusLabel(s: string, type: string = 'delivery'): string {
  const labels: Record<string, string> = {
    nuevo: 'Confirmar', confirmado: 'Preparando',
    preparando: 'Listo', listo: type === 'delivery' ? 'En camino' : 'Entregado',
    en_camino: 'Entregado',
  }
  return labels[s] ?? 'Completado'
}

export function formatDate(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleString('es-PE', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })
}

export function typeLabel(t: string): string {
  return { local: 'Local', recoger: 'Recoger', delivery: 'Delivery' }[t] ?? t
}

export function statusLabel(s: string): string {
  const m: Record<string, string> = {
    nuevo: 'Nuevo', confirmado: 'Confirmado', preparando: 'Preparando',
    listo: 'Listo', en_camino: 'En camino', entregado: 'Entregado', cancelado: 'Cancelado',
  }
  return m[s] ?? s
}

export function statusCls(s: string): string {
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

export function metodoPagoLabel(m: string): string {
  return {
    anticipado: 'Pagado',
    contraentrega_efectivo: 'Efectivo',
    contraentrega_yape: 'Yape/Plin',
    efectivo: 'Efectivo',
    yape: 'Yape/Plin',
    tarjeta: 'Tarjeta',
  }[m] ?? m
}

export function metodoPagoCls(m: string): string {
  return {
    anticipado: 'bg-green-50  text-green-700  border-green-200',
    contraentrega_efectivo: 'bg-amber-50  text-amber-700  border-amber-200',
    contraentrega_yape: 'bg-purple-50 text-purple-700 border-purple-200',
    efectivo: 'bg-amber-50  text-amber-700  border-amber-200',
    yape: 'bg-purple-50 text-purple-700 border-purple-200',
    tarjeta: 'bg-blue-50   text-blue-700   border-blue-200',
  }[m] ?? 'bg-gray-50 text-gray-600 border-gray-200'
}
