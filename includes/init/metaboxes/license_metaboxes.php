<?php
/**
 * FooLicensing License Metaboxes
 * Date: 2013/03/24
 */
if (!class_exists('foolic_license_metaboxes')) {

    class foolic_license_metaboxes {

        private $_plugin_file;
		private $_license = false;

        function __construct($plugin_file) {
            $this->_plugin_file = $plugin_file;
            add_action('add_meta_boxes_' . FOOLIC_CPT_LICENSE, array(&$this, 'add_meta_boxes_to_license'));

            //save extra post data
            add_action('save_post', array(&$this, 'save_post_meta'));
        }

        function save_post_meta($post_id) {
            // check autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // verify nonce
            if (array_key_exists(FOOLIC_CPT_LICENSE . '_nonce', $_POST) &&
                wp_verify_nonce($_POST[FOOLIC_CPT_LICENSE . '_nonce'], plugin_basename($this->_plugin_file))
            ) {
                //if we get here, we are dealing with the license custom post type

                //get all the license data
                $data = apply_filters('foolic_save_license', $_POST[FOOLIC_CPT_LICENSE]);

                //and save it
                update_post_meta($post_id, FOOLIC_CPT_LICENSE, $data);

				//save override slug
				update_post_meta($post_id, foolic_license::META_UPDATE_SLUG, $_POST['update_override_slug']);

				do_action('foolic_save_post_meta_for_license', $post_id, $_POST);
            }
        }

        function add_meta_boxes_to_license($post) {

            add_meta_box(
                'foolicense_details',
                __('License Details', 'foolic'),
                array(&$this, 'render_license_details_metabox'),
                FOOLIC_CPT_LICENSE,
                'normal',
                'high'
            );

            add_meta_box(
                'foolicense_key',
                __('License Key Generation', 'foolic'),
                array(&$this, 'render_license_key_metabox'),
                FOOLIC_CPT_LICENSE,
                'normal',
                'high'
            );

            add_meta_box(
                'foolicense_update',
                __('Checking For Updates'),
                array(&$this, 'render_license_update_metabox'),
                FOOLIC_CPT_LICENSE,
                'normal',
                'high'
            );

			add_meta_box(
				'foolicense_vendor',
				__('Vendor'),
				array(&$this, 'render_license_vendor_metabox'),
				FOOLIC_CPT_LICENSE,
				'side',
				'default'
			);

            do_action('foolic_add_meta_boxes_to_license', $post);
        }

		function get_license($post) {
			if ($this->_license === false) {
				$this->_license = new foolic_license();
				$this->_license->load($post);
			}
			return $this->_license;
		}

		function render_license_vendor_metabox($post) {
			global $current_user;
			if( current_user_can( 'manage_options' ) ) {
				//we are admin, so show all vendor in a dropdown, including all admins
				$users = get_users('role=license_vendor');
				$admins = get_users('role=administrator');
				$users = array_merge($users, $admins);
			} else {
				$users[] = $current_user;
			} ?>
			<table class="form-table">
				<tbody>
					<tr>
						<td style="width:150px" class="first-column" valign="top"><?php _e('License Vendor', 'foolic'); ?></td>
						<td>
							<select name="post_author_override" id="post_author_override">
							<?php
							foreach($users as $user)
							{
								$sel = ($post->post_author == $user->ID)?"selected='selected'":'';
								echo '<option value="'.$user->ID.'"'.$sel.'>'.$user->display_name.'</option>';
							}
							?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}

        function render_license_details_metabox($post) {
            $license = $this->get_license($post);
            ?>
            <input type="hidden" name="<?php echo FOOLIC_CPT_LICENSE; ?>_nonce" id="<?php echo FOOLIC_CPT_LICENSE; ?>_nonce" value="<?php echo wp_create_nonce( plugin_basename($this->_plugin_file) ); ?>" />
            <table class="form-table">
            <tbody>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Domain Limit', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[domain_limit]" size="10" type="text" maxlength="4" value="<?php echo $license->domain_limit; ?>" />
                    <br />
                    <small><?php _e('The maximum number of domains a license key can be attached to. Use 0 for unlimited domains.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Expires In Days', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[expires_in_days]" type="text" size="10" maxlength="3" value="<?php echo $license->expires_in_days; ?>" />
                    <br />
                    <small><?php _e('The number of days that a license key is valid for. Use 0 to never expire.', 'foolic'); ?></small>
                </td>
            </tr>
            <?php
            do_action('foolic_render_license_details_metabox', $post);
            ?>
            </tbody>
            </table><?php
        }

        function render_license_key_metabox($post) {
			$license = $this->get_license($post);
            ?>
            <table class="form-table">
            <tbody>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Key Prefix', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[key_prefix]" size="10" maxlength="10" type="text" value="<?php echo $license->key_prefix; ?>" /><br />
                    <small><?php _e('The license key prefix, e.g. if set to "FOO" your license keys will look like "FOO-XXXX-XXXX-XXXX".', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Key Segments', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[key_segments]" size="2" maxlength="2" type="text" value="<?php echo $license->key_segments; ?>" /><br />
                    <small><?php _e('The number of segments in your license key, e.g. if set to "5" your license keys will look like "XXXX-XXXX-XXXX-XXXX-XXXX".', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Key Segment Length', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[key_segment_length]" size="2" maxlength="2" type="text" value="<?php echo $license->key_segment_length; ?>" /><br />
                    <small><?php _e('The length of each segment in your license key, e.g. if set to "3" your license keys will look like "XXX-XXX-XXX-XXX".', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Key Segment Divider', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[key_divider]" size="2" type="text" maxlength="1" value="<?php echo $license->key_divider; ?>" />
                    <small><?php _e('The divider between each segment in your license key, e.g. if set to " " your license keys will look like "XXXX XXXX XXXX XXXX".', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Example License Key', 'foolic'); ?></td>
                <td>
                    <strong><?php echo $license->generate_license_key(); ?></strong>
                </td>
            </tr>
            <?php
            do_action('foolic_render_license_key_metabox', $post);
            ?>
            </tbody>
            </table><?php
        }

        function render_license_update_metabox($post) {
			$license = $this->get_license($post);
            ?>
            <table class="form-table">
            <tbody>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Current Version', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_version]" size="10" maxlength="20" type="text" value="<?php echo $license->update_version; ?>" /><br />
                    <small><?php _e('The version number that will be used to determine if updates are required.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Plugin Slug', 'foolic'); ?></td>
                <td>
                    <input name="update_override_slug" size="10" maxlength="20" type="text" value="<?php echo $license->update_override_slug; ?>" /><br />
                    <small><?php _e('The plugin slug that is performing the update check. This should match the slug that is set for the plugin', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Updates Require License Key', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_require_license]" type="checkbox" value="on" <?php echo $license->update_require_license ? 'checked="checked"':'' ?> />
                    <br />
                    <small><?php _e('Do checks for updates require a valid license key?', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Last Updated Date', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_date]" size="20" maxlength="20" type="text" value="<?php echo $license->update_date; ?>" /><br />
                    <small><?php _e('The date of the last update or change.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Update Message', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_message]" size="130" type="text" value="<?php echo $license->update_message; ?>" /><br />
                    <small><?php _e('The message displayed when an update is available.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Update WordPress Version Compatible', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_version_compatible]" size="10" maxlength="20" type="text" value="<?php echo $license->update_version_compatible; ?>" /><br />
                    <small><?php _e('The WordPress version that the update is compatible with.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Update WordPress Version Required', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_version_required]" size="10" maxlength="20" type="text" value="<?php echo $license->update_version_required; ?>" /><br />
                    <small><?php _e('The minimum WordPress version that the update requires.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Update Expiry Time', 'foolic'); ?></td>
                <td>
                    <input name="<?php echo FOOLIC_CPT_LICENSE; ?>[update_expiry_time]" size="10" maxlength="20" type="text" value="<?php echo $license->update_expiry_time; ?>" /><br />
                    <small><?php _e('The amount of time that an update is available for until it expires.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Update Changelog', 'foolic'); ?></td>
                <td>
                    <?php
                    $args = array (
                        'media_buttons' => false,
                        'wpautop' => false
                    );
                    wp_editor( $license->update_changelog, FOOLIC_CPT_LICENSE.'[update_changelog]', $args ); ?>
                    <br />
                    <small><?php _e('The changelog that is displayed when an update is available.', 'foolic'); ?></small>
                </td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Validation Message', 'foolic'); ?></td>
                <td>
                    <?php
                    $args = array (
                        'media_buttons' => false,
                        'wpautop' => false
                    );
                    wp_editor( $license->validation_message, FOOLIC_CPT_LICENSE.'[validation_message]', $args ); ?>
                    <br />
                    <small><?php _e('The message that is shown to the user when they have attempted to validate a license key.', 'foolic'); ?></small>
                </td>
            </tr>

            <?php
            do_action('foolic_render_license_update_metabox', $post);
            ?>
            </tbody>
            </table><?php
        }
    }
}