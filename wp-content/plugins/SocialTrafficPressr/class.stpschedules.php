<?php
global $wpdb;

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class STPSchedules_Table extends WP_List_Table {
	function get_columns() {
		return $columns = array(
				'cb' => '<input type="checkbox" />',
				'post_id' => 'Post ID',
				'post_title' => 'Title',
				'node_id' => 'Facebook ID',
				'facebook_page_title' => 'Facebook Title',
				'timescheduled' => 'Created on',
				'actionbtn' => ''
		);
	}
	
	function column_default( $item, $column_name ) {
		if ( $column_name == 'timescheduled' )
			return date('Y-m-d H:i:s', $item[$column_name]);
		return $item[$column_name];
	}
	
	function column_cb($item) {
		return sprintf (
				'<input type="checkbox" name="schedule[]" value="%1$s" />',
				$item ['id'] );
	}
	
	function column_actionbtn($item) {
		return sprintf( 
				'<a href="%1$s">Delete</a>',
				wp_nonce_url( admin_url('admin.php?page=stp_schedules&action=delete&schedule=' . $item['id'] ) )
		);
	}

	function get_bulk_actions() {
		$actions = array (
			'delete' => 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		$table_name = $wpdb->prefix ."stpschedule";
		
		if ( $this->current_action() == 'delete' ) {
			foreach ( $_POST ['schedule'] as $input ) {
				$wpdb->delete ( $table_name, array (
						'id' => $input
				) );
			}
		}
	}
	
	function process_single_action() {
		if ( wp_verify_nonce( $_GET['_wpnonce'] ) && $this->current_action() == 'delete' ) {
			global $wpdb;
			$table_name = $wpdb->prefix ."stpschedule";

			$wpdb->delete ( $table_name, array (
				'id' => $_GET['schedule']
			) );
		}
	}

	function prepare_items() {
		$this->process_bulk_action ();
		$this->process_single_action ();
		
		$per_page = 50;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		global $wpdb;
		$wpdb->show_errors();

		$table_name = $wpdb->prefix ."stpschedule";

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