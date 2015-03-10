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

	<?php

		if (!get_option('foolic_licensekey_setpostmeta')) {
			$args = array(
				'post_type'    => FOOLIC_CPT_LICENSE_KEY,
				'nopaging'     => true,
				'fields'       => 'ids'
			);

			$query = new WP_Query;
			$keys  = $query->query( $args );
			if ( $keys ) {

				foreach( $keys as $licensekey_id ) {
					$key = foolic_licensekey::get_by_id( $licensekey_id );
					$key->store_meta_data();
				}
			}
			_e('Post meta updated!', 'foolic');
			add_option('foolic_licensekey_setpostmeta', true);
		} else {

			echo '<h3>' . __('Summary') . '</h3>';

			echo 'Expiring license keys: ' . count(foolic_get_expiring_licensekeys()) . '<br />';

			echo 'Expired license keys: ' . count(foolic_get_expired_licensekeys()) . '<br />';

			echo 'Exceeded license keys: ' . count(foolic_get_exceeded_licensekeys()) . '<br />';

			return;

			$filter = isset( $_GET['licensekey_filter_status'] ) ? $_GET['licensekey_filter_status'] : '';

			echo __('Overage Type','foolic') . '<select name="licensekey_filter_status"><option>'. __('--select--', 'foolic') .'</option>';

			echo '<option value="expiring"' . ($filter == 'expiring' ? ' selected="selected"' : '') . '>' . __('Expiring', 'foolic') . '</option>';
			echo '<option value="expired"' . ($filter == 'expired' ? ' selected="selected"' : '') . '>' . __('Expired', 'foolic') . '</option>';
			echo '<option value="exceeded"' . ($filter == 'exceeded' ? ' selected="selected"' : '') . '>' . __('Exceeded', 'foolic') . '</option>';

			echo '</select>';

			if ( 'exceeded' === $filter ) {

			} else if ('expiring' == $filter ) {

			} else if ('expired' == $filter ) {

			}


		}

	?>


</div>
