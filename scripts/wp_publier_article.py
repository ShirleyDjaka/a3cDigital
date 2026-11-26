#!/usr/bin/env python3
"""Crée ou met à jour un article WordPress à partir d'un fichier HTML (blocs Gutenberg).

Usage : python3 scripts/wp_publier_article.py contenus/articles/mon-article.html --site https://staging6.a3cdigital.com

En-tête du fichier :
    <!-- title: Titre affiché (H1) -->
    <!-- slug: adresse-de-l-article -->
    <!-- categories: sante-communication-deontologie -->   (slugs séparés par des virgules)
    <!-- tags: partenariat -->                              (facultatif)
    <!-- status: publish -->                                (publish, draft ou future)
    <!-- date_gmt: 2026-12-09T08:00:00 -->                  (obligatoire si status = future, heure UTC)
    <!-- excerpt: Chapô affiché sous le titre -->
    <!-- seo_title: Title Google (60 caractères max) -->
    <!-- seo_description: Meta description (140 à 155 caractères) -->

Authentification : injectée par le proxy de l'environnement Claude Code.
"""
import json
import re
import sys
import urllib.parse
import urllib.request
from pathlib import Path

SITE = sys.argv[sys.argv.index("--site") + 1].rstrip("/") if "--site" in sys.argv else None
UA = "Mozilla/5.0 (a3cdigital-publisher)"


def call(method, path, data=None):
    req = urllib.request.Request(
        f"{SITE}/wp-json/{path}",
        data=json.dumps(data).encode() if data is not None else None,
        method=method,
        headers={"Content-Type": "application/json", "User-Agent": UA},
    )
    with urllib.request.urlopen(req, timeout=60) as r:
        return json.load(r)


def ids_termes(taxonomie, slugs):
    ids = []
    for slug in [s.strip() for s in slugs.split(",") if s.strip()]:
        res = call("GET", f"wp/v2/{taxonomie}?slug={urllib.parse.quote(slug)}&_fields=id")
        if res:
            ids.append(res[0]["id"])
        elif taxonomie == "tags":
            ids.append(call("POST", "wp/v2/tags", {"name": slug, "slug": slug})["id"])
        else:
            sys.exit(f"Catégorie introuvable : {slug}")
    return ids


def main(path):
    if not SITE:
        sys.exit("Préciser --site (la copie de test fait foi : https://staging6.a3cdigital.com)")
    raw = Path(path).read_text(encoding="utf-8")
    meta = dict(re.findall(r"<!--\s*(\w+):\s*(.*?)\s*-->", raw.split("\n\n", 1)[0]))
    contenu = re.sub(r"^(<!--\s*\w+:.*?-->\s*)+", "", raw, flags=re.S).strip()
    if path.endswith(".md"):
        sys.path.insert(0, str(Path(__file__).parent))
        from md_vers_blocs import convertir
        contenu = convertir(contenu)

    # Contrôles éditoriaux (règles du CLAUDE.md)
    alertes = []
    if len(meta.get("seo_title", "")) > 60:
        alertes.append(f"title SEO de {len(meta['seo_title'])} caractères (60 max)")
    if not 140 <= len(meta.get("seo_description", "")) <= 155:
        alertes.append(f"meta description de {len(meta.get('seo_description', ''))} caractères (140 à 155)")
    if "essentiel-titre" not in contenu:
        alertes.append("pas d'encadré « L'essentiel en 30 secondes »")
    if contenu.count("<h2") < 3:
        alertes.append("moins de 3 intertitres H2")
    if alertes and "--forcer" not in sys.argv:
        sys.exit("Publication refusée : " + " ; ".join(alertes))

    donnees = {
        "title": meta["title"],
        "slug": meta["slug"],
        "content": contenu,
        "excerpt": meta.get("excerpt", ""),
        "status": meta.get("status", "draft"),
        "categories": ids_termes("categories", meta.get("categories", "")),
        "tags": ids_termes("tags", meta.get("tags", "")),
        "comment_status": "open",
        "ping_status": "closed",
    }
    if meta.get("date_gmt"):
        donnees["date_gmt"] = meta["date_gmt"]

    existant = call("GET", f"wp/v2/posts?slug={urllib.parse.quote(meta['slug'])}&status=publish,future,draft,private&_fields=id")
    if existant:
        article = call("POST", f"wp/v2/posts/{existant[0]['id']}?_fields=id,link,status,date", donnees)
        action = "mis à jour"
    else:
        article = call("POST", "wp/v2/posts?_fields=id,link,status,date", donnees)
        action = "créé"

    seo = {k: meta[s] for k, s in (("rank_math_title", "seo_title"), ("rank_math_description", "seo_description")) if s in meta}
    if seo:
        call("POST", "rankmath/v1/updateMeta", {"objectID": article["id"], "objectType": "post", "meta": seo})
    print(f"Article {action} : {article['link']} (id {article['id']}, {article['status']}, {article['date']})")


if __name__ == "__main__":
    main(sys.argv[1])
