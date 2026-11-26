<?php
/**
 * Thème A3C Digital : configuration générale.
 */

defined( 'ABSPATH' ) || exit;

define( 'A3C_VERSION', '1.0.0' );

require get_template_directory() . '/inc/audience.php';
require get_template_directory() . '/inc/article.php';
require get_template_directory() . '/inc/accueil.php';
require get_template_directory() . '/inc/patterns.php';

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/main.css' );
		register_nav_menus(
			array(
				'principal' => 'Menu principal',
				'pied'      => 'Pied de page : le média',
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = get_template_directory() . '/assets/css/main.css';
		wp_enqueue_style( 'a3c', get_template_directory_uri() . '/assets/css/main.css', array(), (string) filemtime( $css ) );
		if ( is_singular( 'post' ) ) {
			$js = get_template_directory() . '/assets/js/article.js';
			wp_enqueue_script( 'a3c-article', get_template_directory_uri() . '/assets/js/article.js', array(), (string) filemtime( $js ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		}
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		// Le thème n'utilise pas les styles globaux des blocs « classiques ».
		wp_dequeue_style( 'classic-theme-styles' );
	}
);

/* Précharge la police des titres (affichée au-dessus de la ligne de flottaison). */
add_action(
	'wp_head',
	function () {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_template_directory_uri() . '/assets/fonts/schibsted-grotesk-latin-wght-normal.woff2' )
		);
	},
	1
);

/* Allègements : emojis, oEmbed découverte, générateur. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

/* Classe d'audience sur <body> pour colorer onglets, bandes et barre de lecture. */
add_filter(
	'body_class',
	function ( $classes ) {
		$audience = a3c_audience();
		if ( is_singular( 'post' ) || is_category() ) {
			$classes[] = 'aud-' . ( $audience ? $audience['slug'] : 'neutre' );
		}
		return $classes;
	}
);

/* Extraits plus courts et sans « […] ». */
add_filter( 'excerpt_length', fn() => 32 );
add_filter( 'excerpt_more', fn() => '…' );

/**
 * Menu de secours si aucun menu n'est assigné : les trois rubriques et les guides.
 */
function a3c_menu_secours() {
	foreach ( a3c_audiences() as $slug => $aud ) {
		$term = get_category_by_slug( $slug );
		$url  = $term ? get_category_link( $term ) : home_url( '/' . $slug . '/' );
		printf(
			'<a href="%s"><span class="pastille p-%s" aria-hidden="true"></span>%s</a>',
			esc_url( $url ),
			esc_attr( $slug ),
			esc_html( $aud['court'] )
		);
	}
	$guides = get_category_by_slug( 'guides' );
	printf( '<a href="%s">Guides</a>', esc_url( $guides ? get_category_link( $guides ) : home_url( '/guides/' ) ) );
}

/**
 * Fil d'Ariane : celui de Rank Math s'il est actif, sinon une version simple.
 */
function a3c_fil_ariane() {
	echo '<nav class="fil-ariane conteneur" aria-label="Fil d\'Ariane"><ol>';
	printf( '<li><a href="%s">Accueil</a></li>', esc_url( home_url( '/' ) ) );
	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			$cat       = $cats[0];
			$ancetres  = array_reverse( get_ancestors( $cat->term_id, 'category' ) );
			foreach ( $ancetres as $id ) {
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_category_link( $id ) ), esc_html( get_cat_name( $id ) ) );
			}
			printf( '<li><a href="%s">%s</a></li>', esc_url( get_category_link( $cat ) ), esc_html( $cat->name ) );
		}
	}
	echo '</ol></nav>';
}
