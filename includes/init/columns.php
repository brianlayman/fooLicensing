<?php
/**
 * FooLicensing Admin Columns
 * Date: 2013/03/25
 */
if (!class_exists('foolic_columns')) {

    class foolic_columns {

        function __construct() {
            add_filter('manage_edit-' . FOOLIC_CPT_LICENSE . '_columns', array(&$this, 'license_custom_columns'));
            add_filter('manage_edit-' . FOOLIC_CPT_LICENSE_KEY . '_columns', array(&$this, 'licensekey_custom_columns'));
            add_filter('manage_edit-' . FOOLIC_CPT_DOMAIN . '_columns', array(&$this, 'domain_custom_columns'));
            add_filter('manage_edit-' . FOOLIC_CPT_LOG . '_columns', array(&$this, 'log_custom_columns'));

            add_action('manage_posts_custom_column', array(&$this, 'custom_column_content'));
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
                    break;
//                case FOOLIC_CPT_LOG . '_license':
//                    $data = get_post_meta($post->ID, 'sales-meta', true);
//                    $license = $this->safe_get($data, 'license_key', '?');
//                    $license_id = $this->safe_get($data, 'license_id', 0);
//                    if ($license_id > 0) {
//                        $post_type_object = get_post_type_object(self::CPT_LICENSE);
//                        $url = admin_url(sprintf($post_type_object->_edit_link . '&action=edit', $license_id));
//                        echo "<a href='$url'>$license</a>";
//                    } else {
//                        echo $license;
//                    }
//                    break;
//                case FOOLIC_CPT_LOG . '_domain_count':
//                    $count = get_post_meta($post->ID, 'domain_count', true);
//                    if ($count == '') {
//                        echo '0';
//                    } else {
//                        echo $count;
//                    }
//                    break;
            }
        }        
    }
}