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
	wp_enqueue_style( 'calendar-style', BOOKING_PLUGIN_URL . '/styles/dcalendar.picker.css' );
}

function booking_custom_jscript() {
    wp_enqueue_script( 'calendar-script', BOOKING_PLUGIN_URL . '/scripts/dcalendar.picker.js' );
    wp_enqueue_script( 'booking-script', BOOKING_PLUGIN_URL . '/scripts/script.js' );
}

add_shortcode( 'booking_form', 'booking_form_handler' );

function booking_form_handler() {

	add_action( 'wp_enqueue_scripts', 'booking_custom_assets');
	add_action( 'wp_footer', 'booking_custom_jscript' );

	$html_form = "";
	$html_form .= "<div><p><span style='margin-right:30px'>Rigistered Clients</span>";
	$html_form .= "<select id='select-registered-clients'><option></option></select>";
	$html_form .= "</p></div>";	

	$register_form = "<div id='div-register-form>'";
	$register_form .= "<h2>Register New Client</h2>";
	$register_form .= "<table><tr>";
	$register_form .= "<td>First Name</td>";
	$register_form .= "<td><input type=text /></td></tr>";
	$register_form .= "<tr><td>Last Name</td>";
	$register_form .= "<td><input type=text /></td></tr>";
	$register_form .= "<tr><td>Birth of Date</td>";
	$register_form .= "<td>calendar</td></tr>";
	$register_form .= "</table>";
	$register_form .= "<button id='btn-submit-client'>Submit</button></div>";

	$booking_form = "<div id='div-booking-form'>";
	$booking_form .= "<h2>Booking an Appointment</h2>";
	$booking_form .= "<table><tr>";
	$booking_form .= "<td>Doctor</td>";
	$booking_form .= "<td><select id='select-doctor'><option></option></select></td></tr>";
	$booking_form .= "<td>Location</td>";
	$booking_form .= "<td><select id='select-location'><option></option></select></td></tr>";
	$booking_form .= "<tr><td>Date</td>";
	$booking_form .= "<td><input type=text class='form-control' id='demo' /></td></tr>";
	$booking_form .= "<tr><td>Time</td>";
	$booking_form .= "<td><input type=text class='DatePicker' /></td></tr>";
	$booking_form .= "</table>";
	$booking_form .= "<button id='btn-submit-booking'>Submit</button></div>";

	$html_form .= $register_form . $booking_form . $auth;

	return $html_form;
}

add_action( 'wp_ajax_action_coreplus_api', 'ajax_call_coreplus' );
// add_action( 'wp_ajax_nopriv_action_coreplus_api', 'ajax_call_coreplus' );

function ajax_call_coreplus() {
	
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt($curl, CURLOPT_URL, 'https://sandbox.coreplus.com.au/API/Core/v2/'.$_POST['api_name'].'/');	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

	$jwToken = generateJwToken($_POST['api_name'], $_POST['type']);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    	$jwToken
    ));

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
		  "iss" => "http://localhost",
		  "aud" => "https://sandbox.coreplus.com.au",
		  "nbf" => 1474534917,
		  "exp" => 1483782444,
		  "consumerId" => COREPLUS_CONSUMER_ID,
		  "accessToken" => COREPLUS_API_ACCESS_TOKEN,
		  "url" => "/API/Core/v2/" . $apiName . "/",
		  "httpMethod" => $callType), COREPLUS_SECRET_KEY );

	// $token = "";
	/* $token = "Authorization: JwToken eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cHM6Ly9zdGFnaW5nLmNvcmVwbHVzLmNvbS5hdSIsIm5iZiI6MTQ3MzY4MjQ0NCwiZXhwIjoxNDczNzgyNDQ0LCJjb25zdW1lcklkIjoiMzUzODkxZDMtYTE4Ny00ZDFhLWE2Y2ItODdlYmMyZjZkYjI2IiwiYWNjZXNzVG9rZW4iOiI2MTc2ZGNhMC04YmUyLTQ5NmMtOWU0Ny1jYzRlZDA2ZmQ3YjIiLCJ1cmwiOiIvQVBJL0NvcmUvdjIvbG9jYXRpb24vIiwiaHR0cE1ldGhvZCI6IkdFVCJ9.SKJGvbkNrr2Pcsx1MVrxgsEbLjO2HJ9wAdqWb_MvAGo"; */

	return "Authorization: JwToken " . $auth;
}