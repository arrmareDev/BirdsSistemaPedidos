import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/utils/api";
import { useCartStore } from "./cart";

export const useOrderStore = defineStore("order", () => {
  const currentOrder = ref<any>(null);
  const orderNumber = ref<string | null>(null);
  const loading = ref(false);

  async function placeOrder(formData: {
    client_name:        string;
    client_phone:       string;
    type:               string;        // recoger | delivery
    address?:           string;
    reference?:         string;
    delivery_zone_id?:  number;
    delivery_fee?:      number;
    metodo_pago?:       string;
    note?:              string;
    lat?:               number;
    lng?:               number;
    // ── Florería ─────────────────────────────────────────
    mensaje_tarjeta?:   string;
    fecha_entrega?:     string;
    hora_entrega?:      string;
    entrega_programada?: boolean;
  }) {
    const cartStore = useCartStore();
    loading.value = true;

    try {
      const { data } = await api.post("/orders", {
        client_name:        formData.client_name,
        client_phone:       formData.client_phone,
        type:               formData.type,
        address:            formData.address            || null,
        reference:          formData.reference          || null,
        delivery_zone_id:   formData.delivery_zone_id   || null,
        delivery_fee:       formData.delivery_fee        ?? 0,
        metodo_pago:        formData.metodo_pago         || null,
        note:               formData.note               || null,
        lat:                formData.lat                ?? null,
        lng:                formData.lng                ?? null,
        // ── Florería ─────────────────────────────────────────
        mensaje_tarjeta:    formData.mensaje_tarjeta    || null,
        fecha_entrega:      formData.fecha_entrega      || null,
        hora_entrega:       formData.hora_entrega       || null,
        entrega_programada: formData.entrega_programada ?? false,
        // ── Total ─────────────────────────────────────────────
        total: cartStore.total + (formData.delivery_fee ?? 0),
        items: cartStore.items.map((item) => ({
          product_id:     item.productId,
          qty:            item.qty,
          unit_price:     item.price,
          customization:  item.customization,
          extras:         item.extras,
          custom_summary: item.customSummary,
        })),
      });

      const order = data.data;
      orderNumber.value = String(order.id);
      currentOrder.value = {
        ...order,
        client_phone:       formData.client_phone,
        delivery_fee:       formData.delivery_fee        ?? 0,
        delivery_zone_id:   formData.delivery_zone_id    ?? null,
        reference:          formData.reference           ?? null,
        metodo_pago:        formData.metodo_pago         ?? null,
        // ── Florería ─────────────────────────────────────────
        mensaje_tarjeta:    formData.mensaje_tarjeta     ?? null,
        fecha_entrega:      formData.fecha_entrega       ?? null,
        hora_entrega:       formData.hora_entrega        ?? null,
        entrega_programada: formData.entrega_programada  ?? false,
      };

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
  }

  return { currentOrder, orderNumber, loading, placeOrder, clear };
});