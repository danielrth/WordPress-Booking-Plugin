<?php
function stp_install(){
	global $wpdb;
	$wpdb->show_errors();

	$table_name = $wpdb->prefix ."stppages";   	 
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) { 

		$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		node_id text ,
		title text ,
		watching text,
		talking_about int(11),
		likes int(11),
		keyword text NOT NULL,
		engage text,
		UNIQUE KEY id (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);    
	}

	$table_name = $wpdb->prefix ."stpschedule";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		post_id int(11),
		post_title varchar(500),
		page_id int(11),
		node_id varchar(500),
		timescheduled int(11),
		facebook_page_title varchar(50),
		UNIQUE KEY id (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	$table_name = $wpdb->prefix ."stplog";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		post int(11),
		timesent int(11),
		poc int(11),
		comment text,
		error text,
		UNIQUE KEY id (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	$table_name = $wpdb->prefix ."stpcomments";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
		id INTEGER(100) UNSIGNED AUTO_INCREMENT,
		Comment text,
		UNIQUE KEY id (id)
		)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Nice |Great}{|!|post|info|} {|thanks|thanks a lot} {I|} {love|really love} [LINK]'
		) );

		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Interesting|Cool } {post|info|} {|thanks|so much} {this is|} {really great|really good} {more on [LINK] please|} '
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Awesome|Tremendous|Amazing} {| post}  {|very informative|very interesting } {{I love | I <3}[LINK]|}'
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'Thanks {|!| for the post| for the info|} {big [LINK] fan here|}'
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'Who else {loves [LINK]| thinks [LINK] is cool} {?|}'
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{who else|who else really} {gets|loves} {this|[LINK]} {?|}'
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Absolutely|Always| I always} {love|adore} {everything| anything} {like this|about [LINK]| related to [LINK]}'
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{I think | IMO} {posts|stuff|anything} about [LINK] {is|are} {great|fab|fantastic} {who agrees?|}',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'anyone {|else} {love| like } {[LINK]|this | this post} as much as {me|i do}  ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{loving the| love the| such a great}  {post | page | fanpage}  ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Valuable Post | Important Post| Important Info} {!|} ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'Anything { about | related to} {this|[LINK] } is {so|very|really|} important ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'More {info|posts|stuff} on [LINK] {please|ok?} {like if you agree|like = agree|who agrees?|} ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Worlds|Planets|} {biggest|best} [LINK] {fan|super fan} {|!|right here} ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{Stuff |posts} {like this| about [LINK]} are why {I love | I like | everyone loves | everyone likes} {social media | facebook | your page | this page} ',
		) );
		$wpdb->insert( $table_name,  array(
				'Comment' =>'{<3| I <3 | Who else? <3| Who else? <3}[LINK] {?|} ',
		) );
	}
}
