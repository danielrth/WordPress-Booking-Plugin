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

	$html_form .= $register_form . $booking_form;

	return $html_form;
}

add_action( 'wp_enqueue_scripts', 'booking_custom_assets' );
add_action( 'wp_footer', 'booking_custom_jscript' );

function booking_custom_assets() {
	wp_enqueue_style( 'calendar-style', BOOKING_PLUGIN_URL . '/styles/dcalendar.picker.css' );
}

function booking_custom_jscript() {
    wp_enqueue_script( 'calendar-script', BOOKING_PLUGIN_URL . '/scripts/dcalendar.picker.js' );
    wp_enqueue_script( 'booking-script', BOOKING_PLUGIN_URL . '/scripts/script.js' );
}
