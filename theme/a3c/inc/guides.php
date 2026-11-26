<?php
/**
 * Guides piliers et pages auteur.
 *
 * - [a3c_articles ids="12,34,56" titre="…"] : liste d'articles à lire. Seuls les articles déjà publiés
 *   s'affichent : un guide peut citer des articles programmés, ils apparaissent le jour de leur publication.
 * - Les pages auteur sont indexables (elles présentent l'auteur réel des articles).
 */

defined( 'ABSPATH' ) || exit;

add_shortcode(
	'a3c_articles',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'ids' => '', 'titre' => '' ), $atts, 'a3c_articles' );
		$ids  = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
		if ( ! $ids ) {
			return '';
		}
		$q = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'post__in'            => $ids,
				'orderby'             => 'post__in',
				'posts_per_page'      => count( $ids ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( ! $q->have_posts() ) {
			return '';
		}
		ob_start();
		echo '<div class="a-lire">';
		if ( $atts['titre'] ) {
			printf( '<p class="a-lire-titre">%s</p>', esc_html( $atts['titre'] ) );
		}
		echo '<ul>';
		while ( $q->have_posts() ) {
			$q->the_post();
			printf(
				'<li><a href="%s">%s</a><span>%s</span></li>',
				esc_url( get_permalink() ),
				esc_html( get_the_title() ),
				esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) )
			);
		}
		echo '</ul></div>';
		wp_reset_postdata();
		return ob_get_clean();
	}
);

/* Pages auteur indexables (Rank Math les place en noindex par défaut). */
add_filter(
	'rank_math/frontend/robots',
	function ( $robots ) {
		if ( is_author() && have_posts() ) {
			$robots['index'] = 'index';
		}
		return $robots;
	}
);

/* Biographie détaillée (page auteur) : champ supplémentaire dans le profil. */
$a3c_champ_bio_longue = function ( $user ) {
	printf(
		'<table class="form-table"><tr><th><label for="a3c_bio_longue">Biographie détaillée (page auteur)</label></th><td><textarea id="a3c_bio_longue" name="a3c_bio_longue" rows="8" class="large-text">%s</textarea><p class="description">Parcours, expertise, domaines traités. Une ligne vide = nouveau paragraphe. Vide = la biographie courte est utilisée.</p></td></tr></table>',
		esc_textarea( get_user_meta( $user->ID, 'a3c_bio_longue', true ) )
	);
};
add_action( 'show_user_profile', $a3c_champ_bio_longue, 11 );
add_action( 'edit_user_profile', $a3c_champ_bio_longue, 11 );
$a3c_enregistrer_bio_longue = function ( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['a3c_bio_longue'] ) && check_admin_referer( 'update-user_' . $user_id ) ) {
		update_user_meta( $user_id, 'a3c_bio_longue', sanitize_textarea_field( wp_unslash( $_POST['a3c_bio_longue'] ) ) );
	}
};
add_action( 'personal_options_update', $a3c_enregistrer_bio_longue );
add_action( 'edit_user_profile_update', $a3c_enregistrer_bio_longue );
add_action(
	'init',
	function () {
		register_meta(
			'user',
			'a3c_bio_longue',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => fn( $autorise, $cle, $user_id ) => current_user_can( 'edit_user', $user_id ),
			)
		);
	}
);
