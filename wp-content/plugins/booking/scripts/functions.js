
	function minutesFromStart (timeStr) {
		var hour = timeStr.substr(11, 2) * 60 + timeStr.substr(14, 2) * 1;
		return isNaN(hour) ? -1 : hour;
	}
	//detect if booking timeframe is near an hour of appointment
	function checkInNearAppointment (appArr, bookingStartTime, bookingEndTime) {
		if (appArr.length == 0)	return true;
		for (var i = 0; i < appArr.length; i++) {
			if ((minutesFromStart(appArr[i]['startDateTime']) - 60 <= minutesFromStart(bookingEndTime) && 
				minutesFromStart(appArr[i]['startDateTime']) >= minutesFromStart(bookingEndTime)) ||
				(minutesFromStart(appArr[i]['endDateTime']) + 60 >= minutesFromStart(bookingStartTime) && 
				minutesFromStart(appArr[i]['endDateTime']) <= minutesFromStart(bookingStartTime)))
				return true;
		}
		return false;
	}
	function formatTimeNumber (ttt) {
		return ttt < 10 ? "0" + ttt : "" + ttt;
	}

	function convertOffsetToTimeZone(offsetValue) {
		var strTimezone = offsetValue <= 0 ? "+" : "-";

		offsetValue = Math.abs(offsetValue);
		var offsetHours = offsetValue / 60;

		strTimezone += Math.floor(offsetHours) >= 10 ? Math.floor(offsetHours).toString() : "0" + Math.floor(offsetHours).toString();

		strTimezone += ":";
		var offsetMinutes = offsetValue - offsetHours * 60;
		strTimezone += offsetMinutes >= 10 ? offsetMinutes.toString() : "0" + offsetMinutes.toString();
		return strTimezone;
	}

	function getTimeDiffFromPicker(strTime) {
		var numTime = strTime.split(':');
		return parseInt(numTime[0]) * 3600 * 1000 + parseInt(numTime[1]) * 60 * 1000;
	}

	function isValidEmailAddress(emailAddress) {
	    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
	    return pattern.test(emailAddress);
	};

	function warnInvalidInput(elementObj, valid) {
		if(valid == false) {
			elementObj.css({
                "border": "1px solid red",
                "background": "#FFCECE"
            });
		}
		else {
			elementObj.css({
                "border": "",
                "background": ""
            });
		}
	}

	function disableTimeRanges(elementObj, timeSlots) {

		function convertTimeToPickerTime (strTime) {
			var hour = parseInt(strTime.substr(11,2));
			if (hour == 12)
				return strTime.substr(11,5) + "pm";
			else if (hour > 12)
				return (hour - 12) + strTime.substr(13,3) + "pm";
			else
				return strTime.substr(11, 5) + "am";
		}
		function beforeOneStep (strTime) {
			var indexOfDel = strTime.indexOf(':');
			if (parseInt(strTime.substr(indexOfDel + 1, 2)) == 0) {
				if (parseInt(strTime.substr(0,indexOfDel)) == 1)
					return '12:59' + strTime.substr(indexOfDel + 3, 2);
				else
					return parseInt(strTime.substr(0,indexOfDel)) - 1 + ':59' + strTime.substr(indexOfDel + 3, 2);
			}
			else
				return strTime.substr(0,indexOfDel + 1) + (formatTimeNumber(parseInt(strTime.substr(indexOfDel + 1, 2)) - 1)) + strTime.substr(indexOfDel + 3, 2);
		}
		function nextOneStep (strTime) {
			var indexOfDel = strTime.indexOf(':');
			return strTime.substr(0,indexOfDel + 1) + (formatTimeNumber(parseInt(strTime.substr(indexOfDel + 1, 2)) + 1)) + strTime.substr(indexOfDel + 3, 2);
		}

		var disableSlots = new Array();
		var index = 0;
		for (var idx = 0; idx < timeSlots.length; idx++) {
			
			if (idx == 0)
				disableSlots.push(new Array("12:00am", beforeOneStep( convertTimeToPickerTime( timeSlots[0]['startDateTime'] ) ) ));
			else
				disableSlots.push(new Array( nextOneStep( convertTimeToPickerTime(timeSlots[idx-1]['endDateTime']) ) , beforeOneStep ( convertTimeToPickerTime(timeSlots[idx]['startDateTime']) ) ));
			if (idx == timeSlots.length - 1)
				disableSlots.push(new Array( nextOneStep( convertTimeToPickerTime(timeSlots[idx]['endDateTime']) ) , "11:59pm"));
		}
		// console.log(disableSlots);
		elementObj.timepicker('remove');
		elementObj.timepicker({ 'scrollDefault': 'now','disableTimeRanges': disableSlots });
	}

	function formatDateStringFromTime(timeD) {
		return timeD.toISOString().substr(0, 4) + "-" + timeD.toISOString().substr(5, 2) + "-" + timeD.toISOString().substr(8, 2);
	}

	function filterUserAppointments(arrApps, clientId) {
		var userApps = [];
		for (var idx=0; idx < arrApps.length; idx++) {
			var arrClis = arrApps[idx]['clients'];
			for (var idxCli = 0; idxCli < arrClis.length; idxCli++) {
				if (arrClis[idxCli]['clientId'] == clientId) {
					userApps.push(arrApps[idx]);
					break;
				}
			}
		}
		return userApps;
	}

	function filterPractitionerAppointments(arrApps, praId, checkDate) {
		var praApps = [];
		for (var idx=0; idx < arrApps.length; idx++) {
			var arrClis = arrApps[idx]['clients'];
			if (arrApps[idx]['practitioner']['practitionerId'] == praId &&
				arrApps[idx]['startDateTime'].substr(0,10) == checkDate)
				praApps.push(arrApps[idx]);
		}
		return praApps;
	}

	function showTimeRangeButtons (startDivObj, buttonEndTime, slots) {
		var htmlStartDivObj = '';
		for (var i = 0; i < slots.length; i++) {
			var startHour = parseInt(slots[i]['startDateTime'].substr(11,2));
			var startMin = parseInt(slots[i]['startDateTime'].substr(14,2));
			if (startMin != 0 && startMin <= 30) startHour = startHour + 0.5;
			if (startMin != 0 && startMin > 30) startHour +=1;
			var endHour = parseInt(slots[i]['endDateTime'].substr(11,2));
			var endMin = parseInt(slots[i]['endDateTime'].substr(14,2));
			if (endMin != 0 && endMin <= 30) endHour = endHour + 0.5;
			if (endMin != 0 && endMin > 30) endHour +=1;

			if (i == 0) buttonEndTime.html(getEndTimeHtml(slots[i]['startDateTime'].substr(11,5)));

			for (var iHour = startHour * 2; iHour < endHour * 2; iHour++) {
				if (i == 0 && iHour == startHour * 2)
					htmlStartDivObj += "<button class='btn-booking-time selected'>" + 
								parseInt(iHour/2) + ":" + (iHour % 2 == 0 ? "00" : "30") + "</button>";
				else
					htmlStartDivObj += "<button class='btn-booking-time'>" + 
								parseInt(iHour/2) + ":" + (iHour % 2 == 0 ? "00" : "30") + "</button>";
			}
			htmlStartDivObj += "<br>";
		}
		startDivObj.html(htmlStartDivObj);
	}

	function getEndTimeHtml(strStartTime) { //after 30 mins
		var timeSplit = strStartTime.split(':');
		if (parseInt(timeSplit[1]) >= 30)
			return parseInt(timeSplit[0]) + 1 + ':' + formatTimeNumber((parseInt(timeSplit[1]) + 30) % 60);
		else
			return parseInt(timeSplit[0]) + ':' + formatTimeNumber(parseInt(timeSplit[1]) + 30);
	}