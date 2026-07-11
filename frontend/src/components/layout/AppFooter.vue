<template>
    <footer class="footer-root relative overflow-hidden">

        <!-- Fondo animado -->
        <div class="footer-bg" aria-hidden="true">
            <div class="footer-blob footer-blob-1" />
            <div class="footer-blob footer-blob-2" />
            <div class="footer-grid" />
        </div>

        <!-- Contenido -->
        <div class="relative z-10 max-w-7xl mx-auto px-6 pt-14 pb-8">

            <!-- ── Grid principal ── -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">

                <!-- Brand -->
                <div class="flex flex-col gap-5">
                    <div class="flex items-center gap-3.5">
                        <div class="logo-wrap w-16 h-16 overflow-hidden shrink-0
                        flex items-center justify-center">
                            <!-- ← MARCA -->
                            <img :src="logoUrl" alt="Florería" class="w-full h-full object-contain"
                                @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />

                        </div>
                        <div>
                            <!-- ← MARCA: nombre real -->
                            <p class="font-black text-[19px] text-white leading-none uppercase
                         tracking-wide m-0">
                                Florería
                            </p>
                            <p class="text-[10px] tracking-[2px] uppercase m-0 mt-1 brand-sub">
                                Flores & Detalles
                            </p>
                        </div>
                    </div>

                    <p class="text-[13.5px] text-white/50 leading-relaxed m-0 max-w-[260px]">
                        Las flores más frescas de Chiclayo.
                        Diseños que emocionan para cada ocasión especial. 💐
                    </p>

                    <!-- Rating decorativo -->
                    <div class="rating-pill flex items-center gap-2.5 w-fit
                      px-4 py-2.5 rounded-2xl">
                        <div class="flex gap-0.5">
                            <span v-for="i in 5" :key="i" class="text-[14px] star-icon">⭐</span>
                        </div>
                        <span class="text-[12px] font-bold text-white/80">4.9 · +500 reseñas</span>
                    </div>
                </div>

                <!-- Info contacto -->
                <div class="flex flex-col gap-4">
                    <p class="section-label">Contacto</p>

                    <a href="tel:+51984199340" class="contact-link flex items-center gap-3 group w-fit">
                        <div class="contact-icon-wrap w-9 h-9 rounded-xl flex items-center
                        justify-center shrink-0 transition-all duration-200
                        group-hover:scale-110">
                            <PhoneIcon class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <p class="text-[13px] text-white/50 m-0 leading-none mb-0.5">Teléfono</p>
                            <p class="text-[15px] font-bold text-white m-0 leading-none
                         group-hover:text-brand-yellow transition-colors">
                                984199340
                            </p>
                        </div>
                    </a>

                    <a :href="waLink" target="_blank" class="contact-link flex items-center gap-3 group w-fit">
                        <div class="contact-icon-wrap wa w-9 h-9 rounded-xl flex items-center
                        justify-center shrink-0 transition-all duration-200
                        group-hover:scale-110">
                            <WhatsAppIcon :size="16" />
                        </div>
                        <div>
                            <p class="text-[13px] text-white/50 m-0 leading-none mb-0.5">WhatsApp</p>
                            <p class="text-[15px] font-bold text-white m-0 leading-none
                         group-hover:text-[#25D366] transition-colors">
                                Pedir por WhatsApp
                            </p>
                        </div>
                    </a>

                    <div class="flex items-center gap-3">
                        <div class="contact-icon-wrap w-9 h-9 rounded-xl flex items-center
                        justify-center shrink-0">
                            <MapPinIcon class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <p class="text-[13px] text-white/50 m-0 leading-none mb-0.5">Ubicación</p>
                            <p class="text-[15px] font-bold text-white m-0 leading-none">
                                Chiclayo, Torres Paz N° 361
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Horarios + estado -->
                <div class="flex flex-col gap-4">
                    <p class="section-label">Horarios</p>

                    <div class="horario-card rounded-2xl p-4 flex flex-col gap-3">

                        <!-- Estado en tiempo real -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full status-dot" :class="isOpen ? 'open' : 'closed'" />
                                <span class="text-[13px] font-black"
                                    :class="isOpen ? 'text-green-400' : 'text-red-400'">
                                    {{ isOpen ? 'Abierto ahora' : 'Cerrado ahora' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-white/40 font-mono">{{ currentTime }}</span>
                        </div>

                        <div class="border-t border-white/10 pt-3 flex flex-col gap-2">
                            <div v-for="h in HORARIOS" :key="h.dias" class="flex items-center justify-between">
                                <span class="text-[12.5px] text-white/50">{{ h.dias }}</span>
                                <span class="text-[12.5px] font-semibold text-white/80">{{ h.horas }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CTA rápido -->
                    <RouterLink to="/" class="cta-footer flex items-center justify-center gap-2
                   py-3 rounded-2xl no-underline font-bold text-[14px]
                   text-white transition-all duration-200
                   hover:-translate-y-0.5 active:scale-[0.98]">
                        💐 Ver el catálogo
                    </RouterLink>
                </div>
            </div>

            <!-- ── Divisor ── -->
            <div class="footer-divider mb-6" />

            <!-- ── Bottom bar ── -->
            <div class="flex flex-col sm:flex-row items-center
                  justify-between gap-3">
                <!-- ← MARCA -->
                <p class="text-[12px] text-white/25 m-0">
                    © {{ year }} Florería · Todos los derechos reservados
                </p>

                <div class="flex items-center gap-4">
                    <span class="text-[12px] text-white/25">
                        Chiclayo, Perú 🇵🇪
                    </span>
                    <span class="text-white/10">·</span>
                    <span class="text-[12px] text-white/25">
                        Hecho con ❤️ en Perú
                    </span>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { PhoneIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import WhatsAppIcon from '@/components/icons/WhatsAppIcon.vue'

const logoUrl = '/images/birdsblanco.png'
const year = new Date().getFullYear()

// ── WhatsApp ──────────────────────────────────────────────
const waPhone = (import.meta.env.VITE_WA_PHONE ?? '51984199340').replace(/\D/g, '')
const waLink = `https://wa.me/${waPhone}?text=${encodeURIComponent('Hola Birds quisiera hacer un pedido de flores')}`

// Y el array de horarios también:
const HORARIOS = [
    { dias: 'Lunes – Sábado', horas: '9:00 am – 9:00 pm' },
    { dias: 'Domingos', horas: 'Cerrado' },
]
// ── Reloj + estado ────────────────────────────────────────
const currentTime = ref('')
let clockTimer: ReturnType<typeof setInterval> | null = null

function updateTime() {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString('es-PE', {
        hour: '2-digit', minute: '2-digit', hour12: true,
    })
}

// Footer.vue — corregir isOpen
const isOpen = computed(() => {
    const now = new Date()
    const day = now.getDay()
    if (day === 0) return false // domingo cerrado
    const h = now.getHours()
    return h >= 9 && h < 22   // 9am – 10pm
})
onMounted(() => {
    updateTime()
    clockTimer = setInterval(updateTime, 30_000)
})

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer)
})
</script>

<style scoped>
/* ── Root ── */
.footer-root {
    background: #0F0B09;
    border-top: 1px solid rgba(196, 30, 30, 0.15);
    margin-top: 2.5rem;
}

/* ── Fondo animado ── */
.footer-bg {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.footer-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.25;
}

.footer-blob-1 {
    width: 500px;
    height: 400px;
    background: radial-gradient(circle, rgba(196, 30, 30, 0.5), transparent 70%);
    top: -100px;
    left: -80px;
    animation: footer-blob-move 20s ease-in-out infinite;
}

.footer-blob-2 {
    width: 400px;
    height: 350px;
    background: radial-gradient(circle, rgba(245, 197, 24, 0.2), transparent 70%);
    bottom: -80px;
    right: -60px;
    animation: footer-blob-move 25s ease-in-out infinite reverse;
}

@keyframes footer-blob-move {

    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    40% {
        transform: translate(40px, -30px) scale(1.08);
    }

    70% {
        transform: translate(-20px, 20px) scale(0.94);
    }
}

.footer-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(196, 30, 30, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(196, 30, 30, 0.04) 1px, transparent 1px);
    background-size: 40px 40px;
}

/* ── Brand sub ── */
.brand-sub {
    color: #F5C518;
    letter-spacing: 2px;
}

/* ── Rating ── */
.rating-pill {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
}

.star-icon {
    animation: star-twinkle 2.5s ease-in-out infinite;
}

.star-icon:nth-child(2) {
    animation-delay: 0.2s;
}

.star-icon:nth-child(3) {
    animation-delay: 0.4s;
}

.star-icon:nth-child(4) {
    animation-delay: 0.6s;
}

.star-icon:nth-child(5) {
    animation-delay: 0.8s;
}

@keyframes star-twinkle {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.6;
        transform: scale(0.85);
    }
}

/* ── Section label ── */
.section-label {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #F5C518;
    margin: 0;
    padding-bottom: 4px;
    border-bottom: 1px solid rgba(245, 197, 24, 0.15);
}

/* ── Contact ── */
.contact-link {
    text-decoration: none;
}

.contact-icon-wrap {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.contact-icon-wrap.wa {
    background: rgba(37, 211, 102, 0.15);
    border-color: rgba(37, 211, 102, 0.25);
}

/* ── Horario card ── */
.horario-card {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(8px);
}

/* ── Status dot ── */
.status-dot {
    transition: all 0.3s ease;
}

.status-dot.open {
    background: #4ade80;
    box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.2);
    animation: pulse-green 2s ease-in-out infinite;
}

.status-dot.closed {
    background: #f87171;
}

@keyframes pulse-green {

    0%,
    100% {
        box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.2);
    }

    50% {
        box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.05);
    }
}

/* ── CTA footer ── */
.cta-footer {
    background: linear-gradient(135deg, #C41E1E 0%, #9A0F0F 100%);
    box-shadow: 0 4px 20px rgba(196, 30, 30, 0.35);
}

.cta-footer:hover {
    box-shadow: 0 8px 28px rgba(196, 30, 30, 0.5);
}

/* ── Divider ── */
.footer-divider {
    height: 1px;
    background: linear-gradient(90deg,
            transparent,
            rgba(196, 30, 30, 0.3) 20%,
            rgba(245, 197, 24, 0.2) 50%,
            rgba(196, 30, 30, 0.3) 80%,
            transparent);
}
</style>