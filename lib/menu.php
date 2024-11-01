<?php
	/*
		Menu.php - Countries Visited Plugin
		Description: Handles the logic for creating the admin menu page
		Author: Ryder Damen
	*/
	
class WanderMenuPage
{
    // Global Variables ---------------------------------------------------------------------------------------------------
    private $options;

	// Constructor  -------------------------------------------------------------------------------------------------------
	
    public function __construct() {
	    // Class Constructor
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    // Constructor Helpers ------------------------------------------------------------------------------------------------
    
    public function add_plugin_page() {
	    // Adds the menu page
        add_menu_page(
	        'Wander Plugin',
	        'Wander',
	        'manage_options',
	        'wander',
	        array( $this, 'wander_constructAdminPage' ),
	        'dashicons-location',
	        20
		);
    }

    
    public function wander_constructAdminPage() {
	    // Callback for constructing options / admin page
	    	    
        // Set class property
        $this->options = get_option( 'wander_option' );
        
        // Choose a random header image
        $randomHeader = 'img/header' . rand(1, 5) . '.jpg';
        
        ?>
        <div class="wrap">
	        <div class="cv-header" style=" background-image: url(<?php echo esc_url(plugin_dir_url( dirname(__FILE__) ) . $randomHeader);  ?>);">
		        <div class="cv-header-inner">
			        <h1>Wander</h1>
			        <p>A Plugin For Travel Bloggers</p>
		        </div>
	        </div>        
	       	        
	        <h1>Welcome To Wander</h1>
	        <p>Wander is a comprehensive plugin designed to help you organize your travel blog. Display your current location, a list of countries you've been to, lived in, want to go to, haven't gone to, or a map of the world. For more information on how to get started, check out the <a href="#how" />How To Use This Plugin Section</a>.</p>
            
            <h1>Your Settings</h1>            
            <form method="post" action="options.php" class="form-container">
            <?php
	            submit_button();
                settings_fields( 'wander_option_group' );
                do_settings_sections( 'wander-setting-admin' );
                $this->buildCountryTable(); // Finally, Build the table
                submit_button();
            ?>
            </form>
            <div id="how"></div>
            <h1>How To Use This Plugin</h1>
            <p>Sometimes WordPress plugins can be a little tricky, so here's a quick guide to get you started.</p>
            
            <h2>Displaying Your Current Location</h2>
            <p>Once you set your current location up top, you can include your current location in any page, post, or sidebar with this short code:</p>
	        <code class="wander-example">[wander-location]</code>
	        <br><br>
	        <p>By default, this will link to an external google map. To stop that, use the following shortcode</p>
	        <code class="wander-example">[wander-location link="false"]</code>
	        <br><br>
	        			
			<h2>Displaying A List Of Countries You've Been To</h2>
			<p>Once you've selected the countries you've been to, include this short code in any page or post:</p>
			<code class="wander-example">[wander-list]</code>
			<p>... to see a list that looks like this:</p>
			<ul class="example-ul">
				<li>Australia</li>
				<li>Canada</li>
				<li>Hong Kong</li>
			</ul>
			<br>
			<p>Cool right? As a default, it shows you the list of countries you've visited. If you want to switch it, simply update the shortcode.</p>
			<code class="wander-example">[wander-list type="lived"]</code> shows countries you've lived in, and
			<code class="wander-example">[wander-list type="next"]</code> shows countries you want to travel to next.
			<br><br>
			<p>Want to list the countries you haven't lived in? You can definitely do that. Simply use this shortcode</p>
			<code class="wander-example">[wander-list type="lived" reverse="true"]</code>
			<br><br>
			<p>If you've selected a category for that particular country, it will automatically be linked. To stop linking, use this shortcode</p>
			<code class="wander-example">[wander-list links="false"]</code>
       
        </div>
        
        <?php
	        
	        
    }

    
    // Other Methods ---------------------------------------------------------------------------------------------------
    
    public function buildCountryTable() {
	    // Builds the Country Table for this page
	    
	    ?>
	    <input type="text" id="countrySearch" onkeyup="filterCountries()" placeholder="🔎&nbsp;&nbsp;Search for countries..">
	    <?php
	    
	    // First, retrieve the categories and build the category string
	    $args = array('hide_empty' => false, 'echo' => false);
	    $categories = get_categories($args);
	    $categorySelectString = '<select name="wander_option[countriesCategory][]">';
	    $categorySelectString .= '<option>-</option>';
	    foreach($categories as $category) {
		    $categorySelectString .= '<option value="' . $category->id . '">' . $category->name .'</option>';
	    }
	    $categorySelectString .= '</select>';
	    
	    // Retrieve the list of countries
	    $countries = json_decode(file_get_contents( plugin_dir_path( __FILE__ ) . "countries.json"), true);
	    
	    // Get The Options, Implement Filling Logic
	    
	    
	    // Handling Colours
	    $visitedColor = "#22ccd8";
	    $livedColor = "#11619e";
	    $nextColor = "#92d662";
	    
	    if (isset($this->options['visitedColor'])) {
		    $visitedColor = $this->options['visitedColor'];
	    }
	    if (isset($this->options['livedColor'])) {
		    $livedColor = $this->options['livedColor'];
	    }
	    if (isset($this->options['nextColor'])) {
		    $nextColor = $this->options['nextColor'];
	    }
	    
	    	    
	    // Build The Table
	    ?>
	    <table class="countries-list" id="countries-list">
	    <tr>
		    <th align="center">Country</th>
		    <th align="center">Visited?</th>
		    <th align="center">Lived In?</th>
		    <th align="center">Want To Visit?</th>
		    <th align="center">Category Link</th>
	    </tr>
	    <tr>
		    <td align="center"></td>
		    <td align="center"><input type="text" id='visited-color-picker' value="<?php echo $visitedColor; ?>" name="wander_option[visitedColor]"></input></td>
		    <td align="center"><input type="text" id='lived-color-picker' value="<?php echo $livedColor; ?>" name="wander_option[livedColor]"></input></td>
		    <td align="center"><input type="text" id='next-color-picker' value="<?php echo $nextColor; ?>" name="wander_option[nextColor]"></input></td>
		    <td align="center"></td>
	    </tr>
	    
	    
	    <?php
	    
	    foreach($countries as $country) {
			
			// Retrieve the Country Code
		    $code = esc_attr($country['code']);
		    
		    // Has This Country Been Checked?
		    $countriesVisited_checked = "";
		    $countriesLived_checked = "";
		    $countriesNext_checked = "";
		    
			if (is_array($this->options['countriesVisited'])) {
				in_array($country['code'], $this->options['countriesVisited']) ? $countriesVisited_checked = "checked=true" : $countriesVisited_checked = "";
			}
			
			if (is_array($this->options['countriesLived'])) {
				in_array($country['code'], $this->options['countriesLived']) ? $countriesLived_checked = "checked=true" : $countriesLived_checked = "";
			}
			
			if (is_array($this->options['countriesNext'])) {
				in_array($country['code'], $this->options['countriesNext']) ? $countriesNext_checked = "checked=true" : $countriesNext_checked = "";
			}
			
		    
		    ?>
		    <tr>
			    <td class="country-name" align="center"><?php echo esc_attr($country['name']); ?></td>
				<td align="center"><input type="checkbox" name="wander_option[countriesVisited][]" value="<?php echo $code; ?>" <?php echo $countriesVisited_checked ?>></input></td>
			    <td align="center"><input type="checkbox" name="wander_option[countriesLived][]" value="<?php echo $code; ?>" <?php echo $countriesLived_checked ?>></input></td>
			    <td align="center"><input type="checkbox" name="wander_option[countriesNext][]" value="<?php echo $code; ?>" <?php echo $countriesNext_checked ?>></input></td>
				<td align="center">
					<select name="wander_option[countriesCategory][]">
						<option></option>
						<?php 
							foreach($categories as $category) {
																
								// Needle for Haystack Seach (JSON String)
								$needle = json_encode(array('country'=> $country['code'], 'categoryID' => $category->cat_ID, 'categorySlug' => $category->slug));
								
								$selected = '';
								if (in_array($needle, $this->options['countriesCategory'])) {
									$selected = 'selected="selected"';
								}
								
							    ?>
							    <option value='<? echo $needle; ?>' <?php echo $selected; ?>><?php echo $category->name; ?></option><?php
						    }
						?>
					</select>
				</td>
		    </tr>
		    <?php
		    
	    }
	    
	    ?></table><?php
    }
    
    public function page_init() {    
	    // Registers and Adds Settings    
        register_setting(
            'wander_option_group', // Option group
            'wander_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
        
        
        
        /************
	        CURRENT LOCATION SECTION
	    *************/
        add_settings_section(
            'currentLocation', // ID
            '📌 Current Location', // Title
            array( $this, 'printCurrentLocationInfo' ), // Callback
            'wander-setting-admin' // Page
        );  
        
        add_settings_field(
            'currentLocation', 
            'Your Current Location', 
            array( $this, 'current_location_callback' ), 
            'wander-setting-admin', 
            'currentLocation'
        ); 
        
       /************
	        COUNTRIES SECTION
	    *************/
        

        add_settings_section(
            'visitedCountries', // ID
            '🏴 Countries', // Title
            array( $this, 'printVisitedCountriesInfo' ), // Callback
            'wander-setting-admin' // Page
        );  
        
        add_settings_field(
            'countriesVisited', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        );    
        
        
        add_settings_field(
            'countriesLived', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        );   
        
        add_settings_field(
            'countriesNext', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        ); 
        
        add_settings_field(
            'countriesCategory', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        );   
        
        // Colours, aka "colors"... ugh
        
        add_settings_field(
            'visitedColor', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        );
        
        add_settings_field(
            'livedColor', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        );
        
        add_settings_field(
            'nextColor', 
            '', 
            array( $this, 'blank_callback' ), 
            'wander-setting-admin', 
            'visitedCountries',
            [
		        'class' => 'hidden'
		    ]
        );       
        
        
    }
    
    public function sanitize( $input ) {
	    // Sanitizes each particular setting
	    
        $new_input = array(); // Initialize an array to return
            
        if( isset( $input['visitedCountries'] ) ) // Sanitizing Countries Array
            $new_input['visitedCountries'] =  $input['visitedCountries']; // NOT SANITIZED: TODO
            
        if( isset( $input['currentLocation'] ) ) {
	        $new_input['currentLocation'] =  $input['currentLocation']; // NOT SANITIZED: TODO
        }
        
        
        if( isset( $input['countriesVisited'] ) ) {
	        $new_input['countriesVisited'] =  $input['countriesVisited']; // NOT SANITIZED: TODO
        }
        
        if( isset( $input['countriesLived'] ) ) {
	        $new_input['countriesLived'] =  $input['countriesLived']; // NOT SANITIZED: TODO
        }
        
        if( isset( $input['countriesNext'] ) ) {
	        $new_input['countriesNext'] =  $input['countriesNext']; // NOT SANITIZED: TODO
        }
        
        if( isset( $input['countriesCategory'] ) ) {
	        $new_input['countriesCategory'] =  $input['countriesCategory']; // NOT SANITIZED: TODO
        }
        
        // Sanitizing Colours
        if( isset( $input['visitedColor'] ) ) {
	        $new_input['visitedColor'] =  sanitize_hex_color($input['visitedColor']);
        }
        if( isset( $input['livedColor'] ) ) {
	        $new_input['livedColor'] =  sanitize_hex_color($input['livedColor']);
        }
        if( isset( $input['nextColor'] ) ) {
	        $new_input['nextColor'] =  sanitize_hex_color($input['nextColor']);
        }
                
        return $new_input;
    }

	public function blank_callback() {
		// Do Nothing
	}
    
    
    
    // Current Location ---------------------------------------------------------------------------------------------------
    
    public function current_location_callback() {
	    // Implement Google Maps Current Location
	    
	    // First, see if the option is available
	    
	    if ( isset( $this->options['currentLocation'] ) and $this->isValidJson( $this->options['currentLocation'] ) ) {
		    // If the location has been successfully set before
		    $location = json_decode($this->options['currentLocation'], true);
		    ?>
			<input id="autocomplete" placeholder="Where are you?" onFocus="geolocate()" type="text"></input>
			<p><strong>Example: </strong>I'm currently in <a href="<?php echo $location['url']; ?>"target="_blank" id="currentLocationView"><?php echo $location['city'] . ', ' . $location['country'] ?></a> and it's amazing!</p>
			<strong>Code: </strong><code>I'm currently in [wander-location] and it's amazing!</code>
			<input hidden="true" name="wander_option[currentLocation]" id="currentLocationInput" value='<?php echo $this->options['currentLocation'];?>'></input>
			<?php
	    }
	    else {
		     ?>
			 <input id="autocomplete" placeholder="Where are you?" onFocus="geolocate()" type="text"></input> <a target="_blank" id="currentLocationView"></a>
			 <input hidden="true" name="wander_option[currentLocation]" id="currentLocationInput" value=""></input>
			 <?php  
	    }
	    	   
    }

	public function printCurrentLocationInfo() {
        print 'This section allows you to choose and display your current location on your site.';
    }
    
    
    private function isValidJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
    
    
    // Visited Countries --------------------------------------------------------------------------------------------------
  
    public function printVisitedCountriesInfo() {
        //print '';
    }
    
    public function visited_countries_callback() {
	    // Retrieves the list of countries and outputs it
		$countries = json_decode(file_get_contents( plugin_dir_path( __FILE__ ) . "countries.json"), true); // Include the Countries Asset
		
		?> <div class="visited-countries-list"> <?php
		
	    foreach($countries as $country) {	
		    // Store the value as an array of the visitedCountries option
		    
		    // Has This Country Been Checked?
		    if (is_array($this->options['visitedCountries'])) {
			    in_array($country['code'], $this->options['visitedCountries']) ? $checked = "checked=true" : $checked = "";
		    }
		    else { $checked = ""; }
			
			// Print An Input For Each Country
			?> <input type="checkbox" name="wander_option[visitedCountries][]"  id="visited-<?php echo esc_attr($country['code']); ?>" value="<?php echo esc_attr($country['code']); ?>" <?php echo $checked; ?>>
			<label><?php echo esc_attr($country['name']); ?></label>
			<br>
			
			<?php    
		}
		
		?> </div> <?php
        
    }    
    
    
} // End Of Class


// Hooking To Wp-Admin

if( is_admin() )
    $my_settings_page = new WanderMenuPage();
	
	
	
