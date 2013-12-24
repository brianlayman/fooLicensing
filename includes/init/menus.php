<?php
/**
 * FooLicensing Admin menus
 * Date: 2013/03/25
 */
if (!class_exists('foolic_menus')) {

    class foolic_menus {

        private $_foolicensing = false;

        function __construct($foolicensing) {
            $this->_foolicensing = $foolicensing;
            add_action('admin_menu', array(&$this, 'register_menus'));
        }

        function register_menus() {

            $top_level = 'edit.php?post_type=' . FOOLIC_CPT_LICENSE;

            add_menu_page(__('FooLicensing', 'foolic'), __('FooLicensing', 'foolic'), 'read_' . FOOLIC_CPT_LICENSE, $top_level, '', 'dashicons-admin-network');

            add_submenu_page($top_level, __('Licenses', 'foolic'), __('Licenses', 'foolic'), 'read_' . FOOLIC_CPT_LICENSE, $top_level);

            add_submenu_page($top_level, __('Add License', 'foolic'), __('Add License', 'foolic'), 'edit_' . FOOLIC_CPT_LICENSE, 'post-new.php?post_type=' . FOOLIC_CPT_LICENSE);

            //add_submenu_page($top_level, __('Logs', 'foolic'), __('Logs', 'foolic'), 'read_' . FOOLIC_CPT_LOG, 'edit.php?post_type=' . FOOLIC_CPT_LOG);

            add_submenu_page($top_level, __('Licenses Keys', 'foolic'), __('License Keys', 'foolic'), 'read_' . FOOLIC_CPT_LICENSE_KEY, 'edit.php?post_type=' . FOOLIC_CPT_LICENSE_KEY);

            add_submenu_page($top_level, __('Domains', 'foolic'), __('Domains', 'foolic'), 'read_' . FOOLIC_CPT_DOMAIN, 'edit.php?post_type=' . FOOLIC_CPT_DOMAIN);

            //add_submenu_page($top_level, __('Overages', 'foolic'), __('Overages', 'foolic'),  'view_license_overages', 'foolic_page_overages', array(&$this, 'render_page_overages'));

			if (foolic_get_option('enable_renewals', false)) {
				add_submenu_page($top_level, __('Renewals', 'foolic'), __('Renewals', 'foolic'), 'read_' . FOOLIC_CPT_RENEWAL, 'edit.php?post_type=' . FOOLIC_CPT_RENEWAL);
			}

            add_submenu_page($top_level, __('API Sandbox', 'foolic'), __('API Sandbox', 'foolic'),  'view_license_api_sandbox', 'foolic_page_api', array(&$this, 'render_page_api'));

            add_submenu_page($top_level, __('Settings', 'foolic'), __('Settings', 'foolic'), 'manage_license_settings', 'foolicensing', array($this->_foolicensing, "admin_settings_render_page"));
        }

        function render_page_overages() {
            do_action('foolic_render_page', 'overages');
        }

        function render_page_api() {
            do_action('foolic_render_page', 'api');
        }
    }
}