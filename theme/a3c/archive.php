<?php
/**
 * Rubriques (catégories), étiquettes, auteurs et dates.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$aud    = is_category() ? a3c_audience() : null;
$terme  = get_queried_object();
$racine = $aud ? $aud['term'] : null;
?>
<main id="contenu">
	<header class="rubrique-entete">
		<div class="conteneur">
			<?php if ( $aud ) : ?>
				<span class="onglet-article"><?php echo esc_html( $aud['court'] ); ?></span>
			<?php endif; ?>
			<h1><?php echo esc_html( $aud && $racine->term_id === $terme->term_id ? $aud['long'] : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<?php
			$description = get_the_archive_description();
			if ( $description ) {
				echo '<p>' . esc_html( wp_strip_all_tags( $description ) ) . '</p>';
			}
			if ( $racine ) {
				$enfants = get_categories( array( 'parent' => $racine->term_id, 'hide_empty' => true ) );
				if ( $enfants ) {
					echo '<ul class="sous-rubriques">';
					printf(
						'<li><a href="%s"%s>Tout</a></li>',
						esc_url( get_category_link( $racine ) ),
						$racine->term_id === $terme->term_id ? ' aria-current="page"' : ''
					);
					foreach ( $enfants as $enfant ) {
						printf(
							'<li><a href="%s"%s>%s</a></li>',
							esc_url( get_category_link( $enfant ) ),
							$enfant->term_id === $terme->term_id ? ' aria-current="page"' : '',
							esc_html( $enfant->name )
						);
					}
					echo '</ul>';
				}
			}
			?>
		</div>
	</header>

	<div class="rubrique-liste">
		<div class="conteneur">
			<?php if ( have_posts() ) : ?>
				<?php get_template_part( 'parts/liste-cartes' ); ?>
			<?php else : ?>
				<p class="aucun-article">Aucun article dans cette rubrique pour l'instant.</p>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
