<?php
/**
 * FooLicensing Renewal Functions
 * Date: 2013/12/19
 */

/**
 * @param int $licensekey_id
 */
function foolic_perform_renewal( $licensekey_id ) {

	$license_key = foolic_licensekey::get_by_id( $licensekey_id );

	if ( $license_key->ID == 0 ) {
		return false;
	}

	if ($license_key->does_expire() && !$license_key->is_deactivated()) {
		$date_format = get_option('date_format');

		$expiry_date = $license_key->expires;

		//get the expiry date
		$expiry = strtotime( $license_key->expires );
		//if already expired, then use today's date
		if ($expiry < time()) {
			$expiry = time();
		}

		//ensure we have our connections registered
		foolic_post_relationships::register_connections();

		//load license
		$license = $license_key->get_license();

		if ($license->ID > 0) {
			if ($license->expires_in_days > 0) {
				$expiry_date = date( $date_format, strtotime('+' . $license->expires_in_days . ' days', $expiry) );
			} else {
				$expiry_date = 'never';
			}
		}

		//save new expiry date
		$license_key->expires = $expiry_date;
		$license_key->update();

		//save renewal post
		$renewal_post_args = apply_filters('foolic_renewal_post_args_override', array(
			'post_type' => FOOLIC_CPT_RENEWAL,
			'post_status' => 'publish',
			'post_author' => $license->author,
			'post_title' => $license->name . ' - ' . $license_key->license_key
		));

		//insert the renewal post
		$renewal_id = wp_insert_post($renewal_post_args, true);

		//save renewal post meta
		$existing_renewals = foolic_get_renewals( $license_key->ID );
		$existing_renewals[] = array(
			'renewal_date' => date( $date_format ),
			'renewal_id' => $renewal_id
		);
		foolic_update_renewals( $license_key->ID, $existing_renewals );

		return $renewal_id;
	}

	return false;
}

function foolic_get_renewals( $licensekey_id ) {
	$renewals = get_post_meta( $licensekey_id, 'foolic_renewals', true );
	if ( empty( $renewals ) ) {
		return array();
	}
	return $renewals;
}

function foolic_update_renewals( $licensekey_id, $renewals ) {
	update_post_meta( $licensekey_id, 'foolic_renewals', $renewals );
}

/**
 * @param foolic_licensekey $license_key
 *
 * @returns int
 */
function foolic_renewal_percentage( $license_key ) {
	$discount = 0;

	//we only want to give a discount if the license expires
	if ($license_key->does_expire()) {
		if (!$license_key->has_expired()) {
			//the license key has not expired yet - good boy!
			$discount = foolic_renewal_discount_early();
		} else {

            $expiration_date = strtotime($license_key->expires);

            $grace_period_expiry_date = strtotime('+' . foolic_renewal_grace_period() . ' days', $expiration_date); //days ahead of expiry

            if ($grace_period_expiry_date >= time()) {

				//within grace period - lucky boy!
				$discount = foolic_renewal_discount_grace();

			} else {

				//late - boo!
				$discount = foolic_renewal_discount_late();
			}

		}
	}

	return $discount;
}

function foolic_renewal_discount_early() {
	return intval( foolic_get_option('renewal_discount_early', 50) );
}

function foolic_renewal_discount_grace() {
	return intval( foolic_get_option('renewal_discount_grace', 20) );
}

function foolic_renewal_discount_late() {
	return intval( foolic_get_option('renewal_discount_late', 10) );
}

function foolic_renewal_grace_period() {
	return intval( foolic_get_option('renewal_grace_period', 30) );
}

