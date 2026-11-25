#!/usr/bin/env python3
"""Crée ou met à jour une page WordPress à partir d'un fichier HTML (blocs Gutenberg).

Usage : python3 scripts/wp_publish_page.py contenus/pages/contact.html

Le fichier commence par un en-tête :
    <!-- title: Contact -->
    <!-- slug: contact -->
    <!-- status: publish -->
    <!-- seo_title: ... -->          (facultatif, Rank Math)
    <!-- seo_description: ... -->    (facultatif, Rank Math)

L'authentification est injectée par le proxy de l'environnement Claude Code
(mot de passe d'application WordPress) : aucun secret dans ce script.
"""
import json
import re
import sys
import urllib.request
from pathlib import Path

SITE = "https://a3cdigital.com"
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


def main(path):
    raw = Path(path).read_text(encoding="utf-8")
    meta = dict(re.findall(r"<!--\s*(\w+):\s*(.*?)\s*-->", raw.split("\n\n", 1)[0]))
    content = re.sub(r"^(<!--\s*\w+:.*?-->\s*)+", "", raw, flags=re.S).strip()
    payload = {"title": meta["title"], "slug": meta["slug"], "content": content,
               "status": meta.get("status", "draft"), "comment_status": "closed", "ping_status": "closed"}

    existing = call("GET", f"wp/v2/pages?slug={meta['slug']}&status=publish,draft,private&_fields=id")
    if existing:
        page = call("POST", f"wp/v2/pages/{existing[0]['id']}?_fields=id,link,status", payload)
        action = "mise à jour"
    else:
        page = call("POST", "wp/v2/pages?_fields=id,link,status", payload)
        action = "créée"

    seo = {k: meta[s] for k, s in (("rank_math_title", "seo_title"), ("rank_math_description", "seo_description")) if s in meta}
    if seo:
        call("POST", "rankmath/v1/updateMeta", {"objectID": page["id"], "objectType": "post", "meta": seo})
    print(f"Page {action} : {page['link']} (id {page['id']}, {page['status']})")


if __name__ == "__main__":
    main(sys.argv[1])
