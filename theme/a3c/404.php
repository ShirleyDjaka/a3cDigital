<?php
/**
 * Page introuvable.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="contenu">
	<header class="page-entete">
		<div class="conteneur">
			<h1>Cette page n'existe pas ou a été déplacée</h1>
			<p>Cherchez l'article qui vous intéresse, ou repartez d'une rubrique.</p>
			<form class="recherche recherche-page" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="visuellement-cache" for="q404">Rechercher un article</label>
				<input id="q404" type="search" name="s" placeholder="Statut juridique, avis Google…">
				<button type="submit">Rechercher</button>
			</form>
		</div>
	</header>
	<div class="page-corps">
		<div class="conteneur">
			<ul class="liste-articles">
				<?php
				foreach ( a3c_audiences() as $slug => $aud ) {
					$term = get_category_by_slug( $slug );
					if ( $term ) {
						printf(
							'<li><a class="audience a-%1$s" href="%2$s">%3$s</a><h4><a href="%2$s">%4$s</a></h4></li>',
							esc_attr( $slug ),
							esc_url( get_category_link( $term ) ),
							esc_html( $aud['court'] ),
							esc_html( $aud['titre'] )
						);
					}
				}
				?>
			</ul>
		</div>
	</div>
</main>
<?php
get_footer();
