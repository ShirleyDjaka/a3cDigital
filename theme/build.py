#!/usr/bin/env python3
"""Assemble la feuille de style du thème et produit le zip installable.

    python3 theme/build.py

- assets/css/main.css = polices locales + styles des maquettes (source de vérité du design)
  + compléments WordPress (theme/a3c/assets/src/wordpress.css)
- dist/a3c.zip : thème prêt à téléverser.
"""
import re
import zipfile
from pathlib import Path

RACINE = Path(__file__).resolve().parent.parent
THEME = RACINE / "theme" / "a3c"


def main():
    base = (RACINE / "maquettes" / "styles.css").read_text(encoding="utf-8")
    # Le bandeau « maquette » n'existe que dans les maquettes.
    base = re.sub(r"/\* Bandeau de maquette \*/\n\.bandeau-maquette \{.*?\}\n", "", base, flags=re.S)
    base = re.sub(r"\.bandeau-maquette \{[^}]*\}\n", "", base)
    parties = [
        (THEME / "assets" / "css" / "fonts.css").read_text(encoding="utf-8"),
        base,
        (THEME / "assets" / "src" / "wordpress.css").read_text(encoding="utf-8"),
    ]
    (THEME / "assets" / "css" / "main.css").write_text("\n".join(parties), encoding="utf-8")

    dist = RACINE / "dist"
    dist.mkdir(exist_ok=True)
    with zipfile.ZipFile(dist / "a3c.zip", "w", zipfile.ZIP_DEFLATED) as z:
        for f in sorted(THEME.rglob("*")):
            if f.is_file() and "src" not in f.relative_to(THEME).parts:
                z.write(f, Path("a3c") / f.relative_to(THEME))
    print("main.css et dist/a3c.zip générés")


if __name__ == "__main__":
    main()
