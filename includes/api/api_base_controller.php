<?php
/**
 * API base Controller class
 */

if (!class_exists('foolic_api_base_controller')) {

    class foolic_api_base_controller {
        protected $slug = 'unknown';
        protected $action = 'details';
        protected $args = '';
        protected $_license = false;

        function init() {
            wp_die("The API Controller was not initialized correctly!");
        }

        function init_controller($slug, $action = 'unknown', $args = false, $assert_valid_license = true) {
            $this->slug = $slug;
            $this->action = $action;
            $this->args = $args;

			if ($assert_valid_license) {
            	$this->assert_valid_license();
			}
        }

        function assert_valid_license() {
            $license = $this->get_licence();
            if ($license === false || $license->ID === 0) {
                wp_die("There was a problem fetching the license information!");
            }
        }

        function assert_is_post() {
            if (!$this->is_post()) {
                wp_die("There was a problem with the API request!");
            }
        }

        function is_post() {
            return $_SERVER['REQUEST_METHOD'] === 'POST';
        }

        function has_post_var($var) {
            return array_key_exists($var, $_POST);
        }

        function get_post_var($var) {
            if ($this->has_post_var($var)) {
                return $_POST[$var];
            }
            return false;
        }

        function has_get_var($var) {
            return array_key_exists($var, $_GET);
        }

        function get_get_var($var) {
            if ($this->has_get_var($var)) {
                return $_GET[$var];
            }
            return false;
        }

        function get_licence() {
            //lazy load the license
            if ($this->_license === false) {
                $this->_license = foolic_find_license($this->slug);
            }

            return $this->_license;
        }

        static function array_to_object($array = array()) {
            if (empty($array) || !is_array($array))
                return false;

            $data = new stdClass;
            foreach ($array as $akey => $aval) {
                $data->{$akey} = $aval;
            }
            return $data;
        }

        function get_option($key, $default = false) {
            $options = get_option('foolic');
            if ($options) {
                return (array_key_exists($key, $options)) ? $options[$key] : $default;
            }

            return $default;
        }

    }

}