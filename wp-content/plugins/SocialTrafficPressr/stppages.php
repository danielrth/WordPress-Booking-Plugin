<?php

function stp_pages($campaign){

	if(isset($_POST['stpSaveKeywords'])){
			$stpkeyword1 =   trim($_POST['stpkeyword1']);	
			$stpkeyword2 =   trim($_POST['stpkeyword2']);	
			$stpkeyword3 =   trim($_POST['stpkeyword3']);	
			
				
			update_post_meta('111111113', 'stpkeyword1', $stpkeyword1 );
			update_post_meta('111111113', 'stpkeyword2', $stpkeyword2 );
			update_post_meta('111111113', 'stpkeyword3', $stpkeyword3 );

	}
	if(isset($_POST['stpAddPage'])){

		$keyword = get_post_meta(111111114,$campaign.'stpkeyword1', TRUE);
		$keyword = urlencode($keyword);	
		
		$pageid =  trim($_POST['pageid']);			
		$pagename =  trim($_POST['pagename']);	
		
		global $wpdb;	
		$tbl_kws_fb = $wpdb->prefix . "stppages";			
			
			$wpdb->insert( 
				$tbl_kws_fb, 
				array( 
					'keyword' => "custom", 
					'node_id' =>$pageid,
					'title'=>$pagename,
					'talking_about'=> 999999999,
					'likes' => 999999999,
					'supressed' => 'NO'
				),
				array( 
			'%s', 
			'%s' ,
			'%s' ,
			'%d' ,			
			'%d', 
			'%s' 
			
			) 
		);	
		
	}
		
	$stpkeyword1 = get_post_meta(111111113,'stpkeyword1', TRUE);
	$stpkeyword2 = get_post_meta(111111113,'stpkeyword2', TRUE);
	$stpkeyword3 = get_post_meta(111111113,'stpkeyword3', TRUE);

	
	$setstatus = plugins_url( 'stpSetPageStatus.php', __FILE__ );

	echo "<script type = 'text/javascript'>
	jQuery(document).ready(function () {
		jQuery('.ToggleSwitchSample').toggleSwitch();
		
		
		
	}); 

	function stpchangeStatus(id){
	
		request = jQuery.ajax({
				type: 'post',
				  url: '".$setstatus."',
				  data: { id: id}
				  })
				    .done(function( msg ) {
					
				  });
	}

	
	</script>";
	$cron = plugin_dir_url(__FILE__ ).'stpfetchpages.php';	
					
	echo'<div>		
		<form method="post" action="">
		<table>';
		
		echo"	<tr><td>Keyword1: </td><td ><input type='text' name='stpkeyword1' value='".$stpkeyword1."'>";
		echo"	<tr><td>Keyword2: </td><td ><input type='text' name='stpkeyword2' value='".$stpkeyword2." '>";
		echo"	<tr><td>Keyword3: </td><td ><input type='text' name='stpkeyword3' value='".$stpkeyword3."'>";
	
		echo'	<td><input name="stpSaveKeywords" type="submit" value="Save Keywords" class = "button button-primary" ></td></tr>
	</table>
	</form>';
	echo '
	<br />
	<a href="'.$cron.'" target="_blank">
		<button class = "button button-primary" >Fetch Pages</button>
	</a>
	</div>
	<br />';
	
				
	echo'<div>		
		<form method="post" action="">
		<table>
		
			<td>ID: <input type="text" name="pageid" size = "50"></td><td>Name: <input type="text" name="pagename" size = "50"></td>
			
			';
	echo'	<td><input name="stpAddPage" type="submit" value="Add Custom Page" class = "button button-primary" ></td></tr>
	</table>
	</form>';
	
	echo      '<form id="pagedata" method="post" action="">';

	
			
	$wp_list_table = new stpPages_Table();
		
	
	
	$wp_list_table->prepare_items($campaign);

	$wp_list_table->display();
	echo '</form>';


	

}	





