/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        brand: {
          red: "#C41E1E",
          red2: "#9B0000",
          red3: "#E03030",
          yellow: "#F5C518",
          gold: "#D4A017",
          dark: "#6B0000",
          bg: "#FFFAF5",
        },
        surface: {
          DEFAULT: "#FFFAF5",
          card: "#FFFFFF",
          warm: "#FFF5EE",
          border: "#F0E8DC",
          border2: "#E8D8C8",
        },
        ink: {
          DEFAULT: "#1A0800",
          soft: "#3D1A08",
          mid: "#7A5A48",
          muted: "#A88870",
          faint: "#C8B8A8",
        },
        // ── Aliases para el HeroCarousel ──
        fire: {
          DEFAULT: "#C41E1E",
          deep: "#9B0000",
          glow: "#E03030",
          light: "rgba(196,30,30,0.10)",
        },
        gold: {
          DEFAULT: "#F5C518",
          deep: "#D4A017",
          light: "rgba(245,197,24,0.12)",
          mid: "rgba(245,197,24,0.25)",
        },
        cream: {
          DEFAULT: "#FFFAF5",
          warm: "#FFF5EE",
          card: "#FFFFFF",
          border: "#F0E8DC",
          strong: "#E8D8C8",
        },
      },
      fontFamily: {
        display: ['"Playfair Display"', "serif"],
        body: ['"Plus Jakarta Sans"', "sans-serif"],
      },
      boxShadow: {
        card: "0 1px 4px rgba(26,8,0,0.06), 0 2px 8px rgba(26,8,0,0.04)",
        "card-lg": "0 4px 20px rgba(26,8,0,0.10)",
        "red-sm": "0 2px 12px rgba(196,30,30,0.25)",
        "red-md": "0 4px 24px rgba(196,30,30,0.35)",
        "red-lg": "0 8px 40px rgba(196,30,30,0.45)",
        "gold-sm": "0 2px 12px rgba(245,197,24,0.30)",
        "fire-sm": "0 2px 12px rgba(196,30,30,0.25)",
        "fire-md": "0 4px 24px rgba(196,30,30,0.35)",
        "fire-lg": "0 8px 40px rgba(196,30,30,0.45)",
      },
      borderRadius: {
        "4xl": "2rem",
        "5xl": "2.5rem",
      },
      animation: {
        float: "float 4s ease-in-out infinite",
        shimmer: "shimmer 1.5s ease-in-out infinite",
        "scroll-promo": "scroll-promo 20s linear infinite",
        "pulse-soft": "pulse-soft 2s ease-in-out infinite",
      },
      keyframes: {
        float: {
          "0%,100%": { transform: "translateY(0)" },
          "50%": { transform: "translateY(-12px)" },
        },
        shimmer: {
          "0%,100%": { opacity: "0.5" },
          "50%": { opacity: "1" },
        },
        "scroll-promo": {
          from: { transform: "translateX(0)" },
          to: { transform: "translateX(-50%)" },
        },
        "pulse-soft": {
          "0%,100%": { opacity: "1" },
          "50%": { opacity: "0.6" },
        },
      },
    },
  },
  plugins: [],
};
