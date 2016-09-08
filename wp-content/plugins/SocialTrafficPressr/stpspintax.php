<?php

/* this is for testing */
// URL/wp-content/plugins/SocialTrafficPressr/stpspintax.php
/* require ('../../../wp-blog-header.php');
$spintax = new stpSpintax();
for ( $i = 0; $i< 100; $i++) {
	echo $spintax->chooseOneAndProcessWithLink( 'http://www.google.com' );
	echo "<br/>";
}
*/

class stpSpintax
{
	var $commentList;

	function __construct() {
		global $wpdb;
		$table_name = $wpdb->prefix ."stpcomments";

		$results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY RAND() limit " . rand(3, 5), ARRAY_A);

		$this->commentList = array();

		foreach($results as $result){
			array_push( $this->commentList, $result['Comment'] );
		}
	}
	
    public function process($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            array($this, 'replace'),
            $text
        );
    }

    public function replace($text)
    {
        $text = $this->process($text[1]);
        $parts = explode('|', $text);
        return $parts[array_rand($parts)];
    }

    public function chooseOneAndProcessWithLink( $link ) {
    	$text = $this->commentList[ RAND(0, sizeof($this->commentList)-1 ) ];
    	$text = $this->process($text);
    	return str_replace("[LINK]",$link,$text);
    }
}