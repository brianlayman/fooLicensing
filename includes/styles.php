<?php
/**
 * Load Stylesheets
 *
 * Enqueues the required stylesheets.
 */
function foolic_load_styles() {

	$css_url = FOOLIC_PLUGIN_URL . 'css/foolic-frontend.css';

	wp_enqueue_style( 'foolic-css', $css_url, array(), FOOLIC_VERSION );
}
