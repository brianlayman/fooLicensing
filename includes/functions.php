<?php
/**
 * FooLicensing Common Functions
 * Date: 2013/03/26
 */

function foolic_get_license_posts($status = 'publish') {
    return get_posts( array(
        'post_type' => FOOLIC_CPT_LICENSE,
        'numberposts' => -1,
        'orderby' => 'title',
        'post_status' => $status
    ) );
}

function foolic_validate_licensekey($licensekey, $license, $site = false, $ip = false) {
    return foolic_licensekey_checker::validate($licensekey, $license, $site, $ip);
}

function foolic_get_license($post_id) {
    return foolic_license::get_by_id($post_id);
}

function foolic_get_license_key($license_key) {
	if (!empty($license_key)) {
		return foolic_licensekey::get_by_key($license_key);
	}
	return false;
}

function foolic_find_license($slug) {
	$license = new foolic_license($slug);
	if ($license->ID == 0) {
		$license = foolic_license::find_by_override_slug($slug);
	}
	return $license;
}

function foolic_get_licensekeys_by_user( $user = false ) {
	if ( empty( $user ) ) {
		$user = wp_get_current_user();
	}

	$connected_licensekeys = get_posts ( array(
		'connected_type' => foolic_post_relationships::USER_TO_LICENSEKEYS,
		'connected_items' => $user,
		'post_count' => -1,
		'suppress_filters' => false
	) );

	if ( $connected_licensekeys ) {
		$license_keys = array();
		foreach ( $connected_licensekeys as $licensekey ) {
			$license_keys[] = foolic_licensekey::get( $licensekey );
		}

		return $license_keys;
	}

	//no results - boo!
	return array();
}

/**
 * Detach a domain from a license key
 *
 * @param int $licensekey_id The ID of the license key
 * @param int $domain_id The ID of the domain
 *
 * @return bool If the detachment was successful
 */
function foolic_detach_domain_from_licensekey ( $licensekey_id, $domain_id ) {
	$licensekey = foolic_licensekey::get_by_id( $licensekey_id );
	if ( $licensekey->ID > 0 ) {
		return $licensekey->detach_domain( $domain_id );
	}

	return false;
}

/**
 * Attach a domain to a license key
 *
 * @param int $licensekey_id The ID of the license key
 * @param int $domain_id The ID of the domain
 *
 * @return bool If the attachment was successful
 */
function foolic_attach_domain_to_licensekey ( $licensekey_id, $domain_id ) {
	$licensekey = foolic_licensekey::get_by_id( $licensekey_id );
	if ( $licensekey->ID > 0 ) {
		return $licensekey->attach_domain( $domain_id );
	}

	return false;
}

function foolic_get_option( $setting, $default = false ) {
	$foolic = $GLOBALS['foolicensing'];

	return $foolic->get_option( $setting, $default );
}

function foolic_get_expiring_licensekeys() {

	$args = array(
		'post_type'    => FOOLIC_CPT_LICENSE_KEY,
		'nopaging'     => true,
		'fields'       => 'ids',
		'meta_query'   => array(
			'relation' => 'AND',
			array(
				'key'     => 'foolic_expires',
				'value'   => array(
					current_time( 'timestamp' ),
					strtotime( '+1 month' )
				),
				'compare' => 'BETWEEN'
			),
			array(
				'key'     => 'foolic_deactivated',
				'value'	=> 1,
				'compare' => 'NOT EXISTS'
			)
		)
	);

	$query = new WP_Query;
	$keys  = $query->query( $args );
	if( ! $keys )
		return array(); // no expiring keys found

	return $keys;
}

function foolic_get_expired_licensekeys() {

	$args = array(
		'post_type'    => FOOLIC_CPT_LICENSE_KEY,
		'nopaging'     => true,
		'fields'       => 'ids',
		'meta_query'   => array(
			'relation' => 'AND',
			array(
				'key'     => 'foolic_expires',
				'value'   => current_time( 'timestamp' ),
				'compare' => '<='
			),
			array(
				'key'     => 'foolic_deactivated',
				'value'	=> 1,
				'compare' => 'NOT EXISTS'
			)
		)
	);

	$query = new WP_Query;
	$keys  = $query->query( $args );
	if( ! $keys )
		return array(); // no expiring keys found

	return $keys;
}

function foolic_get_exceeded_licensekeys() {

	$args = array(
		'post_type'    => FOOLIC_CPT_LICENSE_KEY,
		'nopaging'     => true,
		'fields'       => 'ids',
		'meta_query'   => array(
			'relation' => 'AND',
			array(
				'key'     => 'foolic_exceeded',
				'value'   => 1,
				'compare' => '='
			),
			array(
				'key'     => 'foolic_deactivated',
				'value'	=> 1,
				'compare' => 'NOT EXISTS'
			)
		)
	);

	$query = new WP_Query;
	$keys  = $query->query( $args );
	if( ! $keys )
		return array();; // no expiring keys found

	return $keys;
}

function foolic_format_date($date) {
	return date(__('d M Y', 'foolic'), strtotime($date));
}