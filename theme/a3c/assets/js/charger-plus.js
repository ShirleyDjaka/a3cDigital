// « Charger plus d'articles » : récupère la page suivante et ajoute ses cartes.
// Sans JavaScript, le bouton reste un lien normal vers /page/2/, /page/3/…
(function () {
  document.addEventListener('click', function (e) {
    var bouton = e.target.closest('.charger-plus');
    if (!bouton || bouton.getAttribute('aria-busy') === 'true') return;
    e.preventDefault();

    var liste = document.getElementById('cartes');
    var compteur = document.querySelector('.compteur');
    var libelle = bouton.textContent;
    bouton.setAttribute('aria-busy', 'true');
    bouton.textContent = 'Chargement…';

    fetch(bouton.href, { credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) throw new Error(r.status);
        return r.text();
      })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var nouvelles = doc.querySelectorAll('#cartes > li');
        var premierLien = null;
        nouvelles.forEach(function (li) {
          var copie = document.importNode(li, true);
          liste.appendChild(copie);
          if (!premierLien) premierLien = copie.querySelector('a');
        });
        // Le focus va sur le premier nouvel article (navigation au clavier).
        if (premierLien) premierLien.focus({ preventScroll: true });

        var total = bouton.getAttribute('data-total');
        if (compteur) compteur.textContent = liste.children.length + ' articles sur ' + total;

        var suivant = doc.querySelector('.charger-plus');
        if (suivant) {
          bouton.href = suivant.href;
          bouton.textContent = libelle;
          bouton.removeAttribute('aria-busy');
        } else {
          bouton.remove();
        }
      })
      .catch(function () {
        window.location.href = bouton.href;
      });
  });
})();
