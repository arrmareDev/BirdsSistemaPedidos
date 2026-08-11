<script setup lang="ts">
// Resuelve un nombre de ícono en kebab-case (ej: "flower-2", "party-popper",
// tal como se guarda en la base de datos) al componente real de
// lucide-vue-next. Si el nombre no existe en la librería, cae a un ícono
// genérico neutral en vez de romper la vista.
import { computed } from "vue";
import * as LucideIcons from "lucide-vue-next";

const props = withDefaults(
  defineProps<{
    name?: string | null;
    size?: number | string;
    strokeWidth?: number;
  }>(),
  {
    name: null,
    size: 20,
    strokeWidth: 2,
  },
);

const FALLBACK_ICON = "Package";

function toPascalCase(kebab: string): string {
  return kebab
    .split("-")
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join("");
}

const resolvedIcon = computed(() => {
  const icons = LucideIcons as unknown as Record<string, object>;

  if (!props.name) return icons[FALLBACK_ICON];

  const pascalName = toPascalCase(props.name);
  return icons[pascalName] ?? icons[FALLBACK_ICON];
});
</script>

<template>
  <component
    :is="resolvedIcon"
    :size="size"
    :stroke-width="strokeWidth"
  />
</template>
