<?php
	/*
		
			
	*/
	function stp_mygroups($campaign){
		$search = plugin_dir_url(__FILE__ ).'stpGetMyGroups.php?campaign='.$campaign;	
		$loading = plugin_dir_url(__FILE__ ).'ajax-loader.gif';
		$loading ="<img src = '".$loading."'/>";
		echo "<script type = 'text/javascript'>
			function stpGetMyGroups(){
			
				document.getElementById('stploading').innerHTML = \"".$loading."\";
				request = jQuery.ajax({
				type: 'post',	
				  url: '".$search."',	
				data: { campaign: ".$campaign."}
				})
			    .done(function( msg ) {
				location.reload(); 
			  });
				
			}

		
		</script>";
	
		
		
		echo ' <p>
			
			<button class = "button button-primary" onclick="stpGetMyGroups()">Update</button><div  id = "stploading" style = "float: left;"></div>
			</p>';
		
		echo      '<form id="pagedata" method="post" action="">';

	
				
		$wp_list_table = new stpMyGroupsTable();
			
		
		
		$wp_list_table->prepare_items($campaign);

		$wp_list_table->display();
		echo '</form>';


	}
?>