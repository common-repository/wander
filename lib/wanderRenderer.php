<?php
	
	// Wander Renderer
	// Author: Ryder Damen
	// Version: 1.0 
	// Description: This class retrieves options and renders them into HTML for the various shortcode options 
	
 
class WanderRenderer {
	
	// Global Variables ----------------------------------------------------------------------------------------------------------------
	public $option = null;
	private $isoConversionArray = null;
	
	// Constructor --------------------------------------------------------------------------------------------------------------------
	public function __construct() {
		// Retrieve the option
		$this->option = get_option( 'wander_option' );
		// Retrieve the ISO conversion array
		$this->isoConversionArray = json_decode(file_get_contents( plugin_dir_path( __FILE__ ) . "isoConversion.json"), true);
	}
	
	// Deconstructor ---------------------------------------------------------------------------------------------------------------
	public function __destruct() {
		// Destructor for the class
	}
	  
	// Other Methods ---------------------------------------------------------------------------------------------------------------
	private function convertIso2toIso3($input) {
		return $this->isoConversionArray[$input];
	}
	
	
	  
	// Public Return Methods --------------------------------------------------------------------------------------------------------
	public function getWanderMap($atts, $content) {
		
		// This function returns a map of the world, and populates it with countries of the user's choosing
		
		// First, append additional libraries to the head of the document
		// WordPress will prevent the same script being enqueued multiple times
		wp_enqueue_script( 'wanderMap-d3', '//cdnjs.cloudflare.com/ajax/libs/d3/3.5.3/d3.min.js',  array(), null, false);
		wp_enqueue_script( 'wanderMap-topojson', '//cdnjs.cloudflare.com/ajax/libs/topojson/1.6.9/topojson.min.js',  array(), null, false);
		
		// Next, include the local worldMap.js Script
		wp_enqueue_script( 'wanderMap-worldMap', plugin_dir_url( __FILE__ ) .'worldMap.js', array(), null, false);
		
		// Set map colour logic				
		// Handles shortcode map colour overrides; if the shortcode has been specified, use it's colour, else get the option default
		$livedColor = $atts['livedcolor'] ? $atts['livedcolor'] : $this->option['livedColor'];
		$visitedColor = $atts['visitedcolor'] ? $atts['visitedcolor'] : $this->option['visitedColor'];
		$nextColor = $atts['nextcolor'] ? $atts['nextcolor'] : $this->option['nextColor'];
		$defaultFillHex = $atts['defaultcolor'] ? $atts['defaultcolor'] : '#d3d3d3';
		$borderColor = $atts['bordercolor'] ? $atts['bordercolor'] : '#d3d3d3';
		$hoverColor = $atts['hovercolor'] ? $atts['hovercolor'] : '#c6c6c6';
		$highlightedBorderColor = $atts['hoverbordercolor'] ? $atts['hoverbordercolor'] : '#d3d3d3';
		
		// Allows a user to override the message that appears when someone hovers over a certain country
		$livedMessage =  $atts['livedmessage'] ? $atts['livedmessage'] : "(Lived)";
		$visitedMessage = $atts['visitedmessage'] ? $atts['visitedmessage'] : "(Visited)";
		$nextMessage = $atts['nextmessage'] ? $atts['nextmessage'] : "(Up Next)";
		
		// Retrieve countries lived in, visited, and ones up next
		$countriesLived = $this->option['countriesLived'];
		$countriesVisited = $this->option['countriesVisited'];
		$countriesNext = $this->option['countriesNext'];
		
		// Pick a random number for the map ID
		$mapId = rand(0, 9999);
				
		// Render the map
		// Print the div for the map
		$print = '<div id="wander-map-' . $mapId . '" style="position: relative; width: ' 
			. $atts['width'] . '; height: ' . $atts['height'] . ';"></div>';		
				
		// Construct and Append the JavaScript to create the map
		$print.= "	<script>
						$( document ).ready(function() {
							var map = new Datamap({
								element: document.getElementById('wander-map-" . $mapId . "'),
								projection: 'mercator', // User the mercator projection, because it's better looking
								responsive: true, // this responsiveness is sketchy at best
								done: function(datamap) {
									
									// Upon map completion, implement an onClick for countries clicked (if clickable)
							        datamap.svg.selectAll('.datamaps-subunit').on('click', function(geography) {
								        
								        // When the map is clicked...
								        // Find Variables
								        var countryID = geography.id
								        var countryName = geography.properties.name
								        var fillKey = null
								        
								        // If the country isn't on one of the lists, return the function
								        if (typeof datamap.options.data[countryID] == 'undefined') { return; }
								        
								        // Else continue on and get the type of country it is
								        fillKey = datamap.options.data[countryID]['fillKey']
								        								        
							            console.log(countryName + ' ' + fillKey)
							        });
							    },
								fills: {
									defaultFill: '". $defaultFillHex . "',
									livedColor: '" . $livedColor . "',
									visitedColor: '" . $visitedColor . "',
									nextColor: '" . $nextColor . "',		
								},
								data: {";
							
								// Loop through the various lists of countries: priority ruleset: lived > visited > next
								// If the user has explicitly requested to leave a country type off, don't include it
								
								if ($atts['shownext'] === true) {
									foreach ($countriesNext as $country) {
										// Loop through the countries the user wants to go to next
										$newCode = $this->convertIso2toIso3($country);
										$print .= $newCode . ": { fillKey: 'nextColor' },";
									}
								}
								
								if ($atts['showvisited'] === true) {
									foreach ($countriesVisited as $country) {
										// Loop through the countries visited
										$newCode = $this->convertIso2toIso3($country);
										$print .= $newCode . ": { fillKey: 'visitedColor' },";
									}
								}
								
								if ($atts['showlived'] === true) {
									foreach ($countriesLived as $country) {
										// Loop through the countries lived in
										$newCode = $this->convertIso2toIso3($country);
										$print .= $newCode . ": { fillKey: 'livedColor' },";
									}
								}
								
								$print .= "},
								geographyConfig: {
						            borderColor: '" . $borderColor . "',
						            highlightBorderWidth: 1,
						            // don't change color on mouse hover

						            highlightFillColor: function(geo) {
							            // Return either that country's specific fill colour, or the general hover colour
 						                return geo['fillKey'] || '" . $hoverColor . "';
						            },
						           
						            // When hovering, change the border colour
						            highlightBorderColor: '" . $highlightedBorderColor . "',
						            
						            "; // end print
						            
						        // Unless the user specifies "disablelabels", print the labels
						        if ($atts['disablelabels'] == false) {   
						        $print .= "
						            
						            // When hovering, show a popup
						            popupTemplate: function(geo, data) {
						                						                
						                "; 
						                
						                // By default, show no label if a country isn't on our lists
						                if ($atts['showalllabels'] != false) {
							                $print .= "						                
							                // If the country isn't in any of our lists, don't show it.
							                if (!data) {
								                return ['<div class=\"hoverinfo\">',
						                    '<strong>', geo.properties.name, '</strong>',
						                    '</div>'].join('');
							                }
							                ";
						                }
						                
						                // Continue printing
						                $print .= "
						                
						                // Initialize the info string, and assign the hover menu based on the colour chosen
						                var wanderInfoString = null;
						   						                
						                if (data.fillKey == 'nextColor') {
							                wanderInfoString = '" . str_replace("'", "\'", $nextMessage) . "';
							            }
							            if (data.fillKey == 'visitedColor') {
							                wanderInfoString = '" . str_replace("'", "\'", $visitedMessage) . "';
							            }
							            if (data.fillKey == 'livedColor') {
							                wanderInfoString = '" . str_replace("'", "\'", $livedMessage) . "';
							            }
						                
						                // Hover Menu (Displays country's name)
						                return ['<div class=\"hoverinfo\">',
						                    '<strong>', geo.properties.name, '</strong>', ' - ', wanderInfoString,
						                    '</div>'].join('');
						            }";
						        }
						        else {
							        $print .= " popupTemplate: function(geo, data) { return } ";
						        }
						            
						            // Continue printing
						            $print .= "
						        }
							});
						});
					</script>";
		
		return $print;
		
	}

	
	public function getWanderList($atts, $content) {
		
		// First, Retrieve the list of JSON countries and flags
		$countries = json_decode(file_get_contents( plugin_dir_path( __FILE__ ) . "countries.json"), true);
		$flags = json_decode(file_get_contents( plugin_dir_path( __FILE__ ) . "flags.json"), true);
		$includeFlags = $atts['flags'];
		
		// Retrieve the countries categories and strip duplicates
		$categories = array_filter($this->option['countriesCategory']);
		
		// Retrieve a list of countries based on which ever one the user selects in short code
		switch($atts['type']) {
			case 'lived':
				$selected = $this->option['countriesLived'];
				break;
			case 'next':
				$selected = $this->option['countriesNext'];
				break;
			default:
				$selected = $this->option['countriesVisited'];
				break;
		}
				
		if ($atts['number'] != false) {
			// If the user wants a count, return the count or the reverse
			if ($atts['reverse'] != false) {
				return count($countries) - count($selected);
			}
			return count($selected);
		}
		
		// Render the HTML
		$print = '<div class="countries-list"><ul>'; // Open the div and UL
		
		if ($atts['reverse'] != false) { // If The attributes are NOT reversed
			foreach ($countries as $country) { // Loop through the countries
				// Flag Inclusion
				if ($includeFlags != false) {
					$country['name'] = $country['name'] . " " . $flags[$country['code']];
				}
				if (!in_array($country['code'], $selected)) { // If the country is in the selected array
					$hasBeenLinked = false;
					if ($atts['links'] === true) {  // If the user wants links to categories
						foreach ($categories as $cat) { // Loop through the categories
							$category = json_decode($cat, true);
							if ($country['code'] == $category['country']) { // If the current country equals the category's country
								$url = get_category_link( $category['categoryID'] );
								$print .= '<li><a href="' . $url . '" target="_blank">' . $country['name'] . '</a></li>';
								$hasBeenLinked = true; // Mark, so no duplicates
							}
						}
					}
					$hasBeenLinked ? $print .= '' : $print .= '<li>' . $country['name'] . '</li>' ;
				}
			}
		}
		else { // Else, the attributes are reversed
			foreach ($countries as $country) { // Loop through the countries
				// Flag Inclusion
				if ($includeFlags != false) {
					$country['name'] = $country['name'] . " " . $flags[$country['code']];
				}
				if (in_array($country['code'], $selected)) { // If the country is in the selected array
					$hasBeenLinked = false;
					if ($atts['links'] === true) { // If the user wants links to categories
						foreach ($categories as $cat) { // Loop through the categories
							$category = json_decode($cat, true);
							if ($country['code'] == $category['country']) { // If the current country equals the category's country
								$url = get_category_link( $category['categoryID'] );
								$print .= '<li><a href="' . $url . '" target="_blank">' . $country['name'] . '</a></li>';
								$hasBeenLinked = true; // Mark, so no duplicates
							}
						}
					}
					$hasBeenLinked ? $print .= '' : $print .= '<li>' . $country['name'] . '</li>' ;
				}
			}
		}
		
		$print .= '</ul></div>'; // Close the UL and the div
		return $print;

	}
	
	public function getWanderGMap($atts, $content) {
		
		// Get Google Maps API Key from options
		$wander_GoogleMaps_Api_Key = $this->options['googleMapsApiKey'];
		wp_enqueue_script('WanderPlugin_MapStyle', plugins_url( '/js/mapStyle.js', __FILE__ ));
		wp_enqueue_script('Wander_GoogleMaps', 'https://maps.googleapis.com/maps/api/js?key=' 
			. $wander_GoogleMaps_Api_Key . '&callback=loadMap', false, false, true);
		
	    $html = '<div class="wander-map-container">';
	    $html .= '<div id="wander-map"></div>';
	    $html .= '	<div id="wander-map-cover">
	    				<p id="wm-initial-message">Select a country to view more information...</p>
	    				<div id="wm-cover-data">
	    					<h2 id="wm-cover-title"></h2>
							<p id="wm-cover-text"></p>
						</div>
	    				<a href="" target="_blank" id="wm-cover-button-anchor">
	    					<div id="wm-cover-button">See Posts</div>
	    				</a>
	    			</div>
	    		</div>';	    
		return $html;
		
	}
	
	public function getWanderCurrentLocation($atts, $content) {
		
		// This method returns the user's current location and prints it
		// This method accepts the atts and content arrays, (but it only uses the atts)
		
		// Retrieve the user's current location from the option array
		$location = json_decode($this->option['currentLocation'], true);
		$print = null; // Initialize the print variable
	    	  
		// If the user requests the location to be a link, wrap the location in an anchor
		if ( $atts['link'] !== false ) {
			$print .= '<a target="_blank" href="' . $location['url'] . '">';
		}
		
		// If the user wants to just display their city, show the city
		if ($atts['city'] === 'true') {
			$locationString = $location['city'];
		}
		
		// If the user wants to just display the country, show the country
		if ($atts['country'] === 'true') {
			$locationString = $location['country'];
		}
		
		// If the user wants the city and the country, show both, with a comma separating them
		if ($atts['city'] === 'true' and $atts['country'] === 'true') {
			$locationString = $location['city'] . ", " . $location['country'];
		}
		
		// If the user requested a link, close the anchor tag
		if ( $atts['link'] !== false ) {
			$print .= $locationString;
			$print .= '</a>';
		}
		else { // Otherwise, just append the location string
			$print .= $locationString;
		}
		
		// Return the HTML content
		return $print;

	}
	

} // End of Class
	 
