<?php
/**
 * Plugin Name: A3C Déploiement
 * Description: Permet à Claude Code d'installer et d'activer le thème A3C via l'API REST (réservé aux administrateurs authentifiés).
 * Version: 1.0.0
 * Author: Djaka
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	function () {
		$admin = function () {
			return current_user_can( 'install_themes' ) && current_user_can( 'switch_themes' );
		};

		// POST /wp-json/a3c/v1/theme (multipart, champ « zip ») : installe ou met à jour un thème.
		register_rest_route(
			'a3c/v1',
			'/theme',
			array(
				'methods'             => 'POST',
				'permission_callback' => $admin,
				'callback'            => 'a3c_deploiement_installer_theme',
			)
		);

		// POST /wp-json/a3c/v1/activer { "stylesheet": "a3c" } : active un thème installé.
		register_rest_route(
			'a3c/v1',
			'/activer',
			array(
				'methods'             => 'POST',
				'permission_callback' => $admin,
				'callback'            => function ( WP_REST_Request $request ) {
					$stylesheet = sanitize_key( (string) $request->get_param( 'stylesheet' ) );
					$theme      = wp_get_theme( $stylesheet );
					if ( ! $theme->exists() ) {
						return new WP_Error( 'a3c_theme_absent', "Le thème « $stylesheet » n'est pas installé.", array( 'status' => 404 ) );
					}
					switch_theme( $stylesheet );
					return array( 'actif' => get_stylesheet() );
				},
			)
		);
	}
);

/**
 * Installe le zip reçu, en écrasant la version existante du même thème.
 */
function a3c_deploiement_installer_theme( WP_REST_Request $request ) {
	$fichiers = $request->get_file_params();
	if ( empty( $fichiers['zip']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $fichiers['zip']['error'] ) {
		return new WP_Error( 'a3c_zip_absent', 'Aucun fichier zip reçu (champ « zip »).', array( 'status' => 400 ) );
	}
	if ( 'zip' !== strtolower( pathinfo( $fichiers['zip']['name'], PATHINFO_EXTENSION ) ) ) {
		return new WP_Error( 'a3c_zip_invalide', 'Le fichier doit être un .zip.', array( 'status' => 400 ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/theme.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$temp = wp_tempnam( 'a3c-theme.zip' );
	if ( ! move_uploaded_file( $fichiers['zip']['tmp_name'], $temp ) ) {
		return new WP_Error( 'a3c_zip_copie', 'Impossible de lire le fichier reçu.', array( 'status' => 500 ) );
	}

	$skin      = new WP_Ajax_Upgrader_Skin();
	$upgrader  = new Theme_Upgrader( $skin );
	$resultat  = $upgrader->install( $temp, array( 'overwrite_package' => true ) );
	wp_delete_file( $temp );

	if ( is_wp_error( $resultat ) ) {
		return $resultat;
	}
	if ( ! $resultat ) {
		$erreurs = $skin->get_errors();
		return new WP_Error( 'a3c_installation', $erreurs->has_errors() ? $erreurs->get_error_message() : "L'installation a échoué.", array( 'status' => 500 ) );
	}

	$theme = $upgrader->theme_info();
	return array(
		'installe' => $theme ? $theme->get_stylesheet() : null,
		'version'  => $theme ? $theme->get( 'Version' ) : null,
		'actif'    => get_stylesheet(),
	);
}
