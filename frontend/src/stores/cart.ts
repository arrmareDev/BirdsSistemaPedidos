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
    price_modifier: number; // ← NUEVO — necesario para tamaños con precio distinto
  }>;
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
  emoji: string | null;
  imageUrl: string | null;
  businessLine: string | null; // ← NUEVO — 'floreria' | 'cafeteria' | 'menu'
  basePrice: number;
  modifiersPrice: number; // ← NUEVO — suma de price_modifier de la personalización
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

  // ── Detecta si el carrito tiene productos de florería ──────
  // (para mostrar/ocultar el campo de "mensaje para la tarjeta" en checkout)
  const hasFloreria = computed(() =>
    items.value.some(
      (i) => i.businessLine === "floreria" || i.businessLine === null,
    ),
  );

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
  ) {
    const modifiersPrice = calcModifiersPrice(customization);
    const extrasPrice = extras.reduce((sum, e) => sum + e.price * e.qty, 0);
    const summary = buildSummary(customization, extras);

    items.value.push({
      _uid: crypto.randomUUID(),
      productId: product.id,
      name: product.name,
      emoji: product.emoji,
      imageUrl: product.image_url,
      businessLine: product.category?.business_line ?? null,
      basePrice: product.price,
      modifiersPrice,
      extrasPrice,
      price: product.price + modifiersPrice + extrasPrice,
      qty: 1,
      customization,
      extras,
      customSummary: summary,
    });
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

  return {
    items,
    count,
    total,
    isEmpty,
    hasFloreria,
    add,
    updateItem,
    incrementQty,
    decrementQty,
    remove,
    clear,
  };
});
