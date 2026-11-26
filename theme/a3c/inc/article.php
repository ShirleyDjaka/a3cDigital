<?php
/**
 * Fonctions de l'article : temps de lecture, sommaire automatique, contenu partenaire.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Temps de lecture estimé (230 mots/minute).
 */
function a3c_temps_lecture( $post = null ) {
	$texte = trim( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) );
	$mots  = '' === $texte ? 0 : count( preg_split( '/\s+/u', $texte ) );
	return max( 1, (int) round( $mots / 230 ) );
}

/**
 * Ligne de méta : date de mise à jour et temps de lecture.
 */
function a3c_meta_article( $post = null ) {
	$post = get_post( $post );
	printf(
		'Mis à jour le <time datetime="%s">%s</time>, %d min de lecture',
		esc_attr( get_the_modified_date( 'c', $post ) ),
		esc_html( get_the_modified_date( 'j F Y', $post ) ),
		(int) a3c_temps_lecture( $post )
	);
}

/**
 * L'article est-il un contenu partenaire (étiquette « partenariat ») ?
 */
function a3c_est_partenaire( $post = null ) {
	return has_tag( 'partenariat', $post );
}

/**
 * Ajoute des ancres aux intertitres H2 et mémorise le sommaire.
 * Les H2 placés dans les encadrés (« L'essentiel », etc.) sont ignorés.
 */
function a3c_preparer_sommaire( $html ) {
	$GLOBALS['a3c_sommaire'] = array();
	$utilises                = array();

	return preg_replace_callback(
		'#<h2(?![^>]*\bdata-sommaire="non")([^>]*)>(.*?)</h2>#is',
		function ( $m ) use ( &$utilises ) {
			$attrs = $m[1];
			$texte = trim( wp_strip_all_tags( $m[2] ) );
			if ( '' === $texte || false !== stripos( $attrs, 'essentiel' ) ) {
				return $m[0];
			}
			if ( preg_match( '#\bid="([^"]+)"#', $attrs, $id ) ) {
				$ancre = $id[1];
			} else {
				$ancre = sanitize_title( $texte );
				$base  = $ancre;
				$n     = 2;
				while ( isset( $utilises[ $ancre ] ) ) {
					$ancre = $base . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $ancre ) . '"';
			}
			$utilises[ $ancre ]        = true;
			$GLOBALS['a3c_sommaire'][] = array( 'ancre' => $ancre, 'texte' => preg_replace( '#^\d+\.\s*#', '', $texte ) );
			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$html
	);
}

add_filter(
	'the_content',
	function ( $html ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $html;
		}
		return a3c_preparer_sommaire( $html );
	},
	20
);

/**
 * Affiche le sommaire (au moins 3 intertitres).
 */
function a3c_sommaire() {
	$items = $GLOBALS['a3c_sommaire'] ?? array();
	if ( count( $items ) < 3 ) {
		return;
	}
	echo '<details class="sommaire" open><summary>Sommaire</summary><ol>';
	foreach ( $items as $item ) {
		printf( '<li><a href="#%s">%s</a></li>', esc_attr( $item['ancre'] ), esc_html( $item['texte'] ) );
	}
	echo '</ol></details>';
}

/**
 * Initiales de l'auteur pour l'avatar typographique.
 */
function a3c_initiales( $user_id ) {
	$nom = trim( get_the_author_meta( 'first_name', $user_id ) . ' ' . get_the_author_meta( 'last_name', $user_id ) );
	if ( '' === $nom ) {
		$nom = get_the_author_meta( 'display_name', $user_id );
	}
	$mots = preg_split( '/\s+/', $nom );
	$ini  = '';
	foreach ( array_slice( $mots, 0, 2 ) as $mot ) {
		$ini .= mb_strtoupper( mb_substr( $mot, 0, 1 ) );
	}
	return $ini;
}

/**
 * Articles liés : même audience, hors article courant.
 */
function a3c_articles_lies( $nombre = 3 ) {
	$aud  = a3c_audience();
	$args = array(
		'post__not_in'        => array( get_the_ID() ),
		'posts_per_page'      => $nombre,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $aud ) {
		$args['cat'] = $aud['term']->term_id;
	}
	return new WP_Query( $args );
}
