<?php
/**
 * En-tête du site.
 */

defined( 'ABSPATH' ) || exit;
$partenariats = a3c_reglage( 'partenariats_url' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="lien-evitement" href="#contenu">Aller au contenu</a>
<?php if ( is_singular( 'post' ) ) : ?>
	<div class="progression" aria-hidden="true"></div>
<?php endif; ?>

<header class="entete">
	<div class="conteneur">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">A3C <span>Digital</span></a>
		<nav class="nav" aria-label="Navigation principale">
			<?php
			if ( has_nav_menu( 'principal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'principal',
						'container'      => false,
						'depth'          => 1,
					)
				);
			} else {
				a3c_menu_secours();
			}
			?>
		</nav>
		<a class="btn btn-contour" href="<?php echo esc_url( home_url( $partenariats ) ); ?>">Publier un article</a>
		<details class="menu-mobile">
			<summary>Menu</summary>
			<nav aria-label="Navigation mobile">
				<?php
				if ( has_nav_menu( 'principal' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'principal',
							'container'      => false,
							'depth'          => 1,
						)
					);
				} else {
					a3c_menu_secours();
				}
				?>
				<a class="btn btn-contour" href="<?php echo esc_url( home_url( $partenariats ) ); ?>">Publier un article</a>
			</nav>
		</details>
	</div>
</header>
