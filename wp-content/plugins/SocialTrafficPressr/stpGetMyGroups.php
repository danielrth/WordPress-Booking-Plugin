<?php

require('../../../wp-blog-header.php');

global $wpdb;


$params = '&q='.$keyword.'&type='.$searchtype_3.'&fields=name,id&offset='.$offset;
$query = "me/groups";
	
$raw = stpFacebookQuery($query, $params);
	
print_r($raw);
	



?>