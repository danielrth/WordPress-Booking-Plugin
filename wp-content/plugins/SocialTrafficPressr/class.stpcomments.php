<?php
global $wpdb;

if (! class_exists ( 'WP_List_Table' )) {
	require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class STPComments_Table extends WP_List_Table {
	
	function __construct() {
		parent::__construct ( array (
				'singular' => 'page', // Singular label
				'plural' => 'pages', // plural label, also this well be one of the table css class
				'ajax' => false 
		));

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
	function column_comment($item) {
		$comment = $item ['Comment'];
		$comment = stripslashes ( $comment );
		$id = $item ['id'];
		$input = '<input type="text" name="editcomment" id = "editcomment' . $id . '"  value = "' . $comment . '" size = "150"  onchange = "editComment(\'' . $id . '\')">';

		return $input;
	}
	function column_default($item, $column_name) {
		return $item [$column_name];
	}
	function column_keyword($item) {
		$keyword1 = get_post_meta ( 999911115, 'fm2keyword1', TRUE );
		$keyword2 = get_post_meta ( 999911115, 'fm2keyword2', TRUE );
		$keyword3 = get_post_meta ( 999911115, 'fm2keyword3', TRUE );

		$keyword = $item ['Keyword'];

		if ($keyword == '1') {
			return $keyword1;
		}
		if ($keyword == '2') {
			return $keyword2;
		}
		if ($keyword == '3') {
			return $keyword3;
		}
	}
	function column_node_id($item) {
		$id = $item ['node_id'];
		return " <a href = 'http://facebook.com/" . $id . "'>" . $id . "</a>";
	}
	function column_cb($item) {
		return sprintf ( 
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
		    $this->_args ['singular'],
			$item ['id'] );
	}
	function get_columns() {
		return $columns = array (
				'cb' => '<input type="checkbox" />',
				'Comment' => 'Comment' 
		);
	}
	function get_sortable_columns() {
		$sortable_columns = array (
				'Comment' => array (
						'Comment',
						false 
				)
		);
		return $sortable_columns;
	}
	function get_bulk_actions() {
		$actions = array (
				'delete' => 'Delete' 
		);
		return $actions;
	}
	function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix . "stpcomments";

		if ('delete' === $this->current_action ()) {
			foreach ( $_POST ['page'] as $input ) {
				$wpdb->delete ( $table_name, array (
						'id' => $input 
				) );
			}
		}
	}
	function prepare_items() {
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

		$table_name = $wpdb->prefix . "stpcomments";

		$orderby = $_REQUEST ['orderby'];
		$order = $_REQUEST ['order'];

		$query = "SELECT * FROM $table_name";
		$querydata = $wpdb->get_results ( $query, "ARRAY_A" );
		$data = $querydata;

		function usort_reorder($a, $b) {
			$orderby = (! empty ( $_REQUEST ['orderby'] )) ? $_REQUEST ['orderby'] : 'id'; // If no sort, default to title
			$order = (! empty ( $_REQUEST ['order'] )) ? $_REQUEST ['order'] : 'desc'; // If no order, default to asc
			$result = strcmp ( $a [$orderby], $b [$orderby] ); // Determine sort order
			return ($order === 'asc') ? $result : - $result; // Send final sort direction to usort
		}
		usort ( $data, 'usort_reorder' );
		$current_page = $this->get_pagenum ();
		$total_items = count ( $data );

		$data = array_slice ( $data, (($current_page - 1) * $per_page), $per_page );

		$this->items = $data;

		$this->set_pagination_args( array (
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page' => $per_page, // WE have to determine how many items to show on a page
				'total_pages' => ceil ( $total_items / $per_page ) 
		));
	}
}
