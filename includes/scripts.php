<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get FooLic AJAX URL
 *
 * @since 1.3
 * @return string
 */
function foolic_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = edd_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'foolic_ajax_url', $ajax_url );
}

/**
 * Get the current page URL
 *
 * @since 1.3
 * @global $post
 * @return string $page_url Current page URL
 */
function foolic_get_current_page_url() {
	global $post;

	if ( is_front_page() ) :
		$page_url = home_url();
	else :
		$page_url = 'http';

		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
			$page_url .= "s";

		$page_url .= "://";

		if ( $_SERVER["SERVER_PORT"] != "80" )
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	endif;

	return apply_filters( 'foolic_get_current_page_url', esc_url( $page_url ) );
}

/**
 * Load AJAX Scripts
*
 * Enqueues the required scripts.
 */
function foolic_load_ajax_scripts() {

	$js_url = FOOLIC_PLUGIN_URL . 'js/foolic-ajax.js';

	wp_enqueue_script( 'jquery' );

	wp_enqueue_script( 'foolic-ajax', $js_url, array( 'jquery' ), FOOLIC_VERSION );
	wp_localize_script( 'foolic-ajax', 'foolic_scripts', array(
			'ajaxurl'   		=> foolic_get_ajax_url(),
			'ajax_nonce' 		=> wp_create_nonce( 'foolic_ajax_nonce' ),
			'refresh_license'	=> __('Refresh license information', 'foolicensing'),
			'are_you_sure'		=> __('Are you sure?', 'foolicensing'),
			'show_license_key'	=> __('show', 'foolicensing')
		)
	);
}
