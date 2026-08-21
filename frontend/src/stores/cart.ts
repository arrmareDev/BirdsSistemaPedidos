import { defineStore } from "pinia";
import { ref, computed } from "vue";
import type { Product } from "./products";

export interface CartCustomization {
  section_id: number;
  seccion: string;
  label: string;
  selections: Array<{
    option_id: number;
    name: string;
    price_modifier: number;
  }>;
}

export interface CartExtra {
  extra_id: number;
  // "own" = product_extras (propio del producto), "shared" = Extra (tabla
  // compartida). Ambas tablas tienen su propio autoincremental, así que un
  // extra_id por sí solo es ambiguo — el backend lo necesita para saber en
  // qué tabla buscar el precio real al recalcular.
  type: "own" | "shared";
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
  rootCategorySlug: string | null; // slug de la categoría principal del producto
  basePrice: number;
  modifiersPrice: number;
  extrasPrice: number;
  price: number;
  qty: number;
  customization: CartCustomization[];
  extras: CartExtra[];
  customSummary: string;
}

export const useCartStore = defineStore("cart", () => {
  const items = ref<CartItem[]>([]);

  const count = computed(() => items.value.reduce((s, i) => s + i.qty, 0));
  const total = computed(() =>
    items.value.reduce((s, i) => s + i.price * i.qty, 0),
  );
  const isEmpty = computed(() => items.value.length === 0);

  function calcModifiersPrice(customization: CartCustomization[]): number {
    return customization.reduce(
      (sum, sec) =>
        sum +
        sec.selections.reduce((s, sel) => s + (sel.price_modifier ?? 0), 0),
      0,
    );
  }

  function add(
    product: Product,
    customization: CartCustomization[],
    extras: CartExtra[],
    qtyToAdd: number = 1, // ← Agregamos este parámetro para recibir la cantidad directa
  ) {
    const modifiersPrice = calcModifiersPrice(customization);
    const extrasPrice = extras.reduce((sum, e) => sum + e.price * e.qty, 0);
    const summary = buildSummary(customization, extras);

    // ── MAGIA: Buscamos si ya existe en el carrito ──
    // Comparamos que sea el mismo producto y que la personalización sea idéntica
    const existingItem = items.value.find(
      (i) => i.productId === product.id && i.customSummary === summary,
    );

    // Precio base real: si el producto tiene descuento activo, precio_final
    // ya viene con ese descuento aplicado. Usar product.price aquí ignoraría
    // el descuento que el cliente vio en la ficha del producto.
    const basePrice = product.precio_final ?? product.price;

    if (existingItem) {
      // Si ya existe, solo le sumamos la cantidad solicitada
      existingItem.qty += qtyToAdd;
    } else {
      // Si no existe (o tiene una personalización distinta), creamos una nueva fila
      items.value.push({
        _uid: crypto.randomUUID(),
        productId: product.id,
        name: product.name,
        icon: product.icon,
        imageUrl: product.image_url,
        rootCategorySlug: product.category?.root_slug ?? null,
        basePrice,
        modifiersPrice,
        extrasPrice,
        price: basePrice + modifiersPrice + extrasPrice,
        qty: qtyToAdd, // ← Usamos la cantidad inicial aquí
        customization,
        extras,
        customSummary: summary,
      });
    }
  }

  // ── Actualiza un item existente (al editar desde el carrito) ──
  function updateItem(
    uid: string,
    customization: CartCustomization[],
    extras: CartExtra[],
    qty: number,
  ) {
    const item = items.value.find((i) => i._uid === uid);
    if (!item) return;

    const modifiersPrice = calcModifiersPrice(customization);
    const extrasPrice = extras.reduce((sum, e) => sum + e.price * e.qty, 0);

    item.customization = customization;
    item.extras = extras;
    item.modifiersPrice = modifiersPrice;
    item.extrasPrice = extrasPrice;
    item.price = item.basePrice + modifiersPrice + extrasPrice;
    item.qty = qty;
    item.customSummary = buildSummary(customization, extras);
  }

  function buildSummary(
    customization: CartCustomization[],
    extras: CartExtra[],
  ): string {
    const custParts = customization.flatMap((sec) =>
      sec.selections.map((s) => s.name),
    );
    const extraParts = extras
      .filter((e) => e.qty > 0)
      .map((e) => (e.qty > 1 ? `${e.name} ×${e.qty}` : `+ ${e.name}`));
    return [...custParts, ...extraParts].join(" · ");
  }

  function incrementQty(uid: string) {
    const item = items.value.find((i) => i._uid === uid);
    if (item) item.qty++;
  }

  function decrementQty(uid: string) {
    const item = items.value.find((i) => i._uid === uid);
    if (!item) return;
    item.qty <= 1 ? remove(uid) : item.qty--;
  }

  function remove(uid: string) {
    items.value = items.value.filter((i) => i._uid !== uid);
  }

  function clear() {
    items.value = [];
  }

  // ── Carga items directamente (para editar un pedido existente) ──
  function loadItems(newItems: CartItem[]) {
    items.value = newItems;
  }

  return {
    items,
    count,
    total,
    isEmpty,
    add,
    updateItem,
    loadItems,
    incrementQty,
    decrementQty,
    remove,
    clear,
  };
});
