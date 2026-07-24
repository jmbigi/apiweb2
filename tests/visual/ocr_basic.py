#!/usr/bin/env python3
"""
OCR básico con Tesseract para verificar contenido visible en screenshots.
Sin API key, sin dependencias externas (Pillow + pytesseract).
"""
import sys
import json
from pathlib import Path

try:
    from PIL import Image
    import pytesseract
except ImportError as e:
    print(json.dumps({"status": "error", "message": f"Missing dependency: {e}"}))
    sys.exit(1)


def ocr_image(image_path: str, lang: str = "spa+eng") -> dict:
    try:
        img = Image.open(image_path)
        text = pytesseract.image_to_string(img, lang=lang)
        data = pytesseract.image_to_data(img, lang=lang, output_type=pytesseract.Output.DICT)

        words_found = len([w for w in data["text"] if w.strip()])
        confidences = [int(c) for c in data["conf"] if c != "-1"]

        return {
            "status": "ok",
            "text_preview": text[:500],
            "words_found": words_found,
            "avg_confidence": round(sum(confidences) / len(confidences), 1) if confidences else 0,
            "full_text": text,
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Usage: ocr_basic.py <image_path> [lang]"}))
        sys.exit(1)

    image_path = sys.argv[1]
    lang = sys.argv[2] if len(sys.argv) > 2 else "spa+eng"

    if not Path(image_path).exists():
        print(json.dumps({"status": "error", "message": f"File not found: {image_path}"}))
        sys.exit(1)

    result = ocr_image(image_path, lang)
    print(json.dumps(result, ensure_ascii=False, indent=2))
