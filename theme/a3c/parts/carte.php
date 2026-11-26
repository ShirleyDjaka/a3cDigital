<?php
/**
 * Carte d'article (rubriques, recherche). La carte entière est cliquable via le lien du titre.
 */

defined( 'ABSPATH' ) || exit;
$aud = a3c_audience( get_the_ID() );
?>
<li class="carte c-<?php echo esc_attr( $aud ? $aud['slug'] : 'neutre' ); ?>">
	<?php if ( $aud ) : ?>
		<span class="carte-onglet"><?php echo esc_html( $aud['court'] ); ?></span>
	<?php endif; ?>
	<?php if ( a3c_est_partenaire() ) : ?>
		<span class="carte-partenaire">Contenu partenaire</span>
	<?php endif; ?>
	<h2 class="carte-titre"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<p class="carte-extrait"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
	<p class="carte-meta meta"><?php echo (int) a3c_temps_lecture(); ?> min de lecture</p>
</li>
