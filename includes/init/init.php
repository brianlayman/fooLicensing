<?php
/**
 * Foolicensing main initialization
 * Date: 2013/03/25
 */

require_once 'rewrite_rules.php';
require_once 'custom_post_types.php';
require_once 'metaboxes.php';
require_once 'menus.php';
require_once 'columns.php';
require_once 'post_relationships.php';
require_once 'ajax.php';
require_once 'roles.php';

function foolic_init() {
    new foolic_rewrite_rules();
    new foolic_custom_post_types();
    new foolic_post_relationships();
}

function foolic_admin_init($plugin_file, $foolicensing) {
    new foolic_metaboxes($plugin_file);
    new foolic_menus($foolicensing);
    new foolic_columns();
    new foolic_admin_ajax_handler();
	new foolic_roles();
}