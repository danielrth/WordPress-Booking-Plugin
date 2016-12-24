jQuery( function ( $ )
{
	'use strict';

	$(document).ready( function() {

		const timezoneId = "39d62534-501c-49cd-9da9-6fa8d6a81f48";//fixed for Melbourne
		var locationsList = [];
		var clientsList = [];
		var availableLoc = [];
		var availableSlots = [];
		var appointmentsOfDay = [];
		var appointmentsHistory = [];
		var selectedClientId = '';
		var apiIndex = -1;
		var bookingStartDateTime = '';
		var bookingEndDateTime = '';

		$( "input.DatePicker" ).datepicker({
	        showButtonPanel: true,
	        minDate: new Date()
	    });

		$("input.BirthdayPicker").datepicker (
		{
			buttonImageOnly: true,
			yearRange: '1910:2010',
			changeYear: true
		});

		//initially hide all forms except client select
		$('#h-show-alert').hide();
		$('#div-register-form').hide();
		$('#div-verify-form').hide();
		$('#div-booking-select-form').hide();
		$('#div-checking-form').hide();
		$('#div-registered-client-info').hide();

		apiIndex = 1;
		sendRequestToAction('client', 'GET');

		$("input[name=clienttype]:radio").change(function () {
			$('#div-booking-select-form').hide();
			$('#div-checking-form').hide();
			$('#h-show-alert').html('Failed. Try again later.');

			if ($(this).val() == 'new') {
				$('#div-register-form').show();
				$('#div-verify-form').hide();
			}
			else {
				$('#div-register-form').hide();
				$('#div-verify-form').show();	
			}
		});
		//verify client
		$('#btn-verify-client').click(function() {
			var hasClient = false;
			for (var i = 0; i < clientsList.length; i++) {
				if (clientsList[i]['clientId'].substr(0,3).toLowerCase() == $('#input-verify-id').val().toLowerCase() && 
					(clientsList[i]['firstName'] + " " + clientsList[i]['lastName']).toLowerCase() == $('#input-verify-name').val().toLowerCase()) {

						selectedClientId = clientsList[i]['clientId'];
						$('#div-booking-select-form').show();
				    	if (appointmentsHistory.length == 0) {
					    	//get recent appointments +- 20 days
						    var startTime = formatDateStringFromTime( new Date(new Date().getTime() - 1000 * 3600 * 24 * 20) );
						    var endTime = formatDateStringFromTime( new Date(new Date().getTime() + 1000 * 3600 * 24 * 20) );

						    var postData = "startDate=" + startTime + "&endDate=" + endTime + "&timezoneId=" + timezoneId;
							apiIndex = 3;
							sendRequestToAction('appointment', 'GET', postData);	
					    }
						else {
							showAllUserAppointments();
						}

						hasClient = true;
						break;
				}
			}
			if (!hasClient)
				alert("Entered client name or ID is incorrect.");
		});
		//client select
		$('#select-registered-client').on('change', function (e) {
			$('#div-booking-select-form').hide();
			$('#div-checking-form').hide();

		    if ($('#select-registered-client').prop('selectedIndex') == 0) {
		    	$('#div-register-form').show();
		    }
		    else {
		    	selectedClientId = $('#select-registered-client').val();
		    	$('#div-register-form').hide();
		    	$('#div-booking-select-form').show();
		    	if (appointmentsHistory.length == 0) {
			    	//get recent appointments +- 20 days
				    var startTime = formatDateStringFromTime( new Date(new Date().getTime() - 1000 * 3600 * 24 * 20) );
				    var endTime = formatDateStringFromTime( new Date(new Date().getTime() + 1000 * 3600 * 24 * 20) );

				    var postData = "startDate=" + startTime + "&endDate=" + endTime + "&timezoneId=" + timezoneId;
					apiIndex = 3;
					sendRequestToAction('appointment', 'GET', postData);	
			    }
				else {
					showAllUserAppointments();
				}
		    }

		    $('#div-history-form').html('');
		});
		//hide availablility checking form on doctor select change
		$('#select-doctor').on('change', function (e) {
			$('#div-checking-form').hide();
		});
		//cancel appointment from history
		$(document).on("click", ".btn-cancel-appointment", function(){
			apiIndex = 5;
			var postData = {
			 	"appointmentId": this.id.slice(10, this.id.length), 
			 	"deleted": "true"};

			if (confirm("Are you sure to cancel the appointment?")) {
				sendRequestToAction('appointment', 'POST', postData);
				$(this).parent().remove();
			}
		});
		//register new client info
		$('#btn-submit-client').click(function() {
			//validate input data
			var isValid = true;
			
            $('#input-client-firstname,#input-client-lastname,#input-client-birthday,#input-client-phonenumber,#input-client-email').each(function () {
                if ($.trim($(this).val()) == '' || 
                	($(this).attr('id')=='input-client-phonenumber' && (isNaN($(this).val()) || $(this).val().length < 6)) || 
                	($(this).attr('id')=='input-client-email' && !isValidEmailAddress($(this).val()))
                ) {
                    isValid = false;
                    warnInvalidInput ($(this), false);
                }
                else {
                    warnInvalidInput ($(this), true);
                }
            });

            if (isValid == false)
            	return;
            
			//submit info to server API
			apiIndex = 2;
			var postData = {
			 	"firstName": $('#input-client-firstname').val(), 
			 	"lastName": $('#input-client-lastname').val(), 
			 	"birthday": $('#input-client-birthday').val(),
			 	"phoneNumber": $('#input-client-phonenumber').val(),
			 	"eMail": $('#input-client-email').val()};
			sendRequestToAction('client', 'POST', postData);
		});

		//check availability slots of doctor
		$('#btn-check-availability').click(function() {
			var isValid = true;
			$('#select-doctor,#input-date').each(function () {
                if ($.trim($(this).val()) == '' || 
                	($(this).attr('id')=='select-doctor' && $(this).prop('selectedIndex') == 0))
                {
                    isValid = false;
                    warnInvalidInput ($(this), false);
                }
                else {
                    warnInvalidInput ($(this), true);
                }
            });
			if (isValid == false)
            	return;

            $('#div-checking-form').hide();

            var checkDate = new Date ($('#input-date').val());
            var strCheckDate = checkDate.getFullYear() + '-' + 
            			formatTimeNumber(checkDate.getMonth() + 1) + '-' + 
            			formatTimeNumber(checkDate.getDate());

            appointmentsOfDay = filterPractitionerAppointments(appointmentsHistory, $('#select-doctor').val(), strCheckDate);
    		var strAPHtml = "";
    		if (appointmentsOfDay.length == 0)
    			strAPHtml = "No Appointments."
			$.each(appointmentsOfDay, function(key, value) {
		     	strAPHtml += "<p>" + value['startDateTime'].substr(11,5) + " ~ "  + value['endDateTime'].substr(11,5) + "</p>";
			});
    		$('#div-appointments').html(strAPHtml);

            apiIndex = 4;
            var postData = "startDate=" + strCheckDate + "&endDate=" + strCheckDate + "&practitionerId=" + $('#select-doctor').val() + "&timezoneId=" + timezoneId;
			sendRequestToAction('availabilityslot', 'GET', postData);
		});

		//------------submit booking appointment-------------
		$('#btn-submit-booking').click(function() {

			var isValid = true;
			$('#select-doctor,#select-location,#input-date,#input-start-time,#input-end-time')
			.each(function () {
                if ($.trim($(this).val()) == '' || 
                	($(this).attr('id')=='select-doctor' && $(this).prop('selectedIndex') == 0)
                ) {
                    isValid = false;
                    warnInvalidInput ($(this), false);
                }
                else {
                    warnInvalidInput ($(this), true);
                }
            });
			if (!isValid) return;

			if (bookingStartDateTime == '' || bookingEndDateTime == '' || $('.selected').length == 0) {
				alert ("Please choose start time and end time of the appointment.")
				return;
			}

			if (!checkInNearAppointment(appointmentsOfDay, bookingStartDateTime, bookingEndDateTime)) {
				alert ("not in near appointment")
				return;
			}

			var clientsArr = new Array();
			clientsArr.push({"clientId": selectedClientId});
			var doctorsArr = {"practitionerId": $('#select-doctor').val()};
			apiIndex = 6;
			var postData = {
			 	"startDateTime": bookingStartDateTime, 
			 	"endDateTime": bookingEndDateTime, 
			 	"practitioner": doctorsArr,
			 	"locationId": availableLoc['locationId'],
			 	"clients": clientsArr};
			sendRequestToAction('appointment', 'POST', postData);
		});

		function sendRequestToAction(api_name, request_type, post_data = "") {

			if (apiIndex == 6 || apiIndex == 2 || apiIndex == 4 || apiIndex == 1) {
				$('#h-show-alert').html('Waiting response. Please wait a minute...');
				$('#h-show-alert').show();
			}
			else {
				$('#h-show-alert').hide();
			}

			jQuery.post(
			    '/mywp/wp-admin/admin-ajax.php',
			    // '/wp-admin/admin-ajax.php',
			    {
			        'action': 'action_coreplus_api',
			        'api_name':   api_name,
			        'type': request_type,
			        'data': post_data
			    }, 
			    function(response){
			    	response = response.slice(0, -1);
			    	try {

			    		var resp_data = jQuery.parseJSON(response);
				        switch(api_name) {
				        	case "location":
				        		locationsList = resp_data.locations;
				        		break;
				        	case "client":
				        		if (apiIndex == 1) {
				        			clientsList = resp_data.clients;
				        			$.each(clientsList, function(key, value) {
								     $('#select-registered-client')
								         .append($("<option></option>")
								            .attr("value",value['clientId'])
								            .text(value['firstName'] + " " + value['lastName']));
									});

				        			$('#h-show-alert').html('');
				        			apiIndex = 7;
									sendRequestToAction('location', 'GET');
									sendRequestToAction('practitioner', 'GET');
				        		}
				        		else if (apiIndex == 2) {
				        			var newClient = resp_data.client;
				        			newClient['firstName'] = $('#input-client-firstname').val();
				        			newClient['lastName'] = $('#input-client-lastname').val();
								    clientsList.push(newClient);
								    selectedClientId = newClient['clientId'];
								    $('#div-register-form').hide();
								    $('#div-registered-client-info').show();
								    $('#td-registered-name').html($('#input-client-firstname').val() + ' ' + $('#input-client-lastname').val());
								    $('#td-registered-id').html(newClient['clientId'].substr(0,3));

								    $('#h-show-alert').html('You are successfully registered. \n Please remember the registered name and ID');
								    $('#div-booking-select-form').show();
				        		}
				        		
				        		break;
				        	case "practitioner":
				        		var doctors = resp_data.practitioners;
				        		$.each(doctors, function(key, value) {   
								     $('#select-doctor')
								         .append($("<option></option>")
								            .attr("value",value['practitionerId'])
								            .text(value['firstName'] + " " + value['lastName'])); 
								});
				        		break;
				        	case "appointment":
				        		if (apiIndex == 3) {
				        			//get all recent appointments
				        			appointmentsHistory = resp_data.appointments;
					        		showAllUserAppointments();
				        		}
				        		else if (apiIndex == 5) {
				        			//removed appointemtn from history
				        			var removedAppointment = resp_data.appointment;
				        		}
				        		else if (apiIndex == 6) {
				        			//booked new appointment
				        			if ('appointment' in resp_data) {
					        			var newApp = resp_data.appointment;
						        		if ('appointmentId' in newApp) {
						        			$('#h-show-alert').html('Successfully booked an appointment.');
						        		}
					        		}
					        		else if ('result' in resp_data){
					        			var resultRes = resp_data.result[0];
					        			$('#h-show-alert').html(resultRes['reason']);
					        		}
					        		else {
					        			$('#h-show-alert').html('Failed. Try again later.');
					        		}
				        		}
				        		
				        		break;
				        	case "availabilityslot":
				        		availableSlots = resp_data.timeslots;
				        		$('#div-checking-form').show();
				        		
								if (availableSlots.length > 0) {
									for (var key in locationsList) {
										if (locationsList[key]['locationId'] == availableSlots[0]['locationId']){
											availableLoc = locationsList[key];
										}
									}
									$('#div-available-location').html(availableLoc['name']);	
								}
								showTimeRangeButtons($("#div-from-buttons"), $('#button-end-booking'), availableSlots);
								$('#div-from-buttons:first-child').click();
								// disableTimeRanges($('.TimePicker'), availableSlots);
								$('#h-show-alert').html('');
				        		break;
				        	default:
				        		break;
				        }
			    	}
			        catch(e) {
			        	alert(e);
			        	$('#h-show-alert').html('Failed. Try again later.');
			        }
			    }
			);
		}

		function showAllUserAppointments() {
			var strAPHtml = "<h5>Appointments history</h5>";
			var userApps = filterUserAppointments(appointmentsHistory, selectedClientId);
			if (userApps.length == 0)	return;
			$.each(userApps, function(key, value) {
		     	strAPHtml += "<p><span style='margin-right:40px;'>" + value['startDateTime'].substr(0,16).replace('T', '  ') + " ~ "
		     	 + value['endDateTime'].substr(0,16).replace('T', '  ') + "</span>";
		     	strAPHtml += "<button class='btn-cancel-appointment' id=btncancel_" + value['appointmentId'] + ">Cancel</button></p>";
			});
			$('#div-history-form').html(strAPHtml);
		}

		$(document).on("click", ".btn-booking-time", function(){
			$('.btn-booking-time').removeClass('selected');
			$(this).addClass('selected');

			getTimeframeFromButton($(this).html());
		});

		function getTimeframeFromButton(var strStartTimeButton) {
			$('#button-end-booking').html(getEndTimeHtml(strStartTimeButton));

			var startDate = new Date ($('#input-date').val());
			var startTimeDiff = getTimeDiffFromPicker(strStartTimeButton);

			var startTime = new Date (startDate.getTime() + startTimeDiff);
			var endTime = new Date (startDate.getTime() + startTimeDiff + 30 * 60 * 1000);

            startTime = new Date(startTime.getTime() - 60000 * startTime.getTimezoneOffset());
            endTime = new Date(endTime.getTime() - 60000 * endTime.getTimezoneOffset());

			bookingStartDateTime = startTime.toISOString().slice(0, 19) + "+11:00";
			bookingEndDateTime = endTime.toISOString().slice(0, 19) + "+11:00";
		}
	});
});
