<?php
/**
 * Detaches the domain from the license key
 *
 * @since 1.0
 * @return void
 */
function foolic_ajax_detach_domain_from_licensekey() {
	if ( isset( $_POST['domain_id'] ) && isset( $_POST['licensekey_id'] ) && check_ajax_referer( 'foolic_ajax_nonce', 'nonce' ) ) {

		if ( foolic_detach_domain_from_licensekey( $_POST['licensekey_id'], $_POST['domain_id'] ) ) {
			//successfully detached

			$return = array(
				'success' => 1,
				'message' => html_entity_decode ( foolic_get_option( 'detach_message_success', __('Successfully Detached', 'foolicensing')), ENT_COMPAT, 'UTF-8' )
			);

			echo json_encode( $return );

			wp_die();

			return;
		}
	}
	$return = array(
		'success' => 0,
		'message' => html_entity_decode ( foolic_get_option( 'detach_message_error', __('The domain could not be detached!', 'foolicensing')), ENT_COMPAT, 'UTF-8' )
	);
	echo json_encode( $return );
	wp_die();
}
add_action( 'wp_ajax_foolic_detach_domain_from_licensekey', 'foolic_ajax_detach_domain_from_licensekey' );

/**
 * Attaches a domain to the license key
 *
 * @since 1.0
 * @return void
 */
function foolic_ajax_attach_domain_to_licensekey() {
	if ( isset( $_POST['domain_id'] ) && isset( $_POST['licensekey_id'] ) && check_ajax_referer( 'foolic_ajax_nonce', 'nonce' ) ) {

		if ( foolic_attach_domain_to_licensekey( $_POST['licensekey_id'], $_POST['domain_id'] ) ) {
			//successfully detached

			$return = array(
				'success' => 1,
				'message' => html_entity_decode ( foolic_get_option( 'attach_message_success', __('Successfully attached', 'foolicensing')), ENT_COMPAT, 'UTF-8' )
			);

			echo json_encode( $return );

			wp_die();

			return;
		}
	}
	$return = array(
		'success' => 0,
		'message' => html_entity_decode ( foolic_get_option( 'attach_message_error', __('The domain could not be attached!', 'foolicensing')), ENT_COMPAT, 'UTF-8' )
	);
	echo json_encode( $return );
	wp_die();
}
add_action( 'wp_ajax_foolic_attach_domain_to_licensekey', 'foolic_ajax_attach_domain_to_licensekey' );