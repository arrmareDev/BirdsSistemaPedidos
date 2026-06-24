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
  basePrice: number;
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

  function add(
    product: Product,
    customization: CartCustomization[],
    extras: CartExtra[],
  ) {
    const extrasPrice = extras.reduce((sum, e) => sum + e.price * e.qty, 0);
    const summary = buildSummary(customization, extras);

    items.value.push({
      _uid: crypto.randomUUID(),
      productId: product.id,
      name: product.name,
      emoji: product.emoji,
      imageUrl: product.image_url,
      basePrice: product.price,
      extrasPrice,
      price: product.price + extrasPrice,
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

    const extrasPrice = extras.reduce((sum, e) => sum + e.price * e.qty, 0);

    item.customization = customization;
    item.extras = extras;
    item.extrasPrice = extrasPrice;
    item.price = item.basePrice + extrasPrice;
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
    add,
    updateItem,
    incrementQty,
    decrementQty,
    remove,
    clear,
  };
});
