<?php
/**
 * FooLicensing Template Functions
 * Date: 2013/11/25
 */

function foolic_get_template_part( $template_slug, $load = false ) {

	do_action( 'foolic_get_template_part-' . $template_slug );

	$template_dir = trailingslashit( FOOLIC_PLUGIN_DIR . 'templates' );

	$template_path = $template_dir . $template_slug . '.php';

	if ( !file_exists( $template_path ) ) {
		return false;
	}

	if ( ( $load == true ) && ! empty( $template_path ) ) {
		load_template( $template_path, false );
	}

	return $template_path;
}