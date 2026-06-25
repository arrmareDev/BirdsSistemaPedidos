<template>
    <section class="relative overflow-hidden bg-[#1A0A14] select-none" :style="{ height: heroHeight }">

        <!-- ══ SLIDES ══ -->
        <div class="absolute inset-0">
            <TransitionGroup name="slide-fade" tag="div" class="relative w-full h-full">
                <div v-for="(slide, i) in SLIDES" :key="slide.id" v-show="current === i"
                    class="absolute inset-0 flex items-center">

                    <!-- ── Imagen de fondo completa ── -->
                    <div class="absolute inset-0 z-0">
                        <img v-if="slide.bgImage" :src="slide.bgImage" :alt="slide.title"
                            class="w-full h-full object-cover object-center"
                            @error="($event.target as HTMLImageElement).style.display = 'none'" />
                        <!-- Fallback: gradiente si no hay imagen -->
                        <div v-else class="w-full h-full" :style="{ background: slide.bg }" />
                    </div>

                    <!-- Overlay oscuro para legibilidad del texto -->
                    <div class="absolute inset-0 z-[1]"
                        :style="{ background: slide.overlay ?? 'linear-gradient(135deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.3) 60%, rgba(0,0,0,0.1) 100%)' }" />

                    <!-- Pattern decorativo -->
                    <div class="absolute inset-0 z-[2] opacity-[0.04]" style="background-image: repeating-conic-gradient(
              rgba(245,197,24,0.6) 0deg, transparent 4deg,
              transparent 14deg, rgba(245,197,24,0.6) 18deg
            );" />

                    <!-- Gradiente inferior -->
                    <div class="absolute bottom-0 inset-x-0 h-40 z-[3]"
                        style="background: linear-gradient(to top, #1A0A14 0%, transparent 100%)" />

                    <!-- ── Contenido ── -->
                    <div class="relative z-[4] w-full max-w-[1400px] mx-auto
                      px-6 md:px-12 lg:px-16
                      flex flex-col justify-center h-full">

                        <!-- Badge -->
                        <Transition name="slide-up" appear>
                            <div v-if="current === i" class="inline-flex items-center gap-2 px-4 py-2 rounded-full
                       mb-5 border backdrop-blur-sm w-fit" :style="{
                        background: slide.badgeBg,
                        borderColor: slide.badgeBorder,
                    }">
                                <span class="text-base">{{ slide.badgeIcon }}</span>
                                <span class="text-[12px] font-bold tracking-wide" :style="{ color: slide.badgeText }">
                                    {{ slide.badge }}
                                </span>
                            </div>
                        </Transition>

                        <!-- Título -->
                        <Transition name="slide-up-delay" appear>
                            <h2 v-if="current === i" class="font-black leading-[0.92] tracking-tight text-white m-0 mb-4
                       text-[40px] sm:text-[52px] md:text-[64px] lg:text-[72px]
                       uppercase max-w-3xl" style="font-family:'Plus Jakarta Sans',sans-serif;
                       text-shadow: 0 4px 32px rgba(0,0,0,0.5);" v-html="slide.title" />
                        </Transition>

                        <!-- Subtítulo -->
                        <Transition name="slide-up-delay2" appear>
                            <p v-if="current === i" class="text-[15px] md:text-[16px] text-white/80 leading-relaxed
                       mb-6 max-w-lg m-0" style="text-shadow: 0 2px 12px rgba(0,0,0,0.5);">
                                {{ slide.subtitle }}
                            </p>
                        </Transition>

                        <!-- Precio -->
                        <Transition name="slide-up-delay2" appear>
                            <div v-if="current === i && slide.priceTag" class="inline-flex items-baseline gap-3 mb-7">
                                <div v-if="slide.oldPrice" class="text-[20px] font-bold text-white/40 line-through">
                                    S/ {{ slide.oldPrice }}
                                </div>
                                <div class="font-black text-[52px] md:text-[64px] text-brand-yellow
                            leading-none drop-shadow-[0_4px_20px_rgba(245,197,24,0.4)]"
                                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                                    S/ {{ slide.priceTag }}
                                </div>
                                <div v-if="slide.priceSub" class="text-[13px] text-white/60 font-medium
                         max-w-[80px] leading-snug">
                                    {{ slide.priceSub }}
                                </div>
                            </div>
                        </Transition>

                        <!-- CTAs -->
                        <Transition name="slide-up-delay3" appear>
                            <div v-if="current === i" class="flex flex-wrap gap-3">
                                <button @click="$emit('scrollToMenu')" class="px-7 py-3.5 rounded-2xl font-black text-[14px]
                         border-none cursor-pointer transition-all duration-200
                         uppercase tracking-wide hover:-translate-y-0.5
                         hover:shadow-lg active:scale-95" :style="{
                            background: slide.ctaBg,
                            color: slide.ctaColor,
                        }" style="font-family:'Plus Jakarta Sans',sans-serif;">
                                    {{ slide.cta }}
                                </button>
                                <a v-if="slide.ctaWa" :href="waLink" target="_blank" class="flex items-center gap-2 px-7 py-3.5 rounded-2xl
                         font-bold text-[14px] no-underline
                         transition-all duration-200 hover:-translate-y-0.5
                         backdrop-blur-sm" style="background: rgba(37,211,102,0.2);
                         border: 1.5px solid rgba(37,211,102,0.5);
                         color: #4ade80;">
                                    💬 {{ slide.ctaWa }}
                                </a>
                            </div>
                        </Transition>

                        <!-- Floating badges — esquina derecha abajo -->
                        <div v-if="slide.floatBadges" class="absolute bottom-16 right-6 md:right-12
                     flex flex-col gap-2 items-end">
                            <Transition name="slide-up-delay3" appear>
                                <div v-if="current === i" class="flex flex-col gap-2">
                                    <div v-for="fb in slide.floatBadges" :key="fb.text" class="flex items-center gap-2 px-3.5 py-2 rounded-xl
                           backdrop-blur-md border font-bold text-[12px]
                           whitespace-nowrap shadow-sm" :style="{
                            background: fb.bg,
                            borderColor: fb.border,
                            color: fb.color,
                        }">
                                        {{ fb.icon }} {{ fb.text }}
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- ══ INDICADORES ══ -->
        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-30
                flex items-center gap-2">
            <button v-for="(_, i) in SLIDES" :key="i" @click="goTo(i)"
                class="rounded-full transition-all duration-400 cursor-pointer border-none" :class="current === i
                    ? 'bg-brand-yellow w-8 h-2.5'
                    : 'bg-white/30 hover:bg-white/60 w-2.5 h-2.5'" />
        </div>

        <!-- ══ FLECHAS ══ -->
        <button @click="prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-30
             w-11 h-11 rounded-full bg-black/30 border border-white/20
             flex items-center justify-center text-white text-xl cursor-pointer
             hover:bg-black/50 transition-all duration-150
             backdrop-blur-sm hidden md:flex">
            ‹
        </button>
        <button @click="next" class="absolute right-4 top-1/2 -translate-y-1/2 z-30
             w-11 h-11 rounded-full bg-black/30 border border-white/20
             flex items-center justify-center text-white text-xl cursor-pointer
             hover:bg-black/50 transition-all duration-150
             backdrop-blur-sm hidden md:flex">
            ›
        </button>

        <!-- ══ BARRA DE PROGRESO ══ -->
        <div class="absolute bottom-0 inset-x-0 h-0.5 bg-white/10 z-30">
            <div class="h-full bg-brand-yellow transition-none" :style="{
                width: progressWidth + '%',
                transition: paused ? 'none' : `width ${INTERVAL}ms linear`,
            }" />
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

defineEmits(['scrollToMenu'])

const INTERVAL = 5000
const current = ref(0)
const paused = ref(false)
const progress = ref(0)

const waPhone = import.meta.env.VITE_WA_PHONE ?? '51984199340'
const waLink = `https://wa.me/${waPhone}?text=${encodeURIComponent('¡Hola! Quisiera hacer un pedido de flores 💐')}`

// ── Pantalla completa en su sección (dvh evita el salto de la barra móvil) ──
const heroHeight = computed(() => '92dvh')
const progressWidth = computed(() =>
    paused.value ? (progress.value / INTERVAL * 100) : 100
)

// ── Slides — reemplaza bgImage con tus fotos reales ───────
const SLIDES = [
    {
        id: 1,
        bgImage: '/images/rosasrojas.png',  // ← pon tu foto aquí
        bg: 'linear-gradient(135deg,#7B1450,#3A0A28,#1A0A14)',
        overlay: 'linear-gradient(110deg, rgba(0,0,0,0.78) 0%, rgba(0,0,0,0.45) 55%, rgba(0,0,0,0.12) 100%)',
        badgeIcon: '🌹',
        badge: 'Selección del día',
        badgeText: '#F5C518',
        badgeBg: 'rgba(245,197,24,0.15)',
        badgeBorder: 'rgba(245,197,24,0.35)',
        title: 'Ramo de 12<br><span style="color:#F5C518">Rosas Rojas</span>',
        subtitle: 'Rosas frescas seleccionadas a mano, envueltas con papel premium y lazo de seda. El detalle que dice todo.',
        priceTag: '89',
        priceSub: 'precio especial hoy',
        ctaBg: '#C41E1E',
        ctaColor: '#FFFFFF',
        cta: 'Pedir ahora →',
        ctaWa: 'Pedir por WhatsApp',
        floatBadges: [
            { icon: '🌿', text: 'Flores frescas del día', bg: 'rgba(0,0,0,0.55)', border: 'rgba(255,255,255,0.15)', color: '#fff' },
            { icon: '🎀', text: 'Envoltura premium', bg: 'rgba(0,0,0,0.55)', border: 'rgba(255,255,255,0.15)', color: '#fff' },
        ],
    },
    {
        id: 2,
        bgImage: '/images/cajarosas.png',
        bg: 'linear-gradient(135deg,#4A1A38,#2A0D20,#1A0A14)',
        overlay: 'linear-gradient(110deg, rgba(0,0,0,0.78) 0%, rgba(0,0,0,0.42) 55%, rgba(0,0,0,0.10) 100%)',
        badgeIcon: '✨',
        badge: 'Diseño estrella',
        badgeText: '#FFFFFF',
        badgeBg: 'rgba(196,30,30,0.3)',
        badgeBorder: 'rgba(196,30,30,0.5)',
        title: 'Arreglo<br><span style="color:#F5C518">en Caja</span>',
        subtitle: 'Composición de rosas, eucalipto y flores de temporada en caja sombrero. Incluye tarjeta dedicatoria.',
        priceTag: '129',
        oldPrice: '159',
        priceSub: 'incluye tarjeta',
        ctaBg: '#F5C518',
        ctaColor: '#6B1240',
        cta: 'Ver arreglo →',
        ctaWa: null,
        floatBadges: [
            { icon: '💌', text: 'Tarjeta incluida', bg: 'rgba(245,197,24,0.2)', border: 'rgba(245,197,24,0.4)', color: '#F5C518' },
            { icon: '📦', text: 'Caja sombrero', bg: 'rgba(0,0,0,0.55)', border: 'rgba(255,255,255,0.15)', color: '#fff' },
        ],
    },
    {
        id: 3,
        bgImage: '/images/delivery.jpeg',
        bg: 'linear-gradient(135deg,#0A2A1A,#08160F,#1A0A14)',
        overlay: 'linear-gradient(110deg, rgba(0,0,0,0.76) 0%, rgba(0,0,0,0.40) 55%, rgba(0,0,0,0.10) 100%)',
        badgeIcon: '🛵',
        badge: 'Delivery disponible',
        badgeText: '#FFFFFF',
        badgeBg: 'rgba(34,197,94,0.15)',
        badgeBorder: 'rgba(34,197,94,0.35)',
        title: 'Entrega<br><span style="color: #F5C518">a Chiclayo</span>',
        subtitle: 'Llevamos tu detalle a domicilio con entrega programada. Chiclayo, J.L.O., La Victoria, Pimentel y San José.',
        priceTag: null,
        ctaBg: '#25D366',
        ctaColor: '#FFFFFF',
        cta: 'Pedir con delivery →',
        ctaWa: null,
        floatBadges: [
            { icon: '📍', text: 'Chiclayo y alrededores', bg: 'rgba(0,0,0,0.55)', border: 'rgba(255,255,255,0.15)', color: '#fff' },
            { icon: '🕐', text: 'Entrega programada', bg: 'rgba(0,0,0,0.55)', border: 'rgba(255,255,255,0.15)', color: '#fff' },
        ],
    },
    {
        id: 4,
        bgImage: '/images/ocacion.png',
        bg: 'linear-gradient(135deg,#3A0A28,#220618,#1A0A14)',
        overlay: 'linear-gradient(110deg, rgba(0,0,0,0.76) 0%, rgba(0,0,0,0.42) 55%, rgba(0,0,0,0.10) 100%)',
        badgeIcon: '🎁',
        badge: 'Para cada ocasión',
        badgeText: '#FDA4AF',
        badgeBg: 'rgba(251,113,133,0.15)',
        badgeBorder: 'rgba(251,113,133,0.3)',
        title: 'Flores para<br><span style="color:#F5C518">toda Ocasión</span>',
        subtitle: 'Cumpleaños, aniversarios, condolencias, graduaciones. Diseños personalizados para cada momento especial.',
        priceTag: '45',
        priceSub: 'desde',
        ctaBg: '#C41E1E',
        ctaColor: '#FFFFFF',
        cta: 'Ver catálogo →',
        ctaWa: null,
        floatBadges: [
            { icon: '🌸', text: 'Diseños a medida', bg: 'rgba(0,0,0,0.55)', border: 'rgba(255,255,255,0.15)', color: '#fff' },
            { icon: '⭐', text: '4.9 estrellas', bg: 'rgba(245,197,24,0.2)', border: 'rgba(245,197,24,0.4)', color: '#F5C518' },
        ],
    },
]

// ── Timer ─────────────────────────────────────────────────
let timer: any = null
let progTimer: any = null

function startTimer() {
    clearInterval(timer)
    clearInterval(progTimer)
    paused.value = false
    progress.value = 0

    const step = 50
    progTimer = setInterval(() => {
        progress.value += step
        if (progress.value >= INTERVAL) progress.value = INTERVAL
    }, step)

    timer = setTimeout(() => { next(); startTimer() }, INTERVAL)
}

function next() { current.value = (current.value + 1) % SLIDES.length; startTimer() }
function prev() { current.value = (current.value - 1 + SLIDES.length) % SLIDES.length; startTimer() }
function goTo(i: number) { current.value = i; startTimer() }

onMounted(() => startTimer())
onUnmounted(() => { clearTimeout(timer); clearInterval(progTimer) })
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.8s ease, transform 0.8s ease;
    position: absolute;
    inset: 0;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: scale(1.04);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: scale(0.97);
}

.slide-fade-enter-to,
.slide-fade-leave-from {
    opacity: 1;
    transform: scale(1);
}

.slide-up-enter-active {
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(24px);
}

.slide-up-delay-enter-active {
    transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.1s;
}

.slide-up-delay-enter-from {
    opacity: 0;
    transform: translateY(28px);
}

.slide-up-delay2-enter-active {
    transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.2s;
}

.slide-up-delay2-enter-from {
    opacity: 0;
    transform: translateY(22px);
}

.slide-up-delay3-enter-active {
    transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.32s;
}

.slide-up-delay3-enter-from {
    opacity: 0;
    transform: translateY(18px);
}
</style>