// This Script Allows the Autocomplete Population of the user's current location: adapted from google example
// https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform


	
	// Colour Picker Logic
	jQuery(document).ready(function($){
    	$('#visited-color-picker').wpColorPicker();
    	$('#lived-color-picker').wpColorPicker();
    	$('#next-color-picker').wpColorPicker();
	});
	
	
      var placeSearch, autocomplete;
      var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
      };

      function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
            {types: ['geocode']});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
      }

      function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();        
        var city = null;
        var country = null;
        var url = null;
        
        if (place.url) { // If the place can be identified on a map, provide the URL to that map
	        url = place.url;
        }
        
        var lat = autocomplete.getPlace().geometry.location.lat();
        var lon = autocomplete.getPlace().geometry.location.lng();
        
        for (var i = 0; i < place.address_components.length; i++) {
	        // Loop through the address types to find the city and country
          var addressType = place.address_components[i].types[0];
                              
          if (addressType == 'locality') {
	          city = place.address_components[i][componentForm[addressType]];
          }
          if (addressType == 'country') {
	          country = place.address_components[i][componentForm[addressType]];
          }
        }

		// Create an array for the hidden input
        var optionsArray = JSON.stringify({ "city": city, "country": country, "url": url, "lat": lat, "lon": lon });         
        
        var viewString = city + ', ' + country;
        if (city == null || city == "null") {
	        viewString = country;
        }
        
        // Print to the view, and to the hidden input
        document.getElementById('currentLocationView').innerHTML = viewString;
        document.getElementById('currentLocationView').href = url;

        document.getElementById('currentLocationInput').innerHTML = optionsArray;
        document.getElementById('currentLocationInput').value = optionsArray;
        
      }

      // Bias the autocomplete object to the user's geographical location,
      // as supplied by the browser's 'navigator.geolocation' object.
      function geolocate() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
              center: geolocation,
              radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
          });
        }
      }
      
      function filterCountries() {
		  var input, filter, table, tr, td, i;
		  input = document.getElementById("countrySearch");
		  filter = input.value.toUpperCase();
		  table = document.getElementById("countries-list");
		  tr = table.getElementsByTagName("tr");
		  for (i = 0; i < tr.length; i++) {
		    td = tr[i].getElementsByTagName("td")[0];
		    if (td) {
		      if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
		        tr[i].style.display = "";
		      } else {
		        tr[i].style.display = "none";
		      }
		    }       
		  }
		}