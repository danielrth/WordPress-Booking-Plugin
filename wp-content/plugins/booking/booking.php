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
    wp_enqueue_script( 'booking-script', BOOKING_PLUGIN_URL . '/scripts/script.js' );
}

add_shortcode( 'booking_form', 'booking_form_handler' );

function booking_form_handler() {

	add_action( 'wp_footer', 'booking_custom_assets');
	add_action( 'wp_footer', 'booking_custom_jscript' );

	$html_form = "";
	$html_form .= "<div><p><span style='margin-right:30px'>Rigistered Clients</span>";
	$html_form .= "<select id='select-registered-client'><option value='0'>I am a new client</option></select>";
	$html_form .= "</p></div>";

	$register_form = "<div id='div-register-form'>";
	$register_form .= "<h2>Register New Client</h2>";
	$register_form .= "<table><tr>";
	$register_form .= "<td>First Name</td>";
	$register_form .= "<td><input type=text id='input-client-firstname' /></td></tr>";
	$register_form .= "<tr><td>Last Name</td>";
	$register_form .= "<td><input type=text id='input-client-lastname' /></td></tr>";
	$register_form .= "<tr><td>Birth of Date</td>";
	$register_form .= "<td><input type=text class='BirthdayPicker' id='input-client-birthday' /></td></tr>";
	$register_form .= "<tr><td>Phone Number</td>";
	$register_form .= "<td><input type=text id='input-client-phonenumber' /></td></tr>";
	$register_form .= "<tr><td>Email</td>";
	$register_form .= "<td><input type=text id='input-client-email' /></td></tr>";
	$register_form .= "</table>";
	$register_form .= "<button id='btn-submit-client'>Submit</button></div>";

	$booking_form = "<div id='div-booking-form'>";
	$booking_form .= "<h2>Booking an Appointment</h2>";
	$booking_form .= "<table><tr>";
	$booking_form .= "<td>Doctor</td>";
	$booking_form .= "<td><select id='select-doctor'><option value='0'></option></select></td></tr>";
	$booking_form .= "<td>Location</td>";
	$booking_form .= "<td><select id='select-location'><option value='0'>Unknown</option></select></td></tr>";
	$booking_form .= "<tr><td>Date</td>";
	$booking_form .= "<td><input type=text class='DatePicker' id='input-date' /></td></tr>";
	$booking_form .= "<tr><td>Time</td>";
	$booking_form .= "<td><input type=text class='TimePicker' id='input-start-time' size=9 />";
	$booking_form .= "<span style='padding-left:20px; padding-right: 20px;'>to</span>";
	$booking_form .= "<input type=text class='TimePicker' id='input-end-time' size=9 /></td></tr>";
	$booking_form .= "</table>";
	$booking_form .= "<button id='btn-submit-booking'>Submit</button></div>";
	$booking_form .= "<div><h5 id='h-show-alert' style='color:red'></div>";

	$html_form .= $register_form . $booking_form;

	return $html_form;
}

add_action( 'wp_ajax_action_coreplus_api', 'ajax_call_coreplus' );
add_action( 'wp_ajax_nopriv_action_coreplus_api', 'ajax_call_coreplus' );

function ajax_call_coreplus() {
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt($curl, CURLOPT_URL, 'https://sandbox.coreplus.com.au/API/Core/v2/'.$_POST['api_name'].'/');	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

	$jwToken = generateJwToken($_POST['api_name'], $_POST['type']);
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

function generateJwToken($apiName, $callType) {
	require_once( plugin_dir_path() . 'jwt.php' );
	
	$auth = JWT::encode( array( 
		  // "iss" => "http://localhost",
		"iss" => "http://melbournewalking.merapatiala.com",
		  "aud" => "https://sandbox.coreplus.com.au",
		  "nbf" => 1474534917,
		  "exp" => 1483782444,
		  "consumerId" => COREPLUS_CONSUMER_ID,
		  "accessToken" => COREPLUS_API_ACCESS_TOKEN,
		  "url" => "/API/Core/v2/" . $apiName . "/",
		  "httpMethod" => $callType), COREPLUS_SECRET_KEY );

	// $token = "";
	/* $token = "Authorization: JwToken eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cHM6Ly9zdGFnaW5nLmNvcmVwbHVzLmNvbS5hdSIsIm5iZiI6MTQ3MzY4MjQ0NCwiZXhwIjoxNDczNzgyNDQ0LCJjb25zdW1lcklkIjoiMzUzODkxZDMtYTE4Ny00ZDFhLWE2Y2ItODdlYmMyZjZkYjI2IiwiYWNjZXNzVG9rZW4iOiI2MTc2ZGNhMC04YmUyLTQ5NmMtOWU0Ny1jYzRlZDA2ZmQ3YjIiLCJ1cmwiOiIvQVBJL0NvcmUvdjIvbG9jYXRpb24vIiwiaHR0cE1ldGhvZCI6IkdFVCJ9.SKJGvbkNrr2Pcsx1MVrxgsEbLjO2HJ9wAdqWb_MvAGo"; */
	// $auth = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cHM6Ly9zdGFnaW5nLmNvcmVwbHVzLmNvbS5hdSIsIm5iZiI6MTQ3NDUzNDkxNywiZXhwIjoxNDgwNzgyNDQ0LCJjb25zdW1lcklkIjoiMzUzODkxZDMtYTE4Ny00ZDFhLWE2Y2ItODdlYmMyZjZkYjI2IiwiYWNjZXNzVG9rZW4iOiI2MTc2ZGNhMC04YmUyLTQ5NmMtOWU0Ny1jYzRlZDA2ZmQ3YjIiLCJ1cmwiOiIvQVBJL0NvcmUvdjIvY2xpZW50LyIsImh0dHBNZXRob2QiOiJQT1NUIn0.1XbXFQhV-Ckl84HrUmKo1cwts_ywHMCyoh15Z15ZyX8";

	return "Authorization: JwToken " . $auth;
}
