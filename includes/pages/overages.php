<?php
/**
 * FooLicensing Overages Page
 * Date: 2013/03/26
 */

if ( !is_user_logged_in() ) {
    wp_die('You should not be here!');
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

?>
<div class="wrap">
    <div id="icon-options-general" class="icon32">
        <br />
    </div>

    <h2><?php _e('License Key Overages', 'foolic'); ?></h2>
</div>
