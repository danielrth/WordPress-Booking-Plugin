<?php
require ('../../../wp-blog-header.php');
require_once ( 'stpspintax.php' );

set_time_limit ( 200 );
function return_300($seconds) {
	return 300;
}

add_filter ( 'wp_feed_cache_transient_lifetime', 'return_300' );
global $wpdb;

$table_name = $wpdb->prefix . "stpschedule";
$queryResult = $wpdb->get_results("SELECT * FROM $table_name order by timescheduled, id asc", "ARRAY_A");

// $poc = rand(0, 100) % 2;
$poc = 0; // only comment

if ( $poc == 1 ) { // post
	foreach($queryResult as $p) {
		$res = stpPostToFacebook(
			get_the_permalink( $p['post_id'] ),
			get_the_title( $p['post_id'] ),
			$p['node_id']
		);
		echo "<p>Posting to Facebook Page</p>";

		if ( $return->error->message ) {
			stpInsertLog( $return->error->message, $p['node_id'], get_the_permalink( $p['post_id'] ), 0 );
		} else {
			stpInsertLog( $return->id, $p['node_id'], get_the_permalink( $p['post_id'] ), 0 );
		}

		stpRemoveFromSchedule( $p['id'] );
	}
}
else { // comment
	$spinTax = new stpSpintax();

	foreach($queryResult as $c) {
		$pid = stpGetLatestPost( $c['node_id'] );
		echo "<p><em>Getting Latest Post ID {$pid}</em></p>";

		if ( $pid == false ) {
			echo "<p>This page doesn't allow retrieving latest post</p>";
		} else {
			$res = stpPostComment(
				$pid,
				$spinTax->chooseOneAndProcessWithLink( get_the_permalink( $c['post_id'] ) )
			);
			echo "<p>Commenting to Facebook {$c['node_id']} - {$c['facebook_page_title']} Page</p>";
	
			if ( $return->error->message ) {
				stpInsertLog( $return->error->message, $c['node_id'], get_the_permalink( $c['post_id'] ), 1 );
			} else {
				stpInsertLog( $return->id, $c['node_id'], get_the_permalink( $c['post_id'] ), 1 );
			}
		}
		stpRemoveFromSchedule( $c['id'] );
	}
	echo "<p>Completed</p>";
}

?>
