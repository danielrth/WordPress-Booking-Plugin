<?php
	require_once( '../../../wp-blog-header.php' );

	$id = $_POST['id'];

	global $wpdb;
	$wpdb->show_errors();

	$table_name = $wpdb->prefix ."stppages"; 

	$query = "SELECT engage FROM $table_name WHERE id = $id";

	$querydata = $wpdb->get_row($query);

	if ($querydata->engage == "YES"){
		$newstatus = 'NO';
	}
	else {
		$newstatus = 'YES';
	}

	$wpdb->update( 
		$table_name, 
		array( 
			'engage' => $newstatus
		), 
		array( 'id' => $id )
	); 

	exit();
?>