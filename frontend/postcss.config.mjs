/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        fire: {
          DEFAULT: "#E8521A",
          deep: "#C03E0D",
          glow: "#FF6B35",
          light: "#FFF0EB",
          mid: "#FDDDD3",
        },
        gold: {
          DEFAULT: "#C8920A",
          light: "#FDF3DC",
          mid: "#F5D98A",
        },
        cream: {
          DEFAULT: "#FDFAF6",
          warm: "#F7F2EB",
          card: "#FFFFFF",
          border: "#EDE8E0",
          strong: "#DDD6CC",
        },
        ink: {
          DEFAULT: "#1C1713",
          soft: "#3D3530",
          mid: "#6B5E55",
          muted: "#9A8E86",
          faint: "#C4B9B1",
        },
      },
      fontFamily: {
        display: ['"Playfair Display"', "serif"],
        body: ['"Plus Jakarta Sans"', "sans-serif"],
      },
      boxShadow: {
        card: "0 1px 4px rgba(28,23,19,0.06), 0 4px 16px rgba(28,23,19,0.06)",
        "card-lg":
          "0 4px 24px rgba(28,23,19,0.10), 0 1px 4px rgba(28,23,19,0.06)",
        "fire-sm": "0 2px 12px rgba(232,82,26,0.25)",
        "fire-md": "0 4px 24px rgba(232,82,26,0.32)",
        "fire-lg": "0 8px 40px rgba(232,82,26,0.40)",
      },
      animation: {
        float: "float 4s ease-in-out infinite",
        shimmer: "shimmer 1.5s ease-in-out infinite",
      },
      keyframes: {
        float: {
          "0%,100%": { transform: "translateY(0)" },
          "50%": { transform: "translateY(-10px)" },
        },
        shimmer: { "0%,100%": { opacity: "0.5" }, "50%": { opacity: "1" } },
      },
    },
  },
  plugins: [],
};
