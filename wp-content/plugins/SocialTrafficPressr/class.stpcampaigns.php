<?php
global $wpdb;

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class stpCampaigns extends WP_List_Table {
	
	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	 function __construct() {
		 parent::__construct( array(
		'singular'=> 'page', //Singular label
		'plural' => 'pages', //plural label, also this well be one of the table css class
		'ajax'	=> false //We won't support Ajax for this table
		) );
		
		add_action( 'admin_head', array( &$this, 'admin_header' ) );
	 }
	 
	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			
		}
		if ( $which == "bottom" ){
			
		}
	}
	 function column_default($item, $column_name){
		
		return $item[$column_name];		
	}
	
	
	
	
	function column_cb($item) {
		return sprintf(

		    '<input type="checkbox" name="%1$s[]" value="%2$s" />',

		    /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")

		    /*$2%s*/ $item['id']              //The value of the checkbox should be the record's id

		);
	}
	
	function column_actions($item) {
		$id =  $item['id'];		
		$edit = "<a href ='?page=stp_campaigns&edit=".$id."'>Edit</a>";
		$delete = "<a href ='?page=stp_campaigns&delete=".$id."'>Delete</a>";
		$log = "<a href ='?tab=log&page=stp_campaigns&edit=".$id."'>Post Log</a>";
	
		return $edit." | ".$delete." | ".$log;
	}

	function column_status($item){
		$id = $item['id'];
		$status =  $item['status'];	
		if ($status == "ON"){
			return '<input type="checkbox" class="ToggleSwitchSample" checked="checked"  onchange = "changeStatus(\''.$id.'\');"  />';
		}	
		else{
			return '<input type="checkbox" class="ToggleSwitchSample"   onchange = "changeStatus(\''.$id.'\');" />';
		}
		
		
	}
	
	function get_columns() {
		return $columns= array(
		
		'name' => 'Name',		
		'actions' =>'Actions',
		'status' => 'Status'
		
			
			
				
			
			
		);
	}


	
	function get_sortable_columns() {
		$sortable_columns = array(
			
		
			'name'=>array('name',false)			
				
		
			
						
			
			
		);
		return $sortable_columns;
	}
	



    
	
	
 function prepare_items() {
     
	
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 50;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
                  $this->process_bulk_action();
        
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
	global $wpdb;    
	$wpdb->show_errors();
	
	$table_name = $wpdb->prefix ."stpCampaigns"; 
	
	   $orderby = $_REQUEST['orderby'];
	   $order = $_REQUEST['order'];
	   

	$query = "SELECT * FROM $table_name WHERE name != '' ";
	 
	$querydata = $wpdb->get_results($query,"ARRAY_A");
	
        $data=$querydata;
		
      
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
       
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}
    


