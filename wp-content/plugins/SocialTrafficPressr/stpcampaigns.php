<?php

function stp_campaigns(){

	

	global $wpdb;    
	$setstatus = plugins_url( 'stpSetStatus.php', __FILE__ );
		
		
	echo "<script type = 'text/javascript'>
	jQuery(document).ready(function () {
		jQuery('.ToggleSwitchSample').toggleSwitch();
		
		
		
		
	}); 

	function validateMyForm(){			
			var name = jQuery('#stpcampaignname').val();
		
			if (name =='') {
				alert('please enter campaign name!');
				return false;
				
			}
			
			
			
			return true;
			
	}
		
		
	function changeStatus(id){
	
		request = jQuery.ajax({
				type: 'post',
				  url: '".$setstatus."',
				  data: { id: id}
				  })
				    .done(function( msg ) {

				  });
	}

	
	</script>";
	
		
	
	
	if(isset($_POST['stpNewCampaign'])){
	
	
			
		
		$table_name = $wpdb->prefix ."stpCampaigns"; 
	
		$results = $wpdb->get_results( 
		"
		SELECT *
		FROM $table_name WHERE name != ''

		"
		,ARRAY_A);	
		$count = count($results);
		
		
		
		if ($count >= 3){
		
			echo"	<script>
				if (window.confirm('The basic version of Social Traffic Pressr allows 1 campaign per installation. Upgrade to the pro version for UNLIMITED campaigns. ')) 
					{
					window.location.href='http://wpfanmachine.com/v2/sales/oto1.html';
					};
			
			</script>";
		
		}
		else{
	
		
				$wpdb->insert( $table_name,  array( 						 
						'name' =>''
								
							
			) );
			$id = $wpdb->insert_id;
		
			$_GET['edit'] = $id;
			
			echo"<script>
			window.location.replace('?page=stp_campaigns&edit=".$id."');
			</script>";
		}	
		
		
		
	}

	
	if(isset($_GET['edit'])){
	
		stp_editcampaigns($_GET['edit']);
	}
	else{
	
		if(isset($_GET['delete'])){
	
	
			$table_name = $wpdb->prefix ."stpCampaigns"; 	
			
			$wpdb->delete( $table_name, array( 'id' =>$_GET['delete'] ) );
		
	

	
		}

		
		echo '<div class = "wrap">

				<div class = "stphead">

				'.SOCIALTRAFFICPRESSR.'  </div>

				<h1> Campaigns</h1>
				<hr />';
				
		

		echo "
				<div style ='float:left;'>
					    <form method='post' action=''>
					<input name='stpNewCampaign' type='submit' value='Add New Campaign' class = 'button button-primary' id ='stpNewCampaign' >
					</form>
				</div>
				
			";	
		echo '

		    <form id="pagedata" method="post" action="">';

			
					
			$wp_list_table = new stpCampaigns();
				
			
			
			$wp_list_table->prepare_items();

			$wp_list_table->display();
			echo '</form>';
		
	}
}


function stp_editcampaigns($id){

	global $pagenow;
	global $wpdb;
	$wpdb->show_errors();
		

		
	$table_name = $wpdb->prefix ."stpCampaigns"; 
	$query = "SELECT name FROM $table_name WHERE id = $id";

		
	$querydata = $wpdb->get_row($query);
	$name =  $querydata->name;
	
	echo '<div class = "wrap">

				<div class = "stphead">

				'.SOCIALTRAFFICPRESSR.' </div>

				<h1>'. $name.'</h1>
				<hr />';
				
	
	//generic HTML and code goes here

	if ( isset ( $_GET['tab'] ) ) stp_admin_tabs($_GET['tab'], $id); else stp_admin_tabs('settings', $id);

}



function stp_admin_tabs( $current = 'settings' ,$campaign) {

	$campaign = $_GET['edit'];


    $tabs = array( 'settings' => 'Setup', 'findgroups'=>'Find Groups', 'mygroups' => 'My Groups');
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=stp_campaigns&tab=$tab&edit=".$campaign."'>$name</a>";

    }
    echo '</h2>';
      if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
	else $tab = 'settings';
	
      switch ( $tab ){
      
	case 'settings' :
	stp_campaign_settings($campaign);
	break;	
	
	case 'findgroups' :
	stp_findgroups($campaign);
	break;	
	
	case 'mygroups' :
	stp_mygroups($campaign);
	break;	
    
   }
 } 

function stp_campaign_settings($campaign){

		global $wpdb;
		
		if(isset($_POST['stpSaveSettings'])){
			
			
			$stpcampaignname =  trim($_POST['stpcampaignname']);	

			$stppageid =   trim($_POST['stppageid']);	
				
			$stpkeyword1 =   trim($_POST['stpkeyword1']);	
			$stpkeyword2 =   trim($_POST['stpkeyword2']);	
			$stpkeyword3 =   trim($_POST['stpkeyword3']);	
	
			
			update_post_meta('111111113', $campaign.'stpcampaignname', $stpcampaignname );
			
			update_post_meta('111111113', $campaign.'stpkeyword1', $stpkeyword1 );
			update_post_meta('111111113', $campaign.'stpkeyword2', $stpkeyword2 );
			update_post_meta('111111113', $campaign.'stpkeyword3', $stpkeyword3 );
			
			update_post_meta('111111113', $campaign.'stppageid', $stppageid );
		
		
		
			
			$table_name = $wpdb->prefix ."stpCampaigns"; 
					$wpdb->update( 
			$table_name, 
			array( 
				'name' => $stpcampaignname	// string
				
			),
			array( 'id' => $campaign ));
			
		
		
			
		
			
			$successmessage = " <strong>Settings Saved</strong>";
			
		}
		
	
	
		
		$stpcampaignname = get_post_meta(111111113,$campaign.'stpcampaignname', TRUE);		
		$stppageid = get_post_meta(111111113,$campaign.'stppageid', TRUE);
	
		
		$stpkeyword1 = get_post_meta(111111113,$campaign.'stpkeyword1', TRUE);
		$stpkeyword2 = get_post_meta(111111113,$campaign.'stpkeyword2', TRUE);
		$stpkeyword3 = get_post_meta(111111113,$campaign.'stpkeyword3', TRUE);

	
		
		echo "<br />
			<br />
			<form method='post' action='' onsubmit= 'return validateMyForm();'>

				<table>
					<tr><td>Campaign Name: </td><td ><input type='text' name='stpcampaignname' value='".$stpcampaignname."' id ='stpcampaignname'><input name='stpSaveSettings' type='submit' value='Save' class = 'button button-primary' id ='stpSaveSettings' >".$successmessage."</td></tr>";
				
				echo"	<tr><td>Keyword1: </td><td ><input type='text' name='stpkeyword1' value='".$stpkeyword1."' id ='stpcampaignname'>";
				echo"	<tr><td>Keyword2: </td><td ><input type='text' name='stpkeyword2' value='".$stpkeyword2."' id ='stpcampaignname'>";
				echo"	<tr><td>Keyword3: </td><td ><input type='text' name='stpkeyword3' value='".$stpkeyword3."' id ='stpcampaignname'>";
				
				echo '<tr><td>Page: </td><td><select name = "stppageid">';
				$query = "me/accounts";
				$response = stpFacebookQuery($query, "");
				
				foreach ($response ['data'] as $page){
			
					$pagename = $page['name'];
					$id = $page['id'];
					$id = trim($id);				
					$token = $page['access_token'];
						if ($id == $stppageid){
							echo "<option value = '".$id."'  selected = 'selected'>".$pagename."</option>";
							update_post_meta('111111113', $campaign.'pageaccesstoken', $token );
						}
						else{	
							echo "<option value = '".$id."'>".$pagename."</option>";
						}	
				}
				
					
				
				echo '</select><td></tr>';
				
			
				
				echo'</table></form>';
				
				
			
		
	
	
		

}




      
