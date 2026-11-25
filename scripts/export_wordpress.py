#!/usr/bin/env python3
"""Exporte les articles et pages publics d'un WordPress via l'API REST.

Usage : python3 scripts/export_wordpress.py https://a3cdigital.com archive/wordpress-2025

Pour chaque contenu, écrit :
  - <slug>.md   : front matter YAML (titre, dates, catégories, SEO Rank Math) + contenu Markdown
  - _index.json : inventaire complet (sert de base au plan de redirections)
"""
import json
import re
import sys
import urllib.request
from html import unescape
from pathlib import Path

from markdownify import markdownify

UA = {"User-Agent": "Mozilla/5.0 (site-export)"}


def get(url):
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=30) as r:
        return json.load(r), r.headers


def get_all(base, endpoint, params=""):
    items, page = [], 1
    while True:
        data, headers = get(f"{base}/wp-json/wp/v2/{endpoint}?per_page=100&page={page}{params}")
        items += data
        if page >= int(headers.get("X-WP-TotalPages", 1)):
            return items
        page += 1


def yaml_str(value):
    return json.dumps(unescape(value or ""), ensure_ascii=False)


def main(base, out):
    out = Path(out)
    base = base.rstrip("/")
    cats = {c["id"]: c for c in get_all(base, "categories")}
    users = {u["id"]: u["name"] for u in get_all(base, "users")}
    index = []

    for kind in ("posts", "pages"):
        target = out / kind
        target.mkdir(parents=True, exist_ok=True)
        for item in get_all(base, kind, "&_embed=wp:featuredmedia"):
            html = item["content"]["rendered"]
            text = re.sub(r"<[^>]+>", " ", html)
            seo = item.get("rank_math_head") or ""  # absent sans extension dédiée
            head = item.get("yoast_head_json") or {}
            media = (item.get("_embedded", {}).get("wp:featuredmedia") or [{}])[0]
            ext_links = re.findall(r'<a[^>]+href="(https?://(?!(?:www\.)?a3cdigital\.com)[^"]+)"[^>]*>', html)
            record = {
                "type": kind[:-1],
                "id": item["id"],
                "slug": item["slug"],
                "url": item["link"],
                "title": unescape(item["title"]["rendered"]),
                "date": item["date"],
                "modified": item["modified"],
                "author": users.get(item.get("author"), ""),
                "categories": [cats[c]["slug"] for c in item.get("categories", []) if c in cats],
                "excerpt": unescape(re.sub(r"<[^>]+>", "", item.get("excerpt", {}).get("rendered", ""))).strip(),
                "featured_image": media.get("source_url", ""),
                "word_count": len(text.split()),
                "h2_count": html.lower().count("<h2"),
                "external_links": ext_links,
            }
            index.append(record)
            front = "\n".join([
                "---",
                f"title: {yaml_str(record['title'])}",
                f"slug: {record['slug']}",
                f"url_actuelle: {record['url']}",
                f"date: {record['date']}",
                f"modifie: {record['modified']}",
                f"categories: {json.dumps(record['categories'], ensure_ascii=False)}",
                f"image: {record['featured_image']}",
                f"mots: {record['word_count']}",
                f"liens_externes: {json.dumps(ext_links, ensure_ascii=False)}",
                "---",
                "",
            ])
            body = markdownify(html, heading_style="ATX").strip()
            (target / f"{record['slug']}.md").write_text(front + body + "\n", encoding="utf-8")

    (out / "_index.json").write_text(json.dumps(index, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"{len(index)} contenus exportés dans {out}")


if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2])
