<?php
/*
Plugin Name: Karma
Plugin URI: https://wordpress.org/extend/plugins/karma/
Description: Karma is point system database.
Author: Hametuha INC.
Version: 1.0.0
Author URI: https://hametuha.co.jp
*/


defined( 'ABSPATH' ) || die();

$data = get_file_data( __FILE__, [
	'version' => 'Version',
] );
define( 'KARMA_VERSION', $data['version'] );

add_action( 'plugins_loaded', 'karma_init' );

/**
 * Initialize karma
 *
 * @package karma
 * @internal
 */
function karma_init() {
	load_plugin_textdomain( 'karma', false, 'languages' );
	// Check PHP version.
	if ( version_compare( phpversion(), '5.5', '<' ) ) {
		add_action( 'admin_notices', 'karma_version_error' );
		return;
	}
	require __DIR__ . '/vendor/autoload.php';
	call_user_func( array( 'Hametuha\\Karma', 'get_instance' ) );
}

/**
 * Show error message
 *
 * @package karma
 * @internal
 */
function karma_version_error() {
	printf(
		'<div class="error"><p>%s</p></div>',
		sprintf( esc_html__( 'Karma requires PHP 5.5 and higher, but your version is %s.', 'karma' ), phpversion() )
	);
}
