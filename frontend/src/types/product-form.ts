// Tipos del formulario de producto (modal crear/editar) — extraídos de
// CatalogoView.vue para que los componentes de cada tab (Info,
// Personalización, Extras) puedan tiparse igual que el padre sin
// duplicar las interfaces.

export interface FormOption {
  id?: number
  name: string
  price_modifier: number
  image_url?: string
}

export interface FormSection {
  id?: number
  seccion: string
  label: string
  required: boolean
  multiple: boolean
  sort_order: number
  options: FormOption[]
}

export interface FormExtra {
  name: string
  price: number
}

export interface AvailableExtra {
  id: number
  name: string
  price: number
}

export interface SeccionTipo {
  id: number
  nombre: string
  icono: string
  activo: boolean
  sort_order: number
}

export interface CategoriaForm {
  name: string
  icon: string
  parent_id: number | null
  sort_order: number
  active: boolean
}

export interface SeccionTipoForm {
  nombre: string
  icono: string
}

export interface GalleryImage {
  id: number
  image_url: string
  sort_order: number
}

export interface ProductForm {
  name: string
  description: string
  icon: string
  category_id: number | ''
  price: number
  stock: number
  controla_stock: boolean
  stock_minimo: number | null
  available: boolean
  popular: boolean
  tieneDescuento: boolean
  descuento_tipo: 'porcentaje' | 'monto_fijo'
  descuento_valor: number
  descuento_desde: string
  descuento_hasta: string
  sections: FormSection[]
  extras: FormExtra[]
  extra_ids: number[]
}
