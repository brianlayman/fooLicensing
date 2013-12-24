<?php
/**
 * Setup roles for FooLicensing
 */
if (!class_exists('foolic_roles')) {

	class foolic_roles {

		function __construct() {
			$this->setup_roles();
			$this->add_capabilities();
		}

		/**
		 * Add new vendor roles with default capabilities
		 */
		public function setup_roles() {
			add_role('license_vendor', __('License Vendor', 'foolic'), array(
				'read'         => true,
				'upload_files' => true
			));
		}

		/**
		 * Add license vendor capabilities
		 */
		public function add_capabilities() {
			global $wp_roles;

			if (class_exists('WP_Roles'))
				if (!isset($wp_roles))
					$wp_roles = new WP_Roles();

			if (is_object($wp_roles)) {

				$wp_roles->add_cap('administrator', 'view_license_overages');
				$wp_roles->add_cap('administrator', 'view_license_api_sandbox');
				$wp_roles->add_cap('administrator', 'manage_license_settings');

				// Add the main post type capabilities
				$capabilities = $this->get_admin_capabilities(FOOLIC_CPT_LICENSE);
				foreach ($capabilities as $capability) {
					$wp_roles->add_cap('administrator', $capability);
				}

				$capabilities = $this->get_admin_capabilities(FOOLIC_CPT_LICENSE_KEY);
				foreach ($capabilities as $capability) {
					$wp_roles->add_cap('administrator', $capability);
				}

				$capabilities = $this->get_admin_capabilities(FOOLIC_CPT_DOMAIN);
				foreach ($capabilities as $capability) {
					$wp_roles->add_cap('administrator', $capability);
				}

				$capabilities = $this->get_admin_capabilities(FOOLIC_CPT_RENEWAL);
				foreach ($capabilities as $capability) {
					$wp_roles->add_cap('administrator', $capability);
				}

				//add media
				$wp_roles->add_cap('license_vendor', 'upload_files');

				//view all users - only for connections
				$wp_roles->add_cap('license_vendor', 'list_users');

//				$wp_roles->add_cap('license_vendor', 'delete_posts');
//				$wp_roles->add_cap('license_vendor', 'edit_posts');
//				$wp_roles->add_cap('license_vendor', 'read');

				$capabilities = $this->get_vendor_capabilities(FOOLIC_CPT_LICENSE);
				foreach ($capabilities as $capability) {
					$wp_roles->add_cap('license_vendor', $capability);
				}

				$capabilities = $this->get_vendor_capabilities(FOOLIC_CPT_LICENSE_KEY);
				foreach ($capabilities as $capability) {
					$wp_roles->add_cap('license_vendor', $capability);
				}

				$wp_roles->add_cap('license_vendor', 'edit_'. FOOLIC_CPT_DOMAIN . 's');
			}
		}

		/**
		 * Get the admin capabilities for a post type
		 */
		public function get_admin_capabilities($post_type) {
			return array(
				"edit_{$post_type}",
				"read_{$post_type}",
				"delete_{$post_type}",
				"edit_{$post_type}s",
				"edit_others_{$post_type}s",
				"publish_{$post_type}s",
				"read_private_{$post_type}s",
				"delete_{$post_type}s",
				"delete_private_{$post_type}s",
				"delete_published_{$post_type}s",
				"delete_others_{$post_type}s",
				"edit_private_{$post_type}s",
				"edit_published_{$post_type}s",
			);
		}

		/**
		 * Get the vendor capabilities for a post type
		 */
		public function get_vendor_capabilities($post_type) {
			return array(
				"edit_{$post_type}",
				"read_{$post_type}",
				"delete_{$post_type}",
				"edit_{$post_type}s",
				"publish_{$post_type}s",
				"read_private_{$post_type}s",
				"delete_{$post_type}s",
				"delete_private_{$post_type}s",
				"delete_published_{$post_type}s",
				"edit_private_{$post_type}s",
				"edit_published_{$post_type}s",
			);
		}

	}
}