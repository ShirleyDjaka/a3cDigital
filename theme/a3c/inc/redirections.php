<?php
/**
 * Redirections 301 des anciennes adresses (ancienne arborescence de catégories et pages supprimées).
 * Source : seo/redirections.csv. Seules les lignes dont la destination existe déjà sont actives ici ;
 * les renommages d'articles seront ajoutés au moment de leur réécriture.
 */

defined( 'ABSPATH' ) || exit;

function a3c_redirections() {
	return array(
		'/category/business/'                                => '/category/entrepreneurs/',
		'/category/business/juridique/'                      => '/category/entrepreneurs/obligations-juridique/',
		'/category/business/investissement/'                 => '/category/entrepreneurs/financer-investir/',
		'/category/entrepreneuriat/'                         => '/category/entrepreneurs/',
		'/category/entrepreneuriat/finance/'                 => '/category/entrepreneurs/financer-investir/',
		'/category/entrepreneuriat/emploi-formation/'        => '/category/entrepreneurs/creer-son-entreprise/',
		'/category/entrepreneuriat/carriere-vie-professionnelle/' => '/category/entrepreneurs/creer-son-entreprise/',
		'/category/marketing/'                               => '/category/entrepreneurs/marketing-visibilite/',
		'/category/marketing/communication/'                 => '/category/entrepreneurs/marketing-visibilite/',
		'/category/marketing/digital/'                       => '/category/entrepreneurs/marketing-visibilite/',
		'/category/uncategorized/'                           => '/',
		'/business/'                                         => '/category/entrepreneurs/',
		'/marketing/'                                        => '/category/entrepreneurs/marketing-visibilite/',
		'/entreprenariat/'                                   => '/category/entrepreneurs/',
		'/partenariats/'                                     => '/publier-un-article/',
		// Articles réécrits (anciens slugs numérotés, fautes ou année dans l'URL).
		'/business-1/'                                       => '/debuter-investissement-erreurs-a-eviter/',
		'/business-2/'                                       => '/e-commerce-droit-vendre-en-ligne-legalement/',
		'/business-3/'                                       => '/creer-entreprise-etapes-legales/',
		'/business-4/'                                       => '/investissement-passif-vs-actif/',
		'/entrepreneuriat-4/'                                => '/construire-reseau-professionnel/',
		'/micro-influenceurs-secret-des-marques-qui-cartonnes-2025/' => '/micro-influenceurs-marketing/',
		'/le-personal-branding-est-il-entrain-de-remplacer-le-cv/'   => '/personal-branding-vs-cv/',
		'/les-aides-financieres-et-subventions-pour-les-entrepreneurs-en-2025/' => '/aides-financieres-entrepreneurs/',
	);
}

add_action(
	'template_redirect',
	function () {
		$chemin = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$chemin = '/' . trim( rawurldecode( $chemin ), '/' ) . '/';
		// Pagination des anciennes catégories : /category/business/page/2/ -> nouvelle rubrique.
		$chemin = preg_replace( '#page/\d+/$#', '', $chemin );
		$table  = a3c_redirections();
		if ( isset( $table[ $chemin ] ) ) {
			wp_safe_redirect( home_url( $table[ $chemin ] ), 301, 'A3C Digital' );
			exit;
		}
	},
	1
);

/* Après chaque nouvelle version du thème : régénère les règles d'adresses (sitemap, rubriques). */
add_action(
	'init',
	function () {
		if ( get_option( 'a3c_version_installee' ) !== A3C_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'a3c_version_installee', A3C_VERSION, false );
		}
	},
	99
);
