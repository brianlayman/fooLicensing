<?php
/**
 * FooLicensing Admin Settings
 */

if ( !class_exists( 'foolic_admin_settings' ) ) {
	class foolic_admin_settings {

		/**
		 * @param foolicensing $foolic
		 */
		function __construct($foolic) {
			$foolic->admin_settings_add_tab( 'general', __( 'General', 'foolicensing' ) );

			$foolic->admin_settings_add_section_to_tab( 'general', 'license', __( 'License', 'foolicensing' ) );

			$foolic->admin_settings_add( array(
				'id'         => 'license',
				'title'      => __( 'FooLicensing License Key', 'foolicensing' ),
				'type'       => 'license',
				'section'    => 'license',
				'tab'        => 'general',
				'update_url' => foolicensing::UPDATE_URL
			) );

			$foolic->admin_settings_add_section_to_tab( 'general', 'updates', __( 'Admin Updates', 'foolicensing' ) );

			$foolic->admin_settings_add( array(
				'id'      => 'update_popup_css',
				'title'   => __( 'Update Popup CSS path', 'foolicensing' ),
				'type'    => 'text',
				'section' => 'updates',
				'tab'     => 'general'
			) );

			$foolic->admin_settings_add_tab( 'messages', __( 'Messages', 'foolicensing' ) );

			$foolic->admin_settings_add_section_to_tab( 'messages', 'detachments', __( 'Domain Detachments', 'foolicensing' ) );

			$foolic->admin_settings_add( array(
				'id'      => 'detach_message_success',
				'title'   => __( 'Detachment Success Message', 'foolicensing' ),
				'type'    => 'textarea',
				'default' => __( 'The domain has been successfully detached. Please make sure that you remove the license key from the plugin settings page. If you do not remove it, the domain will attach itself to the license key again automatically.', 'foolicensing' ),
				'section' => 'detachments',
				'tab'     => 'messages',
				'class'   => 'medium_textarea'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'detach_message_error',
				'title'   => __( 'Detachment Failure Message', 'foolicensing' ),
				'type'    => 'textarea',
				'default' => __( 'The domain could not be detached!', 'foolicensing' ),
				'section' => 'detachments',
				'tab'     => 'messages',
				'class'   => 'medium_textarea'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'attach_message_success',
				'title'   => __( 'Attachment Success Message', 'foolicensing' ),
				'type'    => 'textarea',
				'default' => __( 'The domain has been successfully attached.', 'foolicensing' ),
				'section' => 'detachments',
				'tab'     => 'messages',
				'class'   => 'medium_textarea'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'attach_message_error',
				'title'   => __( 'Attachment Failure Message', 'foolicensing' ),
				'type'    => 'textarea',
				'default' => __( 'The domain could not be attached!', 'foolicensing' ),
				'section' => 'detachments',
				'tab'     => 'messages',
				'class'   => 'medium_textarea'
			) );

			$foolic->admin_settings_add_tab( 'renewals', __( 'Renewals', 'foolicensing' ) );

			$foolic->admin_settings_add_section_to_tab( 'renewals', 'renewals', __( 'License Renewals', 'foolicensing' ) );

			$foolic->admin_settings_add( array(
				'id'      => 'enable_renewals',
				'title'   => __( 'Enable License Renewals', 'foolicensing' ),
				'type'    => 'checkbox',
				'section' => 'renewals',
				'tab'     => 'renewals'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'renewal_discount_early',
				'title'   => __( '% Discount For Early Renewals', 'foolicensing' ),
				'type'    => 'text',
				'default' => '50',
				'section' => 'renewals',
				'tab'     => 'renewals'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'renewal_discount_grace',
				'title'   => __( '% Discount For Renewals Within Grace Period', 'foolicensing' ),
				'type'    => 'text',
				'default' => '20',
				'section' => 'renewals',
				'tab'     => 'renewals'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'renewal_discount_late',
				'title'   => __( '% Discount For Late Renewals', 'foolicensing' ),
				'type'    => 'text',
				'default' => '10',
				'section' => 'renewals',
				'tab'     => 'renewals'
			) );

			$foolic->admin_settings_add( array(
				'id'      => 'renewal_grace_period',
				'title'   => __( 'Grace Period (days)', 'foolicensing' ),
				'type'    => 'text',
				'default' => '30',
				'section' => 'renewals',
				'tab'     => 'renewals'
			) );
		}
	}
}