# Projet : refonte de a3cdigital.com

Média éditorial national (en français) pour **entrepreneurs, professionnels de santé et professions juridiques**,
monétisé par des articles sponsorisés. La société A3C Digital n'existe plus : le domaine est conservé pour son ancienneté.

Référence complète : `docs/plan-refonte.md` (diagnostic, arborescence, wireframe, DA, migration). La lire avant toute tâche.

## Où travailler

- La copie de test a été **poussée en production le 26/11/2026** : le nouveau site est **https://a3cdigital.com/** (thème A3C) et la copie staging6 n'existe plus.
- On travaille désormais directement sur le site en ligne, par l'API REST (mot de passe d'application injecté par l'environnement) : `--site https://a3cdigital.com`.
- Thème : déployé par l'extension « A3C Déploiement » (`POST /wp-json/a3c/v1/theme`, zip produit par `theme/build.py`). Tout déploiement ou suppression sur le site en ligne requiert l'accord explicite de l'éditrice.

## Calendrier éditorial

- Rythme validé le 26/11/2026 : **4 articles par semaine** à partir du lundi 8 février 2027, en alternance **client / utile** : lundi client, mardi utile, jeudi client, vendredi utile, à 6 h (heure de Paris). Avant cette date : mardi client, jeudi utile.
- Suivi des articles clients : `docs/plan-articles-clients.md` ; liens commerciaux : `seo/liens-commerciaux.csv`.

## Contenu du dépôt

- `archive/wordpress-2025/` : sauvegarde du site d'origine (25 articles et 4 pages en Markdown, `_index.json`). Ne pas modifier : c'est la référence avant refonte.
- `scripts/export_wordpress.py` : régénère l'archive (`python3 scripts/export_wordpress.py https://a3cdigital.com archive/wordpress-2025`).
- `seo/redirections.csv` : plan de redirections 301 (source, destination, code, action).
- `seo/disavow.txt` : fichier de désaveu pour Google Search Console.

## Règles éditoriales (à respecter dans tout contenu produit)

- Français, vouvoiement, ton expert mais accessible, sans jargon inutile.
- Santé : gestion et communication du cabinet uniquement, **jamais de conseil médical**.
- **Sources (exigence de l'éditrice, 26/11/2026)** : tout article s'appuie sur de vraies données vérifiées sur des sites officiels (Légifrance, service-public.fr, Urssaf, impots.gouv.fr, economie.gouv.fr, HAS, CNIL, CNB, Ordres professionnels, ANS, Insee, Bpifrance).
  - Chaque chiffre, date ou règle est vérifié à la source avant publication ; en cas de contradiction entre sources officielles, ne pas donner le chiffre et renvoyer vers la source la plus récente.
  - Section « Sources » en fin d'article : **liens cliquables vers les pages exactes**, avec la date de consultation.
  - **Seulement si le thème le nécessite** (précision de l'éditrice, 26/11/2026) : un article de méthode ou de conseils pratiques qui ne cite ni chiffre, ni règle, ni donnée officielle n'a **pas** de section « Sources » ni de liens de sources.
  - Ne jamais publier un chiffre qui n'a pas été vérifié.
- Chaque article contient :
  - un H1 unique et des H2 environ tous les 300 mots (jamais d'intertitres en simple gras) ;
  - un encadré « L'essentiel en 30 secondes » ;
  - une date de mise à jour et un auteur réel ;
  - un title ≤ 60 caractères et une meta description de 140–155 caractères ;
  - des liens vers sa rubrique, un guide pilier et 2 articles proches.
- **Annonceurs extérieurs (articles vendus)** : liens en **dofollow** (décision de l'éditrice du 25/11/2026).
  - Étiquette `partenariat` et mention discrète « Contenu partenaire » en haut de l'article : c'est l'obligation légale d'identifier la publicité (LCEN art. 20), indépendante de l'attribut du lien.
  - 2 liens maximum, ancres naturelles (marque, URL, descriptive ; pas d'ancre exacte commerciale répétée).
  - Annonceur et sujet en rapport avec les 3 audiences : refuser les sujets hors thématique.
  - Au plus 1 article partenaire sur 4 publications.
- Thématiques refusées en partenariat : médicaments et dispositifs médicaux, jeux d'argent, crypto, CBD, adulte.
- **Liens vers les marques de l'éditrice** (djaka.fr, site-web-medecins.fr / Doctoweb, site-avocat-notaire.fr / E-Juriste) :
  - en dofollow, au plus 1 par article, et seulement si le sujet s'y prête ;
  - ancre de marque ou d'URL de préférence ;
  - mention « X est une marque de l'éditrice du site » ;
  - jamais dans le menu, le pied de page ou la barre latérale.
- **Liens clients de l'agence** : dofollow, sans mention (décision de l'éditrice du 25/11/2026). Pour que l'article reste réellement éditorial :
  - l'article doit être utile sans le lien : guide, cas pratique ou interview d'expert, jamais une présentation commerciale du client ;
  - pas de ton promotionnel ni de formule « nous vous recommandons X » ;
  - le lien client est l'une des sources de l'article, à côté de sources neutres ou officielles ;
  - ancres variées : nom, marque, URL ou ancre descriptive ; jamais deux fois la même ancre optimisée pour un même client ;
  - au plus 1 client par article ;
  - ces articles ne sont jamais proposés sur des plateformes de vente de liens.
- Au moins 50 % des articles ne contiennent aucun lien commercial. Consigner chaque lien commercial dans `seo/liens-commerciaux.csv`.
- Ne jamais donner à un lien partenaire le statut de seul lien sortant d'un article : citer aussi des sources neutres.
- Ne jamais inventer de chiffres d'audience, de témoignages ou de logos clients.

## Vocabulaire

- Côté annonceurs, parler de « publier un article » et jamais de « partenariat » (page `/publier-un-article/`). La mention légale en haut des articles vendus reste « Contenu partenaire ».

## Conventions d'URL

- Articles à la racine : `/slug-court-sans-annee/` (l'année va dans le title, pas dans l'URL).
- Rubriques : `/entrepreneurs/`, `/sante/`, `/juridique/`, `/guides/` (sans préfixe `/category/`).
- Toute URL modifiée ou supprimée doit être ajoutée à `seo/redirections.csv`.
