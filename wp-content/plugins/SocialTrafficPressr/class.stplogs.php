<?php
global $wpdb;

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class STPLogs_Table extends WP_List_Table {
	function get_columns() {
		return $columns = array(
				'post' => 'Facebook ID',
				'comment' => 'Title',
				'timesent' => 'Sent on',
				'error' => 'Error'
		);
	}

	function column_default( $item, $column_name ) {
		if ( $column_name == 'timesent' )
			return date('Y-m-d H:i:s', $item[$column_name]);
		return $item[$column_name];
	}

	function prepare_items() {
		$per_page = 50;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		// $this->process_bulk_action();

		global $wpdb;
		$wpdb->show_errors();

		$table_name = $wpdb->prefix ."stplog";

		$orderby = $_REQUEST['orderby'];
		$order = $_REQUEST['order'];

		if ( empty($orderby) ) {
			$query = "SELECT * FROM $table_name";
		}
		else {
			if ( empty($order) ) {
				$query = "SELECT * FROM $table_name order by {$orderby}";
			}
			else {
				$query = "SELECT * FROM $table_name order by {$orderby} {$order}";
			}
		}

		$queryResult = $wpdb->get_results($query, "ARRAY_A");
		$data = $queryResult;

		$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice($data, (($current_page-1) * $per_page), $per_page);
		$this->items = $data;
		
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
		) );
	}
}