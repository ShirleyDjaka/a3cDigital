<?php
/**
 * Demandes de publication (annonceurs) : formulaire [a3c_demande_publication],
 * enregistrement dans l'administration (menu « Demandes de publication ») et e-mail à l'éditrice.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Choix proposés dans le formulaire.
 */
function a3c_demande_choix() {
	return array(
		'rubrique' => array(
			'entrepreneurs' => 'Entrepreneurs',
			'sante'         => 'Professionnels de santé',
			'juridique'     => 'Professions juridiques',
			'conseil'       => 'Je ne sais pas, conseillez-moi',
		),
		'format'   => array(
			'redaction' => 'Article rédigé par la rédaction',
			'fourni'    => 'Article fourni par mes soins (relu par la rédaction)',
			'mention'   => 'Mention dans un article existant',
		),
	);
}

/* Type de contenu privé : visible uniquement dans l'administration. */
add_action(
	'init',
	function () {
		register_post_type(
			'a3c_demande',
			array(
				'labels'          => array(
					'name'          => 'Demandes de publication',
					'singular_name' => 'Demande de publication',
					'menu_name'     => 'Demandes de publication',
					'edit_item'     => 'Demande de publication',
					'search_items'  => 'Rechercher une demande',
					'not_found'     => 'Aucune demande pour le moment.',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_rest'    => true, // privées : lisibles uniquement par un administrateur authentifié.
				'rest_base'       => 'demandes',
				'menu_icon'       => 'dashicons-megaphone',
				'menu_position'   => 26,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

/* Colonnes utiles dans la liste des demandes. */
add_filter(
	'manage_a3c_demande_posts_columns',
	function ( $colonnes ) {
		return array(
			'cb'       => $colonnes['cb'],
			'title'    => 'Demande',
			'email'    => 'E-mail',
			'rubrique' => 'Rubrique',
			'format'   => 'Format',
			'date'     => 'Reçue le',
		);
	}
);
add_action(
	'manage_a3c_demande_posts_custom_column',
	function ( $colonne, $post_id ) {
		$choix = a3c_demande_choix();
		$val   = get_post_meta( $post_id, '_a3c_' . $colonne, true );
		if ( 'email' === $colonne ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $val ) );
		} elseif ( isset( $choix[ $colonne ][ $val ] ) ) {
			echo esc_html( $choix[ $colonne ][ $val ] );
		}
	},
	10,
	2
);

/**
 * Traitement du formulaire (avant tout affichage, puis redirection : pas de double envoi).
 */
add_action(
	'template_redirect',
	function () {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['a3c_demande'] ) ) {
			return;
		}
		// Retour sur la page du formulaire (wp_get_referer() est vide quand on reste sur la même page).
		$retour = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( '/' );

		if ( ! isset( $_POST['a3c_jeton'] ) || ! wp_verify_nonce( sanitize_key( $_POST['a3c_jeton'] ), 'a3c_demande' ) ) {
			wp_safe_redirect( add_query_arg( 'demande', 'expiree', $retour ) . '#demande' );
			exit;
		}

		// Pièges à robots : champ invisible rempli, ou formulaire envoyé en moins de 4 secondes.
		$debut = isset( $_POST['a3c_debut'] ) ? (int) $_POST['a3c_debut'] : 0;
		if ( ! empty( $_POST['a3c_site_confirm'] ) || ( time() - $debut ) < 4 ) {
			wp_safe_redirect( add_query_arg( 'demande', 'envoyee', $retour ) . '#demande' );
			exit;
		}

		$choix = a3c_demande_choix();
		$champ = fn( $cle, $max = 200 ) => mb_substr( sanitize_text_field( wp_unslash( $_POST[ $cle ] ?? '' ) ), 0, $max );
		$d     = array(
			'nom'      => $champ( 'nom' ),
			'societe'  => $champ( 'societe' ),
			'email'    => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			'tel'      => $champ( 'tel', 30 ),
			'site'     => esc_url_raw( wp_unslash( $_POST['site'] ?? '' ) ),
			'rubrique' => $champ( 'rubrique', 30 ),
			'format'   => $champ( 'format', 30 ),
			'sujet'    => $champ( 'sujet', 300 ),
			'message'  => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ), 0, 3000 ),
			'rgpd'     => ! empty( $_POST['rgpd'] ),
		);

		$erreurs = array();
		if ( '' === $d['nom'] ) {
			$erreurs[] = 'nom';
		}
		if ( ! is_email( $d['email'] ) ) {
			$erreurs[] = 'email';
		}
		if ( ! $d['site'] || ! wp_http_validate_url( $d['site'] ) ) {
			$erreurs[] = 'site';
		}
		if ( ! isset( $choix['rubrique'][ $d['rubrique'] ] ) ) {
			$erreurs[] = 'rubrique';
		}
		if ( ! isset( $choix['format'][ $d['format'] ] ) ) {
			$erreurs[] = 'format';
		}
		if ( ! $d['rgpd'] ) {
			$erreurs[] = 'rgpd';
		}
		if ( $erreurs ) {
			// Les saisies sont conservées 10 minutes pour réafficher le formulaire rempli.
			$cle = wp_generate_password( 12, false );
			set_transient( 'a3c_demande_' . $cle, array( 'donnees' => $d, 'erreurs' => $erreurs ), 10 * MINUTE_IN_SECONDS );
			wp_safe_redirect( add_query_arg( array( 'demande' => 'incomplete', 'cle' => $cle ), $retour ) . '#demande' );
			exit;
		}

		$titre = $d['nom'] . ( $d['societe'] ? ' – ' . $d['societe'] : '' );
		$corps = sprintf(
			"Nom : %s\nSociété : %s\nE-mail : %s\nTéléphone : %s\nSite à promouvoir : %s\nRubrique : %s\nFormat : %s\nSujet proposé : %s\n\nMessage :\n%s",
			$d['nom'],
			$d['societe'],
			$d['email'],
			$d['tel'],
			$d['site'],
			$choix['rubrique'][ $d['rubrique'] ],
			$choix['format'][ $d['format'] ],
			$d['sujet'],
			$d['message']
		);

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'a3c_demande',
				'post_status'  => 'private',
				'post_title'   => $titre,
				'post_content' => $corps,
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( array( 'email', 'societe', 'tel', 'site', 'rubrique', 'format', 'sujet' ) as $cle ) {
				update_post_meta( $post_id, '_a3c_' . $cle, $d[ $cle ] );
			}
		}

		wp_mail(
			get_option( 'admin_email' ),
			'[A3C Digital] Demande de publication : ' . $titre,
			$corps . "\n\n--\nDemande enregistrée dans l'administration : " . admin_url( 'edit.php?post_type=a3c_demande' ),
			array( 'Reply-To: ' . $d['nom'] . ' <' . $d['email'] . '>' )
		);

		wp_safe_redirect( add_query_arg( 'demande', 'envoyee', $retour ) . '#demande' );
		exit;
	}
);

/**
 * Affichage du formulaire : [a3c_demande_publication]
 */
add_shortcode(
	'a3c_demande_publication',
	function () {
		$etat    = isset( $_GET['demande'] ) ? sanitize_key( $_GET['demande'] ) : '';
		$memo    = array( 'donnees' => array(), 'erreurs' => array() );
		$cle_get = isset( $_GET['cle'] ) ? sanitize_key( $_GET['cle'] ) : '';
		if ( 'incomplete' === $etat && $cle_get ) {
			$memo = get_transient( 'a3c_demande_' . $cle_get ) ?: $memo;
		}
		$v   = fn( $cle ) => esc_attr( $memo['donnees'][ $cle ] ?? '' );
		$err = fn( $cle ) => in_array( $cle, $memo['erreurs'], true );
		$choix = a3c_demande_choix();

		ob_start();
		echo '<div class="form-demande" id="demande">';
		if ( 'envoyee' === $etat ) {
			echo '<p class="demande-message demande-ok" role="status"><strong>Demande envoyée.</strong> Nous revenons vers vous par e-mail avec une proposition.</p></div>';
			return ob_get_clean();
		}
		if ( 'incomplete' === $etat ) {
			echo '<p class="demande-message demande-erreur" role="alert"><strong>Certains champs sont à compléter.</strong> Ils sont signalés ci-dessous.</p>';
		}
		if ( 'expiree' === $etat ) {
			echo '<p class="demande-message demande-erreur" role="alert"><strong>La page a expiré.</strong> Merci de renvoyer le formulaire.</p>';
		}
		?>
		<form method="post" action="" novalidate>
			<?php wp_nonce_field( 'a3c_demande', 'a3c_jeton' ); ?>
			<input type="hidden" name="a3c_demande" value="1">
			<input type="hidden" name="a3c_debut" value="<?php echo esc_attr( time() ); ?>">
			<p class="champ-piege" aria-hidden="true"><label>Ne pas remplir <input type="text" name="a3c_site_confirm" tabindex="-1" autocomplete="off"></label></p>

			<div class="grille-2">
				<p class="<?php echo $err( 'nom' ) ? 'en-erreur' : ''; ?>"><label for="d-nom">Nom et prénom *</label>
					<input id="d-nom" name="nom" type="text" required autocomplete="name" value="<?php echo $v( 'nom' ); ?>">
					<?php echo $err( 'nom' ) ? '<span class="aide-erreur">Indiquez votre nom.</span>' : ''; ?></p>
				<p><label for="d-societe">Entreprise</label>
					<input id="d-societe" name="societe" type="text" autocomplete="organization" value="<?php echo $v( 'societe' ); ?>"></p>
				<p class="<?php echo $err( 'email' ) ? 'en-erreur' : ''; ?>"><label for="d-email">E-mail professionnel *</label>
					<input id="d-email" name="email" type="email" required autocomplete="email" value="<?php echo $v( 'email' ); ?>">
					<?php echo $err( 'email' ) ? '<span class="aide-erreur">Indiquez une adresse e-mail valide.</span>' : ''; ?></p>
				<p><label for="d-tel">Téléphone</label>
					<input id="d-tel" name="tel" type="tel" autocomplete="tel" value="<?php echo $v( 'tel' ); ?>"></p>
			</div>

			<p class="<?php echo $err( 'site' ) ? 'en-erreur' : ''; ?>"><label for="d-site">Site à faire connaître *</label>
				<input id="d-site" name="site" type="url" required placeholder="https://" value="<?php echo $v( 'site' ); ?>">
				<?php echo $err( 'site' ) ? '<span class="aide-erreur">Indiquez l\'adresse complète, par exemple https://votre-site.fr</span>' : ''; ?></p>

			<div class="grille-2">
				<p class="<?php echo $err( 'rubrique' ) ? 'en-erreur' : ''; ?>"><label for="d-rubrique">Rubrique visée *</label>
					<select id="d-rubrique" name="rubrique" required>
						<option value="">Choisir…</option>
						<?php foreach ( $choix['rubrique'] as $cle => $libelle ) : ?>
							<option value="<?php echo esc_attr( $cle ); ?>" <?php selected( $memo['donnees']['rubrique'] ?? '', $cle ); ?>><?php echo esc_html( $libelle ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php echo $err( 'rubrique' ) ? '<span class="aide-erreur">Choisissez une rubrique.</span>' : ''; ?></p>
				<p class="<?php echo $err( 'format' ) ? 'en-erreur' : ''; ?>"><label for="d-format">Format souhaité *</label>
					<select id="d-format" name="format" required>
						<option value="">Choisir…</option>
						<?php foreach ( $choix['format'] as $cle => $libelle ) : ?>
							<option value="<?php echo esc_attr( $cle ); ?>" <?php selected( $memo['donnees']['format'] ?? '', $cle ); ?>><?php echo esc_html( $libelle ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php echo $err( 'format' ) ? '<span class="aide-erreur">Choisissez un format.</span>' : ''; ?></p>
			</div>

			<p><label for="d-sujet">Sujet envisagé</label>
				<input id="d-sujet" name="sujet" type="text" placeholder="Ex. : bien choisir sa mutuelle de professionnel libéral" value="<?php echo $v( 'sujet' ); ?>"></p>
			<p><label for="d-message">Précisions (délai, nombre d'articles, questions…)</label>
				<textarea id="d-message" name="message" rows="5"><?php echo esc_textarea( $memo['donnees']['message'] ?? '' ); ?></textarea></p>

			<p class="case <?php echo $err( 'rgpd' ) ? 'en-erreur' : ''; ?>"><label><input type="checkbox" name="rgpd" value="1" <?php checked( ! empty( $memo['donnees']['rgpd'] ) ); ?>> <span>J'accepte que ces informations soient utilisées pour traiter ma demande, conformément à la <a href="<?php echo esc_url( home_url( '/politique-de-confidentialite/' ) ); ?>">politique de confidentialité</a>. *</span></label>
				<?php echo $err( 'rgpd' ) ? '<span class="aide-erreur">Cochez cette case pour envoyer la demande.</span>' : ''; ?></p>

			<p><button class="btn btn-plein" type="submit">Envoyer ma demande</button></p>
			<p class="meta">* Champs obligatoires.</p>
		</form>
		<?php
		echo '</div>';
		return ob_get_clean();
	}
);

/* Les pages contenant le formulaire ne sont jamais mises en cache (jeton de sécurité frais). */
add_action(
	'template_redirect',
	function () {
		if ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'a3c_demande_publication' ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
		}
	},
	5
);
