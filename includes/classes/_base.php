<?php

if (!class_exists('foolic_base')) {

    require_once '_base.php';

    class foolic_base extends stdClass {

        public function to_array() {
            return (array)$this;
        }

        static function get_meta($data, $key, $default) {
            if (!is_array($data)) return $default;
            $value = array_key_exists($key, $data) ? $data[$key] : NULL;
            if ($value === NULL) {
                return $default;
            }

            return $value;
        }

        static function set_meta(&$data, $key, $value) {
            if (!is_array($data)) return;
            $data[$key] = $value;
        }

        static function set_checked(&$data, $key, $value) {
            if (!is_array($data)) return;
            if ($value === true) {
                $data[$key] = $value;
            } else {
                unset($data[$key]);
            }
        }

        static function is_checked($data, $key, $default = false) {
            if (!is_array($data)) return $default;

            if (array_key_exists($key, $data)) {
                return $data[$key] !== false;
            }
            return false;
        }

    }

}