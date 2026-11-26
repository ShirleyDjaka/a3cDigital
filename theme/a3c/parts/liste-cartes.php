<?php
/**
 * Grille de cartes + bouton « Charger plus d'articles » (lien réel vers la page suivante).
 */

defined( 'ABSPATH' ) || exit;
global $wp_query;
$total   = (int) $wp_query->found_posts;
$page    = max( 1, (int) get_query_var( 'paged' ) );
$affiche = min( $total, $page * (int) $wp_query->get( 'posts_per_page' ) );
?>
<ul class="cartes" id="cartes">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'parts/carte', null, array( 'niveau' => $args['niveau'] ?? 'h2' ) );
	endwhile;
	?>
</ul>
<div class="charger-plus-zone">
	<p class="meta compteur" aria-live="polite"><?php echo esc_html( sprintf( '%d article%s sur %d', $affiche, $affiche > 1 ? 's' : '', $total ) ); ?></p>
	<?php if ( $page < (int) $wp_query->max_num_pages ) : ?>
		<a class="btn btn-plein charger-plus" href="<?php echo esc_url( get_pagenum_link( $page + 1 ) ); ?>" data-total="<?php echo esc_attr( $total ); ?>">Charger plus d'articles</a>
	<?php endif; ?>
</div>
