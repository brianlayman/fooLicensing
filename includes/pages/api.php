<?php
/**
 * Foolicensing API Sandbox Page
 * Date: 2013/03/26
 */
if ( !is_user_logged_in() ) {
    wp_die('You should not be here!');
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

$licenses = foolic_get_license_posts();

$license_slug = false;
$generate_api_calls = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_slug = $_POST['foolic-license'];

    if (!empty($_POST['generate_api_calls'])) {
        $generate_api_calls = true;
        $url_details = foolic_rewrite_rules::generate_url($license_slug, 'details');
        $url_validate = foolic_rewrite_rules::generate_url($license_slug, 'validate');
        $url_check = foolic_rewrite_rules::generate_url($license_slug, 'check');
    }
}

?>
<script type="text/javascript">
    jQuery(document).ready(function() {

        jQuery('.api_get').click(function(e) {
            e.preventDefault();
            send_ajax_request(jQuery(this), 'GET');
        });

        jQuery('.api_post').click(function(e) {
            e.preventDefault();
            send_ajax_request(jQuery(this), 'POST');
        });

        function send_ajax_request($button, method) {
            var url = $button.parents('tr:first').find('.api_url').text();

            var nonce = jQuery('#api_nonce').val();

            var data = { action : 'foolic_api', url : url, method : method, nonce : nonce };

            $button.parents('tr:first').find('.api_param').each(function() {
                var name = jQuery(this).data('param');
                var value = jQuery(this).val();
                data[name] = value;
            });

            if ($button.data('type') != '') {
                data['type'] = $button.data('type');
            }

            jQuery('#response').val('fetching response...');

            jQuery.ajax({
                url : ajaxurl,
                cache : false,
                type : 'POST',
                data : data,
                success : function(data) {
                    jQuery('#response').val(data);
                },
                error : function(a,b,c) {
                    jQuery('#response').val('ERROR!');
                }
            });
        }
    });
</script>
<style type="text/css">
    .widefat td {
        padding: 8px 10px !important;
        vertical-align: middle !important;
    }
</style>
<div class="wrap">
    <div id="icon-options-general" class="icon32">
        <br />
    </div>

    <h2><?php _e('FooLicensing API Sandbox', 'foolic'); ?></h2>

    <form method="post">
        <input type="hidden" id="api_nonce" value="<?php echo wp_create_nonce( 'foolic_ajax_api_nonce' ); ?>" />
        <table>
            <tr>
                <td>License:</td>
                <td>
                    <select name="foolic-license">
                        <option value="0">--select--</option>
                        <?php foreach( $licenses as $license ) {
                            echo '<option '. ( $license_slug == $license->post_name ? ' selected="selected" ': '' ) .' value="'. $license->post_name . '">'. $license->post_title . '</option>';
                        } ?>
                    </select>
                    <input name="generate_api_calls" class="button-primary" type="submit" value="<?php _e('Generate API Calls', 'foolic'); ?>" />
                </td>
            </tr>
            <?php if ($generate_api_calls) { ?>
            <tr>
                <td colspan="2">
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>URL</th>
                                <th>Arguments</th>
                                <th>Test</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php _e('Details', 'foolic'); ?></td>
                                <td><span class="api_url"><?php echo $url_details; ?></span></td>
                                <td></td>
                                <td><input class="button-primary api_get" type="submit" value="<?php _e('Get', 'foolic'); ?>" /></td>
                            </tr>
                            <tr>
                                <td><?php _e('Validate', 'foolic'); ?></td>
                                <td><span class="api_url"><?php echo $url_validate; ?></span></td>
                                <td>
                                    <input name="license_key" class="api_param" data-param="license" size="30" type="text" placeholder="<?php _e('License Key', 'foolic'); ?>" /><br />
                                    <input name="site_url" class="api_param" data-param="site" size="30" type="text" placeholder="<?php _e('Site URL', 'foolic'); ?>" />
                                </td>
                                <td><input class="button-primary api_post" type="submit" value="<?php _e('Post', 'foolic'); ?>" /></td>
                            </tr>
                            <tr>
                                <td><?php _e('Check For Updates', 'foolic'); ?></td>
                                <td><span class="api_url"><?php echo $url_check; ?></span></td>
                                <td>
                                </td>
                                <td><input class="button-primary api_get" type="submit" value="<?php _e('Get', 'foolic'); ?>" /></td>
                            </tr>
                            <tr>
                                <td><?php _e('Check For Updates', 'foolic'); ?></td>
                                <td><span class="api_url"><?php echo $url_check; ?></span></td>
                                <td>
                                    <input name="license_key" class="api_param" data-param="license" size="30" type="text" placeholder="<?php _e('License Key', 'foolic'); ?>" /><br />
                                    <input name="site_url" class="api_param" data-param="site" size="30" type="text" placeholder="<?php _e('Site URL', 'foolic'); ?>" /><br />
                                    <input name="version" class="api_param" data-param="version" size="10" type="text" placeholder="<?php _e('Version', 'foolic'); ?>" />
                                    <input name="ip" class="api_param" data-param="ip" size="12" type="text" placeholder="<?php _e('IP Address', 'foolic'); ?>" />
                                </td>
                                <td><input data-type="update" class="button-primary api_post" type="submit" value="<?php _e('Post', 'foolic'); ?>" /></td>
                            </tr>
                            <tr>
                                <td><?php _e('Get Update Info', 'foolic'); ?></td>
                                <td><span class="api_url"><?php echo $url_check; ?></span></td>
                                <td>
                                    <input name="license_key" class="api_param" data-param="license" size="30" type="text" placeholder="<?php _e('License Key', 'foolic'); ?>" /><br />
                                    <input name="site_url" class="api_param" data-param="site" size="30" type="text" placeholder="<?php _e('Site URL', 'foolic'); ?>" /><br />
                                    <input name="version" class="api_param" data-param="version" size="10" type="text" placeholder="<?php _e('Version', 'foolic'); ?>" />
                                    <input name="ip" class="api_param" data-param="ip" size="12" type="text" placeholder="<?php _e('IP Address', 'foolic'); ?>" />
                                    <input class="api_param" data-param="api_action" size="12" type="hidden" value="check-with-info" />
                                </td>
                                <td><input data-type="update" class="button-primary api_post" type="submit" value="<?php _e('Post', 'foolic'); ?>" /></td>
                            </tr>
                        </tbody>
                    </table>
                    <br />
                    <textarea id="response" style="width:700px; height:300px;"></textarea>
                </td>
            </tr>
            <?php } ?>
        </table>
    </form>
</div>
