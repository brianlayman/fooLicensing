<?php
/**
 * API controller to return details about a specific license in JSON format
 */

if (!class_exists('foolic_api_details')) {

    class foolic_api_details extends foolic_api_base_controller {

        function init() {
            add_action( 'foolic_api_get-details', array(&$this, 'execute'), 10, 3 );
        }

        function execute($license, $action, $args) {
            $this->init_controller($license, $action, $args);

            $lic = $this->get_licence();

            $details = array(
                'ID' => $lic->ID,
                'slug' => $lic->slug,
                'name' => $lic->name,
                'domain_limit' => $lic->domain_limit,
                'expires_in_days' => $lic->expires_in_days,
                'update_version' => $lic->update_version
            );

            header('Content-type: application/json');
            echo json_encode($details);
        }

    }

    $GLOBALS['foolic_api'][] = new foolic_api_details();

}