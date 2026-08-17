import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";
import { useCartStore } from "./cart";

const STORAGE_KEY = "birds_last_order";
const EXPIRATION_MS = 24 * 60 * 60 * 1000; // 24 horas

export const useOrderStore = defineStore("order", () => {
  const currentOrder = ref<any>(null);
  const orderNumber = ref<string | null>(null);
  const loading = ref(false);

  // ── Restaurar desde localStorage al iniciar el store ─────
  function restoreFromStorage() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;

      const saved = JSON.parse(raw);
      const savedAt = saved.savedAt ?? 0;
      const isExpired = Date.now() - savedAt > EXPIRATION_MS;

      if (isExpired) {
        localStorage.removeItem(STORAGE_KEY);
        return;
      }

      currentOrder.value = saved.currentOrder ?? null;
      orderNumber.value = saved.orderNumber ?? null;
    } catch (e) {
      console.error("Error al restaurar el último pedido:", e);
      localStorage.removeItem(STORAGE_KEY);
    }
  }
  restoreFromStorage();

  // ── Guardar en localStorage ──────────────────────────────
  function persistToStorage() {
    try {
      if (!currentOrder.value || !orderNumber.value) {
        localStorage.removeItem(STORAGE_KEY);
        return;
      }
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          currentOrder: currentOrder.value,
          orderNumber: orderNumber.value,
          savedAt: Date.now(), // ← marca de tiempo para expiración
        }),
      );
    } catch (e) {
      console.error("Error al guardar el último pedido:", e);
    }
  }

  async function placeOrder(formData: {
    client_name: string;
    client_phone: string;
    type: string;
    mesa?: string;
    address?: string;
    reference?: string;
    delivery_zone_id?: number;
    delivery_fee?: number;
    metodo_pago?: string;
    note?: string;
    lat?: number;
    lng?: number;
    mensaje_tarjeta?: string;
    fecha_entrega?: string;
    hora_entrega?: string;
    entrega_programada?: boolean;
  }) {
    const cartStore = useCartStore();
    loading.value = true;

    try {
      const { data } = await api.post("/orders", {
        client_name: formData.client_name,
        client_phone: formData.client_phone,
        type: formData.type,
        mesa: formData.mesa || null,
        address: formData.address || null,
        reference: formData.reference || null,
        delivery_zone_id: formData.delivery_zone_id || null,
        delivery_fee: formData.delivery_fee ?? 0,
        metodo_pago: formData.metodo_pago || null,
        note: formData.note || null,
        lat: formData.lat ?? null,
        lng: formData.lng ?? null,
        mensaje_tarjeta: formData.mensaje_tarjeta || null,
        fecha_entrega: formData.fecha_entrega || null,
        hora_entrega: formData.hora_entrega || null,
        entrega_programada: formData.entrega_programada ?? false,
        total: cartStore.total + (formData.delivery_fee ?? 0),
        items: cartStore.items.map((item) => ({
          product_id: item.productId,
          qty: item.qty,
          unit_price: item.price,
          customization: item.customization,
          extras: item.extras,
          custom_summary: item.customSummary,
        })),
      });

      const order = data.data;
      orderNumber.value = String(order.codigo);
      currentOrder.value = {
        ...order,
        client_phone: formData.client_phone,
        mesa: formData.mesa ?? null,
        delivery_fee: formData.delivery_fee ?? 0,
        delivery_zone_id: formData.delivery_zone_id ?? null,
        reference: formData.reference ?? null,
        metodo_pago: formData.metodo_pago ?? null,
        mensaje_tarjeta: formData.mensaje_tarjeta ?? null,
        fecha_entrega: formData.fecha_entrega ?? null,
        hora_entrega: formData.hora_entrega ?? null,
        entrega_programada: formData.entrega_programada ?? false,
      };

      persistToStorage();

      cartStore.clear();
      return true;
    } catch (e) {
      console.error("Error al crear pedido:", e);
      return false;
    } finally {
      loading.value = false;
    }
  }

  function clear() {
    currentOrder.value = null;
    orderNumber.value = null;
    localStorage.removeItem(STORAGE_KEY);
  }

  return { currentOrder, orderNumber, loading, placeOrder, clear };
});
