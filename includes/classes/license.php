<?php

if (!class_exists('foolic_license')) {

    require_once '_base.php';

    class foolic_license extends foolic_base {

        const DUMMY_UPDATE_PACKAGE_URL = 'http://fooplugins.com/download?id=123';
        const META_UPDATE_STATS = 'update_stats';
		const META_UPDATE_SLUG = 'update_slug';

        const DEFAULT_UPDATE_EXPIRY_TIME = '+24 hours';

        private $_post;
		private $_upgrade_paths = false;

        function foolic_license($slug = '') {
            $this->ID = 0;

            if (!empty($slug)) {
                $args = array(
                    'name' => $slug,
                    'numberposts' => 1,
                    'post_type' => FOOLIC_CPT_LICENSE,
                    'post_status' => 'publish');

                $licenses = get_posts($args);

                if ($licenses) {
                    $license = $licenses[0];

                    $this->load($license);
                }
            }
        }

        function load($license) {
            $this->_post = $license;

            $this->ID = $license->ID;
            $this->slug = $license->post_name;
            $this->name = $license->post_title;
			$this->author = $license->post_author;

            $data = get_post_meta($this->ID, FOOLIC_CPT_LICENSE, true);

            //number of domains allowed to be attached to the license key
            $this->domain_limit = intval(self::get_meta($data, 'domain_limit', '0'));
            //how long the license key is valid for
            $this->expires_in_days = intval(self::get_meta($data, 'expires_in_days', '0'));

            $this->key_prefix = self::get_meta($data, 'key_prefix', '');
            $this->key_segments = intval(self::get_meta($data, 'key_segments', '4'));
            $this->key_segment_length = intval(self::get_meta($data, 'key_segment_length', '4'));
            $this->key_divider = self::get_meta($data, 'key_divider', '-');

            //the current version of the item to check against when checking for updates
            $this->update_version = self::get_meta($data, 'update_version', '0');
            $this->update_override_slug = get_post_meta($this->ID, self::META_UPDATE_SLUG, true); //self::get_meta($data, 'update_override_slug', '');
            $this->update_date = self::get_meta($data, 'update_date', $license->post_modified);
            $this->update_require_license = self::is_checked($data, 'update_require_license', true);
            $this->update_message = self::get_meta($data, 'update_message', '');
            $this->update_version_compatible = self::get_meta($data, 'update_version_compatible', '3.5.1');
            $this->update_version_required = self::get_meta($data, 'update_version_required', '3.0');
            $this->update_expiry_time = self::get_meta($data, 'update_expiry_time', self::DEFAULT_UPDATE_EXPIRY_TIME);
            $this->update_changelog = self::get_meta($data, 'update_changelog', '');

            $this->update_stats = get_post_meta($this->ID, self::META_UPDATE_STATS, false);

            $this->validation_message = self::get_meta($data, 'validation_message', '');

            //Useful URLs
//            $this->url_check_for_updates = home_url("/api/{$this->slug}/check/");
//            $this->url_validate = home_url("/api/{$this->slug}/validate/");
//            $this->url_details = home_url("/api/{$this->slug}/details/");

            do_action('foolic_license_load_extra', $this, $license);
        }

        function get_underlying_post() {
            return $this->_post;
        }

        function load_by_id($post_id) {
            $post = get_post($post_id);
            if ($post) {
                $this->load($post);
            }
        }

        public static function get($post) {
            $license = new foolic_license();
            $license->load($post);
            return $license;
        }

        public static function get_by_id($post_id) {
            $license = new foolic_license();
            $license->load_by_id($post_id);
            return $license;
        }

		public static function find_by_override_slug($slug) {
			$args = array(
				'numberposts' => 1,
				'post_type' => FOOLIC_CPT_LICENSE,
				'post_status' => 'publish',
				'meta_key' => self::META_UPDATE_SLUG,
				'meta_value' => $slug
			);

			$licenses = get_posts($args);

			if ($licenses) {
				$license = new foolic_license();
				$license->load($licenses[0]);

				return $license;
			}

			return false;
		}

        function increment_updates($version) {

            if ($this->update_stats === false) {
                $this->update_stats = array();
                $this->update_stats[$version] = 1;
            } else {
                if (array_key_exists($version, $this->update_stats)) {
                    $count = intval($this->update_stats[$version]);
                    $this->update_stats[$version] = $count + 1;
                } else {
                    $this->update_stats[$version] = 1;
                }
            }

            update_post_meta($this->ID, self::META_UPDATE_STATS, $this->update_stats);
        }

        function clear_stats() {
            delete_post_meta($this->ID, self::META_UPDATE_STATS);
        }

        function generate_license_key($check_for_duplicates = false) {
			//generate a key
			$key = foolic_key_generator::new_serial($this->key_prefix, $this->key_segments, $this->key_segment_length, $this->key_divider);

			if ($check_for_duplicates) {
				//now check to see it is unique
				while (!$this->is_licensekey_unique($key)) {
					//generate another key!
					$key = foolic_key_generator::new_serial($this->key_prefix, $this->key_segments, $this->key_segment_length, $this->key_divider);
				}
			}

			return $key;
        }

		function is_licensekey_unique($key) {
			return foolic_licensekey::get_by_key($key)->ID === 0;
		}

        function generate_update_package_url() {
            return apply_filters('foolic_license_generate_update_package_url',self::DUMMY_UPDATE_PACKAGE_URL, $this);
        }

        function create_license_key($user_id = 0, $user_identifier = '', $meta = array(), $meta_display = array()) {
            //set expiry dates
            if ($this->expires_in_days > 0) {
                //we need to set a future expiry date
                $date_format = get_option('date_format');
                $current_date = date($date_format);
                $expiry_date = date($date_format, strtotime($current_date. ' + '.$this->expires_in_days.' days'));
            } else {
                $expiry_date = 'never';
            }

            $licensekey_meta = array(
                'expires' => $expiry_date,
                'domain_limit' => $this->domain_limit
            );

			$user_id = absint($user_id);

            $licensekey_meta_extra = array(
                'user' => $user_identifier,
                'user_id' => $user_id,
                'license_id' => $this->ID,
                'license' => $this->slug,
                'meta' => $meta
            );

            $author = apply_filters('foolic_licensekey_author_override', ($user_id > 0) ? $user_id : 1);

			$vendor = $this->author;

			$licence_key_string = $this->generate_license_key(true);

			$licensekey_override_args = array(
				'timestamp' => time(),
				'proposed_licensekey' => $licence_key_string,
				'expires' => $expiry_date,
				'domain_limit' => $this->domain_limit,
				'vendor_id' => $vendor,
				'user' => $user_identifier,
				'user_id' => $user_id,
				'license' => $this->ID,
				'meta' => $meta,
				'meta_display' => $meta_display
			);

            $licence_key_response = apply_filters('foolic_licensekey_response_override', $licence_key_string, $licensekey_override_args);

			//first check if the response is a stdClass and cast to array if so
			if ($licence_key_response instanceof stdClass) {
				$licence_key_response = (array)$licence_key_response;
			}

			if (is_array($licence_key_response)) {
				//handle an array response

				//try get license key
				if (array_key_exists('licensekey', $licence_key_response)) {
					$licence_key_string = $licence_key_response['licensekey'];
					unset($licence_key_response['licensekey']);
				}

				//try get license key again using slightly different key
				if (array_key_exists('license_key', $licence_key_response)) {
					$licence_key_string = $licence_key_response['license_key'];
					unset($licence_key_response['license_key']);
				}

				$meta_display = array_merge($meta_display, $licence_key_response);
			} else {
				//we are getting back a string
				$licence_key_string = $licence_key_response;
			}

            $licence_key = new foolic_licensekey();
            if ($licence_key->create($licence_key_string, $vendor, $licensekey_meta, $licensekey_meta_extra, $meta_display)) {
                //link the licensekey to the license
                $this->link_to_licensekey($licence_key->ID);

                //link the licensekey to the user
                $licence_key->link_to_user($user_id);

                return $licence_key;
            }

            //there was an error!
            return false;
        }

        function link_to_licensekey($licensekey_id) {
            p2p_create_connection( foolic_post_relationships::LICENSE_TO_LICENSEKEY, array(
                'from' => $this->ID,
                'to' => $licensekey_id
            ) );
        }

        function get_update_expiry_time() {
            if (!empty($this->update_expiry_time))
                return $this->update_expiry_time;

            return self::DEFAULT_UPDATE_EXPIRY_TIME;
        }

		/**
		 * Return the available license upgrade paths
		 *
		 * @return bool|array false if no upgrades, or the array of license upgrades
		 */
		function get_upgrade_paths() {
			//lazy load the upgrade paths
			if ($this->_upgrade_paths === false) {
				$connected_licenses = get_posts(array(
					'connected_type' => foolic_post_relationships::LICENSE_TO_LICENSE,
					'connected_items' => $this->_post,
					'connected_direction' => 'to',
					'post_count' => -1,
					'nopaging' => true,
					'post_status' => 'any',
					'suppress_filters' => false
				));
				$this->_upgrade_paths = array();
				if ($connected_licenses) {
					foreach ($connected_licenses as $license) {
						$this->_upgrade_paths[] = array(
							'name' => p2p_get_meta( $license->p2p_id, 'upgrade', true ),
							'license' => $license
						);
					}
				}
			}
			return $this->_upgrade_paths;
		}

		/**
		 * Check to see if the license can be upgraded to the license that is passed in
		 *
		 * @param int $license_id
		 *
		 * @return bool
		 */
		function check_can_upgrade_to_license($license_id) {
			foreach ($this->get_upgrade_paths() as $upgrade) {
				if ($upgrade['license']->ID == $license_id) {
					return true;
				}
			}

			return false;
		}
    }
}