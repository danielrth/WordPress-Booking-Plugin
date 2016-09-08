<?php

require('../../../wp-blog-header.php');

set_time_limit(200);
error_reporting(0);

function return_300( $seconds )
{
  // change the default feed cache recreation period to 2 hours
  return 300;
}


add_filter( 'wp_feed_cache_transient_lifetime' , 'return_300' );


stpGetPageData();
echo "DONE";

function stpGetPageData(){		
	$keyword1 = get_post_meta(111111113,'stpkeyword1', TRUE);
	$keyword2 = get_post_meta(111111113,'stpkeyword2', TRUE);
	$keyword3 = get_post_meta(111111113,'stpkeyword3', TRUE);
	
	if (!empty($keyword1)){

	  stpProcesskeyword($keyword1, $campaign);
	  
	  
	}

	if (!empty($keyword2)){

	  stpProcesskeyword($keyword2, $campaign);
	}

	if (!empty($keyword3)){

	  stpProcesskeyword($keyword3, $campaign);
	}

}





function stpProcesskeyword($keyword, $campaign){

	global $wpdb;
	
//	echo "processing".$keyword;
	
	$tbl_kws_fb = $wpdb->prefix . "stppages";
	$per_page =5000;		
	$keyword = urlencode($keyword);	
	$limit=$per_page;
	$offset=0;
	$searchtype_3 = 'page'; 
	$query = "search";
	
	$params = '&q='.$keyword.'&type='.$searchtype_3.'&fields=name,id,updated_time,link,fan_count,talking_about_count&limit='.$limit.'&offset='.$offset;
	$raw = stpFacebookQuery($query, $params, $campaign);
	
//	print_r($raw);
	
	$result_data = $raw["data"];
	$result_data = array_unique ($result_data, SORT_REGULAR);		
	
	
	
	foreach($result_data as $dt_3){
	
		$nodeid =  $dt_3['id'];			
		$row =  $wpdb->get_row(" SELECT * FROM $tbl_kws_fb WHERE node_id = $nodeid  "); 			
		 
		 if (empty($row)){
			//	echo $nodeid ." node not found <br />"; 
			//echo $nodeid."<br />";
			$wpdb->insert( 
				$tbl_kws_fb, 
				array( 
					'keyword' => $keyword, 
					'node_id' => $nodeid,
					'title'=>$dt_3["name"],
					'talking_about'=> $dt_3['talking_about_count'],
					'likes' =>$dt_3['fan_count'],
					'engage' => 'NO'
				),
				array( 
			'%s', 
			'%s' ,
			'%s', 
			'%d' ,
			'%d' 
			) 
			
			) ;			
		}	
		
		else{				
		
			$talkingabout =  $dt_3['talking_about_count'];
			$likes = $dt_3['fan_count'];
							
					
			$wpdb->update( 
				$tbl_kws_fb, 
				array( 
					'talking_about' => $talkingabout,	
					'likes' => $likes						
				), 
				array( 'node_id' => $nodeid )
			);
		
		}
	}
}








?>