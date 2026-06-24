import { ref, computed, watch } from "vue";
import type { Product, Salsa, Customization } from "@/types";

// Flores/complementos por defecto (antes: salsas)
const DEFAULT_SALSAS = [
  { name: "Rosas", icon: "🌹" },
  { name: "Girasoles", icon: "🌻" },
  { name: "Tulipanes", icon: "🌷" },
  { name: "Lirios", icon: "🌸" },
  { name: "Gerberas", icon: "🌼" },
  { name: "Eucalipto", icon: "🌿" },
];

export function useCustomizer() {
  const isOpen = ref(false);
  const product = ref<Product | null>(null);
  const qty = ref(1);

  // Opciones de personalización
  // (nombres internos conservados para no romper el .vue consumidor)
  const salsas = ref<Salsa[]>([]);        // → flores / complementos
  const ensalada = ref("");               // → envoltura
  const papas = ref("");                  // → follaje
  const coccion = ref<string[]>([]);      // → adicionales (múltiple)
  const termino = ref("");                // → tamaño
  const temperatura = ref("");            // → genérico opcional
  const hielo = ref("");                  // → genérico opcional
  const bebida = ref("");                 // → complemento
  const acompanamiento = ref("");         // → presentación
  const tipo_pollo = ref("");             // → estilo
  const nota = ref("");                   // → dedicatoria / nota

  const extras = ref
    Array<{
      productId: number;
      name: string;
      emoji: string;
      price: number;
      qty: number;
    }>
  >([]);

  // ── Config del producto actual ────────────────────────────
  const config = computed(
    () => (product.value?.customizationConfig ?? {}) as any,
  );

  // ── Flores por unidad base del producto (antes: cremas) ───
  const cremasPerUnit = computed(
    (): number =>
      config.value?.salsas?.per_salsa ??
      config.value?.cremas_per_unit ??
      config.value?.salsas?.max ??
      0,
  );

  /**
   * Máximo de flores/complementos SELECCIONABLES según cantidad pedida.
   * Si el ramo permite elegir 3 tipos: max = 3.
   */
  const maxSalsasSelectable = computed((): number =>
    cremasPerUnit.value > 0 ? cremasPerUnit.value : 0,
  );

  // Cuántos tipos tiene seleccionados actualmente
  const selectedSalsasCount = computed(
    (): number => salsas.value.filter((s) => s.selected).length,
  );

  // Si ya llegó al máximo de tipos seleccionables
  const salsasFull = computed((): boolean => false);

  // ── Extras ───────────────────────────────────────────────
  const extrasTotal = computed(() =>
    extras.value.reduce((sum, e) => sum + e.price * e.qty, 0),
  );

  const subtotal = computed(
    () => (product.value?.price ?? 0) * qty.value + extrasTotal.value,
  );

  // ── Flags de secciones disponibles ───────────────────────
  const hasSalsas = computed(() => maxSalsasSelectable.value > 0);
  const hasEnsalada = computed(() => !!config.value?.ensalada);        // envoltura
  const hasPapas = computed(() => !!config.value?.papas);              // follaje
  const hasCoccion = computed(() => !!config.value?.coccion);          // adicionales
  const hasTermino = computed(() => !!config.value?.termino);          // tamaño
  const hasTemperatura = computed(() => !!config.value?.temperatura);
  const hasHielo = computed(() => !!config.value?.hielo);
  const hasBebida = computed(() => !!config.value?.bebida);            // complemento
  const hasNota = computed(() => !!config.value?.nota);                // dedicatoria
  const hasAcompanamiento = computed(() => !!config.value?.acompanamiento); // presentación
  const hasTipoPollo = computed(() => !!config.value?.tipo_pollo);     // estilo

  const hasExtras = computed(
    () => (product.value?.extrasAvailable?.length ?? 0) > 0,
  );

  const salsaOptions = computed(
    () => config.value?.salsas?.options ?? DEFAULT_SALSAS,
  );

  // ── Resumen legible ───────────────────────────────────────
  const summary = computed(() => {
    const parts: string[] = [];

    const selsNames = salsas.value.filter((s) => s.selected).map((s) => s.name);
    if (selsNames.length) parts.push(selsNames.join(", "));

    if (ensalada.value && ensalada.value !== "Envoltura clásica")
      parts.push(ensalada.value);
    if (papas.value && papas.value !== "Estándar") parts.push(papas.value);
    if (acompanamiento.value) parts.push(acompanamiento.value);
    if (tipo_pollo.value) parts.push(tipo_pollo.value);
    if (coccion.value.length) parts.push(coccion.value.join(", "));
    if (termino.value) parts.push(termino.value);
    if (temperatura.value) parts.push(temperatura.value);
    if (hielo.value) parts.push(hielo.value);
    if (bebida.value) parts.push("Complemento: " + bebida.value);
    if (nota.value) parts.push("💌 " + nota.value);

    const extrasParts = extras.value
      .filter((e) => e.qty > 0)
      .map((e) => (e.qty > 1 ? `${e.name} ×${e.qty}` : `+ ${e.name}`));
    if (extrasParts.length) parts.push(extrasParts.join(", "));

    return parts.join(" · ");
  });

  // ── Acciones ─────────────────────────────────────────────
  function open(p: Product) {
    product.value = p;
    qty.value = 1;
    nota.value = "";
    coccion.value = [];
    extras.value = [];

    // Inicializar flores — todas deseleccionadas
    const opts =
      (p.customizationConfig as any)?.salsas?.options ?? DEFAULT_SALSAS;
    salsas.value = opts.map((s: any) => ({
      name: s.name,
      icon: s.icon,
      selected: false,
    }));

    // Defaults para opciones únicas
    const cfg = p.customizationConfig as any;
    ensalada.value = cfg?.ensalada?.options?.[0] ?? "";
    papas.value = cfg?.papas?.options?.[0] ?? "";
    termino.value = cfg?.termino?.options?.[0] ?? "";
    temperatura.value = cfg?.temperatura?.options?.[0] ?? "";
    hielo.value = cfg?.hielo?.options?.[0] ?? "";
    bebida.value = cfg?.bebida?.options?.[0] ?? "";
    acompanamiento.value = cfg?.acompanamiento?.options?.[0] ?? "";
    tipo_pollo.value = cfg?.tipo_pollo?.options?.[0] ?? "";

    isOpen.value = true;
  }

  function close() {
    isOpen.value = false;
  }

  /**
   * Toggle de una flor/complemento — selección múltiple con límite.
   *
   * Reglas:
   * - Puedes elegir hasta maxSalsasSelectable tipos distintos
   * - Si ya está seleccionada, la deselecciona (sin límite)
   * - Si no está y ya llegaste al límite, no hace nada
   */
  function toggleSalsa(name: string) {
    const s = salsas.value.find((x) => x.name === name);
    if (!s) return;

    if (s.selected) {
      s.selected = false;
    } else {
      if (!salsasFull.value) {
        s.selected = true;
      }
    }
  }

  function toggleCoccion(opt: string) {
    const i = coccion.value.indexOf(opt);
    if (i >= 0) coccion.value.splice(i, 1);
    else coccion.value.push(opt);
  }

  function addExtra(extraDef: any, allProducts: Product[]) {
    const prod = allProducts.find((p) => p.id === extraDef.product_id);
    if (!prod) return;
    const existing = extras.value.find((e) => e.productId === prod.id);
    if (existing) {
      existing.qty++;
    } else {
      extras.value.push({
        productId: prod.id,
        name: prod.name,
        emoji: prod.emoji,
        price: prod.price,
        qty: 1,
      });
    }
  }

  function removeExtra(productId: number) {
    const idx = extras.value.findIndex((e) => e.productId === productId);
    if (idx === -1) return;
    if (extras.value[idx].qty > 1) extras.value[idx].qty--;
    else extras.value.splice(idx, 1);
  }

  function buildCustomization(): Customization {
    const c: Customization = {};
    const selectedSalsas = salsas.value.filter((s) => s.selected);
    if (selectedSalsas.length) c.salsas = selectedSalsas.map((s) => ({ ...s }));
    if (ensalada.value) c.ensalada = ensalada.value;
    if (papas.value) c.papas = papas.value;
    if (coccion.value.length) c.coccion = [...coccion.value];
    if (termino.value) c.termino = termino.value;
    if (temperatura.value) c.temperatura = temperatura.value;
    if (hielo.value) c.hielo = hielo.value;
    if (bebida.value) c.bebida = bebida.value;
    if (acompanamiento.value) c.acompanamiento = acompanamiento.value;
    if (tipo_pollo.value) c.tipo_pollo = tipo_pollo.value;
    if (nota.value) c.nota = nota.value;
    return c;
  }

  return {
    isOpen,
    product,
    qty,
    salsas,
    ensalada,
    papas,
    coccion,
    termino,
    temperatura,
    hielo,
    bebida,
    acompanamiento,
    tipo_pollo,
    nota,
    extras,
    config,
    cremasPerUnit,
    maxSalsasSelectable,
    selectedSalsasCount,
    salsasFull,
    extrasTotal,
    subtotal,
    summary,
    salsaOptions,
    hasSalsas,
    hasEnsalada,
    hasPapas,
    hasCoccion,
    hasTermino,
    hasTemperatura,
    hasHielo,
    hasBebida,
    hasNota,
    hasAcompanamiento,
    hasTipoPollo,
    hasExtras,
    open,
    close,
    toggleSalsa,
    toggleCoccion,
    addExtra,
    removeExtra,
    buildCustomization,
  };
}