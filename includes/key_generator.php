<?php

/**
 * License Key generator
 * Adapted from the code of Jon la Cour
 * @version 1.0
 */

if (!class_exists('foolic_key_generator')) {

  class foolic_key_generator {

      /**
       * Main key generator.
       * @since 1.0
       */
      protected function _generate_key($length = 10, $user_key = false, $chars = '') {
          // Create an array for key characters
          $keychars = array();
          $key = '';

          if ($chars) {
              $keychars = explode(',', $chars);
              // Free some memory
              unset($chars);
          } else {
              $keychars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
          }

          if ($user_key) {
              $ip = str_replace('.', '', $_SERVER['REMOTE_ADDR']);
              $ip = str_replace(':', '', $ip);
              // srand & rand are used here to prevent seeding mt_rand which can
              // happen when generating different types of keys
              srand($ip);
              for ($i = 0; $i < $length; ++$i) {
                  $key .= $keychars[rand(0, 61)];
              }
              // Returns a key
              return $key;
          }

          for ($i = 0; $i < $length; ++$i) {
              $key .= $keychars[mt_rand(0, count($keychars) - 1)];
          }
          // Returns a key
          return $key;
      }

      /**
       * Generates a serial.
       * @since 1.0
       */
      public static function new_serial($pre = '', $segments = 4, $seglength = 4, $divider = '-') {
          // If the pre-chunk is not set than it will automatically generate one.
          if ($pre == '')
              $pre = self::_generate_key($seglength);

          $serial = '';
          $serial .= $pre;
          for ($i = 0; $i < $segments; ++$i) {
              $serial .= $divider . self::_generate_key($seglength);
          }
          $serial = strtoupper($serial);
          // Returns a serial
          return $serial;
      }
  }

}