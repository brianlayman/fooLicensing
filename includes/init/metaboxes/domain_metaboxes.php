<?php
/**
 * FooLicensing Domain Metaboxes
 * Date: 2013/03/27
 */
if (!class_exists('foolic_domain_metaboxes')) {

    class foolic_domain_metaboxes {

        private $_plugin_file;

        function __construct($plugin_file) {
            $this->_plugin_file = $plugin_file;
            add_action('add_meta_boxes_' . FOOLIC_CPT_DOMAIN, array(&$this, 'add_meta_boxes_to_domain'));

            //save extra post data
            add_action('save_post', array(&$this, 'save_post_meta'));
        }

        function save_post_meta($post_id) {
            // check autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // verify nonce
            if (array_key_exists(FOOLIC_CPT_DOMAIN . '_nonce', $_POST) &&
                wp_verify_nonce($_POST[FOOLIC_CPT_DOMAIN . '_nonce'], plugin_basename($this->_plugin_file))
            ) {
                //if we get here, we are dealing with the domain custom post type

                //get all the license data
                $data = apply_filters('foolic_save_domain', $_POST[FOOLIC_CPT_DOMAIN]);

                //and save it
                update_post_meta($post_id, FOOLIC_CPT_DOMAIN, $data);

				do_action('foolic_save_post_meta_for_domain', $post_id, $_POST);
            }
        }

        function add_meta_boxes_to_domain($post) {

            add_meta_box(
                'foodomain_details',
                __('Domain Details', 'foolic'),
                array(&$this, 'render_domain_details_metabox'),
                FOOLIC_CPT_DOMAIN,
                'normal',
                'high'
            );

            do_action('foolic_add_meta_boxes_to_domain', $post);
        }

        function render_domain_details_metabox($post) {
            $domain = new foolic_domain();
            $domain->load($post);
            ?>
            <input type="hidden" name="<?php echo FOOLIC_CPT_DOMAIN; ?>_nonce" id="<?php echo FOOLIC_CPT_DOMAIN; ?>_nonce" value="<?php echo wp_create_nonce( plugin_basename($this->_plugin_file) ); ?>" />
            <table class="form-table">
            <tbody>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Localhost','foolic'); ?></td>
                <td>
                    <?php echo $domain->localhost ? "<strong>YES</strong>" : "no"; ?>
                    <input name="<?php echo FOOLIC_CPT_DOMAIN; ?>[localhost]" type="checkbox" value="on" <?php echo $domain->localhost ? 'checked="checked"':'' ?> />
                </td>
            </tr>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Blacklisted','foolic'); ?></td>
                <td>
                    <?php echo $domain->blacklisted ? "<strong>YES</strong>" : "no"; ?>
                    <input name="<?php echo FOOLIC_CPT_DOMAIN; ?>[blacklisted]" type="checkbox" value="on" <?php echo $domain->blacklisted ? 'checked="checked"':'' ?> />
                </td>
            </tr>
            <tr>
                <td style="width:150px" class="first-column" valign="top"><?php _e('Marked For Blacklisting','foolic'); ?></td>
                <td>
                    <?php echo $domain->marked_for_blacklisting ? "<strong>YES</strong>" : "no"; ?>
                    <input name="<?php echo FOOLIC_CPT_DOMAIN; ?>[marked_for_blacklisting]" type="checkbox" value="on" <?php echo $domain->marked_for_blacklisting ? 'checked="checked"':'' ?> />
                </td>
            </tr>
            <?php
            do_action('foolic_render_domain_details_metabox', $post);
            ?>
            </tbody>
            </table><?php
        }
    }
}