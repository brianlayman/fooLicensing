<?php

if (!class_exists('foolic_licensekey')) {

    require_once '_base.php';

    class foolic_licensekey extends foolic_base {

        const META_ISNEW = 'is_new';
        const META_EXTRA = 'extra';
        const META_DISPLAY = 'display';
        const META_ERROR = 'error';
		const META_VENDOR = 'vendor';

		const META_EXCEEDED = 'foolic_exceeded';
		const META_DEACTIVATED = 'foolic_deactivated';
		const META_EXPIRES = 'foolic_expires';
		const META_DETACHED_COUNT = 'foolic_detached_count';

        private $_post = false;
        private $_license = false;
        private $_domains = false;
		private $_attached_domains = false;

        function foolic_licensekey($key = '') {
            $this->ID = 0;

            if (!empty($key)) {
                $license = get_page_by_title($key, 'OBJECT', FOOLIC_CPT_LICENSE_KEY);

                if ($license) {
                    $this->load($license);
                }
            }
        }

        function load_by_id($post_id) {
            $post = get_post($post_id);
            if ($post) {
                $this->load($post);
            }
        }

        public static function get($post) {
            $license_key = new foolic_licensekey();
            $license_key->load($post);
            return $license_key;
        }

        public static function get_by_id($post_id) {
            $license_key = new foolic_licensekey();
            $license_key->load_by_id($post_id);
            return $license_key;
        }

		public static function get_by_key($key) {
			return new foolic_licensekey($key);
		}

        function load($licensekey) {

            $this->_post = $licensekey;

            $this->deleted = $licensekey->post_status === 'trash';
            $this->ID = $licensekey->ID;
            $this->license_key = $licensekey->post_title;
            $this->date_issued = date(__('d M Y', 'foolic'), strtotime($licensekey->post_date));

            $data = get_post_meta($licensekey->ID, FOOLIC_CPT_LICENSE_KEY, true);

            $this->is_new = get_post_meta($licensekey->ID, self::META_ISNEW, true) != 'NOT';

            $this->activated = self::is_checked($data, 'activated', false);
            $this->deactivated = self::is_checked($data, 'deactivated', false);
            $this->exceeded = self::is_checked($data, 'exceeded', false);
            $this->expires = self::get_meta($data, 'expires', 'never');
            $this->domain_limit = intval(self::get_meta($data, 'domain_limit', 0));

			//store the meta data we want to query
			$this->store_meta_data();

            $this->meta_display = get_post_meta($licensekey->ID, self::META_DISPLAY, true);
            $this->errors = get_post_meta($licensekey->ID, self::META_ERROR, true);
        }

		//Stores the license key meta data. As of v1.1.0, license key info needs to be stored as meta data so that it can be queried for renewals etc
		private function store_meta_data() {
			//if our license key has been exceeded, then store that in meta data
			if ( $this->exceeded === true ) {
				update_post_meta( $this->ID, self::META_EXCEEDED, 1 );
			} else {
				delete_post_meta( $this->ID, self::META_EXCEEDED );
			}

			//if our license key expires then store that in meta data
			if ( $this->expires !== 'never' ) {
				update_post_meta( $this->ID, self::META_EXPIRES, strtotime($this->expires) );
			} else {
				delete_post_meta( $this->ID, self::META_EXPIRES );
			}

			//if our license key has been deactivated then store that in meta
			if ( $this->deactivated === true ) {
				update_post_meta( $this->ID, self::META_DEACTIVATED, 1 );
			} else {
				delete_post_meta( $this->ID, self::META_DEACTIVATED );
			}
		}

        // Creates a new license key
        // $license_key_string = the generated license key string e.g. FOOB-123123-123123-123123
        // $licensekey_meta = the required info for a license key e.g. expires and domain_limit
        // $licensekey_meta_extra = extra info that can be stored when a license key is created, e.g. userId, customer name, price etc
        // $licensekey_meta_display = extra info that is shown on the license key post type edit page
        function create($license_key_string, $license_vendor, $licensekey_meta, $licensekey_meta_extra = array(), $licensekey_meta_display = array()) {
            $licensekey_post_args = $licence_key = apply_filters('foolic_licensekey_post_args_override', array(
                'post_type' => FOOLIC_CPT_LICENSE_KEY,
                'post_status' => 'publish',
                'post_author' => $license_vendor,
                'post_title' => $license_key_string,
                'post_content' => print_r($licensekey_meta, true)
            ));

            //insert the license post
            $licensekey_id = wp_insert_post($licensekey_post_args, true);

            if (!is_wp_error($licensekey_id)) {

                do_action('foolic_licensekey_after_insert', $licensekey_id);

                //set the licensekey meta
                add_post_meta($licensekey_id, FOOLIC_CPT_LICENSE_KEY, $licensekey_meta);
                add_post_meta($licensekey_id, foolic_licensekey::META_EXTRA, $licensekey_meta_extra);
                add_post_meta($licensekey_id, foolic_licensekey::META_DISPLAY, $licensekey_meta_display);
                add_post_meta($licensekey_id, foolic_licensekey::META_ISNEW, 'NOT');
				add_post_meta($licensekey_id, foolic_licensekey::META_VENDOR, $license_vendor);

                $this->load_by_id($licensekey_id);

                return true;
            }

            return false;
        }

        function link_to_user($user_id) {
            if ($user_id > 0) {
                //we have a user ID, so link the licensekey to the user

                p2p_create_connection(foolic_post_relationships::USER_TO_LICENSEKEYS, array(
                    'from' => $user_id,
                    'to' => $this->ID
                ));
            }
        }

		function get_underlying_post() {
			return $this->_post;
		}

		/**
		 * @return foolic_license
		 */
		function get_license() {
            //lazy load the license
            if ($this->_license === false) {

				$this->_license = new foolic_license();

                $connected_licenses = get_posts(array(
                    'connected_type' => foolic_post_relationships::LICENSE_TO_LICENSEKEY,
                    'connected_items' => $this->_post->ID,
                    'post_count' => 1,
                    'suppress_filters' => false
                ));

                if ($connected_licenses) {
                    $this->_license->load($connected_licenses[0]);
                }
            }

            return $this->_license;
        }

		/**
		 * @return WP_User
		 */
		function get_user() {
			//lazy load the connected user
			$connected_users = get_users( array(
				'connected_type' => foolic_post_relationships::USER_TO_LICENSEKEYS,
				'connected_items' => $this->_post
			) );

			if ($connected_users) {
				//just return the first user
				return $connected_users[0];
			}
			return false;
		}

        function get_domains() {
            //lazy load the domains
            if ($this->_domains === false) {
                $connected_domains = get_posts(array(
                    'connected_type' => foolic_post_relationships::LICENSEKEY_TO_DOMAINS,
                    'connected_items' => $this->_post,
                    'post_count' => -1,
                    'post_status' => 'any',
					'nopaging' => true,
                    'suppress_filters' => false
                ));
                if ($connected_domains) {
                    $this->_domains = $connected_domains;
                }
            }
            return $this->_domains;
        }

        function is_deactivated() {
            return $this->deactivated !== false;
        }

        function has_exceeded_domain_limit() {
            return $this->exceeded !== false;
        }

        function does_expire() {
            return $this->expires !== false && $this->expires !== 'never' && $this->expires !== '';
        }

        function has_expired() {
            if (!$this->does_expire()) {
                return false;
            }

            $expiration_date = strtotime($this->expires);
            $today = strtotime(date("Y-m-d"));

            return ($expiration_date < $today);
        }

        function is_time_to_renew($months = 1) {
            if (!$this->does_expire()) {
                return false;
            }

            $expiration_date = strtotime($this->expires);

            $then = strtotime('+' . $months . ' month'); //months into the future

            return ($expiration_date < $then);
        }

        function is_valid() {
            if ($this->is_deactivated() || $this->has_expired() || $this->has_exceeded_domain_limit()) {
                return false;
            }
            return true;
        }

        function when_expires() {
            if (!$this->does_expire()) {
                return 'Never';
            }

            $timestamp = strtotime($this->expires);

            $difference = time() - $timestamp;
            $periods = array(" sec", " min", " hour", " day", " week", " month", " year", " decade");
            $lengths = array("60", "60", "24", "7", "4", "12", "10");

            if ($difference > 0) {
                $ending = " ago.";
            } else {
                $difference = -$difference;
                $ending = ".";
            }

            for ($j = 0; $difference >= $lengths[$j]; $j++) {
                $difference /= $lengths[$j];
            }

            $difference = round($difference);

            if ($difference != 1) {
                $periods[$j] .= "s";
            }

            return $difference . $periods[$j] . $ending;
        }

        function process_domains() {
            //load all connected domains
            $domains = $this->get_domains();

            $attached_count = 0;

            if ($domains) {
                //mark the licensekey as activated
                $this->activated = true;

                //loop thru all domains and if they are attached then increment attached_count
                foreach ($domains as $domain) {
                    $attached = p2p_get_meta($domain->p2p_id, 'attached', true);
                    //ignore localhost domains in the count
                    $localhost = foolic_domain::get($domain)->localhost;
                    if ($attached == "1" && $localhost === false) {
                        $attached_count++;
                    }
                }
            }

            //if the number of attached domains is more than the domain limit then we have exceeded the license key
            $this->exceeded = ($attached_count > $this->domain_limit) && ($this->domain_limit > 0);

            $this->update();
        }

		function get_attached_domains() {
			//lazy load the attached domains
			if ($this->_attached_domains === false) {
				//load all connected domains
				$domains = $this->get_domains();

				$this->_attached_domains = array();

				if ($domains) {
					//mark the licensekey as activated
					$this->activated = true;

					//loop thru all domains and if they are attached then increment attached_count
					foreach ($domains as $domain) {
						$attached = p2p_get_meta($domain->p2p_id, 'attached', true);

						//ignore localhost domains
						$domain_object = foolic_domain::get($domain);
						if ($attached == "1" && $domain_object->localhost === false) {
							$this->_attached_domains[] = $domain_object;
						}
					}
				}
			}
			return $this->_attached_domains;
		}

        function update() {
            $data = get_post_meta($this->ID, FOOLIC_CPT_LICENSE_KEY, true);

            self::set_checked($data, 'activated', $this->activated);
            self::set_checked($data, 'deactivated', $this->deactivated);
            self::set_checked($data, 'exceeded', $this->exceeded);
            self::set_meta($data, 'expires', $this->expires);
            self::set_meta($data, 'domain_limit', $this->domain_limit);

            update_post_meta($this->ID, FOOLIC_CPT_LICENSE_KEY, $data);

			$this->store_meta_data();
        }

        function attach_domain_by_url($domain_url) {
            //get the domain
            $domain = new foolic_domain($domain_url);

            if ($domain->ID > 0) {

                $p2p_id = p2p_type(foolic_post_relationships::LICENSEKEY_TO_DOMAINS)->get_p2p_id($this->ID, $domain->ID);

                if (!$p2p_id) {

                    $p2p_response = p2p_create_connection(foolic_post_relationships::LICENSEKEY_TO_DOMAINS, array(
                        'from' => $this->ID,
                        'to' => $domain->ID,
                        'meta' => array(
                            'date_connected' => current_time('mysql'),
                            'attached' => '1'
                        )
                    ));

                    if ($p2p_response !== false) {
                        //make sure domains are not cached
                        $this->_domains = false;
						$this->_attached_domains = false;
                        $this->process_domains();
                    }
				} else {
					//we have an existing connection
					p2p_add_meta($p2p_id, 'attached', '1', true);

					//make sure domains are not cached
					$this->_domains = false;
					$this->_attached_domains = false;
					$this->process_domains();
				}

                return $domain;
            }

            return false;
        }

		function detach_domain($domain_id) {

			$domains = $this->get_attached_domains();

			if ($domains) {
				foreach ($domains as $domain) {
					if ( intval($domain_id) === $domain->ID) {

						$p2p_id = p2p_type(foolic_post_relationships::LICENSEKEY_TO_DOMAINS)->get_p2p_id($this->ID, $domain->ID);

						if ($p2p_id) {
							//we found the attached domain. Only detach if already attached
							p2p_update_meta($p2p_id, 'attached', '0');

							//increment detached count
							$detached_count = intval( get_post_meta( $this->ID, self::META_DETACHED_COUNT, true ) );
							$detached_count++;
							update_post_meta( $this->ID, self::META_DETACHED_COUNT, $detached_count );

							//update all our domains
							$this->process_domains();
							return true;
						}
					}
				}
			}

			return false;
		}

		function attach_domain($domain_id) {

			$domains = $this->get_domains();

			if ($domains) {
				foreach ($domains as $domain) {
					if ( intval($domain_id) === $domain->ID && !$this->is_domain_attached($domain->post_title) ) {

						$p2p_id = p2p_type(foolic_post_relationships::LICENSEKEY_TO_DOMAINS)->get_p2p_id($this->ID, $domain->ID);

						if ($p2p_id) {
							//we found the detached domain. Only attach if already detached
							p2p_update_meta($p2p_id, 'attached', '1');

							//update all our domains
							$this->process_domains();
							return true;
						}
					}
				}
			}

			return false;
		}

        function is_domain_attached($domain_url) {
            $domains = $this->get_attached_domains();

            if ($domains) {
                foreach ($domains as $domain) {
					if (strtolower($domain_url) == strtolower($domain->url)) {
						return true;
                    }
                }
            }

            return false;
        }

		function disconnect_from_existing_license() {
			$license = $this->get_license();
			if ($license->ID > 0) {
				p2p_type( foolic_post_relationships::LICENSE_TO_LICENSEKEY )->disconnect( $license->get_underlying_post()->ID, $this->_post->ID );
			}
		}
    }

}