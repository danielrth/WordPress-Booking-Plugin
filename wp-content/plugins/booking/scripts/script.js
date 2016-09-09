jQuery( function ( $ )
{
	'use strict';
	
	$(document).ready( function() {
		$(document).html('sdfsdfs');

		$.ajax({
		  type: "POST",
		  url: "https://www.google.co.uk",
		  data: "data",
		  success: function(result){
		        alert(result);
		    }
		});
	})
} );