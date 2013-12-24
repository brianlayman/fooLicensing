<?php
/**
 * Render the license key metaboxes
  * Date: 2013/03/24
 */
if (!class_exists('foolic_licensekey_metaboxes')) {

    class foolic_licensekey_metaboxes {

        private $_plugin_file;

        function __construct($plugin_file) {
            $this->_plugin_file = $plugin_file;
            add_action('add_meta_boxes_' . FOOLIC_CPT_LICENSE_KEY, array(&$this, 'add_meta_boxes_to_license_key'));

            //save extra post data
            add_action('save_post', array(&$this, 'save_post_meta'), 20);
        }

        function save_post_meta($post_id) {
            // check autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // verify nonce
            if (array_key_exists(FOOLIC_CPT_LICENSE_KEY . '_nonce', $_POST) &&
                wp_verify_nonce($_POST[FOOLIC_CPT_LICENSE_KEY . '_nonce'], plugin_basename($this->_plugin_file))
            ) {
                //if we get here, we are dealing with the licensekey custom post type

                //get all the license data
                $data = apply_filters('foolic_save_licensekey', $_POST[FOOLIC_CPT_LICENSE_KEY]);

                //and save it
                update_post_meta($post_id, FOOLIC_CPT_LICENSE_KEY, $data);

                //save some meta so that I know it is not new
                update_post_meta($post_id, foolic_licensekey::META_ISNEW, 'NOT');

				//load the licensekey object
				$license_key = foolic_licensekey::get_by_id($post_id);

				//update the licensekey vendor from the license vendor [this is precautionary to make sure the data is valid]
				$license_vendor = $license_key->get_license()->author;
				update_post_meta($post_id, foolic_licensekey::META_VENDOR, $license_vendor);

				//update the author to be the connected user [this is precautionary to make sure the data is valid]
				$this->save_correct_vendor($license_key);

                //process attached domains
                $license_key->process_domains();

				do_action('foolic_save_post_meta_for_license_key', $post_id, $_POST);
            }
        }

		//update the licensekey vendor to be the connected user
		function save_correct_vendor($license_key) {
			//unhook this function so it doesn't loop infinitely
			remove_action('save_post', array(&$this, 'save_post_meta'), 20);

			$author_id = $license_key->get_user()->ID;

			//update the license key post author to be the license vendor
			wp_update_post(array('ID' => $license_key->ID, 'post_author' => $author_id));

			// re-hook this function
			add_action('save_post', array(&$this, 'save_post_meta'), 20);
		}

        function add_meta_boxes_to_license_key($post) {
            add_meta_box(
                'foolicensing_licensekey_info',
                __('License Key Status', 'foolic'),
                array(&$this, 'render_licensekey_status_metabox'),
                FOOLIC_CPT_LICENSE_KEY,
                'normal',
                'high'
            );

            add_meta_box(
                'foolicensing_licensekey_details',
                __('License Key Details', 'foolic'),
                array(&$this, 'render_licensekey_details_metabox'),
                FOOLIC_CPT_LICENSE_KEY,
                'normal',
                'high'
            );

			add_meta_box(
				'foolicensing_licensekey_vendor',
				__('Vendor'),
				array(&$this, 'render_licensekey_vendor_metabox'),
				FOOLIC_CPT_LICENSE_KEY,
				'side',
				'default'
			);

            do_action('foolic_add_meta_boxes_to_license_key', $post);
        }

		function render_licensekey_vendor_metabox($post) {
			global $current_user;
			if( current_user_can( 'manage_options' ) ) {
				//we are admin, so show all vendor in a dropdown, including all admins
				$users = get_users('role=license_vendor');
				$admins = get_users('role=administrator');
				$users = array_merge($users, $admins);
			} else {
				$users[] = $current_user;
			}
			$vendor = get_post_meta($post->ID, foolic_licensekey::META_VENDOR, true);

			?>
			<table class="form-table">
				<tbody>
				<tr>
					<td style="width:150px" class="first-column" valign="top"><?php _e('License Vendor', 'foolic'); ?></td>
					<td>
						<select name="post_author_override" id="post_author_override">
							<?php
							foreach($users as $user)
							{
								$sel = ($vendor == $user->ID)?"selected='selected'":'';
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

        function render_licensekey_status_metabox($post) {
            $license_key = foolic_licensekey::get($post);
            ?>
            <input type="hidden" name="<?php echo FOOLIC_CPT_LICENSE_KEY; ?>_nonce" id="<?php echo FOOLIC_CPT_LICENSE_KEY; ?>_nonce" value="<?php echo wp_create_nonce( plugin_basename($this->_plugin_file) ); ?>" />
            <table class="form-table">
            <tbody>
            <?php if (!empty($license_key->errors)) { ?>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Errors','foolic'); ?></td>
                <td colspan="3">
                    <?php foreach ($license_key->errors as $error) {
                       echo '<strong style="color:#f00">'. $error . '</span></br />';
                    }?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Status','foolic'); ?></td>
                <td colspan="3">
                <?php if ($license_key->is_new) {?>
                    <?php _e('The license key has not been issued yet', 'foolic'); ?>
                <?php } else {
                        $valid = foolic_licensekey_checker::validate_license_key($license_key);
                        ?>
                    <span style="color:<?php echo $valid['color']; ?>"><?php echo $valid['message']; ?></span>
                <?php } ?>
                </td>
            </tr>
            <?php if (!$license_key->is_new) { ?>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Date Issued','foolic'); ?></td>
                <td><?php echo $license_key->date_issued; ?></td>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Activated','foolic'); ?></td>
                <td><?php echo $license_key->activated ? 'Yes' : '<strong>NO</strong>'; ?></td>
            </tr>
            <?php if (!empty($license_key->meta_display)) {
                    $counter = 0;
                    foreach ($license_key->meta_display as $key=>$value) {
                        if ($counter == 0) echo '<tr>';
                        echo '<td style="width:150px" valign="top">'.$key.'</td>';
                        echo '<td>'.$value.'</td>';
                        $counter++;
                        if ($counter == 2) { echo '</tr>'; $counter = 0; }
                    }
            }?>
            <?php } ?>
            <?php
            do_action('foolic_render_licensekey_status_metabox', $post);
            ?>
            </tbody>
            </table><?php
        }

        function render_licensekey_details_metabox($post) {
            $license_key = foolic_licensekey::get($post);
            ?>
            <table class="form-table">
            <tbody>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Deactivated','foolic'); ?></td>
                <td>
                    <?php echo $license_key->deactivated ? "<strong style='color:#f00'>YES</strong>" : "no"; ?>
                    <input name="<?php echo FOOLIC_CPT_LICENSE_KEY; ?>[deactivated]" type="checkbox" value="on" <?php echo $license_key->deactivated ? 'checked="checked"':'' ?> />
                </td>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Expires','foolic'); ?></td>
                <td><input name="<?php echo FOOLIC_CPT_LICENSE_KEY; ?>[expires]" size="10" maxlength="20" type="text" value="<?php echo $license_key->expires; ?>" /></td>
            </tr>
            <tr>
                <td class="first-column" valign="top"><?php _e('Domain Limit','foolic'); ?></td>
                <td><input name="<?php echo FOOLIC_CPT_LICENSE_KEY; ?>[domain_limit]" size="10" maxlength="4" type="text" value="<?php echo $license_key->domain_limit; ?>" /></td>
                <td class="first-column" valign="top"><?php _e('Domain Limit Exceeded','foolic'); ?></td>
                <td>
                    <?php echo $license_key->exceeded ? "<strong style='color:#f00'>YES</strong>" : "no"; ?>
                </td>
            </tr>
            <?php
            do_action('foolic_render_licensekey_details_metabox', $post);
            ?>
            </tbody>
            </table><?php
        }
    }
}