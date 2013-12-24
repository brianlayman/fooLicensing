<?php
/**
 * Initialize the FooLicensing API
 */

if (!class_exists('foolic_rewrite_rules')) {

    class foolic_rewrite_rules {

        const ARG_API = 'fooapi';
        const ARG_LICENSE = 'foolicense';
        const ARG_ACTION = 'action';
        const ARG_ARGS = 'args';

        const REWRITE_RULE_BASE = 'api';
        const REWRITE_RULE_TYPE_ACTION_ARGS = '^%s/([^/]*)/([^/]*)/([^/]*)/?';  // catch any urls like : /api/license-type-slug/action/args
        const REWRITE_RULE_TYPE_ACTION = '^%s/([^/]*)/([^/]*)/?';               // catch any urls like : /api/license-type-slug/action
        const REWRITE_RULE_TYPE = '^%s/([^/]*)/?';                              // catch any urls like : /api/license-type-slug

        function __construct() {
            //extra check to make sure the rewrite rules are loaded
            add_action( 'wp_loaded', array(&$this, 'check_rewrite_rules_loaded') );

            // Activation hook
            register_activation_hook( __FILE__ , array(&$this, 'activate') );

            // Deactivation hook
            register_deactivation_hook( __FILE__ , array(&$this, 'deactivate') );

            //add init hook to add rewrite tags
            add_action('init', array(&$this, 'add_rewrite_tags') );

            // Template redirect filter
            add_filter('template_redirect', array(&$this, 'redirect') );

            if (!empty($GLOBALS['foolic_api'])) {
                foreach ($GLOBALS['foolic_api'] as $api_controller) {
                    $api_controller->init();
                }
            }
        }

        function check_rewrite_rules_loaded() {
            $rules = get_option( 'rewrite_rules' );

            $rule = sprintf(self::REWRITE_RULE_TYPE_ACTION_ARGS, self::REWRITE_RULE_BASE);

            if ( ! isset( $rules[$rule] ) ) {
                $this->activate();
            }
        }

        function activate() {
            // catch any urls like : /api/license-type-slug/action/args
            add_rewrite_rule(sprintf(self::REWRITE_RULE_TYPE_ACTION_ARGS, self::REWRITE_RULE_BASE),
                'index.php?'.self::ARG_API.'=true&'.self::ARG_LICENSE.'=$matches[1]&'.self::ARG_ACTION.'=$matches[2]&'.self::ARG_ARGS.'=$matches[3]', 'top');

            // catch any urls like : /api/license-type-slug/action
            add_rewrite_rule(sprintf(self::REWRITE_RULE_TYPE_ACTION, self::REWRITE_RULE_BASE),
                'index.php?'.self::ARG_API.'=true&'.self::ARG_LICENSE.'=$matches[1]&'.self::ARG_ACTION.'=$matches[2]', 'top');

            // catch any urls like : /api/license-type-slug
            add_rewrite_rule(sprintf(self::REWRITE_RULE_TYPE, self::REWRITE_RULE_BASE),
                'index.php?'.self::ARG_API.'=true&'.self::ARG_LICENSE.'=$matches[1]', 'top');

            // url : /api/
            add_rewrite_rule('api', 'index.php?'.self::ARG_API.'=true', 'top');

            flush_rewrite_rules();
        }

        function deactivate() {
            flush_rewrite_rules();
        }

        function add_rewrite_tags() {
            add_rewrite_tag('%'.self::ARG_API.'%', 'true');
            add_rewrite_tag('%'.self::ARG_LICENSE.'%', '([^&]+)');
            add_rewrite_tag('%'.self::ARG_ACTION.'%', '([^&]+)');
            add_rewrite_tag('%'.self::ARG_ARGS.'%', '([^&]+)');
        }

        function redirect() {
            global $wp_query;

            if ( isset($wp_query->query_vars[self::ARG_API]) ) {
                if ( isset($wp_query->query_vars[self::ARG_LICENSE]) ) {
                    $license_type = $wp_query->query_vars[self::ARG_LICENSE];

                    if ( isset($wp_query->query_vars[self::ARG_ACTION]) ) {
                        $action = $wp_query->query_vars[self::ARG_ACTION];
                    } else {
                        $action = '';
                    }

                    if ( isset($wp_query->query_vars[self::ARG_ARGS]) ) {
                        $args = $wp_query->query_vars[self::ARG_ARGS];
                    } else {
                        $args = '';
                    }

                    $method = strtolower( $_SERVER['REQUEST_METHOD'] );

                    do_action('foolic_api_call', $method, $license_type, $action, $args);
                    do_action('foolic_api_'.$method.'-'.$action, $license_type, $action, $args);

                    exit;
                }
            }
        }

        public static function generate_url($slug, $action, $args = false) {
            global $wp_rewrite;
            if ($wp_rewrite->permalink_structure == '/%postname%/') {
                //we have pretty URLS

                $url = home_url('/'.self::REWRITE_RULE_BASE.'/' . $slug . '/'.$action);
                if ($args !== false) {
                    $url .= '/' . $args;
                }
            } else {
                $url = home_url() . '?' . self::ARG_API . '=true&' . self::ARG_LICENSE . '=' . $slug . '&' . self::ARG_ACTION . '=' . $action;
                if ($args !== false) {
                    $url .= http_build_query($args);
                }
            }

            return $url;
        }

    }
}