<?php
/**
 * Consentement aux cookies : le code Google Analytics inséré par Rank Math n'est pas reconnu
 * par Complianz. On le transforme en script « en attente » (type text/plain, catégorie statistics)
 * que Complianz n'exécute qu'après un clic sur « Accepter ».
 */

defined( 'ABSPATH' ) || exit;

function a3c_complianz_actif() {
	return defined( 'cmplz_version' ) || function_exists( 'cmplz_has_consent' );
}

/**
 * Transforme les balises gtag de Rank Math présentes dans un fragment HTML.
 */
function a3c_bloquer_gtag( $html ) {
	// Script externe : src -> data-cmplz-src, exécution suspendue.
	$html = preg_replace(
		'#<script([^>]*\bid="google_gtagjs")([^>]*)\ssrc="([^"]+)"([^>]*)>#i',
		'<script$1$2 type="text/plain" data-category="statistics" data-service="google-analytics" data-cmplz-src="$3"$4>',
		$html
	);
	// Script de configuration en ligne.
	$html = preg_replace(
		'#<script([^>]*\bid="google_gtagjs-inline")([^>]*)>#i',
		'<script$1$2 type="text/plain" data-category="statistics" data-service="google-analytics">',
		$html
	);
	return $html;
}

/* Capture uniquement la sortie de wp_head (où Rank Math imprime ses balises), pas toute la page. */
add_action(
	'wp_head',
	function () {
		if ( a3c_complianz_actif() && ! is_admin() ) {
			ob_start();
		}
	},
	0
);
add_action(
	'wp_head',
	function () {
		if ( a3c_complianz_actif() && ! is_admin() && ob_get_level() > 0 ) {
			echo a3c_bloquer_gtag( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput -- sortie de wp_head déjà construite par WordPress et ses extensions.
		}
	},
	PHP_INT_MAX
);
