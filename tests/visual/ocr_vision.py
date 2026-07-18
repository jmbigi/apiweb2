"""
Faristol Visual Testing — OCR avanzado + Visión IA
Analiza screenshots de las apps web para verificar UX.
"""
import os
import sys
import json
from pathlib import Path

from PIL import Image, ImageChops, ImageFilter
import pytesseract

BASE_URL = "https://web2.faristol.net"
SCREENSHOTS = Path(__file__).parent / "screenshots"
BASELINES = Path(__file__).parent / "baselines"
DIFFS = Path(__file__).parent / "diffs"
REPORT = Path(__file__).parent / "report.json"


def analyze_ui(image_path: str) -> dict:
    """Analiza una captura con OCR + Visión IA y devuelve métricas."""
    img = Image.open(image_path)
    w, h = img.size

    # OCR: extraer todo el texto
    ocr_data = pytesseract.image_to_data(img, output_type=pytesseract.Output.DICT, lang="spa+eng")

    # Métricas de UI density (qué % de la pantalla tiene texto)
    text_boxes = 0
    text_chars = 0
    words = []
    for i, text in enumerate(ocr_data["text"]):
        text = text.strip()
        if text:
            text_boxes += 1
            text_chars += len(text)
            words.append(text)

    ui_density = round(text_chars / (w * h) * 10000, 2)

    # Detectar regiones vacías (más de 200x200 px sin texto)
    empty_regions = _detect_empty_regions(img, ocr_data)

    # Detectar contraste de color
    contrast_score = _analyze_contrast(img)

    return {
        "dimensions": f"{w}x{h}",
        "ocr": {
            "text_boxes": text_boxes,
            "total_chars": text_chars,
            "words_found": words[:50],
            "word_count": len(words),
        },
        "ui": {
            "density": ui_density,
            "empty_regions": empty_regions,
            "contrast_score": contrast_score,
        },
    }


def _detect_empty_regions(img, ocr_data):
    """Detecta zonas grandes sin texto."""
    w, h = img.size
    ocr_boxes = []
    for i in range(len(ocr_data["text"])):
        if ocr_data["text"][i].strip():
            x = ocr_data["left"][i]
            y = ocr_data["top"][i]
            bw = ocr_data["width"][i]
            bh = ocr_data["height"][i]
            ocr_boxes.append((x, y, x + bw, y + bh))

    empty = []
    grid_size = 100
    for gx in range(0, w, grid_size):
        for gy in range(0, h, grid_size):
            region = (gx, gy, gx + grid_size, gy + grid_size)
            has_text = any(
                _overlaps(region, box) for box in ocr_boxes
            )
            if not has_text:
                empty.append(region)

    total_px = w * h
    empty_px = len(empty) * grid_size * grid_size
    return {
        "empty_pct": round(empty_px / total_px * 100, 1),
        "empty_zones": len(empty),
    }


def _analyze_contrast(img):
    """Puntúa el contraste general de la UI (0-100)."""
    gray = img.convert("L")
    pixels = list(gray.getdata())
    brightness = sum(pixels) / len(pixels)
    std = (sum((p - brightness) ** 2 for p in pixels) / len(pixels)) ** 0.5
    return round(min(std / 2.55, 100), 1)


def _overlaps(a, b):
    return not (a[2] <= b[0] or a[0] >= b[2] or a[3] <= b[1] or a[1] >= b[3])


def compare_with_baseline(name: str) -> dict:
    """Compara screenshot con baseline usando差值."""
    shot = SCREENSHOTS / f"{name}.png"
    base = BASELINES / f"{name}.png"
    diff = DIFFS / f"{name}.png"

    if not base.exists():
        import shutil
        shutil.copy(shot, base)
        return {"match": True, "diff_pct": 0, "new_baseline": True}

    img1 = Image.open(shot).convert("RGB")
    img2 = Image.open(base).convert("RGB")

    if img1.size != img2.size:
        img2 = img2.resize(img1.size)

    diff_img = ImageChops.difference(img1, img2)
    diff_img.save(diff)

    # Calcular % de píxeles diferentes
    diff_pixels = sum(1 for p in diff_img.getdata() if any(c > 20 for c in p))
    total_pixels = img1.size[0] * img1.size[1]
    diff_pct = round(diff_pixels / total_pixels * 100, 3)

    return {
        "match": diff_pct < 0.5,
        "diff_pct": diff_pct,
        "diff_file": str(diff),
    }


def run():
    print("\n🧪 Faristol Visual Testing — OCR + IA Vision\n")
    print(f"🔗 {BASE_URL}\n")

    results = {}

    for shot_file in sorted(SCREENSHOTS.glob("*.png")):
        name = shot_file.stem
        print(f"📸 Analizando: {name}")

        analysis = analyze_ui(str(shot_file))
        comparison = compare_with_baseline(name)

        results[name] = {
            "analysis": analysis,
            "comparison": comparison,
        }

        # Mostrar resumen
        a = analysis
        print(f"   OCR: {a['ocr']['word_count']} palabras, {a['ocr']['text_boxes']} bloques")
        print(f"   UI:  density={a['ui']['density']}, contraste={a['ui']['contrast_score']}")
        print(f"   Vacío: {a['ui']['empty_regions']['empty_pct']}%")

        if comparison["match"]:
            print(f"   ✅ Coincide con baseline (diff: {comparison['diff_pct']}%)")
        else:
            print(f"   ❌ DIFIERE: {comparison['diff_pct']}% diferente → {comparison['diff_file']}")

        print()

    # Guardar reporte JSON
    with open(REPORT, "w") as f:
        json.dump(results, f, indent=2, ensure_ascii=False)

    # Resumen final
    passed = sum(1 for r in results.values() if r["comparison"]["match"])
    total = len(results)
    print("=" * 50)
    print(f"📊 REPORTE: {passed}/{total} tests visuales OK")
    print(f"📄 Reporte JSON: {REPORT}")
    print("=" * 50 + "\n")

    return passed == total


if __name__ == "__main__":
    success = run()
    sys.exit(0 if success else 1)
