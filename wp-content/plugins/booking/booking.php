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

add_shortcode( 'booking_form', 'booking_form_handler' );

function booking_form_handler() {
	
	$html_form = "";
	$html_form .= "<div><p><span style='margin-right:30px'>Rigistered Clients</span>";
	$html_form .= "<select id='select-registered-clients'><option></option></select>";
	$html_form .= "</p></div>";	

	$register_form = "<div id='div-register-form>'";
	$register_form .= "<h1>Register New Client</h1>";
	$register_form .= "<table><tr>";
	$register_form .= "<td>First Name</td>";
	$register_form .= "<td><input type=text /></td></tr>";
	$register_form .= "<tr><td>Last Name</td>";
	$register_form .= "<td><input type=text /></td></tr>";
	$register_form .= "<tr><td>Birth of Date</td>";
	$register_form .= "<td>calendar</td></tr>";
	$register_form .= "</table>";
	$register_form .= "<button>Submit</button></div>";

	$html_form .= $register_form;

	return $html_form;
}

add_action( 'wp_footer', 'booking_custom_jscript' );

function booking_custom_jscript() {
    wp_enqueue_script( 'booking-script', BOOKING_PLUGIN_URL . '/scripts/script.js' );
}
