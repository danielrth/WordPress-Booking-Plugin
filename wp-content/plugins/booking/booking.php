<?php
/**
 * @package Appointment Booking Plugin
 * @version 1.0
 */

/*
Plugin Name: Appointment Booking
Plugin URI: 
Description: Appointment Booking Plugin
Author: Daniel
Version: 1.0
Author URI: 
*/

define( 'BOOKING_PLUGIN_URL', plugins_url( '', __FILE__ ));
define( 'COREPLUS_CONSUMER_ID', "353891d3-a187-4d1a-a6cb-87ebc2f6db26");
define( 'COREPLUS_API_ACCESS_TOKEN', "6176dca0-8be2-496c-9e47-cc4ed06fd7b2");
define( 'COREPLUS_SECRET_KEY', "1kUwumuhkadPEWfKlgVH/3cUGM+DL4zNfw7YXwUm2zYednvAM8P8QQ==");

function booking_custom_assets() {
	wp_enqueue_style( 'calendar-style', 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css' );
	wp_enqueue_style( 'timepicker-style', BOOKING_PLUGIN_URL . '/styles/jquery.timepicker.css' );
}

function booking_custom_jscript() {

    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script( 'timepicker-script', BOOKING_PLUGIN_URL . '/scripts/jquery.timepicker.js' );
    wp_enqueue_script( 'scheduler-script', BOOKING_PLUGIN_URL . '/scripts/scheduler.js' );
    wp_enqueue_script( 'booking-script', BOOKING_PLUGIN_URL . '/scripts/script.js' );
}

add_shortcode( 'booking_form', 'booking_form_handler' );

function booking_form_handler() {

	add_action( 'wp_footer', 'booking_custom_assets');
	add_action( 'wp_footer', 'booking_custom_jscript' );

	$html_form = 
		"<div>
			<p>
				<span style='margin-right:30px'>Rigistered Clients</span>
				<select id='select-registered-client'>
					<option value='0'>I am a new client</option></select>
			</p>
		</div>";

	$register_form = 
		"<div id='div-register-form'>
			<h2>Register New Client</h2>
			<table>
				<tr><td>First Name</td>
					<td><input type=text id='input-client-firstname' /></td>
				</tr>
				<tr><td>Last Name</td>
					<td><input type=text id='input-client-lastname' /></td>
				</tr>
				<tr><td>Birth of Date</td>
					<td><input type=text class='BirthdayPicker' id='input-client-birthday' /></td></tr>
				<tr><td>Phone Number</td>
					<td><input type=text id='input-client-phonenumber' /></td></tr>
				<tr><td>Email</td>
					<td><input type=text id='input-client-email' /></td></tr>
			</table>
			<button id='btn-submit-client'>Submit</button>
		</div>";

	$booking_form = 
		"<div id='div-booking-form'>
			<h2>Booking an Appointment</h2>
			<table>
				<tr><td>Doctor</td>
					<td><select id='select-doctor'><option value='0'></option></select></td></tr>
				<tr><td>Date</td>
					<td><input type=text class='DatePicker' id='input-date' /></td></tr>

				<tr><td><button id='btn-check-availability'>Check availability</button></td></tr>
				<tr><td colspan=2>
						<h3>Available location</h3>
						<div id='div-available-location'>
						</div></td></tr>
				<tr><td colspan=2>
						<h3>Availability Slots</h3>
						<div id='div-schedule'>
						</div></td></tr>
				<tr><td colspan=2>
						<h3>Booked Appointments</h3>
						<div id='div-appointments'>
						</div></td></tr>
				<tr><td>Time</td>
					<td><input type=text class='TimePicker' id='input-start-time' size=9 />
					<span style='padding-left:20px padding-right: 20px'>to</span>
					<input type=text class='TimePicker' id='input-end-time' size=9 /></td></tr>
			</table>
			<button id='btn-submit-booking'>Submit</button></div>
			<div><h5 id='h-show-alert' style='color:red'></div>";

	$html_form .= $register_form . $booking_form;

	return $html_form;
}

add_action( 'wp_ajax_action_coreplus_api', 'ajax_call_coreplus' );
add_action( 'wp_ajax_nopriv_action_coreplus_api', 'ajax_call_coreplus' );

function ajax_call_coreplus() {
	$curl = curl_init();
	$isSendURLHeader = $_POST['type'] == "GET" && $_POST['data'] != "";

	// Set some options - we are passing in a useragent too here
	$api_url = 'https://sandbox.coreplus.com.au/API/Core/v2/'.$_POST['api_name'] .
		($isSendURLHeader ? "/?" . $_POST['data'] : "/");
	
	// if ( $_POST['api_name'] == "availabilityslot" ) {
	// 	var_dump($api_url);
	// }
	curl_setopt($curl, CURLOPT_URL, $api_url);	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

	$jwToken = generateJwToken($_POST['api_name'], $_POST['type'], $isSendURLHeader, $_POST['data']);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
		$_POST['type'] == "GET" ? 
			array($jwToken) : array($jwToken, "Content-Type: application/json")
	);

	if ( $_POST['type'] == "POST" ) {
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($_POST['data']));
	}

	$resp = curl_exec($curl);
	echo $resp;
	if(curl_error($curl))
	{
	    echo 'error:' . curl_error($curl);
	}
	// Close request to clear up some resources
	curl_close($curl);
}

function generateJwToken($apiName, $callType, $isSendURLHeader, $postParam = '') {
	require_once( plugin_dir_path() . 'jwt.php' );
	
	$auth = JWT::encode( array( 
		  "iss" => "http://localhost",
		// "iss" => "http://melbournewalking.merapatiala.com",
		  "aud" => "https://sandbox.coreplus.com.au",
		  "nbf" => 1474534917,
		  "exp" => 1483782444,
		  "consumerId" => COREPLUS_CONSUMER_ID,
		  "accessToken" => COREPLUS_API_ACCESS_TOKEN,
		  // "url" => "/API/Core/v2/" . $apiName . "/",
		  "url" => "/API/Core/v2/" . $apiName . ($isSendURLHeader ? "/?" . $postParam : "/"),
		  "httpMethod" => $callType), COREPLUS_SECRET_KEY );
	// if ($apiName == "availabilityslot"){
	// 	var_dump($postParam);
	// }
		
	/* $token = "Authorization: JwToken eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cHM6Ly9zdGFnaW5nLmNvcmVwbHVzLmNvbS5hdSIsIm5iZiI6MTQ3MzY4MjQ0NCwiZXhwIjoxNDczNzgyNDQ0LCJjb25zdW1lcklkIjoiMzUzODkxZDMtYTE4Ny00ZDFhLWE2Y2ItODdlYmMyZjZkYjI2IiwiYWNjZXNzVG9rZW4iOiI2MTc2ZGNhMC04YmUyLTQ5NmMtOWU0Ny1jYzRlZDA2ZmQ3YjIiLCJ1cmwiOiIvQVBJL0NvcmUvdjIvbG9jYXRpb24vIiwiaHR0cE1ldGhvZCI6IkdFVCJ9.SKJGvbkNrr2Pcsx1MVrxgsEbLjO2HJ9wAdqWb_MvAGo"; */
	// $auth = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cHM6Ly9zdGFnaW5nLmNvcmVwbHVzLmNvbS5hdSIsIm5iZiI6MTQ3NDUzNDkxNywiZXhwIjoxNDgwNzgyNDQ0LCJjb25zdW1lcklkIjoiMzUzODkxZDMtYTE4Ny00ZDFhLWE2Y2ItODdlYmMyZjZkYjI2IiwiYWNjZXNzVG9rZW4iOiI2MTc2ZGNhMC04YmUyLTQ5NmMtOWU0Ny1jYzRlZDA2ZmQ3YjIiLCJ1cmwiOiIvQVBJL0NvcmUvdjIvY2xpZW50LyIsImh0dHBNZXRob2QiOiJQT1NUIn0.1XbXFQhV-Ckl84HrUmKo1cwts_ywHMCyoh15Z15ZyX8";

	return "Authorization: JwToken " . $auth;
}
