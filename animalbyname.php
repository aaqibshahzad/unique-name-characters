<?php
/* 
Plugin Name: Animal By Name
Description: A WordPress plugin to add animal pictures by customer name in woocommerce.
Version: 1.0
Author: Ali
echo '<pre>';print_r($post_id);'</pre>';die;
*/

if (!defined('ABSPATH')) {
    exit;
}

function abn_plugin_admin_scripts() {
    
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('chat-genie-script', plugins_url('assets/js/abn-color-picker.js', __FILE__), array('jquery', 'wp-color-picker'), '', true);

}
add_action('admin_enqueue_scripts', 'abn_plugin_admin_scripts');

function abn_plugin_enqueue_scripts() {

    wp_register_style('abn-plugin-style', plugins_url('assets/css/abn_style.css', __FILE__), array(), '1.0.0');

    wp_register_style('abn-swiper-style', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');

    wp_register_script('abn-swiper-script', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '1.0.0');

    wp_register_script('abn-plugin-script', plugins_url('assets/js/abn_script.js', __FILE__), array(), '1.0.0');

    wp_enqueue_style('abn-plugin-style');
    wp_enqueue_style('abn-swiper-style');
    wp_enqueue_script('jquery');
    wp_enqueue_script('abn-plugin-script');
    wp_enqueue_script('abn-swiper-script');

    wp_localize_script('abn-plugin-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'plugin_url' => plugins_url(),
        'nonce' => wp_create_nonce('my_ajax_nonce')
    ));

    include_once plugin_dir_path(__FILE__) . 'assets/dynamic-css/abn-plugin-dynamic-css.php';
}
add_action('wp_enqueue_scripts', 'abn_plugin_enqueue_scripts');

function abn_plugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'abn_animal_images';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        animal_name varchar(255) NOT NULL,
        animal_image text NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);
}

register_activation_hook(__FILE__, 'abn_plugin_create_table');

function abn_register_settings() {
    register_setting('abn_settings_group', 'abn_text_for_confirm_my_name');
    register_setting('abn_settings_group', 'abn_confirm_my_name_button_color');
    register_setting('abn_settings_group', 'abn_text_for_validate_my_name');
    register_setting('abn_settings_group', 'abn_validate_my_personalization_button_color');

}

add_action('admin_init', 'abn_register_settings');

function abn_admin_menu() { 
    add_menu_page(__('Animal By Name List', 'animal-by-name'), __('Animal By Name', 'animal-by-name'), 'manage_options', 'abn_list',  'abn_admin_callback_function','dashicons-buddicons-activity'); 

    add_submenu_page('abn_list', __('Add Animal Image', 'animal-by-name'), __('Add Animal Image', 'animal-by-name'), 'manage_options', 'add-animal-image',  'chatgenie_chats_page');

    add_submenu_page('abn_list', __('Settings', 'animal-by-name'), __('Settings', 'animal-by-name'), 'manage_options', 'abn_settings', 'abn_settings_callback'
    );
}

add_action('admin_menu', 'abn_admin_menu');

function abn_admin_callback_function() {
    require_once plugin_dir_path(__FILE__) . 'inc/settings/abn-show-list.php';
}

function chatgenie_chats_page() {
    require_once plugin_dir_path(__FILE__) . 'inc/settings/abn-add-animal-image.php';
}

function abn_settings_callback() {
    require_once plugin_dir_path(__FILE__) . 'inc/settings/abn-setting-page.php';
}

function abn_load_textdomain() {
    load_plugin_textdomain('animal-by-name', false, WP_CONTENT_DIR . '/languages/');
}
add_action('plugins_loaded', 'abn_load_textdomain');



// Add custom field to the WooCommerce product data tab
function add_personalized_animal_poster() {
    global $post;
    if ('product' !== $post->post_type) {
        return;
    }
    $personalized_animal_poster = get_post_meta($post->ID, '_personalized_animal_poster', true);

    ?>
    <div class="misc-pub-section misc-pub-personalized-animal-poster">
        <label for="personalized_animal_poster">
            <input type="checkbox" id="personalized_animal_poster" name="_personalized_animal_poster" value="yes" <?php checked($personalized_animal_poster, 'yes'); ?> />
            <?php _e('Personalized animal poster', 'woocommerce'); ?>
        </label>
    </div>
    <?php
}
add_action('post_submitbox_misc_actions', 'add_personalized_animal_poster');

// Save custom field value
function save_personalized_animal_poster($post_id) {
    $personalizedAnimalPoster = isset($_POST['_personalized_animal_poster']) ? sanitize_text_field($_POST['_personalized_animal_poster']) : '';
    update_post_meta($post_id, '_personalized_animal_poster', $personalizedAnimalPoster);
}
add_action('woocommerce_process_product_meta', 'save_personalized_animal_poster');


// Display the custom checkbox value on the product page
function display_personalized_animal_poster_on_product_page() {
    global $product;
    $personalizedAnimalPoster = get_post_meta($product->get_id(), '_personalized_animal_poster', true);

    if ($personalizedAnimalPoster === 'yes') {
        echo '<div style="margin-bottom: 20px">';
        echo '<strong style="color: #000">'.__('Personalization').':</strong> ';
        echo '<label class="switch">';
        echo    '<input type="checkbox" id="personalizeCheckbox">';
        echo    '<span class="slider round"></span>';
        echo '</label>';
        echo '</div>';

        $sidebar = '<div id="ModalPersonnalisation">
                        <div id="personnalisationModal">
                            <label class="switch">
                                <input type="checkbox" id="personalizeCloseCheckbox" checked>
                                <span class="slider round"></span>
                            </label>
                            <div>
                              <lable>'.__('First name').'*</lable> 
                            </div>
                            <div class="search-filter-info">
                              <input type="text" id="abnUserName" maxlength="15">
                              <span id="maxLengthCount"></span>
                              <button type="button" id="abnConfirmNameBtn" disabled>'.__(get_option('abn_text_for_confirm_my_name', 'Confirm My Name')).'</button>
                            </div>
                           
                            
                            <div id="animal-by-name-list"></div>
                            <div class="abn-button-info">
                            <button type="button" id="abnValidateNameBtn">'.__(get_option('abn_text_for_validate_my_name', 'Validate my personalization')).'</button>
                         </div>
                        </div>
                    </div>';
        echo $sidebar;
    }
}
add_action('woocommerce_before_add_to_cart_button', 'display_personalized_animal_poster_on_product_page');

function get_animals_name_by_user_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'abn_animal_images';

    $userName = strtolower(sanitize_text_field($_POST['abnUserName']));
    $getInputLen = strlen($userName);

    $where_clauses = array();
    for($i=0;$i<$getInputLen;$i++)
    {
        $where_clauses[] = $wpdb->prepare("animal_name LIKE %s", $userName[$i] . '%');
    }

    // $letter_counts = array_count_values($userName);
    $where_sql = implode(' OR ', $where_clauses);

    $sql = "SELECT * FROM $table_name WHERE $where_sql";

    $results =  $wpdb->get_results($sql,ARRAY_A);
    
    $organized_results = [];
    for($i=0;$i<$getInputLen;$i++)
    {
        foreach($results as $res)
        {
            if(strtolower($userName[$i]) == strtolower($res['animal_name'][0]))
            {
                $organized_results[$i."_".$userName[$i]][] = $res;
            }
        }
    }

    wp_send_json_success($organized_results);
    die();
}

add_action('wp_ajax_get_animals_name_by_user_name', 'get_animals_name_by_user_name');
add_action('wp_ajax_nopriv_get_animals_name_by_user_name', 'get_animals_name_by_user_name');

add_filter('woocommerce_add_cart_item_data', 'save_animals_name_to_product_fields', 10, 3);

function save_animals_name_to_product_fields($cart_item_data, $product_id, $variation_id) {
    foreach($_POST as $key => $value) {
        if (strpos($key, 'animal_for_') === 0) {
            $cart_item_data[$key] = sanitize_text_field($value);
        }
    }
    
    $cart_item_data['unique_key'] = md5(microtime() . rand());

    return $cart_item_data;
}

add_filter('woocommerce_get_item_data', 'display_animals_name_product_fields_in_cart', 10, 2);

function display_animals_name_product_fields_in_cart($item_data, $cart_item) {
    foreach($cart_item as $key => $value) {
        if (strpos($key, 'animal_for_') === 0) {
            // Remove number from the key (after "animal_for_" and before the letter)
            $display_key = preg_replace('/animal_for_\d+_/', 'animal_for_', $key);
            $item_data[] = array(
                'name' => ucwords(str_replace('_', ' ', $display_key)),
                'value' => sanitize_text_field($value)
            );
        }
    }
    return $item_data;
}

add_action('woocommerce_add_order_item_meta', 'save_animals_name_fields_to_order', 10, 2);

function save_animals_name_fields_to_order($item_id, $values) {
    foreach($values as $key => $value) {
        if (strpos($key, 'animal_for_') === 0) {
            // Remove number from the key (after "animal_for_" and before the letter)
            $display_key = preg_replace('/animal_for_\d+_/', 'animal_for_', $key);
            wc_add_order_item_meta($item_id, ucwords(str_replace('_', ' ', $display_key)), sanitize_text_field($value));
        }
    }
}

add_action('woocommerce_order_item_meta_start', 'display_animals_name_fields_in_order', 10, 2);

function display_animals_name_fields_in_order($item_id, $item) {
    foreach($item->get_meta_data() as $meta) {
        $key = $meta->key;
        $value = $meta->value;

        if (strpos($key, 'Animal For ') === 0) {
            // Remove number from the key (after "Animal For " and before the letter)
            $display_key = preg_replace('/Animal For \d+ /', 'Animal For ', $key);
            echo '<p><strong>' . esc_html__($display_key) . ':</strong> ' . sanitize_text_field($value) . '</p>';
        }
    }
}