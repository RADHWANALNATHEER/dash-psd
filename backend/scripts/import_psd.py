#!/usr/bin/env python3
"""تحويل ملف PSD إلى قوالب: كل Artboard (أو المستند نفسه إن لم توجد Artboards)
يتحوّل إلى قالب مستقل، وتُستخرج طبقات النص والصور كحقول ديناميكية تلقائيًا.

الخرج: report.json داخل --output-dir بالشكل الذي يتوقعه PsdImportService.
"""
import argparse
import json
import re
import sys
import unicodedata
from pathlib import Path

from psd_tools import PSDImage
from psd_tools.api.layers import Artboard

IMAGE_NAME_HINTS = (
    "img", "image", "photo", "picture", "logo",
    "صورة", "فوتو", "شعار",
)


def slugify(name: str, fallback: str) -> str:
    name = unicodedata.normalize("NFKD", name or "")
    name = re.sub(r"[^a-zA-Z0-9_]+", "_", name).strip("_").lower()
    return name or fallback


def looks_like_image_layer(layer) -> bool:
    if layer.kind not in ("smartobject", "pixel", "shape"):
        return False
    name = (layer.name or "").lower()
    return any(hint in name for hint in IMAGE_NAME_HINTS)


def font_info(layer):
    info = {"font_family": None, "font_size": None, "font_weight": None, "color": None, "align": None}
    try:
        engine = layer.engine_dict
        style_sheet = engine["StyleRun"]["RunArray"][0]["StyleSheet"]["StyleSheetData"]
        font_set = engine.get("DocumentResources", {}).get("FontSet", [])
        font_index = style_sheet.get("Font")
        if font_index is not None and font_index < len(font_set):
            info["font_family"] = font_set[font_index].get("Name")
        size = style_sheet.get("FontSize")
        if size is not None:
            info["font_size"] = round(float(size))
        if style_sheet.get("FauxBold"):
            info["font_weight"] = 700
        fill = style_sheet.get("FillColor", {}).get("Values")
        if fill and len(fill) == 4:
            _, r, g, b = fill
            info["color"] = "#{:02x}{:02x}{:02x}".format(
                round(r * 255), round(g * 255), round(b * 255)
            )
        paragraph = engine["ParagraphRun"]["RunArray"][0]["ParagraphSheet"]["Properties"]
        justification = paragraph.get("Justification")
        info["align"] = {0: "right", 1: "left", 2: "center"}.get(justification, "right")
    except Exception:
        pass
    return info


def extract_layers(root, origin_left, origin_top):
    layers = []
    used_keys = set()

    for layer in root.descendants():
        if layer.is_group() or not layer.is_visible():
            continue

        bbox = layer.bbox
        if not bbox or bbox[2] <= bbox[0] or bbox[3] <= bbox[1]:
            continue

        x = bbox[0] - origin_left
        y = bbox[1] - origin_top
        width = bbox[2] - bbox[0]
        height = bbox[3] - bbox[1]

        base_key = slugify(layer.name, f"layer_{len(layers) + 1}")
        key = base_key
        suffix = 1
        while key in used_keys:
            suffix += 1
            key = f"{base_key}_{suffix}"
        used_keys.add(key)

        if layer.kind == "type":
            info = font_info(layer)
            layers.append({
                "key": key,
                "label": layer.name or key,
                "type": "text",
                "x": x, "y": y, "width": width, "height": height,
                "font_family": info["font_family"],
                "font_size": info["font_size"] or 24,
                "font_weight": info["font_weight"] or 400,
                "color": info["color"] or "#000000",
                "align": info["align"] or "right",
                "direction": "rtl",
                "line_height": 1.3,
                "placeholder": layer.text if hasattr(layer, "text") else None,
                "is_required": True,
            })
        elif looks_like_image_layer(layer):
            layers.append({
                "key": key,
                "label": layer.name or key,
                "type": "image",
                "x": x, "y": y, "width": width, "height": height,
                "object_fit": "cover",
                "border_radius": 0,
                "is_required": True,
            })

    return layers


def hide_dynamic_layers(root):
    hidden = []
    for layer in root.descendants():
        if layer.is_group():
            continue
        if layer.kind == "type" or looks_like_image_layer(layer):
            if layer.is_visible():
                layer.visible = False
                hidden.append(layer)
    return hidden


def restore_layers(layers):
    for layer in layers:
        layer.visible = True


def export_artboard(artboard, name, width, height, origin_left, origin_top, output_dir, index, skipped):
    layers = extract_layers(artboard, origin_left, origin_top)

    hidden = hide_dynamic_layers(artboard)
    try:
        image = artboard.composite()
    finally:
        restore_layers(hidden)

    if image is None:
        skipped.append(f"{name}: تعذر تجهيز صورة الخلفية")
        return None

    background_file = f"background_{index}.png"
    image.convert("RGBA").save(Path(output_dir) / background_file)

    return {
        "name": name,
        "width": int(width),
        "height": int(height),
        "background_file": background_file,
        "layers": layers,
    }


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", required=True)
    parser.add_argument("--output-dir", required=True)
    parser.add_argument("--document-name", default="template")
    args = parser.parse_args()

    output_dir = Path(args.output_dir)
    output_dir.mkdir(parents=True, exist_ok=True)

    psd = PSDImage.open(args.input)

    artboards = [layer for layer in psd if isinstance(layer, Artboard) and layer.is_visible()]

    templates = []
    skipped = []

    if artboards:
        for index, artboard in enumerate(artboards, start=1):
            name = artboard.name or f"{args.document_name} {index}"
            template = export_artboard(
                artboard, name, artboard.width, artboard.height,
                artboard.left, artboard.top, output_dir, index, skipped,
            )
            if template:
                templates.append(template)
    else:
        template = export_artboard(
            psd, args.document_name, psd.width, psd.height,
            0, 0, output_dir, 1, skipped,
        )
        if template:
            templates.append(template)

    if not templates:
        skipped.append("لم يتم استخراج أي قالب من ملف PSD")

    report = {
        "name": args.document_name,
        "templates": templates,
        "skipped": skipped,
    }

    with open(output_dir / "report.json", "w", encoding="utf-8") as f:
        json.dump(report, f, ensure_ascii=False)


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(f"خطأ: {exc}", file=sys.stderr)
        sys.exit(1)
