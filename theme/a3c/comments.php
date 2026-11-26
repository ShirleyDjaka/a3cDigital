<?php
/**
 * Commentaires.
 */

defined( 'ABSPATH' ) || exit;
if ( post_password_required() ) {
	return;
}
?>
<section class="commentaires" id="commentaires" aria-label="Commentaires">
	<?php if ( have_comments() ) : ?>
		<h2><?php echo esc_html( sprintf( _n( '%d commentaire', '%d commentaires', get_comments_number(), 'a3c' ), get_comments_number() ) ); ?></h2>
		<ol>
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'avatar_size' => 0 ) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'          => 'Laisser un commentaire',
			'label_submit'         => 'Publier le commentaire',
			'comment_notes_before' => '<p class="meta">Votre e-mail ne sera pas publié. Les commentaires sont relus avant publication.</p>',
			'fields'               => array(
				'author' => '<p><label for="author">Nom *</label><input id="author" name="author" type="text" required autocomplete="name"></p>',
				'email'  => '<p><label for="email">E-mail *</label><input id="email" name="email" type="email" required autocomplete="email"></p>',
			),
		)
	);
	?>
</section>
