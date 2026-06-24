// ── Productos ─────────────────────────────────────────────
export type ProductCategory =
  | "pollos"
  | "combos"
  | "parrilla"
  | "bebidas"
  | "complementos"
  | "postres";

export interface SalsaOption {
  name: string;
  icon: string;
}

export interface SalsaConfig {
  max: number;
  per_salsa: number;
  options: SalsaOption[];
}

export interface CustomizationConfig {
  salsas?: SalsaConfig;
  cremas_per_unit?: number;
  ensalada?: { options: string[] };
  papas?: { options: string[] };
  coccion?: { options: string[]; multiple?: boolean };
  termino?: { options: string[] };
  temperatura?: { options: string[] };
  hielo?: { options: string[] };
  bebida?: { options: string[] };
  acompanamiento?: { options: string[] };
  tipo_pollo?: { options: string[] };
  nota?: boolean;
}

export interface ExtraProduct {
  product_id: number;
  label: string;
}

export interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  emoji: string;
  imageUrl?: string | null;
  image_url?: string | null;
  category: ProductCategory;
  available: boolean;
  popular?: boolean;
  isNew?: boolean;
  maxSalsas: number;
  hasEnsalada: boolean;
  hasPapas: boolean;
  sort_order?: number;
  customizationConfig: CustomizationConfig;
  extrasAvailable: ExtraProduct[];
}

// ── Personalización ───────────────────────────────────────
export interface Salsa {
  name: string;
  icon: string;
  selected?: boolean; // ← nuevo formato (chips)
  qty?: number; // ← legacy (por si acaso)
}
export interface Customization {
  salsas?: Salsa[];
  ensalada?: string;
  papas?: string;
  coccion?: string[];
  termino?: string;
  temperatura?: string;
  hielo?: string;
  bebida?: string;
  acompanamiento?: string;
  tipo_pollo?: string;
  nota?: string;
}

// ── Carrito ───────────────────────────────────────────────
export interface CartItem {
  _uid: string;
  productId: number;
  name: string;
  emoji: string;
  imageUrl?: string | null;
  price: number;
  qty: number;
  customization: Customization;
  customSummary: string;
}

// ── Pedidos ───────────────────────────────────────────────
export type OrderType = "llevar" | "local" | "delivery";

export interface OrderForm {
  name: string;
  phone: string;
  type: OrderType;
  address: string;
  district: string;
  mesa: string;
  note: string;
}
