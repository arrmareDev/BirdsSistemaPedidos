/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_WA_PHONE: string
  // Aquí puedes agregar otras variables de tu .env en el futuro
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}