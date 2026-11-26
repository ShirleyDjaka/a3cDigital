<?php
/**
 * Page auteur : présentation de l'auteur réel et liste de ses articles.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$auteur   = get_queried_object();
$id       = (int) $auteur->ID;
$fonction = get_the_author_meta( 'a3c_fonction', $id );
$bio      = get_the_author_meta( 'a3c_bio_longue', $id );
$bio      = $bio ? $bio : get_the_author_meta( 'description', $id );
?>
<main id="contenu">
	<header class="rubrique-entete auteur-entete">
		<div class="conteneur">
			<span class="initiales initiales-grandes" aria-hidden="true"><?php echo esc_html( a3c_initiales( $id ) ); ?></span>
			<div>
				<span class="onglet-article">Auteur</span>
				<h1><?php echo esc_html( $auteur->display_name ); ?></h1>
				<?php if ( $fonction ) : ?>
					<p class="auteur-fonction"><?php echo esc_html( ucfirst( $fonction ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<?php if ( $bio ) : ?>
	<section class="auteur-bio" aria-label="Biographie">
		<div class="conteneur"><div class="contenu">
			<?php echo wp_kses_post( wpautop( $bio ) ); ?>
			<?php
			$charte = get_page_by_path( 'charte-editoriale' );
			if ( $charte && 'publish' === $charte->post_status ) :
				?>
				<p class="meta">Nos articles suivent une <a href="<?php echo esc_url( get_permalink( $charte ) ); ?>">charte éditoriale</a> : sources officielles vérifiées, dates de mise à jour et identification des contenus partenaires.</p>
			<?php endif; ?>
		</div></div>
	</section>
	<?php endif; ?>

	<div class="rubrique-liste">
		<div class="conteneur">
			<h2 class="section-titre">Ses articles</h2>
			<?php if ( have_posts() ) : ?>
				<?php get_template_part( 'parts/liste-cartes', null, array( 'niveau' => 'h3' ) ); ?>
			<?php else : ?>
				<p class="aucun-article">Aucun article publié pour l'instant.</p>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
