<?php
/**
 * Pied de page du site.
 */

defined( 'ABSPATH' ) || exit;

$lien_page = function ( $slug, $libelle ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $page ) ), esc_html( $libelle ) );
	}
};
?>
<footer class="pied">
	<div class="conteneur">
		<div>
			<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">A3C <span>Digital</span></a>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : 'Le média des entrepreneurs, des professionnels de santé et des professions juridiques.' ); ?></p>
		</div>
		<div>
			<h2>Rubriques</h2>
			<ul>
				<?php
				foreach ( a3c_audiences() as $slug => $aud ) {
					$term = get_category_by_slug( $slug );
					if ( $term ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_category_link( $term ) ), esc_html( $aud['court'] ) );
					}
				}
				$guides = get_category_by_slug( 'guides' );
				if ( $guides ) {
					printf( '<li><a href="%s">Guides</a></li>', esc_url( get_category_link( $guides ) ) );
				}
				?>
			</ul>
		</div>
		<?php
		ob_start();
		if ( has_nav_menu( 'pied' ) ) {
			wp_nav_menu( array( 'theme_location' => 'pied', 'container' => false, 'depth' => 1 ) );
		} else {
			$lien_page( 'a-propos', 'À propos' );
			$lien_page( 'charte-editoriale', 'Charte éditoriale' );
			$lien_page( 'partenariats', 'Publier sur A3C Digital' );
			$lien_page( 'newsletter', 'Newsletter' );
		}
		$liens_media = trim( ob_get_clean() );
		if ( $liens_media ) :
			?>
		<div>
			<h2>Le média</h2>
			<?php echo has_nav_menu( 'pied' ) ? $liens_media : '<ul>' . $liens_media . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput -- liens échappés ci-dessus. ?>
		</div>
		<?php endif; ?>
		<div>
			<h2>Informations</h2>
			<ul>
				<?php
				$lien_page( 'contact', 'Contact' );
				$lien_page( 'mentions-legales', 'Mentions légales' );
				$lien_page( 'politique-de-confidentialite', 'Confidentialité' );
				?>
			</ul>
		</div>
		<p class="legal">© <?php echo esc_html( wp_date( 'Y' ) ); ?> A3C Digital, média édité par Djaka.</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
