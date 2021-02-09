<?php
/*
Plugin Name: List Table 111
*/

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class List_Table extends WP_List_Table {

    public $per_page = 3;

    function __construct(){
        global $status, $page;
        parent::__construct(
            array(
                'singular'  => 'book',     //singular name of the listed records
                'plural'    => 'books',    //plural name of the listed records
                'ajax'      => false        //does this table support ajax?
            )
        );
    }

    protected function bulk_actions( $which = '' ) {
        if ( is_null( $this->_actions ) ) {
            $this->_actions = $this->get_bulk_actions();
            $this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );  // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
            $two            = '';
        } else {
            $two = '2';
        }

        if ( empty( $this->_actions ) ) {
            return;
        }

        echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
        echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
        echo '<option value="-1">' . '批量操作' . "</option>\n";

        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

            echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
        }

        echo "</select>\n";

        submit_button( '提交', 'action', '', false, array( 'id' => "doaction$two" ) );
        echo "\n";
    }

    // key == sql field, value == column name
    function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'image_column_name' => 'image_column_name',
            'booktitle' => 'Book Title',
            'author'    => 'Author',
            'custom_field'    => 'Custom field',
            'isbn'      => 'ISBN'
        );
        return $columns;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'custom_field':
                return sprintf( '<input type="text" name="num[]" style="width: 50px; height: 20px;" />', '');
            case 'image_column_name':
            case 'booktitle':
            case 'author':
            case 'isbn':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'book_id', $item['ID'] );
    }

    function prepare_items() {
        global $wpdb, $demo_data;

        $data           = $demo_data; // $wpdb->results( sql );

        $total_items    = count( $data );
        $per_page       = $this->per_page;
        $total_pages    = ceil( $total_items / $per_page );
        $current_page   = $this->get_pagenum();

        $data = array_slice( $data, ( ($current_page-1)*$per_page ), $per_page );

        $this->items = $data;

        $this->process_bulk_action();

        $this->_column_headers = array(
            $this->get_columns(),   // $columns     = $this->get_columns();
            array(),                // $hidden      = array();
            array()                 // $sortable    = $this->get_sortable_columns();
        );

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => $total_pages
            )
        );
    }

    function column_booktitle( $item ) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%1$s&action=%2$s&book_id=%3$s">编辑</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%1$s&action=%2$s&book_id=%3$s">删除</a>',$_REQUEST['page'],'delete',$item['ID']),
        );

        return sprintf('%1$s %2$s', $item['booktitle'], $this->row_actions($actions) );
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => '删除'
        );
        return $actions;
    }

    function process_bulk_action() {
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
    }

    function no_items() {
       echo '<a href="">暂时还没有数据，请按流程操作本系统，如有疑问请联系系统管理员！</a href="">';
    }

    function column_image_column_name( $item ) {
        return sprintf( '<img src="%s" style="width:50px; height: 35px;"/>', $item['image_column_name'] );
    }

}


$demo_data = array(
    array('ID' => 1, 'booktitle' => 'Quarter Share', 'author' => 'Nathan Lowell', 'isbn' => '978-0982514542', 'image_column_name' => 'http://lele/erp/wp-content/uploads/2019/11/th.jpeg'),
    array('ID' => 2, 'booktitle' => '7th Son: Descent', 'author' => 'J. C. Hutchins', 'isbn' => '0312384378', 'image_column_name' =>'http://lele/erp/wp-content/uploads/2019/11/th.jpeg'),
    array('ID' => 3, 'booktitle' => 'Shadowmagic', 'author' => 'John Lenahan', 'isbn' => '978-1905548927', 'image_column_name' =>'http://lele/erp/wp-content/uploads/2019/11/th.jpeg'),
    array('ID' => 4, 'booktitle' => 'The Crown Conspiracy', 'author' => 'Michael J. Sullivan', 'isbn' => '978-0979621130', 'image_column_name' =>''),
    array('ID' => 5, 'booktitle' => 'Max Quick: The Pocket and the Pendant', 'author'  => 'Mark Jeffrey', 'isbn' => '978-0061988929', 'image_column_name' =>''),
    array('ID' => 6, 'booktitle' => 'Jack Wakes Up: A Novel', 'author' => 'Seth Harwood', 'isbn' => '978-0307454355', 'image_column_name' =>''),
    array('ID' => 7, 'booktitle' => 'Java', 'author' => 'Sun', 'isbn' => '123-0987654321', 'image_column_name' =>'')
);


function list_table_menu(){
    add_menu_page( 'List Table Menu', 'List Table Menu', 'read', 'list-table', 'lt_menu_func' );
}
add_action( 'admin_menu', 'list_table_menu' );


function admin_header() {
        $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
        if( 'list-table' != $page )
            return;

    echo '<style type="text/css">';
    echo '.wp-list-table .column-image_column_name { width: 15%; }';
    echo '.wp-list-table .column-booktitle { width: 30%; }';
    echo '.wp-list-table .column-author { width: 15%; }';
    echo '.wp-list-table .column-lele { width: 20%; }';
    echo '.wp-list-table .column-isbn { width: 20%; }';
    echo '</style>';
}
add_action( 'admin_head', 'admin_header' );

function lt_menu_func() {
    echo '<div class="wrap">';

    echo '<h1 class="wp-heading-inline">List Table</h1>';
    echo '<hr class="wp-header-end">';
    echo '<ul class="subsubsub">
	        <li class="all">
	            <a href="#">已选择项目数: <span class="count" id="selected-items">( 1 )</span></a> |
            </li>
	        <li class="all">
	            <a href="#">合计数: <span class="count" id="subtotal">( 1 )</span></a>
            </li>
         </ul>';

    $list_table = new List_Table();

    $list_table->prepare_items();
    ?>
    <form method="post">
        <input type="hidden" name="product_name" value="list-table" />
        <?php $list_table->search_box('search', 'search_id'); ?>
    </form>

    <form id="movies-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->
        <?php $list_table->display(); ?>
    </form>


<?php
    echo '</div>';
}
