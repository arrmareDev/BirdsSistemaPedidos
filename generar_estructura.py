import os

# Carpetas que se ignorarán
IGNORAR = {
    "node_modules",
    "vendor",
    ".git",
    "dist",
    "build",
    "storage",
    "__pycache__",
    ".idea",
    ".vscode",
    ".angular",
    "bootstrap",
}

SALIDA = "estructura_proyecto.txt"


def escribir_arbol(ruta, archivo, prefijo=""):
    elementos = sorted(os.listdir(ruta))

    elementos = [
        e for e in elementos
        if e not in IGNORAR
    ]

    for i, elemento in enumerate(elementos):
        ruta_completa = os.path.join(ruta, elemento)

        ultimo = i == len(elementos) - 1
        rama = "└── " if ultimo else "├── "

        archivo.write(prefijo + rama + elemento + "\n")

        if os.path.isdir(ruta_completa):
            extension = "    " if ultimo else "│   "
            escribir_arbol(ruta_completa, archivo, prefijo + extension)


with open(SALIDA, "w", encoding="utf-8") as f:
    f.write(f"Proyecto: {os.path.basename(os.getcwd())}\n\n")
    escribir_arbol(os.getcwd(), f)

print(f"Estructura guardada en {SALIDA}")   