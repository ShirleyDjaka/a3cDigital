#!/usr/bin/env python3
"""Met en place les rubriques du plan (Entrepreneurs / Santé / Juridique / Guides) et y range les articles.

Usage : python3 scripts/restructurer_rubriques.py https://staging6.a3cdigital.com [--appliquer]

Sans --appliquer : affiche ce qui serait fait (aucune modification).
Idempotent : peut être relancé sans dommage. Réutilisable tel quel sur le site en ligne.
Authentification : injectée par le proxy de l'environnement Claude Code.
"""
import json
import sys
import urllib.parse
import urllib.request

UA = "Mozilla/5.0 (a3cdigital-rubriques)"

# Anciennes catégories à renommer (ancien slug -> nouveau slug), avant toute création.
RENOMMER = {
    "juridique": "obligations-juridique",  # libère le slug « juridique » pour la nouvelle rubrique
    "entrepreneuriat": "entrepreneurs",
    "finance": "financer-investir",
    "marketing": "marketing-visibilite",
}

# Rubriques cibles : slug -> (nom, parent, description)
RUBRIQUES = {
    "entrepreneurs": ("Entrepreneurs", None, "Créer son entreprise, la financer, respecter ses obligations et se faire connaître : des guides concrets pour entreprendre en France."),
    "creer-son-entreprise": ("Créer son entreprise", "entrepreneurs", ""),
    "financer-investir": ("Financer & investir", "entrepreneurs", ""),
    "obligations-juridique": ("Obligations & juridique", "entrepreneurs", ""),
    "marketing-visibilite": ("Marketing & visibilité", "entrepreneurs", ""),
    "sante": ("Santé", None, "Installation, gestion et communication du cabinet : ce que les professionnels de santé peuvent faire, et ce que leur déontologie interdit."),
    "installation-gestion-cabinet": ("Installation & gestion du cabinet", "sante", ""),
    "sante-communication-deontologie": ("Communication & déontologie", "sante", ""),
    "visibilite-en-ligne": ("Visibilité en ligne", "sante", ""),
    "outils-numeriques": ("Outils numériques", "sante", ""),
    "juridique": ("Juridique", None, "Avocats, notaires, juristes : développer son cabinet et sa clientèle en respectant les règles de la profession."),
    "developper-son-cabinet": ("Développer son cabinet", "juridique", ""),
    "juridique-communication-deontologie": ("Communication & déontologie", "juridique", ""),
    "legaltech-outils": ("Legaltech & outils", "juridique", ""),
    "guides": ("Guides", None, "Les guides de référence, régulièrement mis à jour."),
}

# Article (slug actuel) -> rubriques
ARTICLES = {
    "business-3": ["creer-son-entreprise"],
    "entrepreneuriat-1": ["creer-son-entreprise"],
    "entrepreneuriat-2": ["creer-son-entreprise"],
    "entrepreneuriat-3": ["creer-son-entreprise"],
    "entrepreneuriat-4": ["creer-son-entreprise"],
    "les-soft-skills-sous-estimes-qui-font-la-difference-en-tant-quentrepreneur": ["creer-son-entreprise"],
    "le-personal-branding-est-il-entrain-de-remplacer-le-cv": ["creer-son-entreprise"],
    "business-1": ["financer-investir"],
    "business-4": ["financer-investir"],
    "investir-dans-lintelligence-artificielle-opportunite-ou-risque": ["financer-investir"],
    "les-aides-financieres-et-subventions-pour-les-entrepreneurs-en-2025": ["financer-investir"],
    "auto-financement-vs-investisseurs-quelle-strategie-choisir": ["financer-investir"],
    "business-2": ["obligations-juridique"],
    "droit-dauteur-et-contenu-en-ligne-ce-que-createurs-et-marques-ignorent": ["obligations-juridique"],
    "micro-influenceurs-secret-des-marques-qui-cartonnes-2025": ["marketing-visibilite"],
    "creer-un-site-web-efficace-pour-votre-entreprise-a-montpellier": ["marketing-visibilite"],
    "choisir-une-agence-web-pour-son-site-ecommerce-sur-wordpress": ["marketing-visibilite"],
    "comment-les-vignobles-peuvent-optimiser-leur-presence-sur-les-reseaux-sociaux": ["marketing-visibilite"],
    "domaine-viticole-comment-se-faire-connaitre-sur-le-web": ["marketing-visibilite"],
    "dentiste-a-montpellier-utilise-seo-pour-attirer-nouveaux-patients": ["visibilite-en-ligne"],
    "communication-digitale-sante-une-pratique-incontournable": ["sante-communication-deontologie"],
    "choisir-une-agence-de-communication-sante-professionnels-du-medical": ["sante-communication-deontologie"],
    "allier-communication-digitale-et-deontologie-etude-notariale": ["juridique-communication-deontologie"],
    "les-avantages-dun-bon-referencement-pour-les-avocats": ["developper-son-cabinet"],
    "succession-les-implications-juridiques-et-pratiques-dun-debarras-immobilier": ["juridique"],
}

# Anciennes catégories à supprimer une fois vides
SUPPRIMER = ["business", "investissement", "communication", "digital", "emploi-formation", "carriere-vie-professionnelle"]


class Site:
    def __init__(self, base, appliquer):
        self.base = base.rstrip("/") + "/wp-json/wp/v2"
        self.appliquer = appliquer

    def appel(self, methode, chemin, donnees=None):
        if methode != "GET" and not self.appliquer:
            print(f"  [simulation] {methode} {chemin} {json.dumps(donnees, ensure_ascii=False) if donnees else ''}")
            return {}
        req = urllib.request.Request(
            self.base + chemin,
            data=json.dumps(donnees).encode() if donnees is not None else None,
            method=methode,
            headers={"Content-Type": "application/json", "User-Agent": UA},
        )
        with urllib.request.urlopen(req, timeout=60) as r:
            return json.load(r)

    def categories(self):
        return {c["slug"]: c for c in self.appel("GET", "/categories?per_page=100&context=edit&_fields=id,slug,name,parent,count,description")}


def main(base, appliquer):
    site = Site(base, appliquer)
    cats = site.categories()

    print("1. Renommage des anciennes catégories")
    for ancien, nouveau in RENOMMER.items():
        if ancien in cats and nouveau not in cats:
            nom = RUBRIQUES[nouveau][0]
            print(f"  {ancien} -> {nouveau} ({nom})")
            site.appel("POST", f"/categories/{cats[ancien]['id']}", {"slug": nouveau, "name": nom})
    cats = site.categories() if appliquer else cats

    print("2. Création / mise à jour des rubriques")
    for slug, (nom, parent, desc) in RUBRIQUES.items():  # parents avant enfants (ordre du dictionnaire)
        parent_id = cats[parent]["id"] if parent and parent in cats else 0
        donnees = {"name": nom, "slug": slug, "parent": parent_id}
        if desc:
            donnees["description"] = desc
        if slug in cats:
            c = cats[slug]
            if c["name"] != nom or c["parent"] != parent_id or (desc and c["description"] != desc):
                print(f"  mise à jour : {slug}")
                site.appel("POST", f"/categories/{c['id']}", donnees)
        else:
            print(f"  création : {slug}")
            res = site.appel("POST", "/categories", donnees)
            if res:
                cats[slug] = res
    cats = site.categories() if appliquer else cats

    print("3. Rangement des articles")
    for slug_article, rubriques in ARTICLES.items():
        posts = site.appel("GET", f"/posts?slug={urllib.parse.quote(slug_article)}&status=any&context=edit&_fields=id,categories")
        if not posts:
            print(f"  introuvable : {slug_article}")
            continue
        ids = [cats[r]["id"] for r in rubriques if r in cats]
        if sorted(posts[0]["categories"]) != sorted(ids):
            print(f"  {slug_article} -> {', '.join(rubriques)}")
            site.appel("POST", f"/posts/{posts[0]['id']}", {"categories": ids})
    cats = site.categories() if appliquer else cats

    print("4. Suppression des anciennes catégories vides")
    for slug in SUPPRIMER:
        if slug in cats:
            if cats[slug]["count"] == 0 or not appliquer:
                print(f"  suppression : {slug}")
                site.appel("DELETE", f"/categories/{cats[slug]['id']}?force=true")
            else:
                print(f"  conservée (encore {cats[slug]['count']} article(s)) : {slug}")
    print("Terminé." if appliquer else "Simulation terminée : relancer avec --appliquer.")


if __name__ == "__main__":
    main(sys.argv[1], "--appliquer" in sys.argv)
