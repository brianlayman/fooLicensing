<?php
/**
 * Create The FooLicensing Custom Post Types
 */

if (!class_exists('foolic_custom_post_types')) {

	class foolic_custom_post_types {

		function __construct() {
			add_action('init', array(&$this, 'register'), 1);

			if (is_admin()) {
				add_filter('get_sample_permalink_html', array(&$this, 'get_sample_permalink_html'), 10, 4);
				add_filter('pre_get_posts', array(&$this, 'posts_filter'));
			}
		}

		function get_sample_permalink_html($sample, $post_id, $new_title, $new_slug) {
			global $post;
			if (isset($post) && $post->post_type == FOOLIC_CPT_LICENSE) {
				$post_slug = $post->post_name;
				if ($post_slug) {
					$sample = str_replace('<strong>Permalink:</strong>', '<strong>License Slug:</strong>', $sample);
					$sample = preg_replace('#(<span id="sample-permalink">).+?(<span id="editable-post-name" title="Click to edit this part of the permalink">).+?</span>/</span>#Us', "$1$2{$post->post_name}</span></span>", $sample);
					$sample = preg_replace("#(<span id='view-post-btn'>.*</span>)#", "", $sample);

					return $sample;
				}
			}

			return $sample;
		}

		function register() {

			//register License custom post type
			register_post_type(FOOLIC_CPT_LICENSE,
				array(
					'labels'          => array(
						'name'               => __('Licenses', 'foolic'),
						'singular_name'      => __('License', 'foolic'),
						'add_new'            => __('Add New', 'foolic'),
						'add_new_item'       => __('Add New License', 'foolic'),
						'edit_item'          => __('Edit License', 'foolic'),
						'new_item'           => __('New License', 'foolic'),
						'view_item'          => __('View License', 'foolic'),
						'search_items'       => __('Search Licenses', 'foolic'),
						'not_found'          => __('No licenses found', 'foolic'),
						'not_found_in_trash' => __('No licenses found in Trash', 'foolic'),
						'menu_name'          => __('Licenses', 'foolic')
					),
					'hierarchical'    => false,
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'capability_type' => FOOLIC_CPT_LICENSE,
					'supports'        => array('title'),
					'rewrite'         => false
				)
			);

			//register Log custom post type
			register_post_type(FOOLIC_CPT_LOG,
				array(
					'labels'          => array(
						'name'               => __('Logs', 'foolic'),
						'singular_name'      => __('Log', 'foolic'),
						'add_new'            => __('Add New', 'foolic'),
						'add_new_item'       => __('Add New Log', 'foolic'),
						'edit_item'          => __('Edit Log', 'foolic'),
						'new_item'           => __('New Log', 'foolic'),
						'view_item'          => __('View Log', 'foolic'),
						'search_items'       => __('Search Logs', 'foolic'),
						'not_found'          => __('No Log found', 'foolic'),
						'not_found_in_trash' => __('No Logs found in Trash', 'foolic'),
						'menu_name'          => __('Log', 'foolic')
					),
					'hierarchical'    => false,
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'capability_type' => FOOLIC_CPT_LOG,
					'supports'        => array('title', 'editor'),
					'rewrite'         => false
				)
			);

			//register license key custom post type
			register_post_type(FOOLIC_CPT_LICENSE_KEY,
				array(
					'labels'          => array(
						'name'               => __('License Keys', 'foolic'),
						'singular_name'      => __('License Key', 'foolic'),
						'add_new'            => __('Add New', 'foolic'),
						'add_new_item'       => __('Add New License Key', 'foolic'),
						'edit_item'          => __('Edit License Key', 'foolic'),
						'new_item'           => __('New License Key', 'foolic'),
						'view_item'          => __('View License Key', 'foolic'),
						'search_items'       => __('Search License Keys', 'foolic'),
						'not_found'          => __('No License Keys found', 'foolic'),
						'not_found_in_trash' => __('No License Keys found in Trash', 'foolic'),
						'menu_name'          => __('License Keys', 'foolic')
					),
					'hierarchical'    => false,
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'capability_type' => FOOLIC_CPT_LICENSE_KEY,
					'supports'        => array('title'),
					'rewrite'         => false
				)
			);

			//register domain custom post type
			register_post_type(FOOLIC_CPT_DOMAIN,
				array(
					'labels'          => array(
						'name'               => __('Domains', 'foolic'),
						'singular_name'      => __('Domain', 'foolic'),
						'add_new'            => __('Add New', 'foolic'),
						'add_new_item'       => __('Add New Domain', 'foolic'),
						'edit_item'          => __('Edit Domain', 'foolic'),
						'new_item'           => __('New Domain', 'foolic'),
						'view_item'          => __('View Domain', 'foolic'),
						'search_items'       => __('Search Domains', 'foolic'),
						'not_found'          => __('No Domains found', 'foolic'),
						'not_found_in_trash' => __('No Domains found in Trash', 'foolic'),
						'menu_name'          => __('Domains', 'foolic')
					),
					'hierarchical'    => false,
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'capability_type' => FOOLIC_CPT_DOMAIN,
					'supports'        => array('title'),
					'rewrite'         => false
				)
			);

			//register domain custom post type
			register_post_type(FOOLIC_CPT_RENEWAL,
				array(
					'labels'          => array(
						'name'               => __('Renewals', 'foolic'),
						'singular_name'      => __('Renewal', 'foolic'),
						'add_new'            => __('Add New', 'foolic'),
						'add_new_item'       => __('Add New Renewal', 'foolic'),
						'edit_item'          => __('Edit Renewal', 'foolic'),
						'new_item'           => __('New Renewal', 'foolic'),
						'view_item'          => __('View Renewal', 'foolic'),
						'search_items'       => __('Search Renewals', 'foolic'),
						'not_found'          => __('No Renewals found', 'foolic'),
						'not_found_in_trash' => __('No Renewals found in Trash', 'foolic'),
						'menu_name'          => __('Renewals', 'foolic')
					),
					'hierarchical'    => false,
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'capability_type' => FOOLIC_CPT_RENEWAL,
					'supports'        => array('title'),
					'rewrite'         => false
				)
			);
		}

		function posts_filter($query) {
			global $pagenow;

			if (current_user_can('manage_options') ||
				!current_user_can('read_' . FOOLIC_CPT_LICENSE)) return;

			$type = 'post';
			if (isset($_GET['post_type'])) {
				$type = $_GET['post_type'];
			}
			$queried_post_type = false;
			if (isset($query->query_vars['post_type'])) {
				$queried_post_type = $query->query_vars['post_type'];
				if (is_array($queried_post_type)) $queried_post_type = $queried_post_type[0];
			}
			if (($queried_post_type == FOOLIC_CPT_LICENSE || $type == FOOLIC_CPT_LICENSE)
				&& is_admin() && $pagenow == 'edit.php') {
				if (current_user_can('read_' . FOOLIC_CPT_LICENSE)) {
					$query->query_vars['author'] = get_current_user_id();
					add_filter('views_edit-' . FOOLIC_CPT_LICENSE, array(&$this, 'fix_license_counts'));
				}
			} else if ($queried_post_type == FOOLIC_CPT_LICENSE_KEY && $type == FOOLIC_CPT_LICENSE_KEY && is_admin() && $pagenow == 'edit.php') {
				if (current_user_can('read_' . FOOLIC_CPT_LICENSE_KEY)) {
					$query->query_vars['meta_key'] = foolic_licensekey::META_VENDOR;
					$query->query_vars['meta_value'] = get_current_user_id();
					add_filter('views_edit-' . FOOLIC_CPT_LICENSE_KEY, array(&$this, 'fix_licensekey_counts'));
				}
			}
		}

		function fix_license_counts($views) {
			global $current_user, $wp_query;

			unset($views['mine']);

			$types = array(
				array('status' => null),
				array('status' => 'publish'),
				array('status' => 'trash')
			);
			foreach ($types as $type) {
				$query  = array(
					'author'      => $current_user->ID,
					'post_type'   => FOOLIC_CPT_LICENSE,
					'post_status' => $type['status']
				);
				$result = new WP_Query($query);
				if ($result->found_posts == 0) {
					if ($type['status'] == null) {
						$views['all'] = sprintf('<a href="%s" class="current">%s <span class="count">(0)</span></a>',
							admin_url('edit.php?post_type=' . FOOLIC_CPT_LICENSE), __('All'));
					} else {
						unset($views[$type['status']]);
					}
				} else {
					$status = null;
					if (isset($wp_query->query_vars['post_status'])) {
						$status = $wp_query->query_vars['post_status'];
					}
					if ($type['status'] == null) {

						$views['all'] = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>',
							admin_url('edit.php?post_type=' . FOOLIC_CPT_LICENSE), ($status == null) ? ' class="current"' : '',
							__('All'), $result->found_posts);

					} else if ($type['status'] == 'publish') {

						$views['publish'] = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>',
							admin_url('edit.php?post_status=publish&post_type=' . FOOLIC_CPT_LICENSE), ($status == 'publish') ? ' class="current"' : '',
							__('Published'), $result->found_posts);

					} else if ($type['status'] == 'trash') {

						$views['trash'] = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>',
							admin_url('edit.php?post_status=trash&post_type=' . FOOLIC_CPT_LICENSE), ($status == 'trash') ? ' class="current"' : '',
							__('Trash'), $result->found_posts);
					}
				}
			}

			return $views;
		}

		function fix_licensekey_counts($views) {
			global $current_user, $wp_query;

			unset($views['mine']);

			$types = array(
				array('status' => null),
				array('status' => 'publish'),
				array('status' => 'trash')
			);
			foreach ($types as $type) {
				$query  = array(
					'author'      => $current_user->ID,
					'post_type'   => FOOLIC_CPT_LICENSE_KEY,
					'post_status' => $type['status']
				);
				$result = new WP_Query($query);
				if ($result->found_posts == 0) {
					if ($type['status'] == null) {
						$views['all'] = sprintf('<a href="%s" class="current">%s <span class="count">(0)</span></a>',
							admin_url('edit.php?post_type=' . FOOLIC_CPT_LICENSE_KEY), __('All'));
					} else {
						unset($views[$type['status']]);
					}
				} else {
					$status = null;
					if (isset($wp_query->query_vars['post_status'])) {
						$status = $wp_query->query_vars['post_status'];
					}
					if ($type['status'] == null) {

						$views['all'] = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>',
							admin_url('edit.php?post_type=' . FOOLIC_CPT_LICENSE_KEY), ($status == null) ? ' class="current"' : '',
							__('All'), $result->found_posts);

					} else if ($type['status'] == 'publish') {

						$views['publish'] = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>',
							admin_url('edit.php?post_status=publish&post_type=' . FOOLIC_CPT_LICENSE_KEY), ($status == 'publish') ? ' class="current"' : '',
							__('Published'), $result->found_posts);

					} else if ($type['status'] == 'trash') {

						$views['trash'] = sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>',
							admin_url('edit.php?post_status=trash&post_type=' . FOOLIC_CPT_LICENSE_KEY), ($status == 'trash') ? ' class="current"' : '',
							__('Trash'), $result->found_posts);
					}
				}
			}

			return $views;
		}
	}
}