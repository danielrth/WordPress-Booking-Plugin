<?php
	require('../../../wp-blog-header.php');
	
	$id = $_POST['id'];
	
	
	
	global $wpdb;
	$wpdb->show_errors();
	
	$table_name = $wpdb->prefix ."stpCampaigns"; 
	
	$query = "SELECT status FROM $table_name WHERE id = $id";
	 
	$querydata = $wpdb->get_row($query);
	

	
	if ($querydata->status == "ON"){
	
		$newstatus = 'OFF';
	}
	else {
	
		$newstatus = 'ON';
		
	}
	
	$wpdb->update( 
	$table_name, 
	array( 
		'status' => $newstatus
		
	), 
	array( 'id' => $id )
	
	); 
	

?>