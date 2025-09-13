<?php 
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ABN_Show_List extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => 'Animal Image',
            'plural'   => 'Animal Images',
            'ajax'     => false,
        ));
    }

    public function get_columns() {
        return array(
            'cb'            => '<input type="checkbox" />',
            'animal_name'   => 'Animal Name',
            'animal_image'  => 'Animal Image',
            'created_at'    => 'Date',
            'id'            => 'Action'
        );
    }

    public function get_sortable_columns() {
        return array(
            'animal_name' => array('animal_name', false),
            'animal_image' => array('animal_image', false),
            'created_at' => array('created_at', false),
        );
    }

    public function prepare_items() {
        $data = $this->getAbnData();
        
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, array(), $sortable);

        $this->process_bulk_action();

        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : false;

        if ($search) {
            $data = $this->search_data($data, $search);
        }

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : false;
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : false;
        if ($start_date && $end_date) {
            $data = $this->filter_by_date($data, $start_date, $end_date);
        }

        $per_page = $this->get_items_per_page('items_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        usort($data, array($this, 'usort_reorder'));

        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;
    }

    public function usort_reorder($a, $b) {
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'created_at';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'desc';
        
        $result = strcmp($a[$orderby], $b[$orderby]);
    
        return ('desc' === $order) ? -$result : $result;
    }

    public function getAbnData() {
        global $wpdb;
    
        $abnTable = $wpdb->prefix.'abn_animal_images';
        $fetch_query = "SELECT * FROM $abnTable ORDER BY created_at DESC";
        $all_results = $wpdb->get_results($fetch_query);
        
        $abnData = array();
        
        foreach ($all_results as $item) {
            $abnData[] = (array) $item;
        }
    
        return $abnData;
    }

    public function search_data($data, $search) {
        $result = array();
    
        foreach ($data as $item) {
            foreach ($item as $value) {
                if ($value != null) {
                    if (stripos($value, $search) !== false) {
                        $result[] = $item;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public function filter_by_date($data, $startDate, $endDate) {
        $filteredData = array();
        foreach ($data as $item) {
            $createdAtDate = date('Y-m-d', strtotime($item['created_at']));
            $startDateFormatted = date('Y-m-d', strtotime($startDate));
            $endDateFormatted = date('Y-m-d', strtotime($endDate));
            
            if ($createdAtDate >= $startDateFormatted && $createdAtDate <= $endDateFormatted) {
                $filteredData[] = $item;
            }
        }
        return $filteredData;
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'animal_image':
                return '<img src="'.$item[ $column_name ].'" width="50" height="50">';
            
            case 'id':
                $edit_link = esc_url(add_query_arg(array('action' => 'edit', 'id' => $item['id']), admin_url('admin.php?page=add-animal-image')));
                return '<a href="' . $edit_link . '">Edit</a>';

            default:
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
        }
    }

    public function column_cb($item) {
        return '<input type="checkbox" name="item[]" value="' . esc_attr($item['id']) . '" />';
    }

    public function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete',
        );
        return $actions;
    }

    public function process_bulk_action() {
        if ('delete' === $this->current_action() && isset($_REQUEST['item']) ) {
            $ids_to_delete = esc_sql($_REQUEST['item']);

            global $wpdb;

            $abnTable = $wpdb->prefix.'abn_animal_images';

            foreach ($ids_to_delete as $id) {
                $h = $wpdb->delete(
                    $abnTable,
                    array('ID' => $id),
                    array('%d')
                );
            }

            add_action('admin_footer', 'reload_admin_page_script');
        }
    }
}

function display_abn_list_table() {

    $abn_list = new ABN_Show_List();
    $abn_list->prepare_items();
    ?>
    
    <div class="wrap">
        <h2>Animal By Name</h2>
        <a href="<?php echo esc_url(admin_url('admin.php?page=add-animal-image')); ?>" class="button button-primary">Add Animal Image</a>
        <form method="post">
            <?php $abn_list->search_box('Search', 'search_id'); ?>
            <?php $abn_list->display(); ?>
        </form>
    </div>
    <?php
}
echo "<br>";

display_abn_list_table();

function reload_admin_page_script() {
    ?>
    <script>
        function reloadAdminPage() {
            document.location.reload(true);
        }

        reloadAdminPage();
    </script>
    <?php
}