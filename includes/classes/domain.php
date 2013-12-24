<?php

if (!class_exists('foolic_domain')) {

    require_once '_base.php';

    class foolic_domain extends foolic_base {

        public static function get($post) {
            $domain = new foolic_domain();
            $domain->load($post);
            return $domain;
        }

        function foolic_domain($domain_url='') {
            $this->ID = 0;

            $domain_url = self::clean($domain_url);

            if (!empty($domain_url)) {
                $domain = get_page_by_title($domain_url, 'OBJECT', FOOLIC_CPT_DOMAIN);

                if ($domain) {
                    $this->load($domain);
                } else {
                    //there is no domain, create a new one
                    $this->create($domain_url);
                }
            }
        }

        function load($domain) {
            $this->ID = $domain->ID;
            $this->url = $domain->post_title;

            $data = get_post_meta($this->ID, FOOLIC_CPT_DOMAIN, true);

            $this->localhost = self::is_checked($data, 'localhost', false);
            $this->blacklisted = self::is_checked($data, 'blacklisted', false);
            $this->marked_for_blacklisting = self::is_checked($data, 'marked_for_blacklisting', false);
        }

        function create($domain_url) {

            $url = self::clean($domain_url);

            //save the domain post
            $domain_post_args = array(
                'post_type' => FOOLIC_CPT_DOMAIN,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_title' => $url
            );

            //insert the domain post
            $this->ID = wp_insert_post($domain_post_args, true);

            //create meta for the domain
            $meta = array(
                'localhost' => self::is_localhost($url),
                'blacklisted' => false,
                'marked_for_blacklisting' => false
            );

            add_post_meta($this->ID, FOOLIC_CPT_DOMAIN, $meta, true);

            $this->url = $url;
            $this->localhost = self::is_localhost($url);
            $this->blacklisted = false;
            $this->marked_for_blacklisting = false;
        }

        public static function clean($url) {
            return strtolower(rtrim($url, '/'));
        }

        public static function is_localhost($url) {
            $parsed = parse_url(self::clean($url));
            if ($parsed === false) return false;
            return $parsed['host'] === 'localhost';
        }
    }

}