<?php
/**
 * FooLicensing Admin Metaboxes
 * Created by brad.
 * Date: 2013/03/24
 */
if (!class_exists('foolic_metaboxes')) {

    require_once "metaboxes/licensekey_metaboxes.php";
    require_once "metaboxes/license_metaboxes.php";
    require_once "metaboxes/domain_metaboxes.php";

    class foolic_metaboxes {

        function __construct($plugin_file) {

            new foolic_license_metaboxes($plugin_file);
            new foolic_licensekey_metaboxes($plugin_file);
            new foolic_domain_metaboxes($plugin_file);

            //remove unwanted metaboxes
            add_action('add_meta_boxes', array(&$this, 'remove_meta_boxes'));
        }

        function remove_meta_boxes() {
            //make sure we do not display yoast SEO metabox on our post type pages
            remove_meta_box('wpseo_meta', FOOLIC_CPT_LICENSE, 'normal');
            remove_meta_box('wpseo_meta', FOOLIC_CPT_LICENSE_KEY, 'normal');
            remove_meta_box('wpseo_meta', FOOLIC_CPT_DOMAIN, 'normal');
            remove_meta_box('wpseo_meta', FOOLIC_CPT_LOG, 'normal');
        }
    }
}