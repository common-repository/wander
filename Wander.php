<?php
/*
Plugin Name: Wander
Plugin URI: http:/ryderdamen.com/projects/wander
Description: A plugin for travel blogs to display a map and list of countries visited, as well as a current location.
Version: 1.0
Author: Ryder Damen
Author URI: https://ryderdamen.com/
*/

// Actions
include( plugin_dir_path( __FILE__ ) . "lib/menu.php"); // Include the Options Menu
add_action( 'wp_enqueue_scripts', 'enqueueWanderScriptsAndStyles', PHP_INT_MAX );
add_action( 'wp_head', 'wander_hookHeader' );
add_action( 'admin_enqueue_scripts', 'countriesVisitedEnqueueAdminStylesAndScripts' );

// Shortcodes
include_once( plugin_dir_path( __FILE__ ) . "lib/shortcodes.php"); // Include Shortcodes
add_shortcode( 'wander-gmap', 'wander_shortcode_gmap' );
add_shortcode( 'wander-map', 'wander_shortcode_default_map' );

add_shortcode( 'wander-list', 'wander_shortcode_countriesVisitedList' );
add_shortcode( 'wander-location', 'wander_shortcode_currentLocation' );


// Methods
function enqueueWanderScriptsAndStyles() {
    // Enqueue the CSS and Javascript for WP
	wp_enqueue_style( 'WanderPlugin_Main_Style', plugins_url( '/css/main.css', __FILE__ ), false, false, 'all' );
    
    // Main Forward Facing JS File
    wp_enqueue_script('WanderPlugin_JavaScript', plugins_url( '/js/main.js', __FILE__ ), array('jquery'), true);
    
    // The main.js file requires options variables to operate, this code provides it to them
    // TODO, migrate to wanderRenderer class (since this is google specific for the most part)
    $options = get_option( 'wander_option' );    
	$wanderOptions = array('options' => $options); 
	wp_localize_script( 'WanderPlugin_JavaScript', 'countriesVisitedOptions', $wanderOptions['options'] );
    
}

function countriesVisitedEnqueueAdminStylesAndScripts() {
	// Handles Admin Styles and Scripts
	wp_enqueue_style( 'WanderPlugin_Admin_Style', plugins_url( '/css/admin.css', __FILE__ ), false, false, 'all' );
	
	// WP Color Picker
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script('WanderPlugin_Admin_JS', plugins_url( '/js/admin.js', __FILE__ ), array('jquery', 'wp-color-picker'), true, true);
	wp_enqueue_script('Wander_GooglePlaces', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBuU_0_uLMnFM-2oWod_fzC0atPZj7dHlU&libraries=places&callback=initAutocomplete', false, false, true); // TODO, replace API key
}

function wander_hookHeader() {
	// Provides a promotional comment in the site's header code
	$attribution = "This site uses the Wander plugin: Visit https://ryderdamen.com/projects/wander for more information.";
	echo "<!-- " . esc_html($attribution) . " -->";
}


