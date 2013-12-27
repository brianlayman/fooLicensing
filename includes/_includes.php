<?php
require_once 'api/_includes.php';
require_once 'constants.php';
require_once 'classes/_base.php';
require_once 'classes/license.php';
require_once 'classes/licensekey.php';
require_once 'classes/domain.php';
require_once 'classes/log.php';
require_once 'licensekey_checker.php';
require_once 'key_generator.php';
require_once 'functions.php';
require_once 'renewals.php';
require_once 'upgrades.php';
require_once 'templates.php';
require_once 'ajax.php';
require_once 'scripts.php';
require_once 'styles.php';

if ( is_admin() ) {
	require_once 'admin_settings.php';
} else {
	require_once 'shortcodes.php';
	require_once 'ajax.php';
}