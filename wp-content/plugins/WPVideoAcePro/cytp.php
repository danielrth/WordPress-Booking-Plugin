<?php
/*
Plugin Name: WP Video Ace Pro

Plugin URI:

Description: Take full control of your videos with powerful engagement effects and custom playback options.

Author: Dan Green

Version: 1.0.5

Author URI: 

*/

// deactivate basic plugin if exists
function cytpro_deactivate_basic_version() {
	if ( is_plugin_active( 'cytp/cytp.php' ) ) {
		deactivate_plugins( 'cytp/cytp.php' );
	}
}
register_activation_hook( __FILE__, 'cytpro_deactivate_basic_version' );

require_once 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
   'https://kernl.us/api/v1/updates/5752dd7e29c78fac1c749d8d/',
   __FILE__,
   'cytp',
   1
);

// define absolute path
define( 'CYTPRO_PLUGIN_PATH', ABSPATH . 'wp-content/plugins/WPVideoAcePro' );

// define absolute url
define( 'CYTPRO_PLUGIN_URL', plugins_url( '', __FILE__ ) );

require_once( CYTPRO_PLUGIN_PATH . '/vendor/meta-box/meta-box.php' );

if ( ! defined( 'MBC_INC_DIR' ) ) {
	require_once( CYTPRO_PLUGIN_PATH . '/vendor/meta-box-conditional-logic/meta-box-conditional-logic.php' );
}

if ( ! class_exists( 'Mobile_Detect' ) ) {
	require_once( CYTPRO_PLUGIN_PATH . '/vendor/mobile_detect.php' );
}

// --- Text Domain ---
add_action( 'after_setup_theme', 'cytpro_load_plugin' );
function cytpro_load_plugin() {
	load_plugin_textdomain( 'cytpro_plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}
// --- End of Text Domain --- 

// --- Styles ---
add_action( 'wp_enqueue_scripts', 'cytpro_custom_assets' );
add_action( 'wp_footer', 'cytpro_custom_scripts' );
add_action( 'admin_enqueue_scripts', 'cytpro_admin_assets' );

function cytpro_custom_assets() {
	wp_enqueue_style( 'wp-mediaelement' );
	wp_enqueue_style( 'cytpro-style', CYTPRO_PLUGIN_URL . '/styles/style.css' );
}

function cytpro_custom_scripts() {
    wp_enqueue_script('wp-mediaelement');
    wp_enqueue_script( 'cytpro-script', CYTPRO_PLUGIN_URL . '/scripts/script.js' );
}

function cytpro_admin_assets() {
	wp_enqueue_style( 'cytpro-admin-style', CYTPRO_PLUGIN_URL . '/styles/style.css' );
	wp_enqueue_script( 'cytpro-admin-script', CYTPRO_PLUGIN_URL . '/scripts/admin.js' );
	wp_localize_script( 'cytpro-admin-script', 'CYTPROPluginURL', array( 'url' => CYTPRO_PLUGIN_URL ) );
}
// --- End of Styles ---

// --- Custom Post Type ---
function cytpro_register_post_type_cytpro() {
	register_post_type ( 'cytpro_video', array (
		'labels' => array (
			'name' 					=> esc_html__( 'Video Players', 'cytpro_plugin' ),
			'all_items' 			=> esc_html__( 'Video Players', 'cytpro_plugin' ),
			'singular_name' 		=> esc_html__( 'Video', 'cytpro_plugin' ),
			'add_new_item'			=> __( 'Add New Video Player', 'cytpro_plugin' ),
			'edit_item'				=> __( 'Edit Video Player', 'cytpro_plugin' ),
			'new_item'				=> __( 'New Video Player', 'cytpro_plugin' ),
			'view_item'				=> __( 'View Video Player', 'cytpro_plugin' ),
			'search_items'			=> __( 'Search Video Player', 'cytpro_plugin' ),
			'not_found'				=> __( 'No Video Player found', 'cytpro_plugin' ),
			'not_found_in_trash'	=> __( 'No Video Player found in trash', 'cytpro_plugin' ),
			'menu_name'				=> __( 'WP Video Ace', 'cytpro_plugin' ),
			'parent_item_colon'		=> '',
		),
		'public' => true,
		'has_archive' => false,
		'exclude_from_search' => true,
		'rewrite' => array (
			'slug' => 'videos',
		),
		'supports' => array (
			'title',
			// 'custom-fields'
			// 'editor',
			// 'thumbnail',
		),
		'can_export' => true,
		'menu_icon'	=> 'dashicons-format-gallery'
	) );
}

add_action( 'init', 'cytpro_register_post_type_cytpro' );
// --- End of Custom Post Type ---

// Add Shortcode Column to Admin Section
add_filter('manage_cytpro_video_posts_columns', 'cytpro_add_column', 10);
add_action('manage_cytpro_video_posts_custom_column', 'cytpro_add_column_content', 10, 2);

// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN
function cytpro_add_column($defaults) {
	$defaults['shortcode'] = 'Shortcode';
	return $defaults;
}
function cytpro_add_column_content($column_name, $post_ID) {
	if ($column_name === 'shortcode') {
		echo "[cytpro_player name=\"" . get_the_title( $post_ID ) . "\"][/cytpro_player]";
	}

	return true;
}

// --- Shortcode ---

function cytpro_player_handler( $atts ) {
	$detect = new Mobile_Detect();

	$a = shortcode_atts( array(
			'name' => '',
	), $atts );
	$postname = $a['name'];

	$video = get_page_by_title( $postname, OBJECT, 'cytpro_video');

	$containerId = uniqid();
	$output = "";

	if ( empty( $video ) ) {
		$output = "<div class='cytpro-error'>No video</div>";
	}
	else {
		$enable_mobile	= get_post_meta( $video->ID, 'cytpro_enable_mobile', true ); // y | n

		if ( $enable_mobile == 'y' && $detect->isMobile() ) {
			$video_type		= get_post_meta( $video->ID, 'cytpro_m_video_type', true ); // type_media | type_external | type_youtube

			if ( $video_type == 'type_media') {
				$attach_id		= get_post_meta( $video->ID, 'cytpro_m_video_media', true );
				$video_url		= wp_get_attachment_url( $attach_id );
			} else if ( $video_type == 'type_external') {
				$video_url 		= get_post_meta( $video->ID, 'cytpro_m_video_external', true );
			} else if ( $video_type == 'type_youtube') {
				$video_url 		= get_post_meta( $video->ID, 'cytpro_m_video_url', true );
				$videoId 		= cytpro_youtube_id_from_url( $video_url );
			}

			$video_width 	= get_post_meta( $video->ID, 'cytpro_m_width', true );
			$video_w_unit 	= get_post_meta( $video->ID, 'cytpro_m_w_unit', true );
			$video_ratio 	= get_post_meta( $video->ID, 'cytpro_m_ratio', true );
			$video_quality 	= get_post_meta( $video->ID, 'cytpro_m_quality', true );
			$video_autoplay = get_post_meta( $video->ID, 'cytpro_m_autoplay', true );

			$video_showcontrol 	= get_post_meta( $video->ID, 'cytpro_m_showcontrol', true );
			$video_enableloop 	= get_post_meta( $video->ID, 'cytpro_m_enableloop', true );
			$video_showinfo 	= get_post_meta( $video->ID, 'cytpro_m_showinfo', true );

			$video_enablefs	= get_post_meta( $video->ID, 'cytpro_m_enablefs', true );
			$video_enablean	= get_post_meta( $video->ID, 'cytpro_m_enablean', true );
			$video_showlogo	= get_post_meta( $video->ID, 'cytpro_m_showlogo', true );
			$video_volume	= get_post_meta( $video->ID, 'cytpro_m_volume', true );

			$video_feature 	= get_post_meta( $video->ID, 'cytpro_m_feature', true ); // full | resize

			$video_start 	= get_post_meta( $video->ID, 'cytpro_m_start', true );
			$video_end 		= get_post_meta( $video->ID, 'cytpro_m_end', true );

			$video_resize		= get_post_meta( $video->ID, 'cytpro_m_resize_percent', true );
			$video_contentpos	= get_post_meta( $video->ID, 'cytpro_m_content_position', true );

			$video_showthumb 	= get_post_meta( $video->ID, 'cytpro_m_showthumb', true );
			$video_thumbcorner 	= get_post_meta( $video->ID, 'cytpro_m_thumbcorner', true );

			$video_frame 	= get_post_meta( $video->ID, 'cytpro_m_frame', true );
		}
		else {
			$video_type		= get_post_meta( $video->ID, 'cytpro_video_type', true ); // type_media | type_external | type_youtube

			if ( $video_type == 'type_media') {
				$attach_id		= get_post_meta( $video->ID, 'cytpro_video_media', true );
				$video_url		= wp_get_attachment_url( $attach_id );
			} else if ( $video_type == 'type_external') {
				$video_url 		= get_post_meta( $video->ID, 'cytpro_video_external', true );
			} else if ( $video_type == 'type_youtube') {
				$video_url 		= get_post_meta( $video->ID, 'cytpro_video_url', true );
				$videoId 		= cytpro_youtube_id_from_url( $video_url );
			}

			$video_width 	= get_post_meta( $video->ID, 'cytpro_width', true );
			$video_w_unit 	= get_post_meta( $video->ID, 'cytpro_w_unit', true );
			$video_ratio 	= get_post_meta( $video->ID, 'cytpro_ratio', true );
			$video_quality 	= get_post_meta( $video->ID, 'cytpro_quality', true );
			$video_autoplay = get_post_meta( $video->ID, 'cytpro_autoplay', true );

			$video_showcontrol 	= get_post_meta( $video->ID, 'cytpro_showcontrol', true );
			$video_enableloop 	= get_post_meta( $video->ID, 'cytpro_enableloop', true );
			$video_showinfo 	= get_post_meta( $video->ID, 'cytpro_showinfo', true );

			$video_enablefs	= get_post_meta( $video->ID, 'cytpro_enablefs', true );
			$video_enablean	= get_post_meta( $video->ID, 'cytpro_enablean', true );
			$video_showlogo	= get_post_meta( $video->ID, 'cytpro_showlogo', true );
			$video_volume	= get_post_meta( $video->ID, 'cytpro_volume', true );

			$video_feature 	= get_post_meta( $video->ID, 'cytpro_feature', true ); // full | resize

			$video_start 	= get_post_meta( $video->ID, 'cytpro_start', true );
			$video_end 		= get_post_meta( $video->ID, 'cytpro_end', true );

			$video_resize		= get_post_meta( $video->ID, 'cytpro_resize_percent', true );
			$video_contentpos	= get_post_meta( $video->ID, 'cytpro_content_position', true );

			$video_showthumb 	= get_post_meta( $video->ID, 'cytpro_showthumb', true );
			$video_thumbcorner 	= get_post_meta( $video->ID, 'cytpro_thumbcorner', true );

			$video_frame 	= get_post_meta( $video->ID, 'cytpro_frame', true );
		}

		$video_desc = get_post_meta( $video->ID, 'cytpro_lockedcontent', true );
		$video_desc = apply_filters('the_content', $video_desc);
		$video_desc = str_replace(']]>', ']]&gt;', $video_desc);

		$videoWUnit = ($video_w_unit == 'percent') ? "%" : "px";

		if ( $video_ratio == 's' ) {
			$videoHeightClass = "cytpro-ratio-square";
		} else if ( $video_ratio == '3' ) {
			$videoHeightClass = "cytpro-ratio-43";
		} else if ( $video_ratio == '9' ) {
			$videoHeightClass = "cytpro-ratio-169";
		}

		$output = "<div class='cytpro-outer-container {$videoHeightClass}' style='width: {$video_width}{$videoWUnit};' 
			data-vtype='{$video_type}' 
			data-vfeature='{$video_feature}' 
			data-pid='{$video->ID}'
			data-vid='{$videoId}'
			data-cid='{$containerId}' 
			data-start='{$video_start}' 
			data-end='{$video_end}' 
			data-autoplay='{$video_autoplay}'
			data-showcontrol='{$video_showcontrol}' 
			data-enableloop='{$video_enableloop}' 
			data-showinfo='{$video_showinfo}'
			data-enablefs='{$video_enablefs}'
			data-enablean='{$video_enablean}'
			data-showlogo='{$video_showlogo}'
			data-volume='{$video_volume}'
			data-quality='{$video_quality}' 
			data-resize='{$video_resize}' 
			data-thumb='{$video_showthumb}'>
		<div class='cytpro-thumb-container {$video_thumbcorner} cytpro-pos-{$video_contentpos}'>
			<div class='cytpro-container'><div class='{$videoHeightClass}'><div class='cytpro-inner-container'>";

		if ( $video_feature == 'resize' && ( $video_contentpos == 'top' || $video_contentpos == 'left' ) ) {
		    $output .= "<div class='cytpro-content'><div class='cytpro-vcenter'>" . $video_desc . "</div></div>";
		}

		$output .= "<div class='cytpro-frame' style='background-image: url(\"".CYTPRO_PLUGIN_URL."/images/frames/{$video_frame}.png\");'><div class='cytpro-frame-inner cytpro-frame-{$video_frame}'>";
		if ( $video_type == 'type_youtube') {
			$output .= "<div id='cytpro-{$containerId}'></div>";
		}
		else {
			$output .= "<div class='cytpro-ml-video'>";
			$output .= "<video id='cytpro-ml-{$containerId}' width='640' height='390' preload='metadata' controls='controls'>"
						. "<source type='video/mp4' src='{$video_url}' />"
						. "<a href='{$video_url}'>"
							. $video_url
						. "</a>"
					. "</video>";
			$output .= "</div>";
		}
		$output .= "</div></div>";

		if ( $video_feature == 'resize' && ( $video_contentpos == 'bottom' || $video_contentpos == 'right' ) ) {
		    $output .= "<div class='cytpro-content'><div class='cytpro-vcenter'>" . $video_desc . "</div></div>";
		}

		$output .= "</div></div></div></div>
	<div class='cytpro-dummy-placeholder {$videoHeightClass}'></div>
</div>";
	}

	return $output;
}

function cytpro_locked_content_handler($atts) {
	$a = shortcode_atts( array(
			'name' => '',
	), $atts );
	$postname = $a['name'];

	$video = get_page_by_title( $postname, OBJECT, 'cytpro_video');
	$video_desc = get_post_meta( $video->ID, 'cytpro_lockedcontent', true );
	$video_desc = apply_filters('the_content', $video_desc);
	$video_desc = str_replace(']]>', ']]&gt;', $video_desc);
	
	$output = "<div class='cytpro-locked-content' data-pid='{$video->ID}'>";
		$output .= $video_desc;
	$output .= "</div>";

	return $output;
}

add_shortcode( 'cytpro_player', 'cytpro_player_handler' );
add_shortcode( 'cytpro_locked_content', 'cytpro_locked_content_handler' );
// --- End of Shortcode ---

// --- Meta Box ---
function cytpro_meta_boxes( $meta_boxes ) {
	$frameOptions = array( 
			'1'		=> __( '1',		'cytpro_plugin' ),
			'2'		=> __( '2',		'cytpro_plugin' ),
			'3'		=> __( '3',		'cytpro_plugin' ),
			'4'		=> __( '4',		'cytpro_plugin' ),
			'5'		=> __( '5',		'cytpro_plugin' ),
			'6'		=> __( '6',		'cytpro_plugin' ),
			'7'		=> __( '7',		'cytpro_plugin' ),
			'8'		=> __( '8',		'cytpro_plugin' ),
			'9'		=> __( '9',		'cytpro_plugin' ),
			'10'	=> __( '10',	'cytpro_plugin' ),
			'11'	=> __( '11',	'cytpro_plugin' ),
			'12'	=> __( '12',	'cytpro_plugin' ),
			'13'	=> __( '13',	'cytpro_plugin' ),
			'14'	=> __( '14',	'cytpro_plugin' ),
			'15'	=> __( '15',	'cytpro_plugin' ),
			'16'	=> __( '16',	'cytpro_plugin' ),
			'17'	=> __( '17',	'cytpro_plugin' ),
			'18'	=> __( '18',	'cytpro_plugin' ),
			'19'	=> __( '19',	'cytpro_plugin' ),
			'20'	=> __( '20',	'cytpro_plugin' ),
			'21'	=> __( '21',	'cytpro_plugin' ),
			'22'	=> __( '22',	'cytpro_plugin' ),
			'23'	=> __( '23',	'cytpro_plugin' ),
			'24'	=> __( '24',	'cytpro_plugin' ),
			'25'	=> __( '25',	'cytpro_plugin' ),
			'26'	=> __( '26',	'cytpro_plugin' ),
			'27'	=> __( '27',	'cytpro_plugin' ),
			'28'	=> __( '28',	'cytpro_plugin' )
	);
	
	$meta_boxes[] = array(
			'id'			=> 'meta-video-options',
			'title'			=> __( 'Video Options', 'cytpro_plugin' ),
			'post_types' 	=> 'cytpro_video',
			'fields'		=> array(
						array(
								'name' 			=> __( 'Video Type', 'cytpro_plugin' ),
								'id'			=> "cytpro_video_type",
								'type' 			=> 'radio',
								'options' 		=> array(
										'type_media' 	=> __( 'WP Media', 		'cytpro_plugin' ),
										'type_external' => __( 'External URL', 	'cytpro_plugin' ),
										'type_youtube' 	=> __( 'Youtube Video', 'cytpro_plugin' ),
								),
								'std'			=> 'type_media'
						),
						array(
								'name' 			=> __( 'Select Video On WP Media', 'cytpro_plugin' ),
								'id'	 		=> "cytpro_video_media",
								'type' 			=> 'file_advanced',
								'max_file_uploads'	=> 1,
								'visible' 		=> array(
									'cytpro_video_type', '=', 'type_media'
								)
						),
						array(
								'name' 			=> __( 'External Video URL', 'cytpro_plugin' ),
								'id'	 		=> "cytpro_video_external",
								'type' 			=> 'url',
								'visible' 		=> array(
										'cytpro_video_type', '=', 'type_external'
								)
						),
						array(
								'name' 			=> __( 'Youtube Video URL', 'cytpro_plugin' ),
								'id'	 		=> "cytpro_video_url",
								'type' 			=> 'oembed',
								'desc' 			=> __( 'Only youtube allowed', 'cytpro_plugin' ),
								'visible' 		=> array(
										'cytpro_video_type', '=', 'type_youtube'
								)
						),
						array(
								'id'	 		=> 'cytpro_width',
								'name' 			=> __( 'Width', 'cytpro_plugin' ),
								'type' 			=> 'number',
						),
						array(
								'id'			=> 'cytpro_w_unit',
								'name'			=> __( 'Width Unit', 'cytpro_plugin' ),
								'type'			=> 'radio',
								'options' 		=> array(
										'percent' 	=> __( 'Percent', 'cytpro_plugin' ),
										'pixel' 	=> __( 'Pixel', 'cytpro_plugin' ),
								),
								'std'			=> 'percent'
						),
						array(
								'id'			=> 'cytpro_ratio',
								'name'			=> __( 'Aspect Ratio', 'cytpro_plugin' ),
								'type'			=> 'radio',
								'options' 		=> array(
										's' 		=> __( 'Square', 'cytpro_plugin' ),
										'3' 		=> __( '4:3', 'cytpro_plugin' ),
										'9' 		=> __( '16:9', 'cytpro_plugin' ),
								),
								'std'			=> '9'
						),
						array(
								'id'			=> 'cytpro_quality',
								'name'			=> __( 'Video Quality', 'cytpro_plugin' ),
								'type'			=> 'radio',
								'options' 		=> array(
										'240' 		=> __( 'Small', 'cytpro_plugin' ),
										'360' 		=> __( 'Medium', 'cytpro_plugin' ),
										'480' 		=> __( 'Large', 'cytpro_plugin' ),
										'hd720' 	=> __( 'HD720', 'cytpro_plugin' ),
										'hd1080' 	=> __( 'HD1080', 'cytpro_plugin' ),
								),
								'std'			=> '360',
								'visible' 		=> array(
										'cytpro_video_type', '=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_autoplay',
								'name'		=> __( 'Auto Play', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytpro_plugin' ),
										'n' 		=> __( 'No', 'cytpro_plugin' ),
								),
								'std'		=> 'n'
						),
						array(
								'id'		=> 'cytpro_showcontrol',
								'name'		=> __( 'Show Control', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y'
						),
						array(
								'id'		=> 'cytpro_enableloop',
								'name'		=> __( 'Enable Loop', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'n'
						),
						array(
								'id'		=> 'cytpro_showinfo',
								'name'		=> __( 'Show Information', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'hidden' 	=> array(
										'cytpro_video_type', '!=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_enablefs',
								'name'		=> __( 'Enable Fullscreen', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y'
						),
						array(
								'id'		=> 'cytpro_enablean',
								'name'		=> __( 'Show Annotations', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'hidden' 	=> array(
										'cytpro_video_type', '!=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_showlogo',
								'name'		=> __( 'Show Youtube Logo', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'hidden' 	=> array(
										'cytpro_video_type', '!=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_feature',
								'name'		=> __( 'Video Feature', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' => array(
										'none' 		=> __( 'None', 'cytpro_plugin' ),
										'full' 		=> __( 'Full Screen Video', 'cytpro_plugin' ),
										'resize' 	=> __( 'Resize Video with Contents', 'cytpro_plugin' ),
										'locked' 	=> __( 'Locked Content in Shortcode', 'cytpro_plugin' ),
								),
								'std'		=> 'none'
						),
						array(
								'id'	 	=> 'cytpro_start',
								'name' 		=> __( 'Start Featuring In Seconds', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'visible' 		=> array(
										'cytpro_feature', '!=', 'none'
								)
						),
						array(
								'id'	 	=> 'cytpro_end',
								'name' 		=> __( 'End Featuring In Seconds', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'visible' 		=> array(
										'cytpro_feature', '!=', 'none'
								)
						),
						array(
								'id'	 	=> 'cytpro_volume',
								'name' 		=> __( 'Volume', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'min'		=> 0,
								'max'		=> 10,
								'std'		=> 10,
								'visible' 		=> array(
										'cytpro_feature', '!=', 'none'
								)
						),
						array(
								'id'	 	=> 'cytpro_resize_percent',
								'name' 		=> __( 'Resize Percentage', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'min'		=> 0,
								'max'		=> 100,
								'visible' 		=> array(
									'cytpro_feature', '=', 'resize'
								)
						),
						array(
								'id'	 	=> 'cytpro_content_position',
								'name' 		=> __( 'Content Position When Resizing Video', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'top' 		=> __( 'Top', 	'cytpro_plugin' ),
										'left' 		=> __( 'Left', 	'cytpro_plugin' ),
										'right' 	=> __( 'Right', 'cytpro_plugin' ),
										'bottom'	=> __( 'bottom','cytpro_plugin' ),
								),
								'visible' 		=> array(
										'cytpro_feature', '=', 'resize'
								)
						),
						array(
								'id'		=> 'cytpro_showthumb',
								'name'		=> __( 'Show Thumbnail On Scroll', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' => __( 'Yes', 'cytpro_plugin' ),
										'n' => __( 'No', 'cytpro_plugin' ),
								),
								'std'		=> 'n'
						),
						array(
								'id'		=> 'cytpro_thumbcorner',
								'name'		=> __( 'Thumbnail Position', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'bottom-right' 	=> __( 'Bottom Right', 'cytpro_plugin' ),
										'top-right' 	=> __( 'Top Right', 'cytpro_plugin' ),
										'bottom-left' 	=> __( 'Bottom Left', 'cytpro_plugin' ),
										'top-left' 		=> __( 'Top Left', 'cytpro_plugin' ),
								),
								'std'		=> 'bottom-right',
								'visible' 	=> array(
										'cytpro_showthumb', '=', 'y'
								)
						),
						array(
								'id'		=> 'cytpro_frame',
								'name'		=> __( 'Video Frame', 'cytpro_plugin' ),
								'type'		=> 'select',
								'options' 	=> $frameOptions,
								'std'		=> '',
						),
			),
			'validation'	=> array(
					'rules'		=> array(
							'cytpro_video_media'	=> array(
									'required'		=> true,
							),
							'cytpro_video_external'	=> array(
									'required'		=> true,
							),
							'cytpro_video_url'	=> array(
									'required'		=> true,
							),
							'cytpro_width'	=> array(
									'required'		=> true,
									'number'		=> true
							),
							'cytpro_resize_percent'	=> array(
									'required'		=> true,
									'number'		=> true,
									'max'			=> 100,
									'min'			=> 1
							),
							'cytpro_start'	=> array(
									'required'		=> true,
									'number'		=> true
							),
							'cytpro_end'		=> array(
									'required'		=> true,
									'number'		=> true
							)
					)
			)
	);
	
	$meta_boxes[] = array(
			'id'			=> 'meta-video-mobile-options',
			'title'			=> __( 'Video Options for Mobile', 'cytpro_plugin' ),
			'post_types' 	=> 'cytpro_video',
			'fields'		=> array(
						array(
								'name' 			=> __( 'Enable Different Option for Mobile Device', 'cytpro_plugin' ),
								'id'			=> "cytpro_enable_mobile",
								'type' 			=> 'radio',
								'options' 		=> array(
										'n'			=> __( 'No', 	'cytpro_plugin' ),
										'y' 		=> __( 'Yes', 		'cytpro_plugin' ),
								),
								'std'			=> 'n'
						),
						array(
								'name' 			=> __( 'Video Type', 'cytpro_plugin' ),
								'id'			=> "cytpro_m_video_type",
								'type' 			=> 'radio',
								'options' 		=> array(
										'type_media' 	=> __( 'WP Media', 		'cytpro_plugin' ),
										'type_external' => __( 'External URL', 	'cytpro_plugin' ),
										'type_youtube' 	=> __( 'Youtube Video', 'cytpro_plugin' ),
								),
								'std'			=> 'type_media',
								'visible' 		=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'name' 			=> __( 'Select Video On WP Media', 'cytpro_plugin' ),
								'id'	 		=> "cytpro_m_video_media",
								'type' 			=> 'file_advanced',
								'max_file_uploads'	=> 1,
								'visible' 		=> array(
									array( 'cytpro_enable_mobile', '=', 'y' ),
									array( 'cytpro_m_video_type', '=', 'type_media' )
								)
						),
						array(
								'name' 			=> __( 'External Video URL', 'cytpro_plugin' ),
								'id'	 		=> "cytpro_m_video_external",
								'type' 			=> 'url',
								'visible' 		=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_video_type', '=', 'type_external' )
								)
						),
						array(
								'name' 			=> __( 'Youtube Video URL', 'cytpro_plugin' ),
								'id'	 		=> "cytpro_m_video_url",
								'type' 			=> 'oembed',
								'desc' 			=> __( 'Only youtube allowed', 'cytpro_plugin' ),
								'visible' 		=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_video_type', '=', 'type_youtube' )
								)
						),
						array(
								'id'	 		=> 'cytpro_m_width',
								'name' 			=> __( 'Width', 'cytpro_plugin' ),
								'type' 			=> 'number',
								'visible' 		=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'			=> 'cytpro_m_w_unit',
								'name'			=> __( 'Width Unit', 'cytpro_plugin' ),
								'type'			=> 'radio',
								'options' 		=> array(
										'percent' 	=> __( 'Percent', 'cytpro_plugin' ),
										'pixel' 	=> __( 'Pixel', 'cytpro_plugin' ),
								),
								'std'			=> 'percent',
								'visible' 		=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'			=> 'cytpro_m_ratio',
								'name'			=> __( 'Aspect Ratio', 'cytpro_plugin' ),
								'type'			=> 'radio',
								'options' 		=> array(
										's' 		=> __( 'Square', 'cytpro_plugin' ),
										'3' 		=> __( '4:3', 'cytpro_plugin' ),
										'9' 		=> __( '16:9', 'cytpro_plugin' ),
								),
								'std'			=> '9',
								'visible' 		=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'			=> 'cytpro_m_quality',
								'name'			=> __( 'Video Quality', 'cytpro_plugin' ),
								'type'			=> 'radio',
								'options' 		=> array(
										'240' 		=> __( 'Small', 'cytpro_plugin' ),
										'360' 		=> __( 'Medium', 'cytpro_plugin' ),
										'480' 		=> __( 'Large', 'cytpro_plugin' ),
										'hd720' 	=> __( 'HD720', 'cytpro_plugin' ),
										'hd1080' 	=> __( 'HD1080', 'cytpro_plugin' ),
								),
								'std'			=> '360',
								'visible' 		=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_video_type', '=', 'type_youtube' )
								)
						),
						array(
								'id'		=> 'cytpro_m_autoplay',
								'name'		=> __( 'Auto Play', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytpro_plugin' ),
										'n' 		=> __( 'No', 'cytpro_plugin' ),
								),
								'std'		=> 'n',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'		=> 'cytpro_m_showcontrol',
								'name'		=> __( 'Show Control', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'		=> 'cytpro_m_enableloop',
								'name'		=> __( 'Enable Loop', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'n',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'		=> 'cytpro_m_showinfo',
								'name'		=> __( 'Show Information', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								),
								'hidden' 	=> array(
										'cytpro_m_video_type', '!=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_m_enablefs',
								'name'		=> __( 'Enable Fullscreen', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'		=> 'cytpro_m_enablean',
								'name'		=> __( 'Show Annotations', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								),
								'hidden' 	=> array(
										'cytpro_m_video_type', '!=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_m_showlogo',
								'name'		=> __( 'Show Youtube Logo', 'cytp_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' 		=> __( 'Yes', 'cytp_plugin' ),
										'n' 		=> __( 'No', 'cytp_plugin' ),
								),
								'std'		=> 'y',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								),
								'hidden' 	=> array(
										'cytpro_m_video_type', '!=', 'type_youtube'
								)
						),
						array(
								'id'		=> 'cytpro_m_feature',
								'name'		=> __( 'Video Feature', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'none' 		=> __( 'None', 'cytpro_plugin' ),
										'full' 		=> __( 'Full Screen Video', 'cytpro_plugin' ),
										'resize' 	=> __( 'Resize Video with Contents', 'cytpro_plugin' ),
										'locked' 	=> __( 'Locked Content in Shortcode', 'cytpro_plugin' ),
								),
								'std'		=> 'none',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'	 	=> 'cytpro_m_start',
								'name' 		=> __( 'Start Featuring In Seconds', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_feature', '!=', 'none' )
								)
								
						),
						array(
								'id'	 	=> 'cytpro_m_end',
								'name' 		=> __( 'End Featuring In Seconds', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_feature', '!=', 'none' )
								)
						),
						array(
								'id'	 	=> 'cytpro_m_volume',
								'name' 		=> __( 'Volume', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'min'		=> 0,
								'max'		=> 10,
								'std'		=> 10,
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_feature', '!=', 'none' )
								)
						),
						array(
								'id'	 	=> 'cytpro_m_resize_percent',
								'name' 		=> __( 'Resize Percentage', 'cytpro_plugin' ),
								'type' 		=> 'number',
								'min'		=> 0,
								'max'		=> 100,
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_feature', '=', 'resize' )
								)
						),
						array(
								'id'	 	=> 'cytpro_m_content_position',
								'name' 		=> __( 'Content Position When Resizing Video', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'top' 		=> __( 'Top', 	'cytpro_plugin' ),
										'left' 		=> __( 'Left', 	'cytpro_plugin' ),
										'right' 	=> __( 'Right', 'cytpro_plugin' ),
										'bottom'	=> __( 'bottom','cytpro_plugin' ),
								),
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_feature', '=', 'resize' )
								)
						),
						array(
								'id'		=> 'cytpro_m_showthumb',
								'name'		=> __( 'Show Thumbnail On Scroll', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'y' => __( 'Yes', 'cytpro_plugin' ),
										'n' => __( 'No', 'cytpro_plugin' ),
								),
								'std'		=> 'n',
								'visible' 	=> array(
										'cytpro_enable_mobile', '=', 'y'
								)
						),
						array(
								'id'		=> 'cytpro_m_thumbcorner',
								'name'		=> __( 'Thumbnail Position', 'cytpro_plugin' ),
								'type'		=> 'radio',
								'options' 	=> array(
										'bottom-right' 	=> __( 'Bottom Right', 'cytpro_plugin' ),
										'top-right' 	=> __( 'Top Right', 'cytpro_plugin' ),
										'bottom-left' 	=> __( 'Bottom Left', 'cytpro_plugin' ),
										'top-left' 		=> __( 'Top Left', 'cytpro_plugin' ),
								),
								'std'		=> 'bottom-right',
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' ),
										array( 'cytpro_m_showthumb', '=', 'y' )
								)
						),
						array(
								'id'		=> 'cytpro_m_frame',
								'name'		=> __( 'Video Frame', 'cytpro_plugin' ),
								'type'		=> 'select',
								'options' 	=> $frameOptions,
								'std'		=> '',
								'visible' 	=> array(
										array( 'cytpro_enable_mobile', '=', 'y' )
								)
						),
			),
			'validation'	=> array(
					'rules'		=> array(
							'cytpro_m_video_media'	=> array(
									'required'		=> true,
							),
							'cytpro_m_video_external'=> array(
									'required'		=> true,
							),
							'cytpro_m_video_url'	=> array(
									'required'		=> true,
							),
							'cytpro_m_width'		=> array(
									'required'		=> true,
									'number'		=> true
							),
							'cytpro_m_resize_percent'	=> array(
									'required'		=> true,
									'number'		=> true,
									'max'			=> 100,
									'min'			=> 1
							),
							'cytpro_m_start'		=> array(
									'required'		=> true,
									'number'		=> true
							),
							'cytpro_m_end'			=> array(
									'required'		=> true,
									'number'		=> true
							)
					)
			)
	);
	
	$meta_boxes[] = array(
			'id'			=> 'meta-locked-content',
			'title'			=> __( 'Locked Content Options', 'cytpro_plugin' ),
			'post_types' 	=> 'cytpro_video',
			'fields'		=> array (
					array (
							'id'		=> 'cytpro_lockedcontent',
							'name'		=> __( 'Locked Content', 'cytpro_plugin' ),
							'type'		=> 'wysiwyg',
					)
			)
	);

	return $meta_boxes;
}

add_filter( 'rwmb_meta_boxes', 'cytpro_meta_boxes' );

function cytpro_gen_shortcode_video_options() {
	global $post; $customHtml = '';

	if ( $post && $post->post_status != 'auto-draft' ) {
		$title = $post->post_title;
		$customHtml = '<div class="postbox" style="background: #F0F0F0; color: #000; margin-top: 20px;"><div class="inside">';
		$customHtml .= "[cytpro_player name=\"{$title}\"][/cytpro_player]";
		$customHtml .= '</div></div>';
	}
	
	echo $customHtml;
}

function cytpro_gen_shortcode_locked_content() {
	global $post; $customHtml = '';
	if ( $post && $post->post_status != 'auto-draft' ) {
		$title = $post->post_title;
		$customHtml = '<div class="postbox" style="background: #F0F0F0; color: #000; margin-top: 20px;"><div class="inside">';
		$customHtml .= "[cytpro_locked_content name=\"{$title}\"][/cytpro_locked_content]";
		$customHtml .= '</div></div>';
	}
	
	echo $customHtml;
}

add_action('rwmb_before_meta-video-options', 'cytpro_gen_shortcode_video_options' );
add_action('rwmb_before_meta-locked-content', 'cytpro_gen_shortcode_locked_content' );
// --- End of Metabox ---

// --- Logo ---
function cytpro_add_plugin_logo() {
	global $my_admin_page;
	$screen = get_current_screen();

	if ( is_admin() ) {
		if ( strpos( $screen->id, 'cytpro_video') > -1 ) {
			echo '<img src="' . CYTPRO_PLUGIN_URL . '/images/logo-300.png" style="margin-top: 10px;"/>';
		}
	}
}
add_action( 'admin_notices', 'cytpro_add_plugin_logo' );
// --- End of Logo ---

// --- Helper functions ---
function cytpro_youtube_id_from_url($url) {
	$pattern =
	'%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
			;
			$result = preg_match($pattern, $url, $matches);
			if ($result) {
				return $matches[1];
			}
			return false;
}