from __future__ import annotations

import colorsys
import json
import math
import re
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageFont, ImageOps


ROOT = Path(__file__).resolve().parents[1]
DUMP_PATH = ROOT / "sql" / "20260606-dump.sql"
BASE_PATH = ROOT / "images" / "creatures" / "adaro_f_blue.webp"
OUT_DIR = ROOT / "images" / "outputstablediff" / "adaro_concepts"


def rgb(value: str) -> tuple[int, int, int]:
    value = value.lstrip("#")
    return tuple(int(value[i : i + 2], 16) for i in (0, 2, 4))


def mix(a: tuple[int, int, int], b: tuple[int, int, int], t: float) -> tuple[int, int, int]:
    return tuple(int(round(a[i] * (1 - t) + b[i] * t)) for i in range(3))


def slugify(name: str) -> str:
    return re.sub(r"[^a-z0-9]+", "_", name.lower()).strip("_")


PALETTES: dict[str, dict[str, object]] = {
    "16-Bit": {"main": "#4865d8", "accent": "#38d6ff", "effect": "pixel16"},
    "8-Bit": {"main": "#2364d2", "accent": "#f5cc2f", "effect": "pixel8"},
    "Agent": {"main": "#22252b", "accent": "#f4f4f4", "skin": "#d4d7dc"},
    "Baby": {"main": "#f5a8c2", "accent": "#a7d8ff", "skin": "#ffd7e4"},
    "Bikini": {"main": "#1aa6b8", "accent": "#ff6e9d", "skin": "#c6f0f2"},
    "Black": {"main": "#242424", "accent": "#666666", "skin": "#d7d7d7"},
    "Blackwhite": {"main": "#f2f2f2", "accent": "#1c1c1c", "skin": "#ededed", "effect": "checker"},
    "Blue": {"main": "#5d91bd", "accent": "#4fa66c", "mode": "base"},
    "Bordeaux": {"main": "#7a1937", "accent": "#d9a0b1", "skin": "#e9b7c5"},
    "Brown": {"main": "#7b5132", "accent": "#caa06e", "skin": "#dfc4a8"},
    "Burlap": {"main": "#b59662", "accent": "#6d5334", "skin": "#d8c4a0", "effect": "speckle"},
    "Candy": {"main": "#ff4fb3", "accent": "#6ff4ff", "skin": "#ffd0eb", "effect": "stripe"},
    "Checkered": {"main": "#f7f7f7", "accent": "#202020", "skin": "#efefef", "effect": "checker"},
    "Cheese": {"main": "#ffd13d", "accent": "#e88725", "skin": "#ffe9a3", "effect": "spots"},
    "Chocolate": {"main": "#5f3422", "accent": "#c68145", "skin": "#d7a77e"},
    "Christmas": {"main": "#d3292f", "accent": "#168449", "skin": "#ffd5d8", "effect": "split"},
    "Clay": {"main": "#a56f4c", "accent": "#d9a06d", "skin": "#dfb18d"},
    "Cloud": {"main": "#c8ecff", "accent": "#ffffff", "skin": "#edf9ff", "effect": "cloud"},
    "Cookie": {"main": "#c98a45", "accent": "#5d321e", "skin": "#ecc99e", "effect": "spots"},
    "Coral": {"main": "#ff705f", "accent": "#ffd16f", "skin": "#ffc7bf"},
    "Cowboy": {"main": "#9a6535", "accent": "#d5a15f", "skin": "#e5bb8b"},
    "Creepy": {"main": "#5f2975", "accent": "#9bd44a", "skin": "#d7b6e0"},
    "Cyan": {"main": "#27d8e8", "accent": "#0b8292", "skin": "#c4f9ff"},
    "Desert": {"main": "#d3a15a", "accent": "#926b36", "skin": "#edd4a9"},
    "Eww": {"main": "#7ab33b", "accent": "#b8d14b", "skin": "#d9ecaf", "effect": "speckle"},
    "Fabric": {"main": "#6f76b8", "accent": "#c9a8e8", "skin": "#d7d9f2", "effect": "stripe"},
    "Fairy": {"main": "#d96fff", "accent": "#83ffc9", "skin": "#f2d1ff"},
    "Fantasy": {"main": "#7251e8", "accent": "#ffcc52", "skin": "#d7cbff"},
    "Feral": {"main": "#4d7550", "accent": "#b69c5c", "skin": "#b8d6b5"},
    "Festival": {"main": "#ff5f5f", "accent": "#f7c948", "skin": "#ffd3d3", "effect": "stripe"},
    "Fire": {"main": "#f24b23", "accent": "#ffd23f", "skin": "#ffc7aa", "effect": "flame"},
    "Forest Camouflage": {"main": "#49672d", "accent": "#b09a57", "skin": "#c5d0a3", "effect": "camo"},
    "Funny": {"main": "#ff9c2f", "accent": "#5ed4ff", "skin": "#ffd9a6"},
    "Giraffe": {"main": "#e5b84d", "accent": "#7d4924", "skin": "#f0d895", "effect": "spots"},
    "Gold": {"main": "#d6a62c", "accent": "#fff1a8", "skin": "#ffe8a3"},
    "Granite": {"main": "#777b83", "accent": "#c6c9cf", "skin": "#d8dade", "effect": "speckle"},
    "Green": {"main": "#4fa66c", "accent": "#8ed66e", "skin": "#c4eecf"},
    "Grey": {"main": "#8b929a", "accent": "#d1d6db", "skin": "#d8dde2"},
    "Gummy": {"main": "#37d6a6", "accent": "#ff7abf", "skin": "#c6f6e9"},
    "Gyaru": {"main": "#d89445", "accent": "#ffe680", "skin": "#f1c184"},
    "Hero": {"main": "#2864c9", "accent": "#e83e43", "skin": "#c5d8ff"},
    "Holiday": {"main": "#2a9d8f", "accent": "#f4a261", "skin": "#c6eee9"},
    "Honey": {"main": "#f1b82d", "accent": "#7f5118", "skin": "#ffe2a0"},
    "Ice": {"main": "#81d7ff", "accent": "#e7fbff", "skin": "#d9f6ff"},
    "Ice Cream": {"main": "#ffb7d5", "accent": "#fff1b8", "skin": "#ffe3ee"},
    "Inverted": {"main": "#ff9b3d", "accent": "#b85cff", "mode": "invert"},
    "Island": {"main": "#2fb5a1", "accent": "#f2ca63", "skin": "#bff0e9"},
    "Jello": {"main": "#4fd3ff", "accent": "#ff5fb7", "skin": "#c8f4ff"},
    "Leather": {"main": "#6d4326", "accent": "#b47c4a", "skin": "#d0a47e"},
    "Maid": {"main": "#2e3238", "accent": "#f2f2f2", "skin": "#d7dbe0"},
    "Marble": {"main": "#e2e4e8", "accent": "#8a92a0", "skin": "#f3f4f6", "effect": "marble"},
    "MGE": {"main": "#b050a8", "accent": "#ffd26e", "skin": "#e8bee4"},
    "Milk": {"main": "#f5f4e8", "accent": "#9bd7ff", "skin": "#fbf8ee"},
    "Moss": {"main": "#667c3a", "accent": "#a7b66b", "skin": "#cdd7b1", "effect": "speckle"},
    "Muffin": {"main": "#b27646", "accent": "#e7b57a", "skin": "#e7c09a"},
    "Neon": {"main": "#00f5a0", "accent": "#ff2bd6", "skin": "#b8ffe8"},
    "Old": {"main": "#8c7a62", "accent": "#c9b99d", "skin": "#d8cab6"},
    "Orange": {"main": "#ee7d2f", "accent": "#ffc15a", "skin": "#ffd0aa"},
    "Origami": {"main": "#ee6f8f", "accent": "#f7d7e0", "skin": "#ffd6df", "effect": "poster"},
    "Pencil": {"main": "#8c8c8c", "accent": "#e7e7e7", "skin": "#e0e0e0", "effect": "pencil"},
    "Pink": {"main": "#f06aa6", "accent": "#ffaed0", "skin": "#ffd3e6"},
    "Pirate": {"main": "#242832", "accent": "#b91f2d", "skin": "#d6d8df"},
    "Plush": {"main": "#86a6d9", "accent": "#f0c1d6", "skin": "#d5e1f5", "effect": "soft"},
    "Polygon": {"main": "#5d8fd8", "accent": "#74d19e", "skin": "#cae0ff", "effect": "poster"},
    "Purple": {"main": "#8e5bd6", "accent": "#c191f0", "skin": "#dfcff7"},
    "Python": {"main": "#4d6d39", "accent": "#e0c16a", "skin": "#c8d4b7", "effect": "scales"},
    "Quint": {"main": "#5b5bd6", "accent": "#f4d35e", "skin": "#ceceff"},
    "Quintessential Quality": {"main": "#7b60d9", "accent": "#f9d96a", "skin": "#ddd2ff"},
    "Rainbow": {"main": "#ff4f8b", "accent": "#43ddff", "skin": "#ffd0df", "effect": "rainbow"},
    "Realistic": {"main": "#5f8b96", "accent": "#6ca06c", "skin": "#bfdde2"},
    "Red": {"main": "#d94a35", "accent": "#f05d5e", "skin": "#ffb8bd"},
    "Regal": {"main": "#5631a4", "accent": "#d8aa2e", "skin": "#d6c4f0"},
    "Relic": {"main": "#6d8c7b", "accent": "#c49d51", "skin": "#cddbd5"},
    "Scary": {"main": "#2d2938", "accent": "#cc273f", "skin": "#c6c1d0"},
    "Shadow": {"main": "#30384a", "accent": "#7e8db8", "skin": "#bac0d0"},
    "Silver": {"main": "#c4c9cf", "accent": "#6d747d", "skin": "#eef1f4"},
    "Skyblue": {"main": "#61c6ff", "accent": "#d8f3ff", "skin": "#d3f2ff"},
    "Snow": {"main": "#e9f7ff", "accent": "#9ed2ff", "skin": "#f6fcff"},
    "Spaghetti": {"main": "#d9a441", "accent": "#b6362a", "skin": "#f0d39a", "effect": "stripe"},
    "Split": {"main": "#5d91bd", "accent": "#d94a35", "skin": "#d6e5f0", "effect": "split"},
    "Sponge": {"main": "#f1d13d", "accent": "#d89b2d", "skin": "#fff0a2", "effect": "spots"},
    "Strawberry": {"main": "#e93d68", "accent": "#6ebd45", "skin": "#ffc3d0", "effect": "spots"},
    "Sunburnt": {"main": "#bc513c", "accent": "#f08a57", "skin": "#e9a28e"},
    "Sunset": {"main": "#ff7043", "accent": "#6f56d9", "skin": "#ffc7b5", "effect": "split"},
    "Synthwave": {"main": "#ff38d1", "accent": "#19d7ff", "skin": "#ffd0f5", "effect": "split"},
    "Thunder": {"main": "#6d6fe5", "accent": "#f8e84c", "skin": "#d7d8ff", "effect": "stripe"},
    "Toon": {"main": "#38a2ff", "accent": "#ffcf33", "skin": "#cfeaff", "effect": "poster"},
    "Topless": {"main": "#d88773", "accent": "#7db4d8", "skin": "#f0b9a9"},
    "Toy": {"main": "#58b6ff", "accent": "#ffcc4d", "skin": "#cae9ff", "effect": "soft"},
    "Training": {"main": "#4277b8", "accent": "#f28c28", "skin": "#c9dcf0"},
    "Transparent": {"main": "#bcefff", "accent": "#ffffff", "skin": "#e7fbff"},
    "Valentine": {"main": "#e94b7a", "accent": "#ffd1dc", "skin": "#ffcbd9"},
    "Voxel": {"main": "#3b83d8", "accent": "#55d38b", "skin": "#c9e0ff", "effect": "pixel8"},
    "Water": {"main": "#2fa6d8", "accent": "#9cecff", "skin": "#c9f3ff"},
    "White": {"main": "#eceff2", "accent": "#9ab5cf", "skin": "#f7f8fa"},
    "Wizard": {"main": "#4433a6", "accent": "#f7c948", "skin": "#cfc8f0"},
    "Yellow": {"main": "#f1ce3f", "accent": "#f3a13a", "skin": "#ffedac"},
    "Zebra": {"main": "#f2f2f2", "accent": "#171717", "skin": "#f6f6f6", "effect": "zebra"},
}


def parse_pet_colors() -> list[dict[str, object]]:
    text = DUMP_PATH.read_text(encoding="utf-8", errors="replace")
    match = re.search(
        r"INSERT INTO `pet_colors`\s*\(`color_id`, `color_name`\)\s*VALUES\s*(.*?);",
        text,
        re.S,
    )
    if not match:
        raise RuntimeError(f"Could not find pet_colors INSERT in {DUMP_PATH}")
    rows = re.findall(r"\((\d+),\s*'((?:[^'\\]|\\.)*)'\)", match.group(1))
    colors = [
        {"color_id": int(color_id), "color_name": name.replace("\\'", "'")}
        for color_id, name in rows
    ]
    return sorted(colors, key=lambda row: int(row["color_id"]))


def make_masks(base_rgba: np.ndarray) -> dict[str, np.ndarray]:
    rgb_arr = base_rgba[..., :3].astype(np.float32)
    alpha = base_rgba[..., 3]
    r = rgb_arr[..., 0]
    g = rgb_arr[..., 1]
    b = rgb_arr[..., 2]
    maxc = np.max(rgb_arr, axis=2)
    minc = np.min(rgb_arr, axis=2)
    sat = np.divide(maxc - minc, maxc, out=np.zeros_like(maxc), where=maxc > 0)
    luma = 0.2126 * r + 0.7152 * g + 0.0722 * b
    opaque = alpha > 8

    cyanish = (b > r + 8) & (g > r + 6) & (np.abs(b - g) < 95)
    blueish = (b > r + 16) & (b >= g - 30)
    greenish = (g > r + 12) & (g >= b - 85)

    skin = opaque & (luma > 105) & cyanish & (sat < 0.55)
    clothing = opaque & (luma > 36) & greenish & (sat > 0.12) & ~skin
    hair = opaque & (luma > 34) & ((blueish & (sat > 0.08)) | (cyanish & (sat >= 0.24))) & ~skin & ~clothing
    primary = skin | clothing | hair
    subject = opaque
    return {
        "skin": skin,
        "clothing": clothing,
        "hair": hair,
        "primary": primary,
        "subject": subject,
        "luma": luma,
    }


def shade(luma: np.ndarray, color: tuple[int, int, int]) -> np.ndarray:
    main = np.array(color, dtype=np.float32)
    dark = np.array(mix(color, (0, 0, 0), 0.62), dtype=np.float32)
    light = np.array(mix(color, (255, 255, 255), 0.62), dtype=np.float32)
    t = np.clip(luma / 255.0, 0.0, 1.0)[..., None]
    mid = 0.58
    lower = dark + (main - dark) * np.clip(t / mid, 0.0, 1.0)
    upper = main + (light - main) * np.clip((t - mid) / (1.0 - mid), 0.0, 1.0)
    return np.where(t <= mid, lower, upper)


def hsv_to_rgb_np(h: np.ndarray, s: float, v: np.ndarray) -> np.ndarray:
    h = h % 1.0
    i = np.floor(h * 6.0).astype(np.int32)
    f = h * 6.0 - i
    p = v * (1.0 - s)
    q = v * (1.0 - f * s)
    t = v * (1.0 - (1.0 - f) * s)
    mod = i % 6
    out = np.zeros(h.shape + (3,), dtype=np.float32)
    out[mod == 0] = np.stack([v, t, p], axis=-1)[mod == 0]
    out[mod == 1] = np.stack([q, v, p], axis=-1)[mod == 1]
    out[mod == 2] = np.stack([p, v, t], axis=-1)[mod == 2]
    out[mod == 3] = np.stack([p, q, v], axis=-1)[mod == 3]
    out[mod == 4] = np.stack([t, p, v], axis=-1)[mod == 4]
    out[mod == 5] = np.stack([v, p, q], axis=-1)[mod == 5]
    return np.clip(out * 255.0, 0, 255)


def chroma_for(main: tuple[int, int, int]) -> tuple[int, int, int]:
    h, s, v = colorsys.rgb_to_hsv(main[0] / 255.0, main[1] / 255.0, main[2] / 255.0)
    if s < 0.18:
        if v < 0.45:
            return (0, 255, 80)
        if v > 0.78:
            return (255, 0, 190)
        return (0, 210, 255)
    r, g, b = colorsys.hsv_to_rgb((h + 0.5) % 1.0, 1.0, 1.0)
    return (int(round(r * 255)), int(round(g * 255)), int(round(b * 255)))


def fallback_palette(name: str) -> dict[str, object]:
    hue = (sum((i + 1) * ord(char) for i, char in enumerate(name)) % 360) / 360.0
    r, g, b = colorsys.hsv_to_rgb(hue, 0.64, 0.84)
    main = (int(r * 255), int(g * 255), int(b * 255))
    return {
        "main": "#%02x%02x%02x" % main,
        "accent": "#%02x%02x%02x" % mix(main, (255, 255, 255), 0.42),
        "skin": "#%02x%02x%02x" % mix(main, (255, 255, 255), 0.65),
    }


def apply_pattern(
    work: np.ndarray,
    masks: dict[str, np.ndarray],
    palette: dict[str, object],
    main: tuple[int, int, int],
    accent: tuple[int, int, int],
    effect: str | None,
) -> np.ndarray:
    if not effect:
        return work

    h, w = work.shape[:2]
    yy, xx = np.indices((h, w))
    luma = masks["luma"]
    primary = masks["primary"]
    accent_shade = shade(luma, accent)

    if effect == "checker":
        pattern = ((xx // 44 + yy // 44) % 2) == 0
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "stripe":
        pattern = ((xx + yy) // 34 % 2) == 0
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "split":
        pattern = xx > (w // 2)
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "spots":
        pattern = ((np.sin(xx * 0.055) + np.cos(yy * 0.071) + np.sin((xx + yy) * 0.031)) > 1.15)
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "speckle":
        noise = ((xx * 37 + yy * 53 + (xx // 13) * 19) % 101) < 18
        work[primary & noise] = accent_shade[primary & noise]
    elif effect == "camo":
        patch = ((xx // 58 * 17 + yy // 47 * 31 + xx // 113) % 5)
        alt1 = shade(luma, accent)
        alt2 = shade(luma, mix(main, (0, 0, 0), 0.38))
        work[primary & (patch == 1)] = alt1[primary & (patch == 1)]
        work[primary & (patch == 3)] = alt2[primary & (patch == 3)]
    elif effect == "zebra":
        pattern = np.sin((xx * 0.055) + (yy * 0.095)) > 0.25
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "scales":
        pattern = (((xx // 24 + yy // 18) % 2) == 0) & (((xx % 24) < 8) | ((yy % 18) < 5))
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "rainbow":
        value = np.clip((luma / 255.0) * 0.72 + 0.28, 0, 1)
        rainbow = hsv_to_rgb_np((xx / w) * 0.92 + (yy / h) * 0.18, 0.78, value)
        work[primary] = rainbow[primary]
    elif effect == "cloud":
        pattern = (np.sin(xx * 0.025) + np.sin(yy * 0.03) + np.cos((xx + yy) * 0.018)) > 1.2
        work[primary & pattern] = accent_shade[primary & pattern]
    elif effect == "flame":
        gradient = np.clip((h - yy) / h, 0, 1)[..., None]
        flame = shade(luma, accent) * (1 - gradient) + shade(luma, main) * gradient
        work[primary] = flame[primary]
    elif effect == "marble":
        veins = np.sin(xx * 0.035 + np.sin(yy * 0.035) * 2.5) > 0.82
        work[primary & veins] = accent_shade[primary & veins]
    return work


def postprocess_subject(subject: Image.Image, effect: str | None) -> Image.Image:
    if effect == "pixel8":
        small = subject.resize((128, 128), Image.Resampling.NEAREST)
        return small.resize(subject.size, Image.Resampling.NEAREST)
    if effect == "pixel16":
        small = subject.resize((256, 256), Image.Resampling.NEAREST)
        return small.resize(subject.size, Image.Resampling.NEAREST)
    if effect == "poster":
        rgb_img = ImageOps.posterize(subject.convert("RGB"), 4)
        rgb_img.putalpha(subject.getchannel("A"))
        return rgb_img
    if effect == "pencil":
        gray = ImageOps.grayscale(subject.convert("RGB"))
        rgb_img = ImageOps.colorize(gray, black="#363636", white="#f4f4f4")
        rgb_img.putalpha(subject.getchannel("A"))
        return rgb_img
    if effect == "soft":
        return subject.filter(ImageFilter.SMOOTH_MORE)
    return subject


def recolor_subject(base_rgba: np.ndarray, masks: dict[str, np.ndarray], color_name: str) -> tuple[Image.Image, tuple[int, int, int]]:
    palette = dict(PALETTES.get(color_name, fallback_palette(color_name)))
    main = rgb(str(palette["main"]))
    accent = rgb(str(palette.get("accent", palette["main"])))
    skin = rgb(str(palette.get("skin", "#%02x%02x%02x" % mix(main, (255, 255, 255), 0.63))))
    effect = palette.get("effect")
    mode = palette.get("mode")

    if mode == "base":
        subject = Image.fromarray(base_rgba, "RGBA")
        return subject, main
    if mode == "invert":
        inverted = base_rgba.copy()
        subject_mask = masks["subject"]
        inverted[..., :3][subject_mask] = 255 - inverted[..., :3][subject_mask]
        return Image.fromarray(inverted, "RGBA"), main

    work = base_rgba[..., :3].astype(np.float32).copy()
    luma = masks["luma"]
    work[masks["hair"]] = shade(luma, main)[masks["hair"]]
    work[masks["skin"]] = shade(luma, skin)[masks["skin"]]
    work[masks["clothing"]] = shade(luma, accent)[masks["clothing"]]
    work = apply_pattern(work, masks, palette, main, accent, str(effect) if effect else None)
    work = np.clip(work, 0, 255).astype(np.uint8)

    subject_rgba = np.dstack([work, base_rgba[..., 3]])
    subject = Image.fromarray(subject_rgba, "RGBA")
    subject = postprocess_subject(subject, str(effect) if effect else None)
    return subject, main


def composite_on_key(subject: Image.Image, key: tuple[int, int, int]) -> Image.Image:
    subject_rgba = np.array(subject.convert("RGBA"), dtype=np.float32)
    alpha = subject_rgba[..., 3:4] / 255.0
    key_arr = np.array(key, dtype=np.float32)
    comp = subject_rgba[..., :3] * alpha + key_arr * (1.0 - alpha)
    return Image.fromarray(np.clip(comp, 0, 255).astype(np.uint8), "RGB")


def load_font(size: int) -> ImageFont.ImageFont:
    for candidate in ("arial.ttf", "segoeui.ttf"):
        try:
            return ImageFont.truetype(candidate, size)
        except OSError:
            continue
    return ImageFont.load_default()


def create_contact_sheet(records: list[dict[str, object]]) -> Path:
    cols = 8
    cell_w = 178
    cell_h = 206
    thumb = 150
    rows = math.ceil(len(records) / cols)
    sheet = Image.new("RGB", (cols * cell_w, rows * cell_h), "#f4f5f7")
    draw = ImageDraw.Draw(sheet)
    font = load_font(15)
    small_font = load_font(12)

    for index, record in enumerate(records):
        x = (index % cols) * cell_w
        y = (index // cols) * cell_h
        path = ROOT / str(record["path"])
        img = Image.open(path).convert("RGB")
        img.thumbnail((thumb, thumb), Image.Resampling.LANCZOS)
        px = x + (cell_w - img.width) // 2
        py = y + 8
        sheet.paste(img, (px, py))
        name = str(record["color_name"])
        slug = str(record["slug"])
        draw.text((x + 8, y + 162), name[:22], fill="#1f2933", font=font)
        draw.text((x + 8, y + 182), slug[:24], fill="#5f6b7a", font=small_font)

    sheet_path = OUT_DIR / "adaro_color_concepts_sheet.png"
    sheet.save(sheet_path, optimize=True)
    return sheet_path


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    base = Image.open(BASE_PATH).convert("RGBA")
    base_rgba = np.array(base)
    masks = make_masks(base_rgba)
    colors = parse_pet_colors()
    records: list[dict[str, object]] = []

    for row in colors:
        color_name = str(row["color_name"])
        slug = slugify(color_name)
        subject, main_rgb = recolor_subject(base_rgba, masks, color_name)
        key_rgb = chroma_for(main_rgb)
        concept = composite_on_key(subject, key_rgb)
        out_path = OUT_DIR / f"adaro_f_{slug}.png"
        concept.save(out_path, optimize=True)
        records.append(
            {
                "color_id": int(row["color_id"]),
                "color_name": color_name,
                "slug": slug,
                "file": out_path.name,
                "path": out_path.relative_to(ROOT).as_posix(),
                "main_rgb": main_rgb,
                "chroma_key_rgb": key_rgb,
                "chroma_key_hex": "#%02x%02x%02x" % key_rgb,
            }
        )

    manifest_path = OUT_DIR / "manifest.json"
    manifest_path.write_text(json.dumps(records, indent=2), encoding="utf-8")
    sheet_path = create_contact_sheet(records)

    print(f"Generated {len(records)} Adaro concepts")
    print(f"Output: {OUT_DIR}")
    print(f"Manifest: {manifest_path}")
    print(f"Contact sheet: {sheet_path}")


if __name__ == "__main__":
    main()
