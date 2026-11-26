# Plan de refonte — a3cdigital.com

> Version du 25/11/2026. Données : crawl du site, API WordPress, SE Ranking (base FR).

## 0. Cadre

- **Nature du site** : média éditorial national, monétisé par des articles sponsorisés. La société A3C Digital n'existe plus ; le domaine est conservé pour son ancienneté (liens depuis 2018).
- **Cibles** : entrepreneurs, professionnels de santé, professions juridiques.
- **Pas de SEO local** pour le média. Le local reste un argument de vente pour les annonceurs (articles géolocalisés).
- **Partenariats** : liens vendus en **dofollow** (décision de l'éditrice du 25/11/2026), avec une mention discrète « Contenu partenaire » pour l'obligation légale d'identifier la publicité. Risque Google accepté par l'éditrice, limité par les garde-fous du §7. La valeur d'un lien dépend du trafic et de l'autorité du site : monétisation réaliste à 4–6 mois.
- **Éditeur légal** : à nommer dans les mentions légales (personne physique ou agence actuelle). C'est aussi l'entité qui facture les partenariats, avec des CGV.

## 1. État des lieux (25/11/2026)

| Indicateur | Valeur |
|---|---|
| Trafic organique FR | ~20 visites/mois, dont ~75 % sur le sous-domaine client drdanvelea.a3cdigital.com ; ~2 visites/mois sur le blog |
| Mots-clés positionnés | 138 |
| Contenus | 25 articles et 4 pages ; dernière publication le 10/06/2025 |
| Stack | WordPress, thème « outdoor », Elementor Pro, Redux, Rank Math, era-magazine, top-10 |
| Poids de la home | 30 scripts, 22 CSS, Lato en 10 graisses plus Poppins ; temps de réponse ~1,1 s |
| Backlinks | 286 domaines référents, dont ~210 de spam apparus entre le 27/10 et le 15/11/2026 |
| Pages légales | `/mentions-legales/` et `/contact/` en 404 |
| Comptes WP | `shirleyDjaka` (admin) et **`SupportYoozeo` (administrateur, auteur de 17 articles)**. Les identifiants sont exposés publiquement par `/wp-json/wp/v2/users` |
| Hébergement | **SiteGround** (Speed Optimizer, Security Optimizer, SiteGround Central) : hébergeur de qualité, pas de migration nécessaire |
| Extensions actives | Elementor 4.4.1 + Pro 3.28.4, Era Magazine, Redux, Rank Math 1.0.282, Top 10, Duplicate Page, **One Click Demo Import (inutile)**, SG Security, SG Speed Optimizer, SiteGround Central |
| Réglages | Adresse WordPress en `http://a3cdigital.com` (https forcé par SiteGround) ; commentaires et pings ouverts par défaut ; fuseau horaire non défini ; slogan « L'actualité du business » ; e-mail admin contact@djaka.fr |

**Corrections faites le 25/11/2026 via l'API** (sauvegarde : `backups/2026-11-25-accueil-elementor-data.json`) :
- Home : « Entreprenriat » → « Entrepreneuriat ».
- Home : title Rank Math « Média pour entrepreneurs, santé & juridique | A3C Digital » et meta description réécrite.
- Contact Form 7 installé ; pages publiées : `/mentions-legales/` (éditeur Djaka), `/politique-de-confidentialite/`, `/contact/` (sources dans `contenus/pages/`, publication via `scripts/wp_publish_page.py`).
- Pied de page Elementor (modèle 144) : liens Mentions légales · Confidentialité · Contact (sauvegarde : `backups/2026-11-25-pied-de-page-elementor-data.json`).
- Compte `SupportYoozeo` (e-mail support@djaka.fr) supprimé, ses 17 articles réattribués à `shirleyDjaka`. One Click Demo Import désactivé.
- Commentaires conservés (ouverts). Antispam Bee 2.11.13 installé. Pings et trackbacks fermés (réglage par défaut et 25 articles existants).
- Les réglages de SG Security ne se pilotent pas par l'API (erreurs 500 sur disable-xml-rpc et hide-wp-version, sans impact sur le site) : à faire dans l'interface.
- **Reste à faire à la main** : coller le formulaire de `contenus/formulaires/contact-cf7.md` dans Contact Form 7, car son API ne sauvegarde pas.
- Point à vérifier : les polices Google sont chargées depuis fonts.googleapis.com (transfert d'IP vers Google). À héberger sur le site lors de la refonte.

### Liens sortants commerciaux (tous en dofollow, aucun signalé)

**Confirmé le 25/11/2026 : aucun de ces liens n'a été payé.** Pas de `sponsored` à poser. Ces liens restent en dofollow, sous conditions (cf. dernière colonne).

| Article | Lien | Traitement lors de la réécriture |
|---|---|---|
| succession-…-debarras-immobilier | m-p-debarras.fr | Garder le lien. Réécrire la meta description, qui est aujourd'hui une publicité. |
| dentiste-a-montpellier-… | dr-sandor-lucas.chirurgiens-dentistes.fr | Garder comme exemple concret dans le guide national `/seo-dentiste/` |
| creer-un-site-web-…-montpellier / choisir-une-agence-web-…-wordpress | djaka.fr | Garder, avec une mention de transparence (« agence de l'éditrice ») |
| comment-les-vignobles-… / domaine-viticole-… | tbweb.fr | Garder 1 seul lien dans l'article fusionné |
| communication-digitale-sante-… / choisir-une-agence-de-communication-sante-… | site-web-medecins.fr (Doctoweb) | Garder 1 seul lien dans le guide fusionné, parmi d'autres ressources |
| allier-communication-…-notariale / les-avantages-…-avocats | site-avocat-notaire.fr (E-Juriste) | Garder, au milieu d'autres ressources (pas en lien unique) |

Règles pour les liens non payés :
- au plus 1 lien par site partenaire et par article ;
- ancre descriptive mais pas sur-optimisée ;
- jamais le seul lien sortant de l'article : citer aussi des sources neutres ou officielles.

Si un lien est un jour payé ou échangé contre un service, il passe en `sponsored`.

### Backlinks à protéger (anciens sites clients, thématiquement parfaits)

Ces liens sont des crédits en pied de page d'anciens sites clients : ancres du type « a3cdigital, agence de création de sites web ».

| Domaine | Liens | Thème |
|---|---|---|
| lair-marchais-noisy-le-grand.notaires.fr | 278 (crédit sur toutes les pages) | Juridique |
| investir-expatrie.com / investirpatrimoine.fr | 51 / 51 | Patrimoine |
| pma-avignon.fr / inovie-fertilite.fr | 24 / 1 | Santé |
| realpromotion.fr, projetude.fr, aef-montpellier.fr, asmillenaire.com, vols-montgolfieres.fr | 1–17 | Divers |
| trustburn.com (DIR 84), yoozeo.fr, dr-dan-velea.fr | — | — |

Ces liens pointent vers la home (http et https) : **les redirections http→https doivent rester en un seul saut.** Option : contacter ces clients pour transformer le crédit « agence » en lien éditorial vers le média.


### Copie de test (staging6.a3cdigital.com), 26/11/2026

- Extension `A3C Déploiement` installée (déploiement du thème par l'API, blocage de l'indexation des copies `staging…`).
- Thème A3C v1.0.1 installé et actif.
- Rubriques en place (`scripts/restructurer_rubriques.py`) : Entrepreneurs (Créer son entreprise, Financer & investir, Obligations & juridique, Marketing & visibilité), Santé (4 sous-rubriques), Juridique (3 sous-rubriques), Guides. Les 25 articles sont rangés, et les 6 anciennes catégories supprimées.
- Extensions supprimées sur la copie : Elementor, Elementor Pro, Era Magazine, Redux, Top 10 (One Click Demo Import déjà absent). Conservées : Rank Math, Contact Form 7, Antispam Bee, SG Security, SG Speed Optimizer, SiteGround Central, Duplicate Page, A3C Déploiement.
- Profil auteur : « Shirley Seywert », fonction et bio. Slogan du site mis à jour.
- Thème v1.1.1 : rubriques et recherche en **cartes** (onglet d'audience, carte entièrement cliquable), 9 par chargement, bouton **« Charger plus d'articles »** (lien réel vers `/page/N/`, donc toujours lisible par Google), compteur d'articles. Correctif : le texte des articles sans sommaire n'est plus coincé dans la colonne de 240 px.
- Google Analytics 4 (G-2HJN0W2MD3, via Rank Math) **conservé** (décision du 26/11/2026). Complianz 7.5.8 est installé et actif sur le site en ligne. L'assistant de configuration est à faire par l'éditrice, puis la politique de confidentialité (mise à jour : GA4 avec consentement, 13 mois) deviendra exacte.
- Thème v1.2.2 : page **« Publier un article »** (`/publier-un-article/`, sans vocabulaire « partenariat ») avec formulaire intégré `[a3c_demande_publication]`. Champs : nom, entreprise, e-mail, téléphone, site, rubrique, format, sujet, précisions, consentement. Anti-robots (champ piège et délai de 4 s), page exclue du cache. Chaque demande est envoyée par e-mail à l'adresse admin et enregistrée dans le menu « Demandes de publication ». Testé de bout en bout sur la copie (demande test id 411).
- Favicon dans la charte (carré nuit, trois onglets, « A » blanc) : `theme/a3c/assets/img/favicon.svg` et `favicon-512.png`, installé sur la copie de test (média 412).
- **Mise en ligne (26/11/2026)** : la copie de test a été poussée en production par l'éditrice. Vérifié en ligne :
  - thème A3C 1.2.3 actif, Elementor et extensions obsolètes absents ;
  - toutes les pages en 200, sans erreur PHP, sans trace de `staging6`, aucun noindex parasite ;
  - favicon, fuseau Europe/Paris, article programmé (09/12) et formulaire « Publier un article » en place ;
  - accueil : 32 Ko et 0,5 s, contre 126 Ko et 1,1 s avant.
- **Corrigé le 26/11/2026** (accord de l'éditrice) : thème 1.2.4 déployé en ligne. Sitemap rétabli (index, 25 articles, 4 pages, 10 rubriques). 9 anciennes adresses redirigées en 301 vers des pages en 200. Pages vides `/business/`, `/marketing/` et `/entreprenariat/` (id 111, 113, 115) à la corbeille. Reste : `/ouvrir-cabinet-dentaire-plan-communication/` absent du sitemap (cache Rank Math), alors qu'il est indexable.
- **Thème 1.3.0 (26/11/2026, en ligne)** : sur l'accueil, la section « À la une » est remplacée par « Les derniers articles », qui affiche les 3 derniers articles publiés en cartes, dans la même grille que les rubriques (3 colonnes, 2 sous 960 px, 1 sur mobile ; thème 1.3.1).
- **Consentement (26/11/2026)** : assistant Complianz terminé, bandeau affiché (Accepter, Refuser, Préférences). Mais le gtag de Rank Math (G-2HJN0W2MD3) se chargeait avant consentement, car Complianz ne le reconnaît pas. Correctif dans le thème 1.3.2 (`inc/consentement.php`, scripts passés en `text/plain` avec `data-category="statistics"`), en attente de l’accord de l’éditrice pour le déploiement.
- **Consentement corrigé (26/11/2026)** : thème 1.3.2 déployé avec l'accord de l'éditrice. Testé : 0 requête Google Analytics avant le choix et après un refus ; chargement après acceptation ; bandeau masqué après le choix.
- **Calendrier éditorial, premier mois** (programmé le 26/11/2026, mardi et jeudi à 6 h, sources officielles en lien) :
  - 03/12 `/previsionnel-financier/` ;
  - 08/12 `/legaltech-outils-avocats/` (E-Juriste) ;
  - 10/12 `/secretariat-medical-a-distance/` ;
  - 15/12 `/micro-entreprise-ou-sasu/` ;
  - 17/12 `/ia-medecins-usages-limites/` (Doctoweb) ;
  - 22/12 `/publicite-avocats-regles/` ;
  - 29/12 `/repondre-avis-google/` (Djaka) ;
  - 05/01/2027 `/logiciel-avocat-criteres/`.

  Sources des articles : `contenus/articles/*.md` (conversion `scripts/md_vers_blocs.py`).
- Détail des problèmes constatés juste après la mise en ligne :
  - `/sitemap_index.xml` en 404 : règles de réécriture non régénérées après le transfert ;
  - anciennes adresses de catégories en 404 et pages vides `/business/`, `/marketing/`, `/entreprenariat/` (redirections 301 dans `inc/redirections.php`).
- **Points ouverts** :
  - Google Analytics chargé deux fois (Rank Math G-2HJN0W2MD3 et Site Kit GT-P3J3SK7) et sans consentement : l'assistant Complianz n'est pas fait ;
  - formulaire Contact Form 7 encore celui par défaut ;
  - demande test « Test Claude » (id 411) à supprimer ;
  - la copie de test staging6 ne répond plus (502).
- **Règle (26/11/2026)** : la copie de test **remplacera le site en ligne** (poussée par SiteGround à la bascule). Tout se fait désormais sur la copie. Report effectué : politique de confidentialité à jour, Complianz installé, fuseau horaire Europe/Paris.
- **Articles Doctoweb (document de l'éditrice du 25/11)** : `/ouvrir-cabinet-dentaire-plan-communication/` publié (Santé > Installation & gestion du cabinet) ; `/identite-visuelle-cabinet-medical-avant-ouverture/` programmé le 01/12/2026 à 6 h (date changée par l’éditrice) (Santé > Communication & déontologie) ; lien vers `/site-dentiste` ajouté dans l'article dentiste existant. Chaque article reçoit des H2, un encadré « L'essentiel », la mention de marque et des liens officiels. Sources : `contenus/articles/`.
- **Nettoyage des extensions (26/11/2026)** :
  - **site en ligne** : One Click Demo Import, Top 10 (simple compteur de vues, sans affichage) et SiteGround Central supprimés ;
  - **copie de test** : SiteGround Central supprimé ;
  - **restent en ligne jusqu'à la bascule de thème** : Elementor, Elementor Pro, Era Magazine, Redux. Le thème actuel « outdoor » en dépend (page d'accueil, en-tête et pied de page Elementor) : les supprimer maintenant casserait l'affichage.

### Plan de visibilité, 26/11/2026 (validé par l'éditrice, sauf LinkedIn et Metricool)

- Titles optimisés sur des requêtes peu concurrencées (SE Ranking) : contrat de collaboration libérale (320 recherches/mois, difficulté 6), convention d'honoraires avocat, facturation électronique obligatoire.
- 8 anciens articles réécrits et renommés (redirections 301 vérifiées) : business-1 à 4, entrepreneuriat-4, micro-influenceurs, personal branding, aides financières.
- Pages publiées : charte éditoriale, à propos ; politique de confidentialité complétée (newsletter).
- Thème 1.4.0 prêt, **déploiement en attente de l'accord de l'éditrice** : newsletter avec double confirmation (abonnés dans l'administration, export CSV), page auteur indexable, code court `[a3c_articles]`.
- Après déploiement : publier les 3 guides piliers (`guide-creer-son-entreprise`, `guide-installation-professionnel-sante`, `guide-developper-cabinet-avocat`) et la page newsletter.
- Reste à faire, avec accord (dépublication) : fusions entrepreneuriat-1 + 2, entrepreneuriat-3 + soft skills, deux articles santé, deux articles viticoles ; réécriture nationale des articles « Montpellier ».
- À faire par l'éditrice : Search Console (propriété, sitemap, désaveu), Bing Webmaster Tools, biographie détaillée sur le profil auteur.

## 2. Diagnostic priorisé

| Priorité | Problème | Action |
|---|---|---|
| P2 | 10 liens sortants vers des sites partenaires (non payés, confirmé) | Rééquilibrer lors des réécritures (cf. tableau) |
| P0 | Mentions légales et contact en 404 | Créer |
| P0 | « Entreprenriat » dans la meta description de la home, « Read More » en anglais | Corriger |
| P0 | Comptes WP exposés, compte prestataire `SupportYoozeo` actif | Désactiver ou réattribuer ; bloquer l'énumération des utilisateurs |
| P1 | Promesse floue, menu de 10 catégories | 3 rubriques par audience |
| P1 | Aucun parcours annonceur | Page `/partenariats/` et media kit |
| P1 | Pas de publication depuis juin 2025 | 2 articles/semaine |
| P1 | Pas d'auteurs, sujets santé et juridique sensibles pour Google | Pages auteur, bio, date de mise à jour |
| P2 | 12 articles sur 25 sans aucun H2 (intertitres en gras) | Restructurer |
| P2 | URLs `/business-N/`, `/entrepreneuriat-N/` | Renommer (cf. `seo/redirections.csv`) |
| P2 | Doublons de sujets (soft skills / compétences ; travailler avant / se former avant) | Fusionner |
| P2 | Vague de spam en backlinks | `seo/disavow.txt` |
| P2 | Lourdeur Elementor | Reconstruction légère |
| P3 | Pages `/business/`, `/marketing/`, `/entreprenariat/` en doublon des catégories | Supprimer + 301 |

## 3. Arborescence cible

```
Accueil  /
├── Entrepreneurs                /entrepreneurs/
│   ├── Créer son entreprise           /entrepreneurs/creer-son-entreprise/
│   ├── Financer & investir            /entrepreneurs/financer-investir/
│   ├── Obligations & juridique        /entrepreneurs/obligations-juridique/
│   └── Marketing & visibilité         /entrepreneurs/marketing-visibilite/
├── Professionnels de santé      /sante/
│   ├── Installation & gestion du cabinet
│   ├── Communication & déontologie
│   ├── Visibilité en ligne
│   └── Outils numériques
├── Professions juridiques       /juridique/
│   ├── Développer son cabinet
│   ├── Communication & déontologie
│   └── Legaltech & outils
├── Guides                       /guides/
├── Publier un article          /publier-un-article/  (formulaire intégré)
├── Charte éditoriale            /charte-editoriale/
├── À propos                     /a-propos/  (+ /auteur/<prenom-nom>/)
├── Newsletter                   /newsletter/
├── Contact                      /contact/
└── Légal                        /mentions-legales/ /confidentialite/ /cgv-partenariats/ /cookies/
```

Règles :
- Les articles restent à la racine (`/slug/`).
- Pas de préfixe `/category/`.
- Étiquettes en noindex.
- Pages auteur indexées seulement si la bio est complète.
- Santé : gestion et communication du cabinet uniquement, jamais de conseil médical.

Menu : `Entrepreneurs · Santé · Juridique · Guides · Recherche · [Publier un article]`.

## 4. Page d'accueil (wireframe)

- **Title** : « A3C Digital – Média business : entrepreneurs, santé & juridique »
- **Meta description** : « Guides pratiques pour entrepreneurs, professionnels de santé et juristes : création, obligations légales, marketing et visibilité en ligne. »

1. **Header** compact et fixe : logo, menu, bouton à contour « Publier un article ».
2. **Hero**
   - H1 : « Le média qui aide entrepreneurs, soignants et juristes à développer leur activité »
   - Sous-titre : « Guides pratiques, obligations légales décryptées, marketing compatible avec votre déontologie. Gratuit, sans jargon, mis à jour régulièrement. »
   - 3 tuiles : « Je suis entrepreneur → », « Je suis professionnel de santé → », « Je suis avocat, notaire ou juriste → »
   - Recherche : « Ex. : statut juridique, avis Google, communication avocat… »
3. **À la une** : 1 grand article et 3 petits. Chaque carte affiche badge d'audience, titre, temps de lecture et date de mise à jour ; elle est entièrement cliquable.
4. **3 blocs audience**
   - « Entrepreneurs : créer, financer, se faire connaître »
   - « Santé : faire grandir son cabinet dans les règles »
   - « Juridique : attirer des clients sans enfreindre sa déontologie »
5. **Guides essentiels** : statut juridique 2026, SEO santé autorisé, communication avocats (RIN), obligations légales d'un site pro.
6. **Pourquoi nous lire** : « Rédigé par des praticiens », « Sources citées, dates de mise à jour affichées », « Partenariats toujours signalés ». Chiffres d'audience réels uniquement, au-delà de 1 000.
7. **Newsletter** : « Un e-mail tous les 15 jours. 3 articles utiles. Zéro spam. » Choix de l'audience.
8. **Annonceurs** : « Vous voulez toucher entrepreneurs, soignants et juristes ? » Boutons « Découvrir les offres » et « Télécharger le media kit ».
9. **FAQ** (balisage FAQPage) : qui écrit, partenariats, proposer un article, fréquence.
10. **Footer** : à propos, charte, media kit, contact, pages légales.

**Page `/partenariats/`**
- Hero : « Faites connaître votre marque auprès de pros qui décident »
- Audience : chiffres réels.
- 3 formats : article rédigé par la rédaction, article fourni puis relu, mention dans un article existant.
- Règles : dofollow, mention « Contenu partenaire », 2 liens maximum, ancres naturelles ; thématiques refusées (médicaments, jeux d'argent, crypto, CBD, adulte).
- Processus en 4 étapes, tarifs « à partir de », formulaire avec paiement, FAQ.
- Maximum 1 article sponsorisé sur 4 publications.

## 5. Direction artistique (validée le 26/11/2026)

Maquettes : `maquettes/` (accueil, article, `styles.css` = source des jetons du futur thème).

- **Concept** : les intercalaires de classeur. Chaque audience est un onglet de couleur, motif répété (hero, en-tête d'article, pastilles).
- **Couleurs** :
  - encre bleu nuit `#16213B` (en-tête, hero, pied de page, boutons) ; newsletter `#0E1730` ;
  - texte `#222B3E`, gris `#4C5568`, filets `#CDD3DD`, bandes « brume » `#E6EAF0`, papier `#FFFFFF` ;
  - Entrepreneurs, jaune surligneur `#E8A817` (feuille `#FDF5DF`, texte `#7A5200`) ;
  - Santé, vert pharmacie `#1E7A5A` (feuille `#E4F2EC`) ;
  - Juridique, rouge code `#B0262E` (feuille `#F8E6E6`).
- **Rythme des contrastes** (demande de l'éditrice : site moins blanc) :
  - en-tête et hero bleu nuit ;
  - sections alternant blanc et brume ;
  - newsletter nuit ;
  - **bloc annonceurs en jaune surligneur** ;
  - pied de page nuit ;
  - article : bande de la couleur d'audience derrière le titre.
- **Typographie** :
  - Schibsted Grotesk (titres et interface, 400 à 800) ;
  - Literata (chapô et corps d'article : 20 px, interligne 1,8, **900 px de large** sur ordinateur à la demande de l'éditrice ; conteneur du site 1280 px, sommaire 220 px) ;
  - polices à héberger sur le site dans le thème.
- **Images** : aucune photo de banque d'images. Le design repose sur la typographie et les couleurs d'audience.
- **Gabarit d'article** : fil d'Ariane → onglet d'audience → H1 → chapô → signature et date → « L'essentiel en 30 secondes » → sommaire (fixe sur ordinateur, repliable sur mobile) → H2 numérotés si étapes → encadrés « À retenir » / « Attention » → newsletter → sources → bio → mention de marque si besoin → « À lire ensuite ».
- **Mobile** : menu déroulant sans JavaScript, intercalaires empilés, aucun débordement à 390 px, focus visible, `prefers-reduced-motion` respecté.

## 6. Migration SEO

- **Redirections** : `seo/redirections.csv`. Une seule 301 par adresse, vers l'équivalent le plus proche, jamais tout vers la home. Liens internes mis à jour en base.
- **Sous-domaines** :
  - drdanvelea.a3cdigital.com et drdanvelea2 : **301 page par page vers dr-dan-velea.fr**, qui existe (il fait déjà des liens vers vous depuis mars 2026), à maintenir au moins 12 mois. Jamais vers a3cdigital.com.
  - Retirer ensuite les propriétés de Search Console.
  - Une baisse d'environ 75 % des métriques SE Ranking est attendue et normale.
- **Désaveu** : `seo/disavow.txt` (vague de spam d'octobre-novembre 2026). C'est une assurance : Google ignore déjà l'essentiel de ces liens.
- **On-page** : title ≤ 60 caractères, meta 140–155 caractères, un H1, des H2 structurés. Données structurées : Organization, WebSite, Article + Person, BreadcrumbList, FAQPage sur les guides.
- **Maillage** : article → rubrique + 1 guide pilier + 2 articles proches, et ponts entre rubriques.
- **Jour J** :
  - retirer le noindex ;
  - tester toutes les anciennes adresses (301 → 200) ;
  - vérifier `http://` et `http://www` → `https://a3cdigital.com/` en un seul saut ;
  - soumettre le sitemap.
- **J+1 à J+60** : suivi hebdomadaire des 404 dans Search Console, des positions et des Core Web Vitals.

## 7. Liens vers les marques de l'éditrice et ses clients

Objectif : servir **Djaka** (djaka.fr), **Doctoweb** (site-web-medecins.fr), **E-Juriste** (site-avocat-notaire.fr) et les clients de l'agence, sans que le média ressemble à un réseau de sites satellites. Si Google le classait ainsi, il dévaluerait tous les liens sortants, y compris ceux vendus aux annonceurs.

### Ce que dit Google (règles sur le spam de liens)
- Un lien vers ses **propres marques** est acceptable s'il est éditorialement justifié et occasionnel. Doctoweb et E-Juriste sont parfaitement dans la thématique : c'est un atout.
- Un lien donné **en contrepartie d'un paiement**, y compris s'il est inclus dans une prestation SEO facturée au client, est un lien payant. Google demande `rel="sponsored"`. **L'éditrice a choisi le dofollow (25/11/2026)** et assume le risque : en cas de détection, action manuelle possible sur a3cdigital.com, avec dévaluation de tous les liens sortants. Les garde-fous ci-dessous réduisent ce risque.

### Règles retenues
| Type de lien | Attribut | Conditions |
|---|---|---|
| Marques de l'éditrice (Djaka, Doctoweb, E-Juriste) | dofollow | 1 lien par article au plus ; uniquement si le sujet s'y prête ; ancre de marque ou d'URL dans au moins 70 % des cas ; jamais dans le menu, le pied de page ou la barre latérale |
| Client de l'agence (interview, cas pratique, citation d'expert, y compris dans une prestation SEO) | dofollow, sans mention | **Décision de l'éditrice du 25/11/2026.** L'article doit rester éditorial : utile sans le lien, pas de ton promotionnel, 1 client par article, sources neutres à côté, ancres variées, jamais proposé sur une plateforme |
| Annonceur extérieur (article vendu) | dofollow + mention « Contenu partenaire » | **Décision de l'éditrice du 25/11/2026.** 2 liens maximum, sujet dans la thématique, au plus 1 article partenaire sur 4 |

### Garde-fous
- **Au moins 50 % des articles ne contiennent aucun lien commercial** (ni marque, ni client, ni annonceur). C'est ce qui fait de a3cdigital.com un média et non un réseau de liens.
- Varier les pages cibles chez les marques : articles de blog, guides, pages services. Ne pas pointer toujours vers la même page commerciale.
- Pas d'échange de liens systématique entre a3cdigital.com et les sites des marques.
- **Transparence** : page « À propos » indiquant que l'éditrice dirige Djaka, Doctoweb et E-Juriste. Mention courte dans les articles concernés : « Doctoweb est une marque de l'éditrice du site. » Cela vous protège aussi juridiquement : un contenu promotionnel présenté comme neutre peut être qualifié de pratique commerciale trompeuse (article L121-1 du code de la consommation).
- Suivi dans `seo/liens-commerciaux.csv` (article, cible, type, attribut, date) pour contrôler les ratios.

## 8. Planning

| Période | Livrables |
|---|---|
| Semaine 0 | P0 sur le site actuel |
| S1–S2 | Mots-clés par rubrique (30–50 sujets), charte, offres, CGV |
| S3–S5 | Maquettes et intégration en préproduction |
| S6 | Migration, réécritures, fusions, pages légales |
| S7 | Mise en ligne |
| M2–M6 | 2 articles/semaine (dont 4–6 guides piliers d'abord), newsletter |
| M4–M6 | Ouverture de l'offre partenaires, inscription sur Getfluence, Paper Club et RocketLinks |

**Indicateurs** : trafic organique, mots-clés en top 10, abonnés à la newsletter, demandes de partenariat.
