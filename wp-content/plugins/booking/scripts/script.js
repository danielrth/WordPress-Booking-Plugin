jQuery( function ( $ )
{
	'use strict';

	$(document).ready( function() {

		jQuery.post(
		    '/mywp/wp-admin/admin-ajax.php', 
		    {
		        'action': 'action_coreplus_api',
		        'api_name':   'location',
		        'type': 'GET'
		    }, 
		    function(response){
		    	response = response.slice(0, -1);

		        var locations = jQuery.parseJSON(response);
		        locations = locations.locations;
		        
		        var htmlSelectorLocations = "";
		        for (var key in locations) {
		        	htmlSelectorLocations += "<option>" + locations[key]['name'] + "</option>";
		        }

		        $('#select-location').html(htmlSelectorLocations);
		    }
		);

		jQuery.post(
		    '/mywp/wp-admin/admin-ajax.php', 
		    {
		        'action': 'action_coreplus_api',
		        'api_name':   'client',
		        'type': 'GET'
		    }, 
		    function(response){
		    	response = response.slice(0, -1);

		        var clients = jQuery.parseJSON(response);
		        clients = clients.clients;
		        
		        var htmlSelectorClients = "";
		        for (var key in clients) {
		        	htmlSelectorClients += "<option>" + clients[key]['firstName'] + "</option>";
		        }

		        $('#select-registered-clients').html(htmlSelectorClients);
		    }
		);

		jQuery.post(
		    '/mywp/wp-admin/admin-ajax.php', 
		    {
		        'action': 'action_coreplus_api',
		        'api_name':   'client',
		        'type': 'POST'
		    }, 
		    function(response){
		    	response = response.slice(0, -1);

		        var clients = jQuery.parseJSON(response);
		        clients = clients.clients;
		        
		        var htmlSelectorClients = "";
		        for (var key in clients) {
		        	htmlSelectorClients += "<option>" + clients[key]['firstName'] + "</option>";
		        }

		        $('#select-registered-clients').html(htmlSelectorClients);
		    }
		);
		{
"firstName":"Phynx",
"middleName": "Huntington", 
"lastName":"Slavia",
"gender":"Male",
"dateOfBirth":"1961-07-30",
"Email":"aaa@ggg.com",
"phoneNumberMobile":"0334819194"
}
		// $("input#demo").dcalendarpicker();
	});

	function sendRequestToAction(action_name, api_name, request_type) {
		jQuery.post(
		    '/mywp/wp-admin/admin-ajax.php', 
		    {
		        'action': 'action_coreplus_api',
		        'api_name':   'client',
		        'type': 'POST'
		    }, 
		    function(response){
		    	response = response.slice(0, -1);

		        var resp_data = jQuery.parseJSON(response);
		        
		    }
		);
	}
});
