<?php
/*
 * Plugin Name: Social Traffic Pressr Plugin URI: http://socialtrafficpressr.com Description: Send EASY, REAL Facebook Traffic To Your Wordpress Site. Version: dev Author: Dan Green
 */
error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );

session_start ();
require_once (ABSPATH . 'wp-includes/pluggable.php');

require_once 'stpsettings.php';
require_once 'stpinstall.php';

require_once 'stpcampaigns.php';
require_once 'class.stpcampaigns.php';

require_once 'stppages.php';
require_once 'class.stppages.php';

require_once 'class.stpschedules.php';
require_once 'class.stplogs.php';
require_once 'class.stpcomments.php';

$image = plugin_dir_url ( __FILE__ ) . 'logo.png';
$favicon = plugin_dir_url ( __FILE__ ) . 'favicon.png';

define ( "SOCIALTRAFFICPRESSR", "Social Traffic Pressr" );
define ( 'WPSL_URL', plugin_dir_url ( __FILE__ ) );

add_action ( 'admin_menu', 'stp_menu' );
add_action ( 'admin_enqueue_scripts', 'stp_admin_scripts' );
register_activation_hook ( __FILE__, 'stp_install' );

function stp_menu() {
	$hook = add_menu_page ( 'Social Traffic Pressr', __ ( 'Social Traffic Pressr', 'stp' ), 'manage_options', 'stp', 'stp_admin', plugin_dir_url ( __FILE__ ) . 'favicon.png' );
	$settings = add_submenu_page ( 'stp', 'Settings', 'Settings', 'manage_options', 'stp', 'stp_settings' );
	$campaigns = add_submenu_page ( 'stp', 'Find Pages', 'Find Pages', 'manage_options', 'stp_pages', 'stp_pages' );
	
	add_submenu_page( 'stp', 'Scheduler', 'Scheduler', 'manage_options', 'stp_schedules', 'stp_schedules' );
	add_submenu_page( 'stp', 'Log', 'Log', 'manage_options', 'stp_logs', 'stp_logs' );
	add_submenu_page( 'stp', 'Comments', 'Comments', 'manage_options', 'stp_comments', 'stp_comments' );

	$support = add_submenu_page ( 'stp', 'Support', 'Support', 'manage_options', 'stp_support', 'stp_support' );
	// add_action( 'admin_print_styles-' . $campaigns, 'stp_plugin_admin_styles' );

	/*
	 * $search = add_submenu_page( 'stp', 'Hot Pages', 'Hot Pages', 'manage_options', 'stp_search_data', 'stp_search_data' ); $commenter = add_submenu_page( 'stp', 'Comments', 'Comments', 'manage_options', 'stp_commenter', 'stp_commenter' ); $replies = add_submenu_page( 'stp', 'Replies', 'Replies', 'manage_options', 'stp_replies', 'stp_replies' ); $log = add_submenu_page( 'stp', 'Log', 'Log', 'manage_options', 'stp_log', 'stp_log' );(/ add_action( 'admin_print_styles-' . $page, 'my_plugin_admin_styles' );
	 */
}

function stp_admin_scripts() {
	wp_enqueue_style ( 'fb-admin-css', plugins_url ( 'admin-style.css', __FILE__ ), false );
	wp_enqueue_style ( 'togglescss', plugins_url ( 'css/tinytools.toggleswitch.min.css', __FILE__ ), false );
	wp_enqueue_script ( 'jquery' );
	wp_enqueue_script ( 'toggles', plugins_url ( 'tinytools.toggleswitch.min.js', __FILE__ ), false );
	wp_enqueue_script ( 'chart', plugins_url ( 'chart.js', __FILE__ ), false );
}

function stp_support() {
	echo '<div class = "wrap">
			<div class = "fbvahead">
			' . SOCIALTRAFFICPRESSR . ' </div>
			<h1> Support</h1>
			<hr />
			<iframe src="http://wpfanmachine.com/support/stpsupport.html" width = "100%" height = "3500px" scrolling = "no"></iframe>';
}

function stp_admin() {
}

function stpFacebookQuery($query, $params) {
	$post_url = 'https://graph.facebook.com/' . $query;
	$accesstoken = get_post_meta ( 111111119, 'stpaccesstoken', TRUE );
	// echo "access token = ".$accesstoken;
	$params = $params . "&limit=5000";
	$post_url = $post_url . '?access_token=' . $accesstoken . $params;
	
	// echo $post_url;
	
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $post_url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	
	$result = json_decode ( $result, TRUE );
	
	return $result;
}

function stp_humanTiming($time) {
	$time = strtotime ( $time );
	$time = time () - $time; // to get the time since that moment
	
	$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second' 
	);
	
	foreach ( $tokens as $unit => $text ) {
		if ($time < $unit)
			continue;
		$numberOfUnits = floor ( $time / $unit );
		return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
	}
}



// --- hr ---
add_action ( 'admin_post_nopriv_fb_publish', 'stp_facebook_publish' );
add_action ( 'admin_post_fb_publish', 'stp_facebook_publish' );

add_action ( 'admin_post_nopriv_fb_updatemeta', 'stp_facebook_updatemeta' );
add_action ( 'admin_post_fb_updatemeta', 'stp_facebook_updatemeta' );

function stp_facebook_publish() {
	$id = $_GET ['pid'];
	$post = get_post( $id );

	global $wpdb;
	$wpdb->show_errors();

	$table_name = $wpdb->prefix ."stppages";
	$query = "SELECT * FROM $table_name WHERE engage = 'YES'";
	$data = $wpdb->get_results($query);

	foreach( $data as $d ) {
		$wpdb->insert( $wpdb->prefix . "stpschedule", array(
				"post_id" => $id, 
				"post_title" => get_the_title( $id ),
				"page_id" => $d->id,
				"node_id" => $d->node_id,
				"timescheduled" => time(),
				"facebook_page_title" => $d->title
			) 
		); 
	}
	
	$title = get_post_meta( $id, 'stp_facebook_title', true );
	stpPostToFacebook( 
		get_the_permalink( $id ),
		empty($title) ? get_the_title( $id ) : $title
	);

	header( "location: {$_SERVER["HTTP_REFERER"]}" );
}

function stp_facebook_updatemeta() {
	$id = $_GET ['pid'];
	$post = get_post( $id );

	update_post_meta($id, "stp_facebook_title", $_GET['fbtitle']);
	update_post_meta($id, "stp_facebook_image", $_GET['fbimage']);

	header( "location: {$_SERVER["HTTP_REFERER"]}" );
}

function stpPostToFacebook($link, $title, $pageid = ''){
	$title =  html_entity_decode($title);
	$accesstoken = get_post_meta(111111119, 'stppageaccesstoken', TRUE);
	
	if ( empty( $pageid ) ) {
		$pageid = get_post_meta(111111119, 'stppageid', TRUE);
	}

	echo "<p><em>Posting to $pageid</em></p>";
	
	$data['link'] = $link;
	$data['message'] = $title;
	$data['access_token'] = $accesstoken;

	$post_url = 'https://graph.facebook.com/'.$pageid.'/feed';

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$return = curl_exec($ch);
	curl_close($ch);

	$return = json_decode($return);
	//var_dump($return);
	return $return;
}

function stpPostComment($postid, $comment){
	
	$title =  html_entity_decode($title);
	$accesstoken = get_post_meta(111111119, 'stppageaccesstoken', TRUE);
	$fbpageid = get_post_meta(111111119, 'stppageid', TRUE);

	$data['message'] = $comment;
	$data['access_token'] = $accesstoken;

	$post_url = 'https://graph.facebook.com/'.$postid.'/comments';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	$result = curl_exec($ch);
	curl_close($ch);

	$result = json_decode($result, TRUE);
	return $result;

}

function stpGetLatestPost($pageid){
	$title =  html_entity_decode($title);
	$accesstoken = get_post_meta(111111119, 'stppageaccesstoken', TRUE);
	// $fbpageid = get_post_meta(111111119, 'stppageid', TRUE);

	$data['access_token'] = $accesstoken;

	$post_url = 'https://graph.facebook.com/'.$pageid."/posts?limit=1&access_token={$data['access_token']}";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_GET, 1);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	$result = curl_exec($ch);
	curl_close($ch);

	$result = json_decode($result, TRUE);

	if ( count( $result["data"] ) > 0 )
		return $result["data"][0]["id"];
	else
		return false;
}

function stpInsertLog($code, $fb_id, $title, $poc){
	global $wpdb;
	$wpdb->show_errors();
	$table_name = $wpdb->prefix ."stplog";

	$result = $wpdb->insert( $table_name,  array(
			'post' => $fb_id,
			'timesent' => time(),
			'comment' => $title,
			'poc' => $poc,
			'error' => $code
	) );
}

function stp_schedules() { 
	echo "<h1>Schedules</h1>";
	echo '<form method="post" action="">';
	$wp_list_table = new STPSchedules_Table();
	$wp_list_table->prepare_items();
	$wp_list_table->display();
	echo "</form>";
}

function stp_logs() {
	echo "<h1>Logs</h1>";
	
	$wp_list_table = new STPLogs_Table();
	$wp_list_table->prepare_items();
	$wp_list_table->display();
}

function stp_comments() {
	echo "<h1>Comments</h1>";

	if (isset($_POST['stpAddComment'])){
		$stpComment =  trim($_POST['stpComment']);

		global $wpdb;
		$wpdb->show_errors();
		$table_name = $wpdb->prefix ."stpcomments";
		$wpdb->query( $wpdb->prepare(
				"
				INSERT INTO $table_name
				( Comment )
				VALUES ( %s )
				",
				$stpComment
		) );
	}

	$spintaxtut = plugin_dir_url(__FILE__ ).'spintax.html';

	$setcomment = plugins_url( 'stpsetcomment.php', __FILE__ );
	// $spincomment = plugins_url( 'stpspincomment.php', __FILE__ );
	
	echo '
		<script type="text/javascript">
			function editComment(comment) {
				var text = document.getElementById("editcomment"+comment).value;
				request = jQuery.ajax({
						type: "post",
						url: "'.$setcomment.'",
						data: { id: comment, spintax: text , campaign: "'.$campaign.'"}
				}).done(function( msg ) {
				});
			}
		</script>';

		echo "<div>
			<form method='post' action=''>
			<table>
			<tr>
			<td>Comment <input type='text' value='".$stpComment."' name='stpComment' id='stpComment' size='100' /> </td>
			<td>
				<input name='stpAddComment' type='submit' value='Add Comment' class = 'button button-primary' /></td></tr>
			</table>
			</form></div>";

	echo "<div id ='testingspintax'></div>";
	echo"<p><a href ='".$spintaxtut."' target='_blank'>How Do I Use Spintax?</a></p>";

	echo '<form id="pagedata" method="post" action="">';

	$wp_list_table = new STPComments_Table();
	$wp_list_table->prepare_items();
	$wp_list_table->display();

	echo '</form>';
}

function stpRemoveFromSchedule( $id ) {
	global $wpdb;
	$wpdb->show_errors();
	$wpdb->get_results("delete from " . $wpdb->prefix . "stpschedule where id=" . $id);
}

function stp_facebook_preview() {
	include( "stp_preview.php" );
}

function stp_preview( $post_type ) {
	if ( in_array( $post_type, array( 'post', 'page' ) ) ) {
		global $post;
		if ($post->post_type == 'post' && $post->post_status == 'publish') {
			add_meta_box(
				'facebook_preview_meta',
				'Facebook Preview',
				'stp_facebook_preview',
				$post_type,
				'advanced', // change to something other then normal, advanced or side
				'high'
			);
		}
	}
}

function stp_add_preview() {
	global $post, $wp_meta_boxes;
	do_meta_boxes( get_current_screen(), 'facebook-preview', $post );
	unset($wp_meta_boxes['post']['facebook-preview']);
}

add_action('add_meta_boxes', 'stp_preview');
add_action('edit_form_after_title', 'stp_add_preview');
add_action('wp_head', 'stp_head');

function stp_head() {
	global $post;

	if ( $post->post_type == 'post' ) {
		$postId = $post->ID;
		$postTitle = get_post_meta($postId, "stp_facebook_title", true);
		if ( empty($postTitle) ) $postTitle = $post->post_title;
		$postImage = get_post_meta($postId, "stp_facebook_image", true);

		if ( empty($postImage) ) {
			$feat_image = wp_get_attachment_url( get_post_thumbnail_id($postId) );
			if ( empty($feat_image) ) {
				$match = array();
				preg_match( "/<img.+src=[\'\"](?P<src>.+?)[\'\"].*>/i", $post->post_content, $match );
				if ( sizeof($match) > 0 ) {
					$postImage = $match["src"];
				}
			}
			else {
				$postImage = $feat_image;
			}
		}

		echo '<meta property="og:title" content="'.$postTitle.'">' . "\n";
		echo '<meta property="og:description" content="'.get_the_excerpt($postId).'">' . "\n";
		echo '<meta property="og:url" content="'.get_the_permalink($postId).'">' . "\n";
		echo '<meta property="og:image" content="'.$postImage.'">' . "\n";
	}
}