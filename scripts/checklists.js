#!/usr/bin/env node
/**
 * Génère les checklists PDF offertes aux abonnés de la newsletter.
 * Source : contenus/checklists/<profil>.md  ->  theme/a3c/assets/pdf/checklist-<profil>.pdf
 *
 * Syntaxe : « # Titre », ligne suivante = sous-titre, « ## Section », « - [ ] élément », « > note de bas ».
 * Usage : node scripts/checklists.js
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const RACINE = path.resolve(__dirname, '..');
const FONTS = path.join(RACINE, 'theme/a3c/assets/fonts');
const SORTIE = path.join(RACINE, 'theme/a3c/assets/pdf');
const COULEURS = { entrepreneurs: '#E8A817', sante: '#1E7A5A', juridique: '#B0262E' };
const LIBELLES = { entrepreneurs: 'Entrepreneurs', sante: 'Santé', juridique: 'Juridique' };

const police = (f) => fs.readFileSync(path.join(FONTS, f)).toString('base64');
const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

function html(profil, md) {
  const lignes = md.split('\n');
  let titre = '', sous = '', corps = '', note = '', attendSous = false;
  for (const l of lignes) {
    if (l.startsWith('# ')) { titre = l.slice(2); attendSous = true; continue; }
    if (attendSous && l.trim()) { sous = l.trim(); attendSous = false; continue; }
    if (l.startsWith('## ')) { corps += `${corps ? '</ul></section>' : ''}<section><h2>${esc(l.slice(3))}</h2><ul>`; continue; }
    if (l.startsWith('- [ ] ')) { corps += `<li><span class="case"></span><span>${esc(l.slice(6))}</span></li>`; continue; }
    if (l.startsWith('> ')) { note = l.slice(2); }
  }
  corps += '</ul></section>';
  const c = COULEURS[profil];
  return `<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>
@font-face { font-family: "Schibsted"; src: url("data:font/woff2;base64,${police('schibsted-grotesk-latin-wght-normal.woff2')}") format("woff2"); font-weight: 400 900; }
@font-face { font-family: "Schibsted"; src: url("data:font/woff2;base64,${police('schibsted-grotesk-latin-ext-wght-normal.woff2')}") format("woff2"); font-weight: 400 900; unicode-range: U+0100-024F, U+20AC; }
@page { size: A4; margin: 13mm 14mm 16mm; }
* { box-sizing: border-box; }
body { margin: 0; font: 400 9.6pt/1.35 "Schibsted", system-ui, sans-serif; color: #222B3E; }
header { border-top: 8px solid ${c}; padding-top: 10px; margin-bottom: 14px; }
.marque { font: 800 11pt "Schibsted"; color: #16213B; letter-spacing: -0.01em; }
.marque span { color: #4C5568; font-weight: 600; }
.onglet { display: inline-block; margin-left: 8px; padding: 2px 8px; border-radius: 4px; background: ${c}; color: ${profil === 'entrepreneurs' ? '#16213B' : '#fff'}; font: 700 8.5pt "Schibsted"; vertical-align: 1px; }
h1 { font: 800 20pt/1.1 "Schibsted"; letter-spacing: -0.02em; color: #16213B; margin: 12px 0 4px; }
.sous { margin: 0; color: #4C5568; font-size: 11pt; }
section { break-inside: avoid; margin-top: 9px; }
h2 { font: 700 12pt "Schibsted"; color: #16213B; margin: 0 0 6px; padding-bottom: 4px; border-bottom: 2px solid #16213B; }
ul { list-style: none; margin: 0; padding: 0; }
li { display: flex; gap: 9px; padding: 2.5px 0; border-bottom: 1px solid #E6EAF0; }
.case { flex: none; width: 12px; height: 12px; margin-top: 2px; border: 1.6px solid #16213B; border-radius: 2px; }
.note { break-inside: avoid; margin-top: 10px; padding: 8px 10px; background: #F1F3F6; border-radius: 6px; font-size: 8.5pt; color: #4C5568; }
</style></head><body>
<header><span class="marque">A3C <span>Digital</span></span><span class="onglet">${LIBELLES[profil]}</span>
<h1>${esc(titre)}</h1><p class="sous">${esc(sous)}</p></header>
${corps}
${note ? `<p class="note">${esc(note)}</p>` : ''}
</body></html>`;
}

(async () => {
  fs.mkdirSync(SORTIE, { recursive: true });
  const navigateur = await chromium.launch();
  const page = await navigateur.newPage();
  for (const profil of Object.keys(COULEURS)) {
    const md = fs.readFileSync(path.join(RACINE, 'contenus/checklists', `${profil}.md`), 'utf8');
    await page.setContent(html(profil, md), { waitUntil: 'load' });
    await page.evaluate(() => document.fonts.ready);
    const fichier = path.join(SORTIE, `checklist-${profil}.pdf`);
    await page.pdf({
      path: fichier, format: 'A4', printBackground: true, preferCSSPageSize: true, displayHeaderFooter: true,
      headerTemplate: '<span></span>',
      footerTemplate: '<div style="width:100%;margin:0 14mm;font:8px sans-serif;color:#4C5568;display:flex;justify-content:space-between"><span>a3cdigital.com · Information générale, ne remplace pas un conseil personnalisé</span><span><span class="pageNumber"></span>/<span class="totalPages"></span></span></div>',
    });
    console.log('PDF généré :', path.relative(RACINE, fichier));
  }
  await navigateur.close();
})();
