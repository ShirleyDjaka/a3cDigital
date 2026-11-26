<?php
/**
 * Newsletter : formulaire [a3c_newsletter], double confirmation par e-mail (double opt-in),
 * liste des abonnés dans l'administration (menu « Abonnés newsletter ») et export CSV
 * pour un outil d'envoi (Brevo, Mailchimp…).
 *
 * Le formulaire ne contient pas de jeton de sécurité : il reste compatible avec le cache des pages.
 * La protection repose sur un champ piège, une limite d'envois par adresse IP et la confirmation par e-mail.
 */

defined( 'ABSPATH' ) || exit;

/** Rubriques proposées à l'inscription. */
function a3c_newsletter_rubriques() {
	return array(
		'entrepreneurs' => 'Je suis entrepreneur',
		'sante'         => 'Je suis professionnel de santé',
		'juridique'     => 'Je suis avocat, notaire ou juriste',
	);
}

/** Statuts d'un abonné. */
function a3c_newsletter_statuts() {
	return array(
		'attente'    => 'En attente de confirmation',
		'confirme'   => 'Confirmé',
		'desinscrit' => 'Désinscrit',
	);
}

/* Type de contenu privé : un abonné par adresse e-mail. */
add_action(
	'init',
	function () {
		register_post_type(
			'a3c_abonne',
			array(
				'labels'          => array(
					'name'          => 'Abonnés newsletter',
					'singular_name' => 'Abonné',
					'menu_name'     => 'Abonnés newsletter',
					'edit_item'     => 'Abonné',
					'search_items'  => 'Rechercher un abonné',
					'not_found'     => 'Aucun abonné pour le moment.',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_rest'    => false,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 27,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

/** Retrouve un abonné par e-mail ou par jeton. */
function a3c_newsletter_trouver( $cle, $valeur ) {
	$ids = get_posts(
		array(
			'post_type'      => 'a3c_abonne',
			'post_status'    => 'private',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_a3c_' . $cle, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $valeur, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/** Page de retour (page « Newsletter » si elle existe, sinon l'accueil). */
function a3c_newsletter_page() {
	$page = get_page_by_path( 'newsletter' );
	return ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : home_url( '/' );
}

/** Lien de confirmation ou de désinscription. */
function a3c_newsletter_lien( $action, $jeton ) {
	return add_query_arg( array( 'a3c_nl' => $action, 'jeton' => $jeton ), home_url( '/' ) );
}

/* Inscription (POST). */
add_action(
	'template_redirect',
	function () {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['a3c_newsletter'] ) ) {
			return;
		}
		$retour = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['a3c_retour'] ?? '' ) ), home_url( '/' ) );
		$aller  = function ( $etat ) use ( $retour ) {
			wp_safe_redirect( add_query_arg( 'newsletter', $etat, $retour ) . '#newsletter' );
			exit;
		};

		// Champ piège rempli : on fait comme si tout allait bien.
		if ( ! empty( $_POST['a3c_site_confirm'] ) ) {
			$aller( 'attente' );
		}
		// Au plus 5 inscriptions par heure et par adresse IP.
		$ip    = 'a3c_nl_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) . wp_salt() );
		$essai = (int) get_transient( $ip );
		if ( $essai >= 5 ) {
			$aller( 'limite' );
		}
		set_transient( $ip, $essai + 1, HOUR_IN_SECONDS );

		$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$rubrique = sanitize_key( $_POST['rubrique'] ?? '' );
		if ( ! is_email( $email ) ) {
			$aller( 'email' );
		}
		if ( ! isset( a3c_newsletter_rubriques()[ $rubrique ] ) ) {
			$rubrique = 'entrepreneurs';
		}
		$email = strtolower( $email );

		$id = a3c_newsletter_trouver( 'email', $email );
		if ( $id && 'confirme' === get_post_meta( $id, '_a3c_statut', true ) ) {
			// Déjà abonné : même message, sans révéler l'information à un tiers.
			$aller( 'attente' );
		}
		if ( ! $id ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'a3c_abonne',
					'post_status' => 'private',
					'post_title'  => $email,
				)
			);
			if ( ! $id || is_wp_error( $id ) ) {
				$aller( 'erreur' );
			}
			update_post_meta( $id, '_a3c_email', $email );
		}
		$jeton = wp_generate_password( 32, false );
		update_post_meta( $id, '_a3c_jeton', $jeton );
		update_post_meta( $id, '_a3c_statut', 'attente' );
		update_post_meta( $id, '_a3c_rubrique', $rubrique );
		update_post_meta( $id, '_a3c_date_demande', current_time( 'mysql' ) );
		update_post_meta( $id, '_a3c_source', esc_url_raw( $retour ) );

		$message = sprintf(
			"Bonjour,\n\nVous avez demandé à recevoir la newsletter d'A3C Digital (%s).\n\nPour confirmer votre inscription, cliquez sur ce lien :\n%s\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail : vous ne recevrez rien.\n\nA3C Digital\n%s",
			a3c_newsletter_rubriques()[ $rubrique ],
			a3c_newsletter_lien( 'confirmer', $jeton ),
			home_url( '/' )
		);
		wp_mail( $email, 'Confirmez votre inscription à la newsletter A3C Digital', $message );

		$aller( 'attente' );
	}
);

/* Confirmation et désinscription (liens reçus par e-mail). */
add_action(
	'template_redirect',
	function () {
		$action = isset( $_GET['a3c_nl'] ) ? sanitize_key( $_GET['a3c_nl'] ) : '';
		if ( ! in_array( $action, array( 'confirmer', 'desinscrire' ), true ) ) {
			return;
		}
		nocache_headers();
		$jeton = isset( $_GET['jeton'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_GET['jeton'] ) ) : '';
		$id    = strlen( $jeton ) === 32 ? a3c_newsletter_trouver( 'jeton', $jeton ) : 0;
		$page  = a3c_newsletter_page();
		if ( ! $id ) {
			wp_safe_redirect( add_query_arg( 'newsletter', 'lien', $page ) . '#newsletter' );
			exit;
		}
		if ( 'confirmer' === $action ) {
			update_post_meta( $id, '_a3c_statut', 'confirme' );
			update_post_meta( $id, '_a3c_date_confirmation', current_time( 'mysql' ) );
			wp_safe_redirect( add_query_arg( 'newsletter', 'confirmee', $page ) . '#newsletter' );
		} else {
			update_post_meta( $id, '_a3c_statut', 'desinscrit' );
			update_post_meta( $id, '_a3c_date_desinscription', current_time( 'mysql' ) );
			wp_safe_redirect( add_query_arg( 'newsletter', 'desinscrit', $page ) . '#newsletter' );
		}
		exit;
	},
	2
);

/* Les réponses (?newsletter=…) ne sont jamais mises en cache. */
add_action(
	'template_redirect',
	function () {
		if ( isset( $_GET['newsletter'] ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
		}
	},
	3
);

/**
 * Formulaire : [a3c_newsletter variante="bandeau|encart" titre="…" texte="…"]
 * La rubrique est présélectionnée selon l'audience de la page.
 */
add_shortcode(
	'a3c_newsletter',
	function ( $atts ) {
		static $n = 0;
		++$n;
		$atts = shortcode_atts(
			array(
				'variante' => 'encart',
				'titre'    => 'Recevez nos articles utiles',
				'texte'    => 'Une sélection d\'articles pratiques, deux fois par mois. Désinscription en un clic.',
			),
			$atts,
			'a3c_newsletter'
		);
		$aud      = function_exists( 'a3c_audience' ) ? a3c_audience() : null;
		$defaut   = $aud ? $aud['slug'] : 'entrepreneurs';
		$retour   = is_singular() ? get_permalink() : home_url( trailingslashit( (string) ( $GLOBALS['wp']->request ?? '' ) ) );
		$etat     = isset( $_GET['newsletter'] ) ? sanitize_key( $_GET['newsletter'] ) : '';
		$messages = array(
			'attente'    => array( 'ok', '<strong>Presque terminé !</strong> Nous venons de vous envoyer un e-mail : cliquez sur le lien qu\'il contient pour confirmer votre inscription.' ),
			'confirmee'  => array( 'ok', '<strong>Inscription confirmée.</strong> Merci, vous recevrez notre prochaine newsletter.' ),
			'desinscrit' => array( 'ok', '<strong>Vous êtes désinscrit.</strong> Vous ne recevrez plus la newsletter.' ),
			'email'      => array( 'erreur', '<strong>Adresse e-mail invalide.</strong> Merci de vérifier votre saisie.' ),
			'limite'     => array( 'erreur', '<strong>Trop de tentatives.</strong> Merci de réessayer dans une heure.' ),
			'lien'       => array( 'erreur', '<strong>Ce lien n\'est plus valide.</strong> Inscrivez-vous de nouveau ci-dessous.' ),
			'erreur'     => array( 'erreur', '<strong>Une erreur est survenue.</strong> Merci de réessayer.' ),
		);
		$classe   = 'bandeau' === $atts['variante'] ? 'form-newsletter' : 'newsletter-inline';
		$id       = 'nl-' . $n;
		$cible    = 1 === $n ? ' id="newsletter"' : '';

		ob_start();
		if ( 'bandeau' !== $atts['variante'] ) {
			printf( '<aside class="newsletter-inline"%s aria-label="Newsletter"><strong>%s</strong>%s', $cible, esc_html( $atts['titre'] ), esc_html( $atts['texte'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			printf( '<div class="form-newsletter-zone"%s>', $cible ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( isset( $messages[ $etat ] ) ) {
			printf( '<p class="nl-message nl-%s" role="status">%s</p>', esc_attr( $messages[ $etat ][0] ), wp_kses( $messages[ $etat ][1], array( 'strong' => array() ) ) );
		}
		if ( ! in_array( $etat, array( 'attente', 'confirmee' ), true ) ) :
			?>
			<form class="<?php echo esc_attr( $classe ); ?>" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="a3c_newsletter" value="1">
				<input type="hidden" name="a3c_retour" value="<?php echo esc_url( $retour ); ?>">
				<span class="champ-piege" aria-hidden="true"><label>Ne pas remplir <input type="text" name="a3c_site_confirm" tabindex="-1" autocomplete="off"></label></span>
				<div class="ligne">
					<label class="visuellement-cache" for="<?php echo esc_attr( $id ); ?>-email">Votre e-mail</label>
					<input id="<?php echo esc_attr( $id ); ?>-email" name="email" type="email" required placeholder="Votre e-mail" autocomplete="email">
					<label class="visuellement-cache" for="<?php echo esc_attr( $id ); ?>-rubrique">Votre profil</label>
					<select id="<?php echo esc_attr( $id ); ?>-rubrique" name="rubrique">
						<?php foreach ( a3c_newsletter_rubriques() as $cle => $libelle ) : ?>
							<option value="<?php echo esc_attr( $cle ); ?>" <?php selected( $defaut, $cle ); ?>><?php echo esc_html( $libelle ); ?></option>
						<?php endforeach; ?>
					</select>
					<button class="btn <?php echo 'bandeau' === $atts['variante'] ? '' : 'btn-plein'; ?>" type="submit">Je m'inscris</button>
				</div>
				<p class="nl-mention">Vous recevrez un e-mail pour confirmer. Vos données ne servent qu'à l'envoi de la newsletter ; désinscription à tout moment. <a href="<?php echo esc_url( home_url( '/politique-de-confidentialite/' ) ); ?>">Politique de confidentialité</a>.</p>
			</form>
			<?php
		endif;
		echo 'bandeau' !== $atts['variante'] ? '</aside>' : '</div>';
		return ob_get_clean();
	}
);

/* Administration : colonnes, filtre par statut et export CSV. */
add_filter(
	'manage_a3c_abonne_posts_columns',
	function ( $colonnes ) {
		return array(
			'cb'       => $colonnes['cb'],
			'title'    => 'E-mail',
			'rubrique' => 'Profil',
			'statut'   => 'Statut',
			'date'     => 'Créé le',
		);
	}
);
add_action(
	'manage_a3c_abonne_posts_custom_column',
	function ( $colonne, $post_id ) {
		$val = get_post_meta( $post_id, '_a3c_' . $colonne, true );
		$ref = 'rubrique' === $colonne ? a3c_newsletter_rubriques() : a3c_newsletter_statuts();
		echo esc_html( $ref[ $val ] ?? $val );
	},
	10,
	2
);
add_action(
	'manage_posts_extra_tablenav',
	function ( $ou ) {
		if ( 'top' === $ou && 'a3c_abonne' === ( get_current_screen()->post_type ?? '' ) && current_user_can( 'manage_options' ) ) {
			printf(
				'<a class="button" href="%s">Exporter les abonnés confirmés (CSV)</a>',
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=a3c_export_abonnes' ), 'a3c_export_abonnes' ) )
			);
		}
	}
);
add_action(
	'admin_post_a3c_export_abonnes',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'a3c_export_abonnes' ) ) {
			wp_die( 'Accès refusé.' );
		}
		$ids = get_posts(
			array(
				'post_type'      => 'a3c_abonne',
				'post_status'    => 'private',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_a3c_statut', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'confirme', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="abonnes-newsletter-' . wp_date( 'Y-m-d' ) . '.csv"' );
		$sortie = fopen( 'php://output', 'w' );
		fputcsv( $sortie, array( 'email', 'profil', 'date_confirmation', 'lien_desinscription' ) );
		foreach ( $ids as $id ) {
			fputcsv(
				$sortie,
				array(
					get_post_meta( $id, '_a3c_email', true ),
					get_post_meta( $id, '_a3c_rubrique', true ),
					get_post_meta( $id, '_a3c_date_confirmation', true ),
					a3c_newsletter_lien( 'desinscrire', get_post_meta( $id, '_a3c_jeton', true ) ),
				)
			);
		}
		fclose( $sortie ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/* Les abonnés en attente depuis plus de 30 jours sont supprimés (aucune donnée gardée sans confirmation). */
add_action(
	'a3c_newsletter_menage',
	function () {
		$ids = get_posts(
			array(
				'post_type'      => 'a3c_abonne',
				'post_status'    => 'private',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'date_query'     => array( array( 'before' => '30 days ago' ) ),
				'meta_key'       => '_a3c_statut', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'attente', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}
);
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'a3c_newsletter_menage' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'a3c_newsletter_menage' );
		}
	}
);
