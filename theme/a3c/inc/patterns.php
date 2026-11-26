<?php
/**
 * Compositions de blocs pour la rédaction (bouton « + » > Compositions > A3C Digital).
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_block_pattern_category( 'a3c', array( 'label' => 'A3C Digital' ) );

		register_block_pattern(
			'a3c/essentiel',
			array(
				'title'       => "L'essentiel en 30 secondes",
				'description' => "Encadré de résumé, à placer en tout début d'article.",
				'categories'  => array( 'a3c' ),
				'content'     => '<!-- wp:group {"className":"essentiel","layout":{"type":"default"}} -->
<div class="wp-block-group essentiel"><!-- wp:heading {"className":"essentiel-titre"} -->
<h2 class="wp-block-heading essentiel-titre">L\'essentiel en 30 secondes</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>Premier point clé.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Deuxième point clé.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Troisième point clé.</li><!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->',
			)
		);

		register_block_pattern(
			'a3c/a-retenir',
			array(
				'title'      => 'Encadré « À retenir »',
				'categories' => array( 'a3c' ),
				'content'    => '<!-- wp:group {"className":"encadre","layout":{"type":"default"}} -->
<div class="wp-block-group encadre"><!-- wp:paragraph {"className":"titre-encadre"} -->
<p class="titre-encadre">À retenir</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Texte de l\'encadré.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
			)
		);

		register_block_pattern(
			'a3c/attention',
			array(
				'title'      => 'Encadré « Attention »',
				'categories' => array( 'a3c' ),
				'content'    => '<!-- wp:group {"className":"encadre encadre-attention","layout":{"type":"default"}} -->
<div class="wp-block-group encadre encadre-attention"><!-- wp:paragraph {"className":"titre-encadre"} -->
<p class="titre-encadre">Attention</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Texte de la mise en garde.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
			)
		);

		register_block_pattern(
			'a3c/mention-marque',
			array(
				'title'       => 'Mention « marque de l\'éditrice »',
				'description' => 'À ajouter quand l\'article cite Djaka, Doctoweb ou E-Juriste.',
				'categories'  => array( 'a3c' ),
				'content'     => '<!-- wp:paragraph {"className":"marque"} -->
<p class="marque">Doctoweb est une marque de l\'éditrice du site.</p>
<!-- /wp:paragraph -->',
			)
		);
	}
);
