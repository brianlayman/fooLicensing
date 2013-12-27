<?php
/**
 * FooLicensing Upgrade Functions
 * Date: 2013/12/19
 */

/**
 * @param int $licensekey_id
 * @param int $upgrade_license_id
 */
function foolic_perform_upgrade( $licensekey_id, $upgrade_license_id ) {

	$license_key = foolic_licensekey::get_by_id( $licensekey_id );

	if ( $license_key->ID == 0 ) {
		return false;
	}

	if ( $license_key->domain_limit > 0 && !$license_key->is_deactivated() ) {
		$date_format = get_option('date_format');

		$expiry_date = $license_key->expires;
		$domain_limit = $license_key->domain_limit;

		//get the expiry date
		$expiry = strtotime( $license_key->expires );
		//if already expired, then use today's date
		if ($expiry < time()) {
			$expiry = time();
		}

		//ensure we have our connections registered
		foolic_post_relationships::register_connections();

		//load new license
		$license = foolic_license::get_by_id( $upgrade_license_id );

		if ($license->ID > 0) {

			$domain_limit = $license->domain_limit;

			if ($license->expires_in_days > 0) {
				$expiry_date = date( $date_format, strtotime('+' . $license->expires_in_days . ' days', $expiry) );
			} else {
				$expiry_date = 'never';
			}
		}

		//save new expiry date and domain limit
		$license_key->domain_limit = $domain_limit;
		$license_key->expires = $expiry_date;
		$license_key->process_domains();	//process domains immediately and update

		//delete connection
		$license_key->disconnect_from_existing_license();

		//connect to new license
		$license->link_to_licensekey( $license_key->ID );

		//save upgrade post meta
		$existing_upgrades = foolic_get_upgrades( $license_key->ID );
		$existing_upgrades[] = array(
			'upgrade_date' 		=> date( $date_format ),
			'upgrade_details' 	=> __('Upgrade to', 'foolicensing') . ' ' . $license->name
		);
		foolic_update_upgrades( $license_key->ID, $existing_upgrades );

		return true;
	}

	return false;
}

function foolic_get_upgrades( $licensekey_id ) {
	$upgrades = get_post_meta( $licensekey_id, 'foolic_upgrades', true );
	if ( empty( $upgrades ) ) {
		return array();
	}
	return $upgrades;
}

function foolic_update_upgrades( $licensekey_id, $upgrades ) {
	update_post_meta( $licensekey_id, 'foolic_upgrades', $upgrades );
}
