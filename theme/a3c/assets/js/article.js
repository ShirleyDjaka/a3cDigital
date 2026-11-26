// Barre de progression de lecture et sommaire replié sur mobile.
(function () {
  var barre = document.querySelector('.progression');
  if (barre) {
    var maj = function () {
      var h = document.documentElement;
      var max = h.scrollHeight - h.clientHeight;
      barre.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + '%';
    };
    addEventListener('scroll', maj, { passive: true });
    maj();
  }
  var sommaire = document.querySelector('.sommaire');
  if (sommaire && matchMedia('(max-width: 960px)').matches) {
    sommaire.removeAttribute('open');
  }
})();
