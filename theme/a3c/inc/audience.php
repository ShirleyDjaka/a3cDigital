<?php
/**
 * Les trois audiences du média (rubriques de premier niveau).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liste des audiences : slug de catégorie => libellés.
 */
function a3c_audiences() {
	return array(
		'entrepreneurs' => array(
			'court' => 'Entrepreneurs',
			'long'  => 'Entrepreneurs',
			'titre' => 'Créer, financer, se faire connaître',
		),
		'sante'         => array(
			'court' => 'Santé',
			'long'  => 'Professionnels de santé',
			'titre' => 'Faire grandir son cabinet dans les règles',
		),
		'juridique'     => array(
			'court' => 'Juridique',
			'long'  => 'Professions juridiques',
			'titre' => 'Attirer des clients sans enfreindre sa déontologie',
		),
	);
}

/**
 * Audience d'un article ou de la catégorie affichée.
 *
 * @param int|WP_Post|null $post Article (par défaut : l'article courant).
 * @return array|null [ 'slug', 'court', 'long', 'titre', 'term' ] ou null.
 */
function a3c_audience( $post = null ) {
	$audiences = a3c_audiences();
	$termes    = array();

	if ( null === $post && is_category() ) {
		$termes = array( get_queried_object() );
	} elseif ( null !== $post || is_singular( 'post' ) || in_the_loop() ) {
		$termes = get_the_category( $post ? get_post( $post )->ID : get_the_ID() );
	}

	foreach ( $termes as $terme ) {
		if ( ! $terme instanceof WP_Term ) {
			continue;
		}
		$racine = $terme;
		while ( $racine->parent ) {
			$racine = get_term( $racine->parent, 'category' );
		}
		if ( isset( $audiences[ $racine->slug ] ) ) {
			return array_merge( $audiences[ $racine->slug ], array( 'slug' => $racine->slug, 'term' => $racine ) );
		}
	}
	return null;
}

/**
 * Pastille d'audience cliquable (utilisée dans les listes d'articles).
 */
function a3c_pastille_audience( $post = null ) {
	$aud = a3c_audience( $post );
	if ( ! $aud ) {
		return;
	}
	printf(
		'<a class="audience a-%s" href="%s">%s</a>',
		esc_attr( $aud['slug'] ),
		esc_url( get_category_link( $aud['term'] ) ),
		esc_html( $aud['court'] )
	);
}
