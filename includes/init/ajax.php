<?php
/**
 * FooLicensing Admin Ajax handler
 * Date: 2013/03/26
 */
if (!class_exists('foolic_admin_ajax_handler')) {

    class foolic_admin_ajax_handler {

        function __construct() {
            add_action( 'wp_ajax_foolic_api', array($this, 'ajax_api') );
        }

        function ajax_api() {
            if ( wp_verify_nonce( $_REQUEST['nonce'], 'foolic_ajax_api_nonce' ) ) {

                $url = $_REQUEST['url'];
                $method = $_REQUEST['method'];
                $type = array_key_exists('type', $_REQUEST) ? $_REQUEST['type'] : 'normal';
                $request = $this->prepare_request($type);

                if ($method == 'POST') {
                    $response_raw = wp_remote_post($url, $request);
                } else {
                    $response_raw = wp_remote_get($url, $request);
                }

                if (is_wp_error($response_raw)) {
                    echo sprintf( __('Invalid API request : %s', 'foolic'), $response_raw->get_error_message());
                    die;
                } else {
                    if (wp_remote_retrieve_response_code($response_raw) != 200) {
                        echo sprintf( __('Invalid response code from API request : %s', 'foolic'), wp_remote_retrieve_response_code($response_raw));
                        die;
                    } else {
                        //all good!
                        $response = $response_raw['body'];
                        echo $response;
                        die;
                    }
                }
            }

            echo __('Invalid API request', 'foolic');
            die;
        }

        function safe_get($data, $key, $default) {
            if (!is_array($data)) return $default;
            $value = array_key_exists($key, $data) ? $data[$key] : NULL;
            if ($value === NULL) {
                return $default;
            }

            return $value;
        }

        function prepare_request($type) {
            global $wp_version;

            $site = $this->safe_get($_REQUEST, 'site', '');
            $license = $this->safe_get($_REQUEST, 'license', '');
            $slug = $this->safe_get($_REQUEST, 'slug', '');
            $version = $this->safe_get($_REQUEST, 'version', '');
            $ip = $this->safe_get($_REQUEST, 'ip', '');

            $action = $this->safe_get($_REQUEST, 'api_action', '');

            $arr = array(
                'body' => array(),
                'user-agent' => 'WordPress/' . $wp_version . '; ' . $site
            );

            $body = array();

            if (!empty($license)) $body['license'] = $license;
            if (!empty($site)) $body['site'] = $site;
            if (!empty($slug)) $body['slug'] = $slug;
            if (!empty($version)) $body['version'] = $version;
            if (!empty($ip)) $body['ip'] = $ip;

            if ($type === 'update') {
                //send a special update request
                $arr['body']['request'] = serialize($body);
                if (!empty($action)) $arr['body']['action'] = $action;
            } else {
                //do things normally
                $arr['body'] = $body;
            }

            return $arr;
        }
    }
}