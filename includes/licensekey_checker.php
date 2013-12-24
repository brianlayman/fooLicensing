<?php
/**
 * FooLicensing License Key Helper Class
 * Date: 2013/03/26
 */
if (!class_exists('foolic_licensekey_checker')) {

    class foolic_licensekey_checker {

        const STATUS_ERROR = 'ERROR';
        const STATUS_VALID = 'VALID';
        const STATUS_REQUIRED = 'REQUIRED';
        const STATUS_DETACHED = 'DETACHED';
        const STATUS_DEACTIVATED = 'DEACTIVATED';
        const STATUS_EXCEEDED = 'EXCEEDED';
        const STATUS_EXPIRED = 'EXPIRED';

        const CODE_NO_LICENSE = 'NO_LICENSE';
        const CODE_BAD_LICENSE = 'BAD_LICENSE';
        const CODE_KEY_REQUIRED = 'KEY_REQUIRED';
        const CODE_KEY_NOT_REQUIRED = 'CODE_KEY_NOT_REQUIRED';
        const CODE_BAD_KEY = 'CODE_BAD_KEY';
        const CODE_LICENSE_KEY_MISMATCH = 'CODE_LICENSE_KEY_MISMATCH';
        const CODE_IP_BLACKLISTED = 'CODE_IP_BLACKLISTED';
        const CODE_DOMAIN_DETACHED = 'CODE_DOMAIN_DETACHED';
        const CODE_DOMAIN_BLACKLISTED = 'CODE_DOMAIN_BLACKLISTED';
        const CODE_KEY_DEACTIVATED = 'CODE_KEY_DEACTIVATED';
        const CODE_KEY_EXCEEDED = 'CODE_KEY_EXCEEDED';
        const CODE_KEY_EXPIRED = 'CODE_KEY_EXPIRED';
        const CODE_KEY_VALID = 'CODE_KEY_VALID';
        const CODE_KEY_VALID_EXPIRING_SOON = 'CODE_KEY_VALID_EXPIRING_SOON';
        const CODE_KEY_VALID_NEVER_EXPIRES = 'CODE_KEY_VALID_NEVER_EXPIRES';

        const COLOR_ERROR = '#F00';
        const COLOR_WARNING = '#FF8C00';
        const COLOR_NEUTRAL = '#222';
        const COLOR_GOOD = '#0A0';

        private $license_instance = false;
        private $licensekey_instance = false;

        public static function get_responses() {
            return array(
                self::CODE_NO_LICENSE => array (
                    'valid' => false,
                    'status' => self::STATUS_ERROR,
                    'message' => __('The type of license was not specified', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => true
                ),
                self::CODE_BAD_LICENSE => array (
                    'valid' => false,
                    'status' => self::STATUS_ERROR,
                    'message' => __('The type of license does not exist', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => true
                ),
                self::CODE_KEY_REQUIRED => array (
                    'valid' => false,
                    'status' => self::STATUS_REQUIRED,
                    'message' => __('A license key is required', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => false
                ),
                self::CODE_KEY_NOT_REQUIRED => array (
                    'valid' => true,
                    'status' => self::STATUS_VALID,
                    'message' => __('A license key is not required', 'foolic'),
                    'color' => self::COLOR_NEUTRAL,
                    'error' => false
                ),
                self::CODE_BAD_KEY => array (
                    'valid' => false,
                    'status' => self::STATUS_ERROR,
                    'message' => __('The license key [%s] is NOT valid', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => true
                ),
                self::CODE_LICENSE_KEY_MISMATCH => array (
                    'valid' => false,
                    'status' => self::STATUS_ERROR,
                    'message' => __('The license key [%s] is invalid', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => true
                ),
                self::CODE_IP_BLACKLISTED => array (
                    'valid' => false,
                    'status' => self::STATUS_ERROR,
                    'message' => __('The IP address [%s] has been blacklisted', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => true
                ),
                self::CODE_DOMAIN_DETACHED => array (
                    'valid' => false,
                    'status' => self::STATUS_DETACHED,
                    'message' => __('The license key has been detached from the domain %s', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => false
                ),
                self::CODE_DOMAIN_BLACKLISTED => array (
                    'valid' => false,
                    'status' => self::STATUS_ERROR,
                    'message' => __('The domain [%s] has been blacklisted', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => true
                ),
                self::CODE_KEY_DEACTIVATED => array (
                    'valid' => false,
                    'status' => self::STATUS_DEACTIVATED,
                    'message' => __('The license key has been deactivated', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => false
                ),
                self::CODE_KEY_EXCEEDED => array (
                    'valid' => false,
                    'status' => self::STATUS_EXCEEDED,
                    'message' => __('The domain limit for the license key has been reached', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => false
                ),
                self::CODE_KEY_EXPIRED => array (
                    'valid' => false,
                    'status' => self::STATUS_EXPIRED,
                    'message' => __('The license key expired on %s', 'foolic'),
                    'color' => self::COLOR_ERROR,
                    'error' => false
                ),
                self::CODE_KEY_VALID_NEVER_EXPIRES => array (
                    'valid' => true,
                    'status' => self::STATUS_VALID,
                    'message' => __('The license key never expires', 'foolic'),
                    'color' => self::COLOR_GOOD,
                    'error' => false
                ),
                self::CODE_KEY_VALID_EXPIRING_SOON => array (
                    'valid' => true,
                    'status' => self::STATUS_VALID,
                    'message' => __('The license key will expire soon (%s)', 'foolic'),
                    'color' => self::COLOR_WARNING,
                    'error' => false
                ),
                self::CODE_KEY_VALID => array (
                    'valid' => true,
                    'status' => self::STATUS_VALID,
                    'message' => __('The license key expires in %s', 'foolic'),
                    'color' => self::COLOR_GOOD,
                    'error' => false
                )
            );
        }

        public function get_license_instance() {
            return $this->license_instance;
        }

        public function get_licensekey_instance() {
            return $this->licensekey_instance;
        }

		private function find_and_load_license($license) {
			//if the licensekey exists, then get the license
			if ($this->licensekey_instance !== false && $this->licensekey_instance->ID > 0) {
				$this->license_instance = $this->licensekey_instance->get_license();
			} else {
				$this->license_instance = foolic_find_license($license);
			}
		}

        public function validate($license_key, $license, $site = false, $ip = false) {

            $responses = self::get_responses();

            //check we have a license
            if (empty($license)) {
                return $responses[self::CODE_NO_LICENSE];
            }

			//load and check the license key exists
			if (!empty($license_key)) {
				$this->licensekey_instance = new foolic_licensekey($license_key);
				if ($this->licensekey_instance->ID == 0 || $this->licensekey_instance->deleted === true) {
					$response = $responses[self::CODE_BAD_KEY];
					$response['message'] = sprintf( $response['message'], $license_key );
					return $response;
				}
			}

            //find and load the license, then check it exists
            $this->find_and_load_license($license);
            if ($this->license_instance->ID == 0) {
                return $responses[self::CODE_BAD_LICENSE];
            }

            //if we have no license key then check if we need one based on the license
            if (empty($license_key)) {
                if ($this->license_instance->update_require_license) {
                    return $responses[self::CODE_KEY_REQUIRED];
                } else {
                    return $responses[self::CODE_KEY_NOT_REQUIRED];
                }
            }

            //check that the IP is not blacklisted
            if (!empty($ip) && self::check_ip_blacklisted($ip)) {
                $response = $responses[self::CODE_IP_BLACKLISTED];
                $response['message'] = sprintf( $response['message'], $ip );
                return $response;
            }

            if (!empty($site)) {
                //attach the domain to the license key
                $domain = $this->licensekey_instance->attach_domain_by_url($site);

                //check the domain is not blacklisted
                if ($domain !== false && $domain->blacklisted) {
                    $response = $responses[self::CODE_DOMAIN_BLACKLISTED];
                    $response['message'] = sprintf( $response['message'], $site );
                    return $response;
                }
            }

            //check that the license key is valid for the license
            if (!$this->validate_license_slug()) {
				$response = $responses[self::CODE_LICENSE_KEY_MISMATCH];
				$response['message'] = sprintf( $response['message'], $license_key );
				return $response;
            }

            //if we got this far, then we can just validate the license key object
            return $this->validate_license_key($this->licensekey_instance);
        }

		function validate_license_slug() {
			if ($this->licensekey_instance->get_license()->slug === $this->license_instance->slug) {
				return true;
			}
			$override_slug_from_key = $this->licensekey_instance->get_license()->update_override_slug;
			$override_slug_from_license = $this->license_instance->update_override_slug;

			if (!empty($override_slug_from_key) && !empty($override_slug_from_license)) {
				return $override_slug_from_key === $override_slug_from_license;
			}

			return false;
		}

        public function validate_license_key($license_key) {

            $responses = self::get_responses();

            if ($license_key->ID == 0) {

                //license key does not exist
                return $responses[self::CODE_BAD_KEY];

            } else if ($license_key->is_deactivated()) {

                //manually deactivated this license key
                return $responses[self::CODE_KEY_DEACTIVATED];

            } else if ($license_key->has_exceeded_domain_limit()) {

                //domain limit has been exceeded
                return $responses[self::CODE_KEY_EXCEEDED];

            } else if ($license_key->has_expired()) {

                //has gone over expiry date
                $response = $responses[self::CODE_KEY_EXPIRED];
                $response['message'] = sprintf( $response['message'], $license_key->expires );
                return $response;

            } else if ($license_key->is_time_to_renew()) {

                $response = $responses[self::CODE_KEY_VALID_EXPIRING_SOON];
                $response['message'] = sprintf( $response['message'], $license_key->expires );
                return $response;

            } else if (!$license_key->does_expire()) {

                //valid and never expires
                return $responses[self::CODE_KEY_VALID_NEVER_EXPIRES];

            } else {

                //Valid but will expire in the future
                $response = $responses[self::CODE_KEY_VALID];
                $response['message'] = sprintf( $response['message'], $license_key->when_expires() );
                return $response;
            }
        }

        private function check_ip_blacklisted($ip) {
//            $GLOBALS['foolicensing']
//
//            $ips = $this->get_option('blacklisted_ips', false);
//            if ($ips === false) return false;
            return false;
        }
    }
}