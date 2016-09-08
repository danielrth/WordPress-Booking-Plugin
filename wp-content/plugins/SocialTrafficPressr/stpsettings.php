<?php







function stp_settings(){
	global $wpdb;

	require_once 'src/facebook.php';
	
	
		
		if(isset($_POST['stpAuth'])){

			$id = trim($_POST['apikey']);

			$secret =  trim($_POST['apisecret']);
			
			
		
			
		
		
			
		

			update_post_meta('111111119', 'stpFbID', $id );	

			update_post_meta('111111119', 'stpFbSecret', $secret );
			
		
			
			update_post_meta('111111119', 'stptimezone', $stptimezone );	

			
		}

		if(isset($_POST['stpSaveSettings'])){
				$stptimezone =  trim($_POST['stptimezone']);
				$stppageid = trim($_POST['stppageid']);
				
				update_post_meta('111111119', 'stptimezone', $stptimezone );	
				update_post_meta('111111119', 'stppageid', $stppageid );	

		}

			

		$id = get_post_meta(111111119,'stpFbID', TRUE);

		$secret = get_post_meta(111111119,'stpFbSecret', TRUE);
		
		$google = get_post_meta(111111119,'stpGoogle', TRUE);
	
		
		$stptimezone = get_post_meta(111111119,'stptimezone', TRUE);
		
	
				

		$facebook = new stpFacebook(array(

			  'appId'  => $id,

			  'secret' => $secret,

			));



		$user = $facebook->getUser();

		

		

		if ($user) {

		try {

		// Proceed knowing you have a logged in user who's authenticated.

		$user_profile = $facebook->api('/me');

		} catch (wpfmFacebookApiException $e) {

		error_log($e);

		$user = null;

		}

		}

		// Login or logout url will be needed depending on current user state.

		if ($user) {

		$logoutUrl = $facebook->getLogoutUrl();

		

		$accesstoken = $facebook->getAccessToken();	

		
		update_post_meta('111111119', 'stpaccesstoken', $accesstoken );	

		

		} else {

		$loginUrl = $facebook->getLoginUrl(array(
		'scope'         => 'publish_actions,manage_pages,publish_pages,user_managed_groups'));

		

			

		}

		$id = get_post_meta(111111119,'stpFbID', TRUE);

		$secret = get_post_meta(111111119,'stpFbSecret', TRUE);

		$authurl = get_post_meta(111111119,'stpFbAuthURL', TRUE);
		
		
		
	

		$authfacebook = plugin_dir_url(__FILE__ ).'authfacebook.php';

		$cron = plugin_dir_url(__FILE__ ).'auto.php';
		$alertcron = plugin_dir_url(__FILE__ ).'auto2.php';
		$cron3 = plugin_dir_url(__FILE__ ).'auto3.php';
			

		

		

		echo '<div class = "wrap">

			<div class = "fbvahead">

			'.SOCIALTRAFFICPRESSR.' </div>

			<h1> Settings</h1>
			<hr />
			';

			

		

		echo "<form method='post' action=''>

				<table>

					

					
					<tr><td>Facebook App Key: </td><td ><input type='text' name='apikey' value='".$id."'></td></tr>
					<tr><td>Facebook App Secret: </td><td ><input type='text' name='apisecret' value='".$secret."'></td></tr>
				";
					
					
			/*		<tr><td>Report Email: </td><td ><input type='text' name='emailreport' value='".$emailreport."'></td></tr>
					<tr><td>Enable Daily Reports </td><td>";
					if ($enablereports == "no"){
						echo"	<input type='radio' name='enablereports' value='yes'>Yes <input type='radio' name='enablereports' value='no' checked='checked'>No</td></tr>	";		
					}
					else{
						echo"	<input type='radio' name='enablereports' value='yes'  checked='checked'>Yes <input type='radio' name='enablereports' value='no' >No</td></tr>	";	
					
					}*/
					
					
					
					
				
				
					echo"
				
					
					<tr><td>

					";

					

						echo"</td><td><input name='stpAuth' type='submit' value='Begin Auth' class = 'button button-primary'></td>";

						

						if (!empty($loginUrl )){

							echo "<td><a href = '".$loginUrl."'>Complete Auth</a></td></tr>";

						}

						/*else if (!empty($logoutUrl )){

							echo "<td><a href = '".$logoutUrl."'>Unauth Facebook</a></td></tr>";

						}*/

						else{

							echo "</tr>";

						}

					echo'

					

				</table>
				
			</form>
		<form method="post" action="">';
			echo '<tr><td>Select Page: </td><td><select name = "stppageid">';
				$query = "me/accounts";
				$response = stpFacebookQuery($query, "");
				
				foreach ($response ['data'] as $page){
			
					$pagename = $page['name'];
					$id = $page['id'];
					$id = trim($id);				
					$token = $page['access_token'];
						if ($id == $stppageid){
							echo "<option value = '".$id."'  selected = 'selected'>".$pagename."</option>";
							update_post_meta('111111119', "stppageaccesstoken", $token );
						}
						else{	
							echo "<option value = '".$id."'>".$pagename."</option>";
						}	
				}
				echo '
					</select><tr><td>Select Your Timezone: </td><td>
					<select name="stptimezone" >';
					for($i= 12; $i>0; $i--){
						if ($stptimezone == "-".$i){
							echo '<option value= -'.$i.' selected = "selected">UTC -'.$i.':00</option>';
						}
						else{
							echo '<option value= -'.$i.' >UTC -'.$i.':00</option>';
						}
					}
					for($i= 0; $i<13; $i++){
						if ($stptimezone == "+".$i){
							echo '<option value= +'.$i.' selected = "selected">UTC +'.$i.':00</option>';
						}
						else{
							echo '<option value= +'.$i.' >UTC +'.$i.':00</option>';
						}
					}
					echo"</select></td><td><input name='stpSaveSettings' type='submit' value='Save' class = 'button button-primary'></td>";

					
		echo'</form>

		<hr />
		
		<h2>Cron Info</h2>';
		
		echo"
		<table>
		<tr><td>Once Per Hour Cron URL: </td><td colspan = '2'>wget -O /dev/null ".$cron."</td></tr>
		<tr><td>Once Per Day Cron URL: </td><td colspan = '2'>wget -O /dev/null ".$alertcron."</td></tr>
		<tr><td>Twice Per Hour Cron URL: </td><td colspan = '2'>wget -O /dev/null ".$cron3."</td></tr>
					
					
		
		</table>";
		echo'<h3>Alternative Cron Commands (Try these if the above dont work on your webhost) </h3>';
		
			echo"
		<table>
		<tr><td>Once Per Hour URL: </td><td colspan = '2'>lynx -dump /dev/null ".$cron."</td></tr>
		<tr><td>Once Per Day Cron URL: </td><td colspan = '2'>lynx -dump /dev/null ".$alertcron."</td></tr>
		<tr><td>Twice Per Hour  Cron URL: </td><td colspan = '2'>lynx -dump /dev/null ".$cron3."</td></tr>
		<tr></tr>
	
		
		</table>";
		
		
		
		echo"</div>";
		
		
		
	
	

}

