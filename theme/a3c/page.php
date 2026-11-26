<?php
/**
 * Page (mentions légales, contact, partenariats…).
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
<main id="contenu">
	<header class="page-entete">
		<div class="conteneur">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<div class="page-corps">
		<div class="conteneur">
			<div class="contenu">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
</main>
	<?php
endwhile;
get_footer();
