<template>
    <footer class="bg-[#121216] text-zinc-300 font-sans border-t border-zinc-800">

        <!-- Contenido Principal -->
        <div class="max-w-7xl mx-auto px-6 pt-16 pb-12">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-12">

                <!-- Columna 1: Branding (5 cols) -->
                <div class="md:col-span-5 flex flex-col items-start gap-5">
                    <div class="h-24 flex items-center">
                        <div v-if="!brandingStore.loaded" class="w-12 h-12 rounded-xl bg-zinc-800 animate-pulse" />
                        <img v-else :src="brandingStore.branding.logo_url" :alt="brandingStore.branding.nombre_negocio"
                            class="h-full w-auto object-contain brightness-110"
                            @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
                    </div>


                    <p class="text-zinc-400 text-sm leading-relaxed max-w-sm">
                        El aroma inconfundible del café más especial de Chiclayo se mezcla con la frescura de las flores
                        que despiertan emociones en cada ocasión.
                    </p>
                </div>

                <!-- Columna 2: Contacto (3 cols) -->
                <div class="md:col-span-3 flex flex-col gap-4">
                    <h4 class="text-xs font-semibold tracking-wider text-emerald-500 uppercase">Contacto</h4>

                    <ul class="space-y-3.5">
                        <li v-if="brandingStore.branding.telefono">
                            <a :href="`tel:+51${brandingStore.branding.telefono}`"
                                class="group flex items-center gap-3 text-sm text-zinc-300 hover:text-white transition-colors">
                                <div
                                    class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700/60 flex items-center justify-center text-zinc-400 group-hover:border-emerald-400/40 group-hover:text-emerald-500 transition-all">
                                    <PhoneIcon class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-zinc-500 leading-none mb-1">Teléfono</p>
                                    <span class="font-medium">+51 {{ brandingStore.branding.telefono }}</span>
                                </div>
                            </a>
                        </li>

                        <li>
                            <a :href="waLink" target="_blank"
                                class="group flex items-center gap-3 text-sm text-zinc-300 hover:text-white transition-colors">
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-950/40 border border-emerald-800/40 flex items-center justify-center text-emerald-400 group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                    <WhatsAppIcon :size="15" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-zinc-500 leading-none mb-1">WhatsApp</p>
                                    <span class="font-medium group-hover:text-emerald-400 transition-colors">Pedir por
                                        WhatsApp</span>
                                </div>
                            </a>
                        </li>

                        <li class="flex items-center gap-3 text-sm text-zinc-300">
                            <div
                                class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700/60 flex items-center justify-center text-zinc-400 shrink-0">
                                <MapPinIcon class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-[11px] text-zinc-500 leading-none mb-1">Ubicación</p>
                                <span class="font-medium line-clamp-1">Torres Paz 261 | Chiclayo - Perú</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Columna 3: Horarios y Estado (4 cols) -->
                <div class="md:col-span-4 flex flex-col gap-4">
                    <h4 class="text-xs font-semibold tracking-wider text-emerald-500 uppercase">Atención</h4>

                    <div class="bg-zinc-800/50 border border-zinc-700/60 rounded-2xl p-4">

                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-zinc-700/50">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span v-if="isOpen"
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                        :class="isOpen ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                </span>
                                <span class="text-xs font-semibold tracking-wide uppercase"
                                    :class="isOpen ? 'text-emerald-400' : 'text-rose-400'">
                                    {{ isOpen ? 'Abierto ahora' : 'Cerrado' }}
                                </span>
                            </div>
                            <span class="text-xs font-mono text-zinc-400">{{ currentTime }}</span>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div v-for="h in HORARIOS" :key="h.dias"
                                class="flex justify-between items-center text-zinc-400">
                                <span>{{ h.dias }}</span>
                                <span class="font-medium text-zinc-200">{{ h.horas }}</span>
                            </div>
                        </div>
                    </div>

                    <RouterLink to="/"
                        class="w-full py-3 px-4 rounded-xl font-medium text-sm text-center text-white bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 transition-all duration-200 shadow-sm active:scale-[0.99] flex items-center justify-center gap-2">
                        Ver el catálogo completo
                    </RouterLink>
                </div>

            </div>

        </div>

        <!-- Barra Inferior en Gris Oscuro (Corporate Split) -->
        <div class="bg-[#111113] border-t border-zinc-800/80 py-6">
            <div
                class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-500">
                <p>© {{ year }} {{ brandingStore.branding.nombre_negocio }}. Todos los derechos reservados.</p>

                <div class="flex items-center gap-2">
                    <span>Hecho con</span>
                    <Heart :size="12" class="text-rose-500 fill-rose-500" />
                    <span>en Chiclayo, Perú</span>
                </div>
            </div>
        </div>

    </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { PhoneIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import { Heart } from 'lucide-vue-next'
import WhatsAppIcon from '@/components/icons/WhatsAppIcon.vue'
import { useBrandingStore } from '@/stores/branding'

const brandingStore = useBrandingStore()
const year = new Date().getFullYear()

const waLink = computed(() => {
    let phone = (brandingStore.branding.whatsapp
        ?? import.meta.env.VITE_WA_PHONE
        ?? '51984199340').replace(/\D/g, '')

    // Si no empieza con 51, lo agregamos
    if (!phone.startsWith('51')) {
        phone = '51' + phone
    }

    const msg = `Hola ${brandingStore.branding.nombre_negocio}, quisiera hacer un pedido`
    return `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`
})


const HORARIOS = [
    { dias: 'Lunes – Sábado', horas: '9:00 am – 9:00 pm' },
    { dias: 'Domingos', horas: 'Cerrado' },
]

const currentTime = ref('')
let clockTimer: ReturnType<typeof setInterval> | null = null

function updateTime() {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString('es-PE', {
        hour: '2-digit', minute: '2-digit', hour12: true,
    })
}

const isOpen = computed(() => {
    const now = new Date()
    const day = now.getDay()
    if (day === 0) return false
    const h = now.getHours()
    return h >= 9 && h < 21
})

onMounted(() => {
    updateTime()
    clockTimer = setInterval(updateTime, 30_000)
})

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer)
})
</script>