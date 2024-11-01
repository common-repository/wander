<?php

// This file handles the shortcode setup, and initialization of the WanderRenderer class.
	
function wander_shortcode_gmap( $atts, $content = null ) {
    // Creates the shortcode for displaying the Google Maps map (deprecated)
    
    include_once( plugin_dir_path( __FILE__ ) . "wanderRenderer.php");
    
    // Set attributes and defaults
    $atts = shortcode_atts(
		array(
            'height' => 600,
            'width' => 600,
		), 
		$atts,
		'wander-gmap'
    );
        
    try {
	    $render = new WanderRenderer();
    	return $render->getWanderGMap($atts, $content);		
	}
	catch (Exception $e) {
		return "<strong>" . esc_html('Wander Plugin Error: ' . $e->getMessage()) . "</strong>";
	}
}

function wander_shortcode_default_map( $atts, $content = null ) {
    // Creates the shortcode for displaying the default map
    
    include_once( plugin_dir_path( __FILE__ ) . "wanderRenderer.php");
    
    // Set attributes and defaults
    $atts = shortcode_atts(
		array(
            'height' => '600px', // style overrides
            'width' => '100%',
            'livedcolor' => false, // color overrides
            'visitedcolor' => false,
            'nextcolor' => false,
            'defaultcolor' => false,
            'bordercolor' => false,
            'hovercolor' => false,
            'hoverbordercolor' => false,
            'showlived' => true, // shows the 'lived' category of countries
            'showvisited' => true, // shows the 'visited' category of countries
            'shownext' => true, // shows the 'next' category of countries
            'disablelabels' => false, // disables labels entirely
            'livedmessage' => false, // Allows override of hovering messages
            'visitedmessage' => false,
            'nextmessage' => false,
            'showalllabels' => false, // shows labels on countries not specified
            
		), 
		$atts,
		'wander-map'
    );	
	
	try {
		$render = new WanderRenderer();
    	return $render->getWanderMap($atts, $content);		
		}
	catch (Exception $e) {
		return "<strong>" . esc_html('Wander Plugin Error: ' . $e->getMessage()) . "</strong>";
	}
	
	
}

function wander_shortcode_countriesVisitedList( $atts, $content = null ) {
	//This shortcode prints a ul list of all countries the user has visited, and the option to reverse the list
	
	include_once( plugin_dir_path( __FILE__ ) . "wanderRenderer.php");
    
    // Set attributes and defaults
    $atts = shortcode_atts(
		array(
			'type' => 'visited',
            'reverse' => false,
            'number' => false,
            'links' => true,
            'flags' => true,
		), 
		$atts,
		'wander-list'
    );
    
    try {
		$render = new WanderRenderer();
    	return $render->getWanderList($atts, $content);		
		}
	catch (Exception $e) {
		return "<strong>" . esc_html('Wander Plugin Error: ' . $e->getMessage()) . "</strong>";
	}
}

function wander_shortcode_currentLocation( $atts, $content = null ) {
	//This shortcode creates a text, or link value of the current city and/or country of the user; all values are customizable
    
    include_once( plugin_dir_path( __FILE__ ) . "wanderRenderer.php");
        
    // Set attributes and defaults
    $atts = shortcode_atts(
		array(
            'link' => false,
            'city' => 'true',
            'country' => 'true'
		), 
		$atts,
		'wander-location'
    );    
    
    try {
    	$render = new WanderRenderer();
    	return $render->getWanderCurrentLocation($atts, $content);
    }
    catch (Exception $e) { // If there is an error, return the error
	    return "<strong>" . esc_html('Wander Plugin Error: ' . $e->getMessage()) . "</strong>";
    }	
    	
    	
}