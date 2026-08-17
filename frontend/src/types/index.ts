// ── Productos ─────────────────────────────────────────────
export interface CustomizationOption {
  id: number;
  name: string;
}

export interface CustomizationSection {
  id: number;
  seccion: string;
  label: string;
  required: boolean;
  multiple: boolean;
  options: CustomizationOption[];
}

export interface ProductExtra {
  id: number;
  name: string;
  price: number;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  parent_id: number | null;
  icon: string | null;
  sort_order: number;
  active: boolean;
  products_count?: number;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  image_url: string | null;
  price: number;
  popular: boolean;
  available: boolean;
  stock?: number;
  controla_stock?: boolean;
  category: Category | null;
  customization_sections: CustomizationSection[];
  extras: ProductExtra[];
}

// ── Carrito ───────────────────────────────────────────────
export interface CartSelection {
  option_id: number;
  name: string;
}

export interface CartCustomization {
  section_id: number;
  seccion: string;
  label: string;
  selections: CartSelection[];
}

export interface CartExtra {
  extra_id: number;
  name: string;
  price: number;
  qty: number;
}

export interface CartItem {
  _uid: string;
  productId: number;
  name: string;
  icon: string | null;
  imageUrl: string | null;
  basePrice: number;
  extrasPrice: number;
  price: number;
  qty: number;
  customization: CartCustomization[];
  extras: CartExtra[];
}

// ── Checkout ──────────────────────────────────────────────
// Ya no se pide contraentrega — todo pedido confirmado se
// entiende como pagado (coordinado por Yape/transferencia).
export type MetodoPago = "anticipado";

export interface OrderForm {
  name: string;
  phone: string;
  address: string;
  district: string;
  reference: string;
  note: string;
  metodo_pago: MetodoPago;
  lat: number | null;
  lng: number | null;
}
