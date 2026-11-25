# Formulaire de contact (Contact Form 7, id 403)

L'API de Contact Form 7 ne permet pas d'enregistrer le formulaire : à coller dans
l'administration WordPress, menu **Contact → Formulaires → « Contact form 1 »**.

## Titre

```
Contact A3C Digital
```

## Onglet « Formulaire » (remplacer tout le contenu)

```
<label>Votre nom *
[text* nom autocomplete:name]</label>

<label>Votre e-mail *
[email* email autocomplete:email]</label>

<label>Vous êtes
[select objet "Lecteur ou lectrice" "Annonceur : publier un article" "Expert : proposer une contribution" "Autre demande"]</label>

<label>Votre message *
[textarea* message]</label>

[acceptance rgpd] J'accepte que mes données soient utilisées pour traiter ma demande, conformément à la <a href="/politique-de-confidentialite/">politique de confidentialité</a>. [/acceptance]

[submit "Envoyer le message"]
```

## Onglet « E-mail »

| Champ | Valeur |
|---|---|
| Destinataire | `contact@djaka.fr` |
| De | `A3C Digital <wordpress@a3cdigital.com>` |
| Objet | `[A3C Digital] [objet] – message de [nom]` |
| En-têtes additionnels | `Reply-To: [email]` |

Corps du message :

```
Nom : [nom]
E-mail : [email]
Objet : [objet]

Message :
[message]

--
Envoyé depuis https://a3cdigital.com/contact/
```

## Onglet « Messages »

Contact Form 7 propose de traduire les messages en français (bouton en haut de l'onglet) si la langue du site est le français.
