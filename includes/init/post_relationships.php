<?php
/**
 * Create The FooLicensing Post Relationships
 * Requires the Posts 2 Posts plugin
 */

define( 'FOOLIC_P2P_VERSION', '1.5.2' );

if (!class_exists('foolic_post_relationships')) {

    class foolic_post_relationships {

        const LICENSE_TO_LICENSEKEY = 'license_to_licensekeys';
        const LICENSEKEY_TO_DOMAINS = 'licensekey_to_domains';
        const USER_TO_LICENSEKEYS = 'users_to_licensekeys';
		const LICENSE_TO_LICENSE = 'license_to_license_upgrades';

        function __construct() {
            // show a notice for when Posts 2 Posts plugin is not installed or not correct version
            add_action( 'admin_notices', array( $this, 'show_admin_notice') );

            //regsiter connections
            add_action( 'p2p_init', array( 'foolic_post_relationships', 'register_connections'), 1 );
        }

        function check_P2P_installed() {
            return (class_exists('P2P_Autoload'));
        }

        function check_P2P_version() {
            if (self::check_P2P_installed()) {

                return version_compare(P2P_PLUGIN_VERSION, FOOLIC_P2P_VERSION) >= 0;
            }
            return false;
        }
		private static $already_registered = false;

        public static function register_connections() {
			if (self::$already_registered) {
				return;
			}

			self::$already_registered = true;

            p2p_register_connection_type( array(
                'name' => self::LICENSE_TO_LICENSEKEY,
                'from' => FOOLIC_CPT_LICENSE,
                'to' => FOOLIC_CPT_LICENSE_KEY,
				'to_query_vars' => array ('orderby' => 'none'),
				'from_query_vars' => array ('orderby' => 'none'),
				'can_create_post' => false,
                'cardinality' => 'one-to-many',
                'admin_column' => 'to',
                'admin_dropdown' => 'to',
                'title' => array(
                    'from' => __('License Keys', 'foolic'),
                    'to' => __('License', 'foolic')
                ),
                'to_labels' => array(
                    'column_title' => __('License', 'foolic'),
                ),
                'from_labels' => array(
                    'create' => __('Select License', 'foolic')
                ),
                'admin_box' => array(
                    'show' => 'to',
                    'context' => 'side'
                )
            ) );

            p2p_register_connection_type( array(
                'name' => self::LICENSEKEY_TO_DOMAINS,
                'from' => FOOLIC_CPT_LICENSE_KEY,
                'to' => FOOLIC_CPT_DOMAIN,
                'title' => array(
                    'from' => __( 'Connected Domains', 'foolic' ),
                    'to' => __( 'License Keys', 'foolic' )
                ),
				'to_query_vars' => array ('orderby' => 'none'),
				'from_query_vars' => array ('orderby' => 'none'),
                'to_labels' => array(
                    'create' => __('Attach Domain', 'foolic')
                ),
                'admin_box' => array(
                    'show' => 'any',
                    'context' => 'advanced'
                ),
                'fields' => array(
                    'attached' => array(
                        'title' => 'Attached',
                        'type' => 'checkbox',
                        'default' => '1'
                    ),
                    'date_connected' => array(
                        'title' => 'Date Connected',
                        'type' => 'text',
                        'default_cb' => array( 'foolic_post_relationships', 'default_domain_connection_date')
                    )
                )
            ) );

            p2p_register_connection_type( array(
                'name' => self::USER_TO_LICENSEKEYS,
                'from' => 'user',
                'to' =>  FOOLIC_CPT_LICENSE_KEY,
				'can_create_post' => false,
                'cardinality' => 'one-to-many'
            ) );

			p2p_register_connection_type( array(
				'name' => self::LICENSE_TO_LICENSE,
				'from' => FOOLIC_CPT_LICENSE,
				'to' => FOOLIC_CPT_LICENSE,
				'can_create_post' => false,
				'admin_column' => 'to',
				'cardinality' => 'many-to-many',
				'sortable' => 'any',
				'title' => array(
					'from' => __( 'Can Upgrade From The Following Licenses', 'foolic' ),
					'to' => __( 'Available License Upgrade Paths', 'foolic' )
				),
				'to_labels' => array (
					'create' => __('Add Upgrade Path (Reverse)', 'foolic')
				),
				'from_labels' => array(
					'create' => __('Add Upgrade Path', 'foolic')
				),
				'admin_box' => array(
					'show' => 'any',
					'context' => 'advanced'
				),
				'fields' => array(
					'upgrade' => array(
						'title' => 'Upgrade Short Description',
						'type' => 'text'
					)
				)
			) );

            do_action('foolic_p2p_register_connections');
        }

        public static function default_domain_connection_date($connection, $direction) {
            return date(__('d M Y', 'foolic'));
        }

        function show_admin_notice() {
            $message = false;

            $p2p_name = __('Posts 2 Posts', 'foolic');

            $link = sprintf('<a target="_blank" href="http://wordpress.org/extend/plugins/posts-to-posts">%s</a>', $p2p_name);

            if (!$this->check_P2P_installed()) {
                $message = sprintf( __('The %s plugin is required for FooLicensing to work. Please install the %s plugin now!', 'foolic'), $p2p_name, $link );
            } else if (!$this->check_P2P_version()) {
                $message = sprintf( __('The %s plugin version is not up to date. FooLicensing requires %s version %s in order to function correctly. Please update the %s plugin now!', 'foolic'), $p2p_name, $p2p_name, FOOLIC_P2P_VERSION, $link );
            } else {
                return;  //all good - get out!
            }

            if ($message !== false) {
                echo '<div class="error"><p>';
                echo '<strong>' . __('FooLicensing Notice : ', 'foolic') . '</strong>';
                echo $message;
                echo '</p></div>';
            }
        }
    }
}



