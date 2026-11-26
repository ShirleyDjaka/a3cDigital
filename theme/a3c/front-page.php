<?php
/**
 * Page d'accueil.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$recents   = new WP_Query( array( 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
$cat_guide = get_category_by_slug( 'guides' );
$guides    = $cat_guide ? new WP_Query( array( 'cat' => $cat_guide->term_id, 'posts_per_page' => 4, 'no_found_rows' => true ) ) : null;
$newsletter_code = a3c_reglage( 'newsletter_code' );
$media_kit       = a3c_reglage( 'media_kit_url' );
?>
<main id="contenu">
	<section class="hero">
		<div class="conteneur">
			<div>
				<h1><?php echo wp_kses_post( a3c_reglage( 'hero_titre' ) ); ?></h1>
				<p class="chapo"><?php echo wp_kses_post( a3c_reglage( 'hero_chapo' ) ); ?></p>
				<form class="recherche" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="visuellement-cache" for="q">Rechercher un article</label>
					<input id="q" type="search" name="s" placeholder="Statut juridique, avis Google, communication avocat…">
					<button type="submit">Rechercher</button>
				</form>
			</div>

			<ol class="intercalaires" aria-label="Choisir votre rubrique">
				<?php
				foreach ( a3c_audiences() as $slug => $aud ) :
					$term = get_category_by_slug( $slug );
					if ( ! $term ) {
						continue;
					}
					$enfants = get_categories( array( 'parent' => $term->term_id, 'hide_empty' => false ) );
					$sujets  = $enfants ? implode( ', ', wp_list_pluck( $enfants, 'name' ) ) : wp_strip_all_tags( term_description( $term ) );
					?>
					<li class="intercalaire i-<?php echo esc_attr( $slug ); ?>">
						<a href="<?php echo esc_url( get_category_link( $term ) ); ?>">
							<span class="onglet"><?php echo esc_html( $aud['court'] ); ?></span>
							<strong><?php echo esc_html( $aud['titre'] ); ?></strong>
							<span class="sujets"><?php echo esc_html( $sujets ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<?php if ( $recents->have_posts() ) : ?>
	<section class="section section-brume" aria-labelledby="titre-derniers">
		<div class="conteneur">
			<h2 class="section-titre" id="titre-derniers">Les derniers articles</h2>
			<ul class="cartes">
				<?php
				while ( $recents->have_posts() ) :
					$recents->the_post();
					get_template_part( 'parts/carte', null, array( 'niveau' => 'h3' ) );
				endwhile;
				wp_reset_postdata();
				?>
			</ul>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $guides && $guides->have_posts() ) : ?>
	<section class="section section-brume" id="guides" aria-labelledby="titre-guides">
		<div class="conteneur">
			<h2 class="section-titre" id="titre-guides">Les guides à garder sous la main</h2>
			<div class="guides">
				<?php
				while ( $guides->have_posts() ) :
					$guides->the_post();
					?>
					<article class="guide">
						<?php a3c_pastille_audience( get_the_ID() ); ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="section" aria-labelledby="titre-engagements">
		<div class="conteneur engagements">
			<p class="manifeste" id="titre-engagements"><?php echo wp_kses_post( a3c_reglage( 'manifeste' ) ); ?></p>
			<dl>
				<div>
					<dt>Rédigé par des professionnels</dt>
					<dd>Chaque article est signé. Vous savez qui l'a écrit et à quel titre.</dd>
				</div>
				<div>
					<dt>Des sources, des dates</dt>
					<dd>Les textes officiels sont cités, et chaque article indique sa date de mise à jour.</dd>
				</div>
				<div>
					<dt>Les partenariats sont signalés</dt>
					<dd>Un article réalisé avec une entreprise porte la mention « Contenu partenaire », en haut de page.</dd>
				</div>
			</dl>
		</div>
	</section>

	<?php if ( $newsletter_code ) : ?>
	<section class="newsletter" aria-labelledby="titre-newsletter">
		<div class="conteneur">
			<div>
				<h2 id="titre-newsletter">Un e-mail tous les 15 jours, trois articles utiles</h2>
				<p>Choisissez votre rubrique, vous ne recevrez que ce qui vous concerne. Désinscription en un clic.</p>
			</div>
			<div class="form-newsletter"><?php echo do_shortcode( wp_kses_post( $newsletter_code ) ); ?></div>
		</div>
	</section>
	<?php endif; ?>

	<section class="annonceurs" id="annonceurs" aria-labelledby="titre-annonceurs">
		<div class="conteneur">
			<div>
				<h2 id="titre-annonceurs">Vous voulez toucher entrepreneurs, soignants et juristes ?</h2>
				<p>Publiez un article sur un média spécialisé, lu par votre cible, dans une rubrique qui correspond à votre métier.</p>
			</div>
			<div class="actions">
				<a class="btn btn-plein" href="<?php echo esc_url( home_url( a3c_reglage( 'partenariats_url' ) ) ); ?>">Publier un article</a>
				<?php if ( $media_kit ) : ?>
					<a class="btn btn-contour" href="<?php echo esc_url( $media_kit ); ?>">Télécharger le media kit</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="section" aria-labelledby="titre-faq">
		<div class="conteneur">
			<h2 class="section-titre" id="titre-faq">Questions fréquentes</h2>
			<div class="faq">
				<details>
					<summary>Qui écrit les articles ?</summary>
					<p>Des professionnels de chaque secteur, sous la responsabilité de la directrice de la publication. Le nom et la fonction de l'auteur figurent en haut de chaque article.</p>
				</details>
				<details>
					<summary>Les articles sont-ils sponsorisés ?</summary>
					<p>La grande majorité ne l'est pas. Quand un article est réalisé avec une entreprise, il porte la mention « Contenu partenaire » en haut de page.</p>
				</details>
				<details>
					<summary>Comment proposer un article ou une interview ?</summary>
					<p>Écrivez-nous depuis la page contact en précisant votre métier et le sujet. Les contributions d'experts sont relues par la rédaction avant publication.</p>
				</details>
				<details>
					<summary>À quelle fréquence publiez-vous ?</summary>
					<p>Deux nouveaux articles par semaine, et les guides sont mis à jour dès qu'une règle change.</p>
				</details>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
