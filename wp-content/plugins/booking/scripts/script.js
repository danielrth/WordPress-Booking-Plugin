jQuery( function ( $ )
{
	'use strict';

	$(document).ready( function() {

		jQuery.post(
		    '/wp-admin/admin-ajax.php', 
		    {
		        'action': 'action1',
		        'data':   'foobarid'
		    }, 
		    function(response){
		        alert('The server responded: ' + response);
		    }
		);

		$("input#demo").dcalendarpicker();
	})
});
