<?php
	require('../../../wp-blog-header.php');

	$id = $_POST['id'];
	$text =  $_POST['spintax'];
	$campaign =  $_POST['campaign'];

//	echo $id." ".$text." ".$campaign;

	global $wpdb;
	$wpdb->show_errors();

	$table_name = $wpdb->prefix ."stpcomments"; 

	$wpdb->update( 
		$table_name, 
		array( 
			'Comment' => $text
		), 
		array( 'id' => $id )
	); 
?>