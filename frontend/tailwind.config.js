/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        brand: {
          green: "#163020",
          green2: "#102418",
          green3: "#1C3B27",
          accent: "#E6D5C3",
          terracotta: "#9A6B56",
          dark: "#0F1A13",
          bg: "#F8F5F0",
        },
        surface: {
          DEFAULT: "#F9FAF9",
          card: "#FFFFFF",
          warm: "#F3F5F3",
          border: "#E0E5E1",
          border2: "#C2CCC4",
        },
        ink: {
          DEFAULT: "#0F1A13",
          soft: "#1A2E22",
          mid: "#3D5C4A",
          muted: "#7A9987",
          faint: "#A8BFB3",
        },
        // ── Aliases para componentes ──
        birds: {
          DEFAULT: "#0C542C",
          deep: "#083B1E",
          glow: "#126B38",
          light: "rgba(12,84,44,0.10)",
        },
        cream: {
          DEFAULT: "#FFFAF5",
          warm: "#FFF5EE",
          card: "#FFFFFF",
          border: "#F0E8DC",
          strong: "#E8D8C8",
        },
        // ── Rojo de marca (POS / catálogo florería) ──
        "brand-red": {
          DEFAULT: "#C41E1E",
          dark: "#9B1717",
          light: "#FDEDED",
        },
      },
      fontFamily: {
        display: ['"Playfair Display"', "serif"],
        body: ['"Plus Jakarta Sans"', "sans-serif"],
      },
      boxShadow: {
        card: "0 1px 4px rgba(12,84,44,0.06), 0 2px 8px rgba(12,84,44,0.04)",
        "card-lg": "0 4px 20px rgba(12,84,44,0.10)",
        "green-sm": "0 2px 12px rgba(12,84,44,0.25)",
        "green-md": "0 4px 24px rgba(12,84,44,0.35)",
        "green-lg": "0 8px 40px rgba(12,84,44,0.45)",
        "red-sm": "0 2px 12px rgba(196,30,30,0.25)",
        "red-md": "0 4px 24px rgba(196,30,30,0.35)",
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
