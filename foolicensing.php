<?php
/*
 Plugin Name: FooLicensing License Key Manager
 Plugin URI: http://fooplugins.com
 Description: A license key manager for your plugins and themes that you sell
 Author: Brad Vincent
 Version: 1.1.2
 */

if (!class_exists('foolicensing')) {

    require_once 'includes/wp_pluginbase_v2_2.php';

    require_once 'includes/_includes.php';
    require_once 'includes/init/init.php';

    class foolicensing extends wp_pluginbase_v2_2 {

        const UPDATE_URL = 'http://fooplugins.com/api/foolicensing/check/';

        function init() {
			$this->setup_constants();

            $this->plugin_slug = 'foolicensing';
            $this->plugin_title = 'FooLicensing License Management';
            $this->plugin_version = FOOLIC_VERSION;
            $this->has_settings = false;

            //call base init
            parent::init();

            //run our init classes
            foolic_init();

			if (is_admin()) {
            	add_filter('foolic_default_popup_css_url', array($this, 'get_popup_css_url'));
			} else {
				new foolic_shortcodes();
			}
        }

		function setup_constants() {
			// Plugin Folder Path
			if ( ! defined( 'FOOLIC_PLUGIN_DIR' ) )
				define( 'FOOLIC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin Folder URL
			if ( ! defined( 'FOOLIC_PLUGIN_URL' ) )
				define( 'FOOLIC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			// Plugin Version
			if ( ! defined( 'FOOLIC_VERSION' ) )
				define( 'FOOLIC_VERSION', '1.1.2' );

			// Plugin Root File
			if ( ! defined( 'FOOLIC_PLUGIN_FILE' ) )
				define( 'FOOLIC_PLUGIN_FILE', __FILE__ );
		}

        function get_popup_css_url() {
            return plugins_url('css/update-popup.css', __FILE__);
        }

        function admin_init() {
            foolic_admin_init(__FILE__, $this);
            add_action('foolic_render_page', array(&$this, 'render_page'));
        }

        function render_page($page_name) {
            $page_file = $this->plugin_dir . 'includes/pages/' . $page_name . '.php';
            if (file_exists($page_file)) {
                include_once($page_file);
            }
        }

        function admin_settings_init() {
			new foolic_admin_settings($this);

            do_action('foolicensing_admin_settings', $this);
        }

        function render_sandbox_page() {
            include_once($this->plugin_dir . "includes/foolicensing_sandbox.php");
        }

        function render_licence_page() {
            include_once($this->plugin_dir . "includes/foolicensing_license_manager.php");
        }
    }

    //run the plugin!
    $GLOBALS['foolicensing'] = new foolicensing();
}