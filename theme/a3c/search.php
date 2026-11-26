<?php
/**
 * Résultats de recherche.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="contenu">
	<header class="page-entete">
		<div class="conteneur">
			<h1>Recherche : <?php echo esc_html( get_search_query() ); ?></h1>
			<form class="recherche recherche-page" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="visuellement-cache" for="q2">Rechercher un article</label>
				<input id="q2" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
				<button type="submit">Rechercher</button>
			</form>
		</div>
	</header>
	<div class="rubrique-liste">
		<div class="conteneur">
			<?php if ( have_posts() ) : ?>
				<ul class="liste-articles">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<li>
							<?php a3c_pastille_audience( get_the_ID() ); ?>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="extrait"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</li>
					<?php endwhile; ?>
				</ul>
				<?php the_posts_pagination( array( 'prev_text' => 'Précédent', 'next_text' => 'Suivant' ) ); ?>
			<?php else : ?>
				<p class="aucun-article">Aucun article ne correspond à « <?php echo esc_html( get_search_query() ); ?> ». Essayez un mot plus général, par exemple « statut », « SEO » ou « déontologie ».</p>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
