<?php

require('../../../wp-blog-header.php');

set_time_limit(200);
function return_300( $seconds )
{
  // change the default feed cache recreation period to 2 hours
  return 300;  
}



$campaign = $_POST["campaign"];	

stpSearchGroups($campaign);

echo "DONE";
	

function stpSearchGroups($campaign){

	$keyword1 = get_post_meta(111111113,$campaign.'stpkeyword1', TRUE);
	$keyword2 = get_post_meta(111111113,$campaign.'stpkeyword2', TRUE);
	$keyword3 = get_post_meta(111111113,$campaign.'stpkeyword3', TRUE);
	
	
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
	
	//echo "processing".$keyword;
	
	$tbl_kws_fb = $wpdb->prefix . "stpFoundGroups";
	$per_page =5000;		
	$keyword = urlencode($keyword);	
	$limit=$per_page;
	$offset=0;
	$searchtype_3 = 'group'; 
	$query = "search";
	
	$params = '&q='.$keyword.'&type='.$searchtype_3.'&fields=name,id,updated_time,privacy&offset='.$offset;
	$raw = stpFacebookQuery($query, $params);
	
	//print_r($raw);
	
	$result_data = $raw["data"];
	$result_data = array_unique ($result_data, SORT_REGULAR);		
	
	
	
	foreach($result_data as $dt_3){
	
		$nodeid =  $dt_3['id'];			
		$row =  $wpdb->get_row(" SELECT * FROM $tbl_kws_fb WHERE node_id = $nodeid  "); 			
		 
		 if (empty($row)){
			
			//echo $nodeid."<br />";
			$wpdb->insert( 
				$tbl_kws_fb, 
				array( 
					'keyword' => $keyword, 
					'node_id' => $nodeid,
					'title'=>$dt_3["name"],
					'updated_time'=> $dt_3['updated_time'],
					'privacy' =>$dt_3['privacy']
				
				),
				array( 
			'%s', 
			'%s' ,
			'%s', 
			'%s' ,
			'%s' 
			) 
			
			) ;			
		}	
		
		else{				
		
										
					
			$wpdb->update( 
				$tbl_kws_fb, 
				array( 
					'keyword' => $keyword, 
					'updated_time' => $dt_3['updated_time'],
					'privacy' =>$dt_3['privacy']			
				), 
				array( 'node_id' => $nodeid )
			);
		
		}
	}
}







?>
