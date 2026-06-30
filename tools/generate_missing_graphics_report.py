#!/usr/bin/env python3
"""Regenerate the root missing-graphics report from the current catalogs/assets."""

from __future__ import annotations

import re
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
REPORT_PATH = ROOT / "missing_graphics_report.md"
ITEM_EXTENSIONS = ("png", "webp", "jpg", "jpeg", "gif", "svg")
CREATURE_EXTENSIONS = ("webp", "png", "jpg", "jpeg", "gif")
DEFAULT_COLORS = ("purple", "red", "blue", "green", "yellow")


def sql_string(value: str) -> str:
    return (
        value.replace("\\'", "'")
        .replace('\\"', '"')
        .replace("''", "'")
        .replace("\\\\", "\\")
    )


def load_insert_names(path: Path, table: str) -> list[tuple[int, str]]:
    marker = re.compile(rf"^INSERT INTO `{re.escape(table)}` .* VALUES$")
    row = re.compile(r"^\s*\(\s*(\d+)\s*,\s*'((?:\\.|''|[^'])*)'")
    collecting = False
    values: list[tuple[int, str]] = []

    for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
        if not collecting:
            collecting = bool(marker.match(line))
            continue

        match = row.match(line)
        if match:
            values.append((int(match.group(1)), sql_string(match.group(2))))
        if line.rstrip().endswith(";"):
            break

    if not values:
        raise RuntimeError(f"No {table} rows found in {path.relative_to(ROOT)}")
    return values


def item_variants(name: str) -> list[str]:
    candidates = [name, name.lower(), name.replace("'", ""), name.replace("'", "").lower()]
    variants: list[str] = []
    for candidate in candidates:
        candidate = candidate.strip()
        if candidate:
            variants.extend((candidate, candidate.replace(" ", "_"), candidate.replace(" ", "-")))
    variants.append(re.sub(r"[^a-z0-9]+", "-", name.lower()))
    return list(dict.fromkeys(value for value in variants if value))


def item_has_graphic(name: str, item_dir: Path) -> bool:
    return any(
        (item_dir / f"{variant}.{extension}").is_file()
        for variant in item_variants(name)
        for extension in ITEM_EXTENSIONS
    )


def creature_slug(name: str) -> str:
    return re.sub(r"^_+|_+$", "", re.sub(r"[^a-z0-9]+", "_", name.lower()))


def preserved_art_descriptions() -> dict[int, str]:
    if not REPORT_PATH.is_file():
        return {}
    text = REPORT_PATH.read_text(encoding="utf-8", errors="replace")
    if "## Creature Art Descriptions" not in text:
        return {}
    section = text.split("## Creature Art Descriptions", 1)[1]
    if "\n## " in section:
        section = section.split("\n## ", 1)[0]
    descriptions: dict[int, str] = {}
    for line in section.splitlines():
        match = re.match(r"^- (\d+) - .+", line)
        if match:
            descriptions[int(match.group(1))] = line
    return descriptions


def build_report() -> tuple[str, dict[str, int]]:
    items = load_insert_names(ROOT / "sql" / "items.sql", "items")
    creatures = load_insert_names(ROOT / "database.sql", "pet_species")
    item_dir = ROOT / "images" / "items"
    creature_dir = ROOT / "images" / "creatures"
    creature_files = {
        file.name.lower()
        for file in creature_dir.iterdir()
        if file.is_file()
    }

    missing_items = [(item_id, name) for item_id, name in items if not item_has_graphic(name, item_dir)]
    missing_creatures: list[tuple[int, str, str]] = []
    missing_defaults: dict[str, list[tuple[int, str, str]]] = {color: [] for color in DEFAULT_COLORS}

    for species_id, name in creatures:
        slug = creature_slug(name)
        if not any(filename.startswith(slug) for filename in creature_files):
            missing_creatures.append((species_id, name, slug))
        for color in DEFAULT_COLORS:
            basename = f"{slug}_f_{color}"
            if not any(f"{basename}.{extension}" in creature_files for extension in CREATURE_EXTENSIONS):
                missing_defaults[color].append((species_id, name, basename))

    missing_default_total = sum(len(rows) for rows in missing_defaults.values())
    lines = [
        "# Missing Graphics Report",
        "",
        "## Sources And Rules",
        "",
        "- Items source: `sql/items.sql`",
        "- Item graphics source: `images/items`",
        "- Item matching rule: mirrors `shop_find_item_image()` in `lib/shops.php`.",
        "- Creature source: `database.sql` table `pet_species`.",
        "- Creature graphics source: direct files in `images/creatures`.",
        "- A creature is missing all graphics when no direct file begins with its normalized species slug.",
        "- Default-color matching requires an exact `<species>_f_<color>` basename in a supported image extension; alternate styles such as `tblue`, `blue2`, or `realistic` do not satisfy a default color.",
        "",
        "## Summary",
        "",
        f"- Items missing resolvable graphics: {len(missing_items)} of {len(items)}",
        f"- Creatures missing any graphics: {len(missing_creatures)} of {len(creatures)}",
        f"- Missing default-color creature graphics: {missing_default_total} of {len(creatures) * len(DEFAULT_COLORS)}",
    ]
    for color in DEFAULT_COLORS:
        lines.append(f"  - {color.title()}: {len(missing_defaults[color])} of {len(creatures)}")

    lines.extend(["", "## Items Missing Graphics", ""])
    if missing_items:
        lines.extend(f"- {item_id} - {name}" for item_id, name in missing_items)
    else:
        lines.append("- None")

    lines.extend(["", "## Creatures Missing Any Graphics", ""])
    if missing_creatures:
        lines.extend(f"- {species_id} - {name} (`{slug}`)" for species_id, name, slug in missing_creatures)
    else:
        lines.append("- None")

    lines.extend(["", "## Missing Default-Color Creature Graphics", ""])
    lines.append("Each entry names the exact missing basename; any supported image extension can satisfy it.")
    for color in DEFAULT_COLORS:
        rows = missing_defaults[color]
        lines.extend(["", f"### {color.title()} ({len(rows)})", ""])
        if rows:
            lines.extend(
                f"- {species_id} - {name} (`{basename}.*`)"
                for species_id, name, basename in rows
            )
        else:
            lines.append("- None")

    descriptions = preserved_art_descriptions()
    retained = [descriptions[species_id] for species_id, _, _ in missing_creatures if species_id in descriptions]
    if retained:
        lines.extend([
            "",
            "## Creature Art Descriptions",
            "",
            "These existing art-direction notes are retained only for creatures still missing all graphics.",
            "",
            *retained,
        ])

    counts = {
        "items": len(missing_items),
        "creatures": len(missing_creatures),
        "defaults": missing_default_total,
    }
    return "\n".join(lines) + "\n", counts


def main() -> None:
    report, counts = build_report()
    REPORT_PATH.write_text(report, encoding="utf-8", newline="\n")
    print(
        "Updated missing_graphics_report.md: "
        f"{counts['items']} missing items, "
        f"{counts['creatures']} creatures without graphics, "
        f"{counts['defaults']} missing default-color variants."
    )


if __name__ == "__main__":
    main()
