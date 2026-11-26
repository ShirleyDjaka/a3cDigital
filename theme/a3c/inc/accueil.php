<?php
/**
 * Réglages de la page d'accueil, modifiables dans Apparence > Personnaliser > Page d'accueil A3C.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valeurs par défaut des textes modifiables.
 */
function a3c_reglages_defaut() {
	return array(
		'hero_titre'      => 'Développez votre activité, sans vous perdre dans le droit ni dans le digital.',
		'hero_chapo'      => 'Le média des entrepreneurs, des soignants et des juristes : guides pratiques, obligations légales expliquées simplement et marketing compatible avec votre déontologie.',
		'manifeste'       => 'Un média écrit pour celles et ceux qui ont un métier à exercer, et pas de temps à perdre.',
		'newsletter_code' => '',
		'partenariats_url' => '/partenariats/',
		'media_kit_url'   => '',
	);
}

function a3c_reglage( $cle ) {
	$defaut = a3c_reglages_defaut();
	return get_theme_mod( 'a3c_' . $cle, $defaut[ $cle ] ?? '' );
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'a3c_accueil',
			array(
				'title'    => "Page d'accueil A3C",
				'priority' => 30,
			)
		);
		$champs = array(
			'hero_titre'       => array( 'Grand titre', 'textarea' ),
			'hero_chapo'       => array( 'Texte sous le titre', 'textarea' ),
			'manifeste'        => array( 'Phrase « engagements »', 'textarea' ),
			'newsletter_code'  => array( 'Code court du formulaire newsletter (ex. Brevo). Vide = section masquée.', 'text' ),
			'partenariats_url' => array( 'Adresse de la page Partenariats', 'text' ),
			'media_kit_url'    => array( 'Adresse du media kit (PDF). Vide = bouton masqué.', 'url' ),
		);
		foreach ( $champs as $cle => list( $libelle, $type ) ) {
			$wp_customize->add_setting(
				'a3c_' . $cle,
				array(
					'default'           => a3c_reglages_defaut()[ $cle ],
					'sanitize_callback' => 'url' === $type ? 'esc_url_raw' : 'wp_kses_post',
				)
			);
			$wp_customize->add_control(
				'a3c_' . $cle,
				array(
					'label'   => $libelle,
					'section' => 'a3c_accueil',
					'type'    => $type,
				)
			);
		}
	}
);

/**
 * Article « à la une » : le dernier article épinglé, sinon le plus récent.
 */
function a3c_article_une() {
	$epingles = get_option( 'sticky_posts' );
	$args     = array( 'posts_per_page' => 1, 'no_found_rows' => true, 'ignore_sticky_posts' => true );
	if ( $epingles ) {
		$args['post__in'] = $epingles;
	}
	$q = new WP_Query( $args );
	return $q->have_posts() ? $q->posts[0] : null;
}
