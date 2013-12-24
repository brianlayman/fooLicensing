<?php
/**
 * FooLicensing Shortcodes
 * Date: 2013/11/25
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('foolic_shortcodes')) {

    class foolic_shortcodes {

        function __construct() {
			add_shortcode( 'foolicense-listing', array($this, 'license_listing' ) );
        }

		function license_listing($atts) {

			if ( is_user_logged_in() ) {
				foolic_load_ajax_scripts();
				foolic_load_styles();
			}

			//check if the user is logged in
			ob_start();

			if ( !is_user_logged_in() ) {
				foolic_get_template_part( 'licenses-not-logged-in', true );
			} else {
				foolic_get_template_part( 'licenses-listing', true );
			}

			$html = ob_get_clean();

			return $html;
		}
    }
}