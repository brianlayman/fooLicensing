<?php
/**
 * FooLicensing Admin Columns
 * Date: 2013/03/25
 */
if (!class_exists('foolic_columns')) {

    class foolic_columns {

        function __construct() {
            add_filter('manage_edit-' . FOOLIC_CPT_LICENSE . '_columns', array($this, 'license_custom_columns'));
            add_filter('manage_edit-' . FOOLIC_CPT_LICENSE_KEY . '_columns', array($this, 'licensekey_custom_columns'));
            add_filter('manage_edit-' . FOOLIC_CPT_DOMAIN . '_columns', array($this, 'domain_custom_columns'));
            add_filter('manage_edit-' . FOOLIC_CPT_LOG . '_columns', array($this, 'log_custom_columns'));

            add_action('manage_posts_custom_column', array($this, 'custom_column_content'));

			add_action('restrict_manage_posts', array($this, 'licensekey_filter_list') );
			add_filter('parse_query', array($this, 'licensekey_filtering') );
        }

        function license_custom_columns($columns) {
            $columns = array(
                'cb' => '<input type="checkbox" />',
                'title' => __('License', 'foolic'),
                FOOLIC_CPT_LICENSE . '_domains' => __('Allowed Domains', 'foolic'),
                FOOLIC_CPT_LICENSE . '_expires' => __('Expires', 'foolic'),
                FOOLIC_CPT_LICENSE . '_version' => __('Update Version', 'foolic')
            );

            return $columns;
        }

        function log_custom_columns($columns) {
//            $columns = array(
//                'cb' => '<input type="checkbox" />',
//                'date' => __('Date', 'foolic'),
//                FOOLIC_CPT_LOG . '_licensekey' => __('License Key', 'foolic'),
//                FOOLIC_CPT_LOG . '_customer' => __('Customer', 'foolic')
//            );

            return $columns;
        }

        function licensekey_custom_columns($columns) {
            $columns = array(
                'cb' => '<input type="checkbox" />',
                'title' => __('License Key', 'foolic'),
                FOOLIC_CPT_LICENSE_KEY . '_issued' => __('Issued', 'foolic'),
                FOOLIC_CPT_LICENSE_KEY . '_status' => __('Status', 'foolic')
            );

            return $columns;
        }

        function domain_custom_columns($columns) {
            return $columns;
        }

        function safe_get($array, $key, $default = NULL) {
            if (!is_array($array)) return $default;
            $value = array_key_exists($key, $array) ? $array[$key] : NULL;
            if ($value === NULL)
                return $default;

            return $value;
        }

        function custom_column_content($column) {
            global $post;
            switch ($column) {
                case FOOLIC_CPT_LICENSE . '_version':
                    $license = new foolic_license();
                    $license->load($post);
                    echo $license->update_version;
                    break;
                case FOOLIC_CPT_LICENSE . '_domains':
                    $license = new foolic_license();
                    $license->load($post);
                    $limit =  $license->domain_limit;
					if ($limit == '' || $limit == '0') {
						echo 'Unlimited';
					} else {
						echo $limit;
					}
                    break;
                case FOOLIC_CPT_LICENSE . '_expires':
                    $license = new foolic_license();
                    $license->load($post);
                    $days = $license->expires_in_days;

                    if ($days == '' || $days == '0') {
                        echo 'Never';
                    } else {
                        echo sprintf(__('%s days', 'foolic'), $days);
                    }
                    break;
                case FOOLIC_CPT_LICENSE_KEY . '_issued':
                    echo date('d M Y', strtotime($post->post_date));
                    break;
                case FOOLIC_CPT_LICENSE_KEY . '_status':
                    $licensekey = new foolic_licensekey();
                    $licensekey->load($post);
                    $valid = foolic_licensekey_checker::validate_license_key($licensekey);
                    echo '<span title="' . $valid['message'] . '" style="color:' . $valid['color'] . '">' . $valid['status'] . '</span>';
					if ( $licensekey->has_exceeded_domain_limit() ) {
						echo ' ' . __('Usage:','foolic') . ' ('  . $licensekey->usage_html(). ')';
					}
					if ( $licensekey->has_expired() ) {
						echo ' ' . __('Expires:','foolic') . ' ' . $licensekey->expires;
					}
                    break;
            }
        }

		function licensekey_filter_list() {
			$screen = get_current_screen();
			global $wp_query;
			if ( $screen->post_type == FOOLIC_CPT_LICENSE_KEY ) {
				$filter = isset( $_GET['licensekey_filter_status'] ) ? $_GET['licensekey_filter_status'] : '';

				echo '<select name="licensekey_filter_status"><option>'. __('All Statuses', 'foolic') .'</option>';

				echo '<option value="expiring"' . ($filter == 'expiring' ? ' selected="selected"' : '') . '>' . __('Expiring', 'foolic') . '</option>';
				echo '<option value="expired"' . ($filter == 'expired' ? ' selected="selected"' : '') . '>' . __('Expired', 'foolic') . '</option>';
				echo '<option value="exceeded"' . ($filter == 'exceeded' ? ' selected="selected"' : '') . '>' . __('Exceeded', 'foolic') . '</option>';

				echo '</select>';
			}
		}

		function licensekey_filtering( $query ) {
			if ( !function_exists('get_current_screen') ) return;
			$screen = get_current_screen();
			if ( !isset( $screen ) ) return;

			$qv = &$query->query_vars;


			if ( $screen->post_type == FOOLIC_CPT_LICENSE_KEY && $query->query['post_type'] == FOOLIC_CPT_LICENSE_KEY ) {

				$filter = isset( $_GET['licensekey_filter_status'] ) ? $_GET['licensekey_filter_status'] : '';

				if ( $filter ) {

					if ( 'exceeded' === $filter ) {
						$qv['meta_query'][] = array(
							'key'     => 'foolic_exceeded',
							'value'   => 1,
							'compare' => '='
						);
						$qv['meta_query'][] =array(
							'key'     => 'foolic_deactivated',
							'value'	=> 1,
							'compare' => 'NOT EXISTS'
						);
					} else if ('expiring' == $filter ) {

						$qv['meta_query'][] = array(
							'key'     => 'foolic_expires',
							'value'   => array(
								current_time( 'timestamp' ),
								strtotime( '+1 month' )
							),
							'compare' => 'BETWEEN'
						);
						$qv['meta_query'][] = array(
							'key'     => 'foolic_deactivated',
							'value'	=> 1,
							'compare' => 'NOT EXISTS'
						);

					} else if ('expired' == $filter ) {
						$qv['meta_query'][] = array(
							'key'     => 'foolic_expires',
							'value'   => current_time( 'timestamp' ),
							'compare' => '<='
						);
						$qv['meta_query'][] = array(
							'key'     => 'foolic_deactivated',
							'value'	=> 1,
							'compare' => 'NOT EXISTS'
						);
					}
				}

			}
		}
	}
}
