# Visual Testing Suite — Faristol Web Apps

Sistema automatizado de revisión visual para Faristol App y Control App
usando **OCR avanzado** (Tesseract) y **Visión IA** (Pillow/image diff).

## Stack

| Herramienta | Propósito |
|---|---|
| **Playwright** (Node.js) | Automatiza Chrome, navega apps, toma screenshots |
| **Tesseract OCR** | Extrae texto de las capturas para verificar contenido |
| **Pillow** (Python) | Análisis de imagen, detección de regiones vacías, contraste |
| **ImageMagick** | Comparación pixel-exacta con baselines |

## Flujo

1. `runner.js` — navega cada app, toma screenshot, extrae texto con OCR,
   verifica elementos clave visibles (CIF, Email, "Faristol App", etc.)
2. `ocr_vision.py` — análisis profundo: OCR estructurado, densidad UI,
   regiones vacías, score de contraste, diff con baseline

## Uso

```bash
# 1. Tomar screenshots y verificar con OCR (Node.js)
cd tests/visual
node runner.js

# 2. Actualizar baselines (cuando el UI cambia intencionalmente)
node runner.js --update-baselines

# 3. Análisis avanzado con OCR + Visión IA (Python)
python3 ocr_vision.py
```

## Métricas

- **Densidad UI**: % de la pantalla ocupado por texto (ideal: 5-15%)
- **Regiones vacías**: zonas >100px² sin texto
- **Contraste**: score 0-100 (mínimo recomendado: 40)
- **Diff con baseline**: % de píxeles diferentes (aceptable: <0.5%)

## Estructura

```
tests/visual/
├── runner.js             # Playwright: navegación + screenshots + OCR básico
├── ocr_vision.py         # Python: OCR profundo + análisis de imagen
├── screenshots/          # Capturas actuales
├── baselines/            # Capturas de referencia
├── diffs/                # Diferencias detectadas
├── report.json           # Reporte JSON del análisis
└── README.md
```
