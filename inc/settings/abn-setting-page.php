<?php
/**
 * Settings Page for Animal By Name Plugin.
 *
 * This template displays the settings form for customizing button texts and colors.
 *
 * @package AnimalByName
 * @since 1.0.0
 */
?>
<div class="wrap">
    <h2><?php esc_html_e('Settings', 'animal-by-name'); ?></h2>
    <form method="post" action="options.php">
        <?php 
            settings_fields('abn_settings_group'); 
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="abn_text_for_confirm_my_name"><?php esc_html_e('Text For Confirm Name Button', 'animal-by-name'); ?></label></th>
                <td>
                    <input type="text" id="abn_text_for_confirm_my_name" name="abn_text_for_confirm_my_name" class="regular-text" value="<?php echo esc_attr(get_option('abn_text_for_confirm_my_name')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="abn_confirm_my_name_button_color"><?php esc_html_e('Confirm Name Button Color', 'animal-by-name'); ?></label>
                </th>
                <td>
                    <input type="text" id="abn_confirm_my_name_button_color" name="abn_confirm_my_name_button_color" class="my-color-field" value="<?php echo esc_attr(get_option('abn_confirm_my_name_button_color')); ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="abn_text_for_validate_my_name"><?php esc_html_e('Text For Validate my personalization', 'animal-by-name'); ?></label></th>
                <td>
                    <input type="text" id="abn_text_for_validate_my_name" name="abn_text_for_validate_my_name" class="regular-text" value="<?php echo esc_attr(get_option('abn_text_for_validate_my_name')); ?>" />
                    <div id="validation_msg_chatgenie"></div>
                    <br>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="abn_validate_my_personalization_button_color"><?php esc_html_e('Validate my personalization Button Color', 'animal-by-name'); ?></label></th>
                <td><input type="text" id="abn_validate_my_personalization_button_color" name="abn_validate_my_personalization_button_color" class="my-color-field" value="<?php echo esc_attr(get_option('abn_validate_my_personalization_button_color')); ?>"></td>
            </tr>
        </table>
        <?php submit_button('Save Settings');
        ?>
    </form>
</div>