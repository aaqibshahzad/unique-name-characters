<?php
global $wpdb;
$table_name = $wpdb->prefix . 'abn_animal_images';

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'animal-by-name'));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_animal_data_nonce']) && wp_verify_nonce($_POST['save_animal_data_nonce'], 'save_animal_data')) {
    $animal_name = sanitize_text_field($_POST['animal_name']);
    $animal_image_url = '';

    // Determine if we are adding or editing
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Edit existing record
        $existing_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $id), ARRAY_A);
        $animal_image_url = $existing_data['animal_image']; // Preserve existing image

        // Handle file upload
        if (!empty($_FILES['animal_image']['name'])) {
            $uploaded_file = $_FILES['animal_image'];
            $upload_overrides = array('test_form' => false);

            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $animal_image_url = $movefile['url']; // Update image URL if a new image is uploaded
            } else {
                echo "<div class='error'><p>Error uploading file: " . $movefile['error'] . "</p></div>";
            }
        }

        if (!isset($movefile['error'])) {
            $wpdb->update(
                $table_name,
                array(
                    'animal_name' => $animal_name,
                    'animal_image' => $animal_image_url,
                ),
                array('ID' => $id),
                array(
                    '%s',
                    '%s',
                ),
                array('%d')
            );

            echo "<div class='updated'><p>Animal Name: $animal_name updated successfully</p></div>";
        }
    } else {
        // Insert new record
        if (!empty($_FILES['animal_image']['name'])) {
            $uploaded_file = $_FILES['animal_image'];
            $upload_overrides = array('test_form' => false);

            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $animal_image_url = $movefile['url'];
            } else {
                echo "<div class='error'><p>Error uploading file: " . $movefile['error'] . "</p></div>";
            }
        }

        if (!isset($movefile['error'])) {
            $wpdb->insert(
                $table_name,
                array(
                    'animal_name' => $animal_name,
                    'animal_image' => $animal_image_url,
                    'created_at' => current_time('mysql')
                ),
                array(
                    '%s',
                    '%s',
                    '%s'
                )
            );

            echo "<div class='updated'><p>Animal Name: $animal_name added successfully</p></div>";
        }
    }
}

// Handle edit action
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$animal_data = array();

if ($id > 0) {
    // Fetch existing data
    $animal_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $id), ARRAY_A);
}
?>

<div class="wrap">
    <h1><?php echo $id > 0 ? __('Edit Animal Image', 'animal-by-name') : __('Add Animal Image', 'animal-by-name'); ?></h1>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('save_animal_data', 'save_animal_data_nonce'); ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>" />
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Animal Name', 'animal-by-name'); ?></th>
                <td><input type="text" name="animal_name" value="<?php echo esc_attr($animal_data['animal_name'] ?? ''); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Animal Image', 'animal-by-name'); ?></th>
                <td>
                    <input type="file" name="animal_image" />
                    <?php if (!empty($animal_data['animal_image'])): ?>
                        <p><img src="<?php echo esc_url($animal_data['animal_image']); ?>" width="100" height="100" /></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php submit_button($id > 0 ? __('Update Animal', 'animal-by-name') : __('Save Animal', 'animal-by-name')); ?>
    </form>
</div>
