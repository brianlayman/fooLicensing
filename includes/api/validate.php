<?php
/**
 * API controller to validate a license key
 */

if (!class_exists('foolic_api_validate')) {

    class foolic_api_validate extends foolic_api_base_controller {

        function init() {
            add_action( 'foolic_api_post-validate', array(&$this, 'execute'), 10, 3 );
        }

        function execute($license, $action, $args) {

            $site = $this->get_post_var('site');
            $license_key = $this->get_post_var('license');
            $ip = $this->get_post_var('ip');

            $checker = new foolic_licensekey_checker();
            $validation_response = $checker->validate($license_key, $license, $site, $ip);
            $validation_message = '';
            $expires = '';
			$license_message = '';
			$domains = array();


			if ($checker->get_license_instance() !== false) {
                $validation_message = $checker->get_license_instance()->validation_message;
				if ($validation_response['valid'] === true)
					$license_message = sprintf( __('Thank you for validating your %s license key : ', 'foolicensing'), $checker->get_license_instance()->name );
				else
					$license_message = sprintf( __('You do not have a valid %s license key : ', 'foolicensing'), $checker->get_license_instance()->name );
			}
            if ($checker->get_licensekey_instance() !== false && $checker->get_licensekey_instance()->ID > 0) {
				$expires = $checker->get_licensekey_instance()->expires;
				$domains = $checker->get_licensekey_instance()->get_domains();
			}

            $details = array(
                'slug' => $license,
                'license_key' => $license_key,
                'site' => $site,
                'validation_date' => date(DATE_RFC822),
                'response' => $validation_response,
                'validation_message' => $validation_message,
                'expires' => $expires,
				'license_message' => $license_message,
				'domains' => $domains
            );

            header('Content-type: application/json');
            echo json_encode($details);
        }
    }

    $GLOBALS['foolic_api'][] = new foolic_api_validate();

}