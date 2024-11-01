// Countries Visited - Main.js
// Description: Handles the logic for implementing countries maps
// Author: Ryder Damen (http://ryderdamen.com)
// Version: 1.0

// Global Variables
var $ = jQuery;

// Map Colours
var visitedColor;
var livedColor;
var nextColor;

// Misc
var currentLocation;
var visitedCountries;
var livedCountries;
var nextCountries;

// Booleans
var showCurrentLocation;
var showVisitedCountries;
var showLivedCountries;
var showNextCountries;

// Style Selectors
var visitedSelector = "ISO_2DIGIT IN ('QQ')";
var livedSelector = "ISO_2DIGIT IN ('QQ')";
var nextSelector = "ISO_2DIGIT IN ('QQ')"; // Set a fake country as a default, so the style won't appear

var map;
var mapCover; // Map Cover

// Page Lifecycle
initialize();




// Methods
function initialize() {
	
	// If the map is used on this page, initialize the map
	initializeMap();
	
}

function stripCountriesString(input) {
	// Replaces square brackets with normal ones, and replaces double quotes with single quotes
	input = JSON.stringify(input).split(/[\{\[]/).join('(').split(/[\}\]]/).join(')');	
	input = input.replace(/"/g, "'");
	return input;
}

function initializeMap() {
	
	// First, Retrieve options from Global Variables
	var options = countriesVisitedOptions;
	
	// Set Colour Strings
	visitedColor = options.visitedColor;
	livedColor = options.livedColor;
	nextColor = options.nextColor;
	
	// Parse current location json
	currentLocation = JSON.parse(countriesVisitedOptions.currentLocation);
	
	// Retrieve Country Map
	if (countriesVisitedOptions.countriesVisited != undefined) {
		visitedCountries = stripCountriesString(countriesVisitedOptions.countriesVisited);
		visitedSelector = 'ISO_2DIGIT IN ' + visitedCountries;
	}
	if (countriesVisitedOptions.countriesLived != undefined) {
		livedCountries = stripCountriesString(countriesVisitedOptions.countriesLived);
		livedSelector = 'ISO_2DIGIT IN ' + livedCountries;
	}
	if (countriesVisitedOptions.countriesNext != undefined) {
		nextCountries = stripCountriesString(countriesVisitedOptions.countriesNext);
		nextSelector = 'ISO_2DIGIT IN ' + nextCountries;
	}
	
	
}

	
function loadMap() {
	
	// Create the map
	map = new google.maps.Map(document.getElementById('wander-map'), {
		center: new google.maps.LatLng(30,0),
		zoom: 2,
		styles: mapStyleLight,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});	
	
	// Prevent Zoom from exceeding 2 (and countries appearing as dots on the fusion layer)
	google.maps.event.addListener(map, 'zoom_changed', function() {
    	zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
            if (this.getZoom() < 2) {
                this.setZoom(2);
            }
    	});
	});
	
	mapCover = document.getElementById("wander-map-cover");
	
	// Set up on-click listener for the overlay
/*
	document.getElementById("wm-close-data-box").addEventListener("click", function(){
    	mapCover.style.display = "none";
	});
*/
	
	// Build the styles
	var overlayStyles = [
			{
		  		polygonOptions: {
		  			fillColor: "#FFFFFF",
		  			fillOpacity: 0.0001,
		  			strokeColor: "#fff",
		  			strokeWeight: "0"
      			}
    		}, 
    		{
				where: visitedSelector,
				polygonOptions: { fillOpacity: 0.15, fillColor: visitedColor }
    		},
    		{
				where: livedSelector,
				polygonOptions: { fillOpacity: 0.15, fillColor: livedColor }
    		},
    		{
				where: nextSelector,
				polygonOptions: { fillOpacity: 0.15, fillColor: nextColor }
    		}
    ];
    	
	
	// Implementing The Various Layers (Visited, Lived, Next)
	var countriesLayer = new google.maps.FusionTablesLayer({
	        query: {
	        	select: 'geometry',
				from: '1N2LBk4JHwWpOY4d9fobIn27lfnZ5MDy-NoqqRpk'
	        },
	        styles: overlayStyles,
		  	map: map,
		    suppressInfoWindows: true
    });
    google.maps.event.addListener(countriesLayer, 'click', function(e) {
        mapWasClicked(e, "visited");
    });
    
    
    
  	    
}

function mapWasClicked(e, typeOfCountry) {
	// The Map Was Clicked
	
	console.log(countriesVisitedOptions);

	
	// Hide the initial message
	document.getElementById('wm-initial-message').style.display = "none";
	
	/**
		Alright, so we need to set up some sort of priority system in order to determine which dialogue box to display:
		
		// Oh, maybe set up a gradient? That would require a hex to RGB conversion
		
		1. The User Lived in the country (top priority)
		2. The User Visited the country
		3. The User Wants to Visit the country (lowest priority)
		
		// A situation system will be used to determine what's happening
		0 - nothing
		1 - user only visited
		2 - user only lived
		3 - user only wants to visit
		4 - user visited and lived
		5 - user visited and wants to visit??
		6 - user lived and wants to visit
		7 - user visited, wants to visit, and lived
	*/
	
	// Get Variables
	var mapLabel = document.getElementById('wander-map-selector');
	var countryName = e.row.Name.value;
	var countryCode = e.row.ISO_2DIGIT.value;
	
	// Check which array of countries this belongs to // -1 = does not exist, other ints are array index
	var didVisit = jQuery.inArray(countryCode, countriesVisitedOptions.countriesVisited);
	var didLive = jQuery.inArray(countryCode, countriesVisitedOptions.countriesLived);
	var didNext = jQuery.inArray(countryCode, countriesVisitedOptions.countriesNext);
	
	// Set up and assign Boolean Constants
	var bl_didVisit = false;
	var bl_didLive = false;
	var bl_didNext = false;
	
	if (didVisit != -1) bl_didVisit = true;
	if (didLive != -1) bl_didLive = true;
	if (didNext != -1) bl_didNext = true;
	
	// Set up the situation
	var situation = 0;
	if (bl_didVisit && !bl_didLive && !bl_didNext) situation = 1;
	if (!bl_didVisit && bl_didLive && !bl_didNext) situation = 2;
	if (!bl_didVisit && !bl_didLive && bl_didNext) situation = 3;
	if (bl_didVisit && bl_didLive && !bl_didNext) situation = 4;
	if (bl_didVisit && !bl_didLive && bl_didNext) situation = 5;
	if (!bl_didVisit && bl_didLive && bl_didNext) situation = 6;
	if (bl_didVisit && bl_didLive && bl_didNext) situation = 7;
	
	// Get the country category slug from the country code (if it has one)
	
	
	var overlayBackgroundColor = null;
	var overlayBackgroundGradient = null;
	var overlayText = null;

	switch (situation) {
		case 1: // User Only Visited
			overlayBackgroundColor = visitedColor;
			overlayText = "Visited";
			break;
		case 2: // User Only Lived
			overlayBackgroundColor = livedColor;
			overlayText = "Lived";
			break;
		case 3: // User Only Wants to Visit
			overlayBackgroundColor = nextColor;
			overlayText = "On My List";
			break;
		case 4: // User Visited and Lived
			overlayBackgroundColor = livedColor;
			overlayBackgroundGradient = "linear-gradient( to right, " + visitedColor + ", " + livedColor +" );";
			overlayText = "Lived / Visited";
			break;
		case 5: // User Visited and Wants To Visit
			overlayBackgroundColor = visitedColor;
			overlayBackgroundGradient = "linear-gradient( to right, " + visitedColor + ", " + nextColor +" );";
			overlayText = "Visited / On My List";
			break;
		case 6: // User Lived and Wants To Visit
			overlayBackgroundColor = livedColor;
			overlayBackgroundGradient = "linear-gradient( to right, " + livedColor + ", " + nextColor +" );";
			overlayText = "Lived / On My List";
			break;
		case 7: // User Visited, wants to visit, and lived
			overlayBackgroundColor = livedColor;
			overlayBackgroundGradient = "linear-gradient( to right, " + livedColor + ", " + visitedColor +", " + nextColor + " );"; 
			overlayText = "Lived / Visited / On My List";
			break;
		default: // Do nothing (accounts for case 0)
			return;
			break;
	}	
	
	
	console.log(overlayBackgroundGradient);
	// Set variables in the view
	mapCover.style.backgroundColor = overlayBackgroundColor;
 	mapCover.style.background = overlayBackgroundGradient; // TODO fixme
	document.getElementById('wm-cover-title').innerHTML = countryName;
	document.getElementById('wm-cover-text').innerHTML = overlayText;
	
	
	$( "#wander-map-cover" ).fadeIn( "fast", function() { // Last, fade in the cover
    	// onComplete
  	});
		
/*
	// Populate Overlay with data
	if (didVisit) {
		document.getElementById('wm-country-name').innerHTML = countryName + " (Visited)";
		}
	else if (didLive) {
		document.getElementById('wm-country-name').innerHTML = countryName + " (Lived)";
		}
	else if (didNext) {
		document.getElementById('wm-country-name').innerHTML = countryName + " (Up Next)";
		}
*/
	
	
}




    
