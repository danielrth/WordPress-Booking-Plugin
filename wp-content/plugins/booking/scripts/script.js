jQuery( function ( $ )
{
	'use strict';

	$(document).ready( function() {

		$("input.DatePicker").datepicker();
		$("input.BirthdayPicker").datepicker (
		{
			buttonImageOnly: true,
			yearRange: '1910:2010',
			changeYear: true
		});
		// $("input.TimePicker").timepicker();

		sendRequestToAction('location', 'GET');
		sendRequestToAction('client', 'GET');
		sendRequestToAction('practitioner', 'GET');

		$('#btn-submit-client').click(function() {
			/* 
			var postDataString = "";
			postDataString += ", firstName: " + $('#input-client-firstname').val();
			postDataString += ", lastName: " + $('#input-client-lastname').val();
			postDataString += ", dateOfBirth: " + $('#input-client-birthday').val();
			postDataString += ", phoneNumberMobile: " + $('#input-client-phonenumber').val();
			postDataString += ", Email: " + $('#input-client-email').val(); */
			
			var postData = {
			 	"firstName": $('#input-client-firstname').val(), 
			 	"lastName": $('#input-client-lastname').val(), 
			 	"birthday": $('#input-client-birthday').val(),
			 	"phoneNumber": $('#input-client-phonenumber').val(),
			 	"eMail": $('#input-client-email').val()};
			// alert (postDataString);
			sendRequestToAction('client', 'POST', JSON.stringify( postData ));
		});
	});

	function sendRequestToAction(api_name, request_type, post_data = "") {

		jQuery.post(
		    '/mywp/wp-admin/admin-ajax.php',
		    {
		        'action': 'action_coreplus_api',
		        'api_name':   api_name,
		        'type': request_type,
		        'data': post_data
		    }, 
		    function(response){
		    	response = response.slice(0, -1);

		        var resp_data = jQuery.parseJSON(response);
		        var htmlStr = "";
		        
		        switch(api_name) {
		        	case "location":
		        		var locations = resp_data.locations;
				        for (var key in locations) {
				        	htmlStr += "<option>" + locations[key]['name'] + "</option>";
				        }
				        $('#select-location').html(htmlStr);
		        		break;
		        	case "client":
		        		if (request_type == "GET") {
		        			var clients = resp_data.clients;
					        for (var key in clients) {
					        	htmlStr += "<option>" + clients[key]['firstName'] + "</option>";
					        }
					        $('#select-registered-client').html(htmlStr);
		        		}
		        		else {
		     //    			"firstName":"Phynx",
							// "middleName": "Huntington", 
							// "lastName":"Slavia",
							// "gender":"Male",
							// "dateOfBirth":"1961-07-30",
							// "Email":"aaa@ggg.com",
							// "phoneNumberMobile":"0334819194"
		        		}
		        		
		        		break;
		        	case "practitioner":
		        		var doctors = resp_data.practitioners;
				        for (var key in doctors) {
				        	htmlStr += "<option>" + doctors[key]['firstName'] + "</option>";
				        }
				        $('#select-doctor').html(htmlStr);
		        		break;
		        	case "appointment":
		        		break;
		        	default:
		        		break;
		        }
		    }
		);
	}

});
