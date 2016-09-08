<?php
global $wpdb;

if (! class_exists ( 'WP_List_Table' )) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class stpPages_Table extends WP_List_Table {

	function __construct() {
		parent::__construct ( array (
				'singular' => 'page', // Singular label
				'plural' => 'pages', // plural label, also this well be one of the table css class
				'ajax' => false 
			) // We won't support Ajax for this table
 		);

		add_action ( 'admin_head', array (
				&$this,
				'admin_header' 
		) );
	}
	function extra_tablenav($which) {
		if ($which == "top") {
		}
		if ($which == "bottom") {
		}
	}
	function column_default($item, $column_name) {
		if ($item [$column_name] == 999999999) {
			return "CUSTOM PAGE";
		}
		return $item [$column_name];
	}
	function column_node_id($item) {
		$id = $item ['node_id'];
		return " <a href = 'http://facebook.com/" . $id . "'>" . $id . "</a>";
	}
	function column_cb($item) {
		return sprintf ( 
		'<input type="checkbox" name="%1$s[]" value="%2$s" />',
		    /*$1%s*/ $this->_args ['singular'], // Let's simply repurpose the table's singular label ("movie")
		$item ['node_id'] );
	}
	function column_engage($item) {
		$id = $item ['id'];
		$status = $item ['engage'];

		if ($status == "YES") {
			return '<input type="checkbox" class="ToggleSwitchSample" checked="checked"  onchange = "stpchangeStatus(\'' . $id . '\');"  />';
		} else {
			return '<input type="checkbox" class="ToggleSwitchSample"   onchange = "stpchangeStatus(\'' . $id . '\');" />';
		}
	}

	function get_columns() {
		$help = plugin_dir_url ( __FILE__ ) . 'help.gif';

		return $columns = array (
				'cb' => '<input type="checkbox" />',
				'title' => 'Name',
				'node_id' => 'ID',
				'likes' => 'Likes <img src = " ' . $help . ' "title = "The total likes at the last cron job run" />',
				'talking_about' => 'Talking About <img src = " ' . $help . ' "  title = "The talking about metric at the last cron job run"/>',
				'engage' => 'Engage?' 
		);
	}
	function get_sortable_columns() {
		$sortable_columns = array (
				'node_id' => array (
						'node_id',
						false 
				),
				'title' => array (
						'title',
						false 
				),
				'likes' => array (
						'likes',
						false 
				),
				'talking_about' => array (
						'talking_about',
						false 
				),
				'engage' => array (
						'engage',
						false 
				) 
		);
		return $sortable_columns;
	}
	function get_bulk_actions() {
		$actions = array (
				
				'add' => 'Suppress',
				'remove' => 'Stop Suppressing',
				'delete' => 'Delete' 
		);
		return $actions;
	}
	function process_bulk_action() {
		global $wpdb;

		$table_name = $wpdb->prefix . "stppages";
		$wpdb->show_errors ();

		if ('add' === $this->current_action ()) {
			foreach ( $_POST ['page'] as $input ) {
				$row = $wpdb->get_row ( "SELECT title FROM $table_name WHERE node_id = $input" );

				$wpdb->update ( $table_name, array (
						'supressed' => 'YES' 
					) // string
					, array (
						'node_id' => $input 
				) );
			}
		}

		if ('remove' === $this->current_action ()) {
			foreach ( $_POST ['page'] as $input ) {
				$row = $wpdb->get_row ( "SELECT title FROM $table_name WHERE node_id = $input" );
				$wpdb->update ( $table_name, array (
						'supressed' => '' 
					) // string
					, array (
						'node_id' => $input 
				) );
			}
		}
		if ('delete' === $this->current_action ()) {
			foreach ( $_POST ['page'] as $input ) {
				$wpdb->delete ( $table_name, array (
						'node_id' => $input 
				) );
			}
		}
	}
	function prepare_items() {
		$keyword1 = get_post_meta ( 111111113, 'stpkeyword1', TRUE );
		$keyword2 = get_post_meta ( 111111113, 'stpkeyword2', TRUE );
		$keyword3 = get_post_meta ( 111111113, 'stpkeyword3', TRUE );

		$keyword1 = urlencode ( $keyword1 );
		$keyword2 = urlencode ( $keyword2 );
		$keyword3 = urlencode ( $keyword3 );

		$per_page = 50;

		$columns = $this->get_columns ();
		$hidden = array ();
		$sortable = $this->get_sortable_columns ();

		$this->_column_headers = array (
				$columns,
				$hidden,
				$sortable 
		);
		
		$this->process_bulk_action ();

		global $wpdb;
		$wpdb->show_errors ();

		$table_name = $wpdb->prefix . "stppages";

		$orderby = $_REQUEST ['orderby'];
		$order = $_REQUEST ['order'];

		if (empty ( $orderby )) {
			$query = "SELECT * FROM $table_name WHERE (node_id != '2147483647' ) AND (keyword = '$keyword1' OR keyword = '$keyword2' OR keyword = '$keyword3'  OR keyword = 'custom') ORDER BY likes desc ";
		} 
		else if ($orderby == "engage") {
			$query = "SELECT * FROM $table_name  WHERE (node_id != '2147483647' ) AND (keyword = '$keyword1' OR keyword = '$keyword2' OR keyword = '$keyword3' OR keyword = 'custom') ORDER BY engage $order ";
		} 
		else if ($orderby == "title") {
			$query = "SELECT * FROM $table_name  WHERE (node_id != '2147483647' ) AND (keyword = '$keyword1' OR keyword = '$keyword2' OR keyword = '$keyword3' OR keyword = 'custom') ORDER BY title $order ";
		} 
		else {
			$query = "SELECT * FROM $table_name  WHERE  (node_id != '2147483647' ) AND  (keyword = '$keyword1' OR keyword = '$keyword2' OR keyword = '$keyword3' OR keyword = 'custom') ORDER BY CAST(`$orderby` AS SIGNED) $order  ";
		}

		$querydata = $wpdb->get_results ( $query, "ARRAY_A" );
		$data = $querydata;
		$current_page = $this->get_pagenum ();
		$total_items = count ( $data );
		$data = array_slice ( $data, (($current_page - 1) * $per_page), $per_page );
		$this->items = $data;

		$this->set_pagination_args ( array (
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page' => $per_page, // WE have to determine how many items to show on a page
				'total_pages' => ceil ( $total_items / $per_page ) 
			)
 		);
	}
}
    

