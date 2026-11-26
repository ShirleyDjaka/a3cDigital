<?php
/**
 * Article.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$aud     = a3c_audience();
	$auteur  = (int) get_the_author_meta( 'ID' );
	$bio     = get_the_author_meta( 'description', $auteur );
	// Le contenu est préparé avant l'affichage pour construire le sommaire.
	$contenu = apply_filters( 'the_content', get_the_content() );
	$contenu = str_replace( ']]>', ']]&gt;', $contenu );
	?>
<main id="contenu">
	<div class="bande-article">
		<?php a3c_fil_ariane(); ?>
		<header class="article-entete">
			<div class="conteneur"><div class="entete-texte">
				<?php if ( $aud ) : ?>
					<a class="onglet-article" href="<?php echo esc_url( get_category_link( $aud['term'] ) ); ?>"><?php echo esc_html( $aud['court'] ); ?></a>
				<?php else : ?>
					<span class="onglet-article">A3C Digital</span>
				<?php endif; ?>
				<div class="feuille">
					<?php if ( a3c_est_partenaire() ) : ?>
						<p class="mention-partenaire">Contenu partenaire</p>
					<?php endif; ?>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="chapo"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<div class="signature">
						<span class="initiales" aria-hidden="true"><?php echo esc_html( a3c_initiales( $auteur ) ); ?></span>
						<p><strong><?php the_author_posts_link(); ?></strong>
						<?php
						$fonction = get_the_author_meta( 'a3c_fonction', $auteur );
						if ( $fonction ) {
							echo ', ' . esc_html( $fonction );
						}
						?>
						<br><span class="meta"><?php a3c_meta_article(); ?></span></p>
					</div>
				</div>
			</div></div>
		</header>
	</div>

	<div class="article-corps">
		<div class="conteneur">
			<?php a3c_sommaire(); ?>
			<article <?php post_class( 'contenu' ); ?>>
				<?php
				echo $contenu; // phpcs:ignore WordPress.Security.EscapeOutput -- contenu filtré par the_content.
				wp_link_pages();
				?>

				<section class="bio" aria-label="À propos de l'auteur">
					<span class="initiales" aria-hidden="true"><?php echo esc_html( a3c_initiales( $auteur ) ); ?></span>
					<div>
						<strong><?php the_author(); ?></strong>
						<?php if ( $bio ) : ?>
							<p><?php echo wp_kses_post( $bio ); ?></p>
						<?php endif; ?>
					</div>
				</section>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</article>
		</div>
	</div>

	<?php
	$lies = a3c_articles_lies();
	if ( $lies->have_posts() ) :
		?>
	<section class="lies" aria-labelledby="titre-lies">
		<div class="conteneur">
			<h2 class="section-titre" id="titre-lies">À lire ensuite</h2>
			<ul>
				<?php
				while ( $lies->have_posts() ) :
					$lies->the_post();
					?>
					<li><?php a3c_pastille_audience( get_the_ID() ); ?><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3></li>
				<?php endwhile; wp_reset_postdata(); ?>
			</ul>
			<p class="meta">Vous êtes une entreprise et souhaitez publier un contenu partenaire ? <a href="<?php echo esc_url( home_url( a3c_reglage( 'partenariats_url' ) ) ); ?>">Voir les offres</a></p>
		</div>
	</section>
	<?php endif; ?>
</main>
	<?php
endwhile;
get_footer();
