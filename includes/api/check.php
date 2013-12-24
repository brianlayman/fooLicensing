<?php
/**
 * API controller to check license
 */

if (!class_exists('foolic_api_check')) {

    class foolic_api_check extends foolic_api_base_controller {

        private $request_args;
        private $response;
        private $validator_response;
		private $license_message;

        function init() {
            add_action( 'foolic_api_get-check', array(&$this, 'execute_get'), 10, 3 );
            add_action( 'foolic_api_post-check', array(&$this, 'execute_check'), 10, 3 );
            add_action( 'foolic_api_post-check-with-info', array(&$this, 'execute_check_info'), 10, 3 );
        }

        function execute_get($slug, $action, $args) {

            $this->init_controller($slug, $action, $args);

            $l = $this->get_licence();

            $json_array = array(
                'slug' => $slug,
                'date_requested' => date(DATE_RFC822),
                'version' => $l->update_version,
                'date' => date(DATE_RFC822, strtotime($l->update_date))
            );

            header('Content-type: application/json');
            echo json_encode($json_array);
        }

        function validate_request() {
			//check to see we have the request arguments
			if (!$this->has_post_var('request')) {
				$this->response->upgrade_notice = __('Invalid API request!');
				echo serialize($this->response);
				die;
			}
		}

		function execute_base($slug, $action, $args) {

			//init!
            $this->init_controller($slug, $action, $args, false);

			//create new response
			$this->response = new stdClass();

			//validate!
			$this->validate_request();

			//get the request args
			$this->request_args = self::array_to_object(unserialize(stripslashes($_POST['request'])));

			$this->attempt_to_load_license_from_license_key();

			//get the slug
            $this->response->slug = $this->get_license_slug();

			//check to see if our license is valid
			if ($this->_license->ID == 0) {
				$this->response->upgrade_notice = __('No license found!!');
				echo serialize($this->response);
				die;
			}
        }

		function attempt_to_load_license_from_license_key() {

			$key = $this->get_requested_license_key();

			if (!empty($key)) {
				//load the license key
				$license_key = foolic_get_license_key($this->get_requested_license_key());

				//if the license key exists, then set the license to it's license
				if ($license_key->ID > 0) {
					$this->_license = $license_key->get_license();
				}
			}
		}

        function get_license_slug() {
            if (!empty($this->get_licence()->update_override_slug)) {
                return $this->get_licence()->update_override_slug;
            }

            return $this->slug;
        }

		function get_requested_license_key() {
			return isset($this->request_args->license) ? $this->request_args->license : '';
		}

		function get_requested_site() {
			return isset($this->request_args->site) ? $this->request_args->site : '';
		}

		function get_requested_ip() {
			return isset($this->request_args->ip) ? $this->request_args->ip : '';
		}

		function do_check() {
			$license_key = $this->get_requested_license_key();
			$site = $this->get_requested_site();
			$ip = $this->get_requested_ip();

            //validate all is good with the license
            $checker = new foolic_licensekey_checker();
            $this->validator_response = $checker->validate($license_key, $this->slug, $site, $ip);

			if ($checker->get_license_instance() !== false) {
				if ($this->validator_response['valid'] === true)
					$this->license_message = sprintf( __('You have a valid %s license key : ', 'foolicensing'), $checker->get_license_instance()->name );
				else
					$this->license_message = sprintf( __('You do not have a valid %s license key : ', 'foolicensing'), $checker->get_license_instance()->name );
			}

            $this->response->message = $this->validator_response['message'];

            if (!$this->validator_response['valid']) {
                $this->response->upgrade_notice = $this->validator_response['message'];
            }
        }

        function execute_check($slug, $action, $args) {

            $this->execute_base($slug, $action, $args);

            $license = $this->get_licence();

            //do a simple version check
            if (version_compare($this->request_args->version, $license->update_version, '<')) {
                $this->response->new_version = $license->update_version;
            } else {
                //if we are checking and the version is up to date then get out now
                echo serialize($this->response);
                die;
            }

            $this->do_check();

            if ($this->validator_response['valid']) {
                //validated fine - lets continue to check for an update
                $this->response->package = $license->generate_update_package_url();
                $this->response->upgrade_notice = $license->update_message;
            }

            echo serialize($this->response);
            die;
        }

        function execute_check_info($slug, $action, $args) {
            $this->execute_base($slug, $action, $args);

            $license = $this->get_licence();

            $this->do_check();

            if ($this->validator_response['valid']) {
                //getting the plugin info
                $this->response->last_updated = $license->update_date;
                $this->response->download_link = $license->generate_update_package_url();
                $this->response->tested = $license->update_version_compatible;
                $this->response->requires = $license->update_version_required;
            }

            $this->response->date = $license->update_date;
            $this->response->body = $this->generate_response($license);

//          $response->sections = array(
//            'description' => '<h2>$_POST</h2><small><pre>'.var_export($_POST, true).'</pre></small>'
//                . '<h2>Args</h2><small><pre>'.var_export($args, true).'</pre></small>'
//                . '<h2>Response</h2><small><pre>'.var_export($response, true).'</pre></small>'
//                . '<h2>License</h2><small><pre>'.var_export($license, true).'</pre></small>'
//                . '<h2>Error</h2><small><pre>'.var_export(error_get_last(), true).'</pre></small>',
//            'changelog' => '<p>'.$prod->changelog.'</p>'
//          );

            echo serialize($this->response);
            die;
        }

        function generate_response($license) {

            $options = get_option('foolic');
            $update_popup_css = $options['update_popup_css'];

            if (empty($update_popup_css)) {
                $update_popup_css = apply_filters('foolic_default_popup_css_url', '');
            }

            if (!empty($update_popup_css)) {
                $update_popup_css = '
    ' . sprintf('<link rel="stylesheet" type="text/css" media="all" href="%s" />', $update_popup_css) . '
';
            }

            $response = '<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8" />' . $update_popup_css . '
</head>
<body>
';
            $class = $this->validator_response['valid'] ? 'notice' : 'error';
            $response .= '<div class="' . $class . '">' . $this->license_message . ' <strong style="color:' . $this->validator_response['color'] . '">' . $this->validator_response['message'] . '</strong></div>';
            if (!empty($license->validation_message)) {
                $response .= '<div>' . $license->validation_message . '</div>';
            }

            $response .= $license->update_changelog;

            $response .= '
</body>
</html>';

            return $response;
        }
    }

    $GLOBALS['foolic_api'][] = new foolic_api_check();
}