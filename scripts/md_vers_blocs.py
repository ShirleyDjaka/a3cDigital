#!/usr/bin/env python3
"""Convertit un article rédigé en texte simple (Markdown réduit) en blocs Gutenberg.

Syntaxe :
    ## Intertitre                 -> titre H2
    ### Sous-titre                -> titre H3
    - élément / 1. élément        -> liste à puces / numérotée
    :::essentiel ... :::          -> encadré « L'essentiel en 30 secondes » (liste à l'intérieur)
    :::retenir ... :::            -> encadré « À retenir »
    :::attention ... :::          -> encadré « Attention »
    :::marque Texte :::           -> mention « … est une marque de l'éditrice du site »
    [texte](https://…)            -> lien ; **gras**
    Ligne vide                    -> nouveau paragraphe
"""
import html
import re


def en_ligne(texte):
    t = html.escape(texte, quote=False)
    t = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", t)
    t = re.sub(r"\[([^\]]+)\]\(([^)\s]+)\)", lambda m: f'<a href="{m.group(2)}">{m.group(1)}</a>', t)
    return t.replace("'", "’")


def bloc_paragraphe(texte, classe=None):
    if classe:
        return f'<!-- wp:paragraph {{"className":"{classe}"}} -->\n<p class="{classe}">{en_ligne(texte)}</p>\n<!-- /wp:paragraph -->'
    return f"<!-- wp:paragraph -->\n<p>{en_ligne(texte)}</p>\n<!-- /wp:paragraph -->"


def bloc_titre(texte, niveau=2, classe=None):
    attrs = {} if niveau == 2 else {"level": niveau}
    if classe:
        attrs["className"] = classe
    a = (" " + str(attrs).replace("'", '"')) if attrs else ""
    c = f"wp-block-heading{' ' + classe if classe else ''}"
    return f'<!-- wp:heading{a} -->\n<h{niveau} class="{c}">{en_ligne(texte)}</h{niveau}>\n<!-- /wp:heading -->'


def bloc_liste(items, ordonnee=False):
    li = "\n".join(f"<!-- wp:list-item -->\n<li>{en_ligne(i)}</li>\n<!-- /wp:list-item -->" for i in items)
    if ordonnee:
        return f'<!-- wp:list {{"ordered":true}} -->\n<ol class="wp-block-list">{li}</ol>\n<!-- /wp:list -->'
    return f'<!-- wp:list -->\n<ul class="wp-block-list">{li}</ul>\n<!-- /wp:list -->'


def blocs(lignes):
    """Convertit une suite de lignes (hors encadrés) en blocs."""
    sortie, para, liste, ordonnee = [], [], [], False

    def vider():
        nonlocal para, liste
        if para:
            sortie.append(bloc_paragraphe(" ".join(para)))
            para = []
        if liste:
            sortie.append(bloc_liste(liste, ordonnee))
            liste = []

    for ligne in lignes:
        l = ligne.rstrip()
        if not l.strip():
            vider()
        elif l.startswith("### "):
            vider()
            sortie.append(bloc_titre(l[4:], 3))
        elif l.startswith("## "):
            vider()
            sortie.append(bloc_titre(l[3:], 2))
        elif re.match(r"^- ", l):
            if para:
                sortie.append(bloc_paragraphe(" ".join(para)))
                para = []
            if liste and ordonnee:
                vider()
            ordonnee = False
            liste.append(l[2:])
        elif re.match(r"^\d+\. ", l):
            if para:
                sortie.append(bloc_paragraphe(" ".join(para)))
                para = []
            if liste and not ordonnee:
                vider()
            ordonnee = True
            liste.append(re.sub(r"^\d+\. ", "", l))
        else:
            if liste:
                vider()
            para.append(l.strip())
    vider()
    return sortie


def encadre(type_, lignes):
    if type_ == "essentiel":
        corps = blocs(lignes)
        inner = bloc_titre("L'essentiel en 30 secondes", 2, "essentiel-titre") + "\n\n" + "\n\n".join(corps)
        return f'<!-- wp:group {{"className":"essentiel","layout":{{"type":"default"}}}} -->\n<div class="wp-block-group essentiel">{inner}</div>\n<!-- /wp:group -->'
    titre, classe = {"retenir": ("À retenir", "encadre"), "attention": ("Attention", "encadre encadre-attention")}[type_]
    inner = bloc_paragraphe(titre, "titre-encadre") + "\n\n" + "\n\n".join(blocs(lignes))
    return f'<!-- wp:group {{"className":"{classe}","layout":{{"type":"default"}}}} -->\n<div class="wp-block-group {classe}">{inner}</div>\n<!-- /wp:group -->'


def convertir(texte):
    sortie, tampon, dans, type_ = [], [], False, None
    for ligne in texte.splitlines():
        m = re.match(r"^:::(essentiel|retenir|attention)\s*$", ligne.strip())
        mm = re.match(r"^:::marque\s+(.+?)\s*:::\s*$", ligne.strip())
        if mm:
            sortie += blocs(tampon)
            tampon = []
            sortie.append(bloc_paragraphe(mm.group(1), "marque"))
        elif m and not dans:
            sortie += blocs(tampon)
            tampon, dans, type_ = [], True, m.group(1)
        elif ligne.strip() == ":::" and dans:
            sortie.append(encadre(type_, tampon))
            tampon, dans = [], False
        else:
            tampon.append(ligne)
    sortie += blocs(tampon)
    return "\n\n".join(sortie)
