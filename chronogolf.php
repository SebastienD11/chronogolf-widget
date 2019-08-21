<?php
/**
 * Plugin Name: ChronoGolf
 * Plugin URI: http://pro.chronogolf.com/
 * Description: Add the ChronoGolf's booking widget on your website !
 * Version: 5.0
 * Author: ChronoGolf
 * Author URI: http://pro.chronogolf.com/
 * License: GPL2
 */
 
/**
 * Main Class
 */
class Chronogolf {
  
    /*--------------------------------------------*
     * Attributes
     *--------------------------------------------*/
  
    /** Refers to a single instance of this class. */
    private static $instance = null;
     
    /* Saved options */
    public $options;
  
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
  
    /**
     * Creates or returns an instance of this class.
     *
     * @return  Chronogolf A single instance of this class.
     */
    public static function get_instance() {
  
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
  
        return self::$instance;
  
    } // end get_instance;
  
    /**
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    private function __construct() { 
	 
		// Add the page to the admin menu
		add_action( 'admin_menu', array( &$this, 'add_page' ) );
		 
		// Register page options
		add_action( 'admin_init', array( &$this, 'register_page_options') );
		 
		// Register javascript
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_script' ) );
		add_action('in_admin_footer', array( $this, 'enqueue_admin_script_footer'));
		add_action('wp_footer',  array( $this, 'enqueue_footer_js' ) );

		add_action( 'wp_head', array( $this, 'styleFrontend'));
		
		 
		// Get registered option
		$this->options = get_option( 'chronogolf_settings_options' );

		if(isset( $this->options['itunes_app_id']) && !empty($this->options['itunes_app_id'])){
			add_action('wp_head',  array( $this, 'head_meta' ) );
		}

		add_action( 'admin_init', array( $this, 'chronOverride'  ) );

		require 'plugin-update-checker/plugin-update-checker.php';
		// $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		// 	'http://www.chronogolf.net/chronogolf-wordpress/plugin.json',
		// 	__FILE__, //Full path to the main plugin file or functions.php.
		// 	'chronogolf'
		// );
		$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/SebastienD11/chronogolf-widget/',
			__FILE__,
			'chronogolf'
		);

		//Optional: If you're using a private repository, specify the access token like this:
		$myUpdateChecker->setAuthentication('b8f697b06f38041c139ef4bac95f925019e2ff7b');

		
	}
  
    /*--------------------------------------------*
     * Functions
     *--------------------------------------------*/

    public function chronOverride(){
	    if(is_admin()) {
	    	global $current_user;
			$current_user = wp_get_current_user();
			if(	
				// Customer account
				$current_user->user_login !== 'chronogolf' && 
				$current_user->user_login !== 'sebastien' 
			){
				remove_menu_page( 'edit.php?post_type=portfolio' );
			} else {
				// Chrono Team
				// add_action( 'admin_init', 'wpse_136058_debug_admin_menu' );
				// add_action('avia_builder_mode', array( $this, "builder_set_debug" ) );
			}
		}	
	}
     
    public function wpse_136058_debug_admin_menu() {
		echo '<pre>' . print_r( $GLOBALS[ 'menu' ], TRUE) . '</pre>';
	}
     
    /**
	 * Function that will add script file.
	 */
	public function enqueue_admin_script_footer(){
		// Intercom Only in Admin
		$user_info = get_userdata(get_current_user_id());
		?>
			<script>
			window.intercomSettings = {
				name: "<?php echo $user_info->user_login; ?>",
				app_id: "a4ee4ab2660a081ee070bbbca07f45627795972f",
			};
			</script>
			<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/a4ee4ab2660a081ee070bbbca07f45627795972f';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();</script>
		<?php
	}
	public function enqueue_admin_script($hook) { 
				global $page_hook_suffix;
		if( $hook != $page_hook_suffix )
			return; 
		 
		// Make sure to add the wp-color-picker dependecy to js file
		wp_enqueue_script( 'datepicker_js', plugins_url( 'js/jquery.datepicker.js', __FILE__ ), array( 'jquery'), '', true  );
		wp_enqueue_script( 'chronogolf_js', plugins_url( 'js/jquery.chronogolf.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), '', true  );
		wp_enqueue_style( 'cronogolf', plugins_url( 'css/chronogolf.css', __FILE__ ) );
		//wp_enqueue_style( 'chronogolf_css', plugins_url( 'css/chronogolf.css', __FILE__ ) );		
		// Css rules for Color Picker
		wp_enqueue_style( 'wp-color-picker' );
	}

	public function styleFrontend(){
		
		$widget_css = "
		<style type='text/css'>
			@media only screen and (max-width: 680px){
				body{padding-bottom: 50px;}
				body .chrono-container .chrono-bookingbutton{
					width: 100%;
					left: 0;
					bottom: 0;
					border-radius: 0;
				}
			}
		</style>";
		echo $widget_css;
	}


    /**
	 * Function that will add the options page under Setting Menu.
	 */
	public function add_page() { 
		global $page_hook_suffix;
		 $page_hook_suffix = add_menu_page(
			__( 'ChronoGolf', 'chronogolf' ),
			'ChronoGolf',
			'manage_options',
			'chronogolf',
			array($this, 'display_page'),
			plugins_url( 'chronogolf/img/ico-chronogolf.png' ));		
	}

	/**
	 * Function that will register admin page options.
	 */
	public function register_page_options() { 
		 
		// Add Section for option fields
		add_settings_section( 'chronogolf_section', '', array( $this, 'display_section' ), __FILE__ ); // id, title, display cb, page
		 
		// Add Club Field
		add_settings_field( 'chronogolf_club_field', 'N° Club', array( $this, 'club_settings_field' ), __FILE__, 'chronogolf_section' ); // id, title, display cb, page, section

		// Add Environment Field
		add_settings_field( 'chronogolf_environement_field', 'Environnement', array( $this, 'environement_settings_field' ), __FILE__, 'chronogolf_section' ); 	

		// Add Exclude Page Field
		add_settings_field( 'chronogolf_exclude_field', 'Excludes Page IDs', array( $this, 'exclude_settings_field' ), __FILE__, 'chronogolf_section' ); 

		// Add Include Page Field
		add_settings_field( 'chronogolf_include_field', 'Include Page IDs', array( $this, 'include_settings_field' ), __FILE__, 'chronogolf_section' ); 

		// Add Background Color Field
		add_settings_field( 'chronogolf_bg_field', 'Widget Color', array( $this, 'color_settings_field' ), __FILE__, 'chronogolf_section' );

		// Add Itune App ID
		add_settings_field( 'chronogolf_itunes_app_id', 'Itunes App ID', array( $this, 'itunes_app_id_settings_field' ), __FILE__, 'chronogolf_section' ); 
		 
		// Register Settings
		register_setting( __FILE__, 'chronogolf_settings_options', array( $this, 'validate_options' ) ); // option group, option name, sanitize cb 
	}
	 
	/**
	 * Functions that display the fields.
	 */
	public function club_settings_field() { 
		 
		$val = ( isset( $this->options['club'] ) ) ? $this->options['club'] : '';
		echo '<input type="text" name="chronogolf_settings_options[club]" value="' . $val . '" />';
	}   
	 
	/**
	 * Functions that display the fields.
	 */
	public function exclude_settings_field() { 
		 
		$val = ( isset( $this->options['exclude'] ) ) ? $this->options['exclude'] : '';
		echo '<input type="text" name="chronogolf_settings_options[exclude]" value="' . $val . '" />';
		echo '<br/><em>ex: 13,14,56,145</em>';
	}   

	public function include_settings_field() { 
		 
		$val = ( isset( $this->options['include'] ) ) ? $this->options['include'] : '';
		echo '<input type="text" name="chronogolf_settings_options[include]" value="' . $val . '" />';
		echo '<br/><em>ex: 13,14,56,145</em>';
	}  

	public function itunes_app_id_settings_field() { 
		 
		$val = ( isset( $this->options['itunes_app_id'] ) ) ? $this->options['itunes_app_id'] : '';
		echo '<input type="text" name="chronogolf_settings_options[itunes_app_id]" value="' . $val . '" />';
		echo '<br/><em>Only useful if Chronogolf built your mobile app.</em>';
	}  

	public function color_settings_field() { 
		 
		$val = ( isset( $this->options['background'] ) ) ? $this->options['background'] : '';
		echo '<input type="text" name="chronogolf_settings_options[background]" value="' . $val . '" class="chronogolf-color-picker" >';
		 
	}
	 
	public function environement_settings_field() { 
		$val = ( isset( $this->options['environement'] ) ) ? $this->options['environement'] : '';
		if($val == 'demo')
			$checkedDemo = 'checked';
		else 
			$checkedDemo = '';

		if($val == 'prod' || !$val || $val == '')
			$checkedProd = 'checked';
		else 
			$checkedProd = '';
		echo '<input type="radio" id="demo" name="chronogolf_settings_options[environement]" '.$checkedDemo.' value="demo" class="" ><label for="demo">'.__('Demo Environnement','').'</label><br />';
		echo '<input type="radio" id="prod" name="chronogolf_settings_options[environement]" '.$checkedProd.' value="prod" class="" ><label for="prod">'.__('Production Environnement','').'</label>';
		 
	}
     
	/**
	* Function that will validate all fields.
	*/
	public function validate_options( $fields ) { 
		 
		$valid_fields = array();
		 
		// Validate Club Field
		$club = trim( $fields['club'] );
		$valid_fields['club'] = strip_tags( stripslashes( $club ) );

		// Validate Exclude Field
		$exclude = trim( $fields['exclude'] );
		$valid_fields['exclude'] = strip_tags( stripslashes( $exclude ) );

		// Validate Include Field
		$include = trim( $fields['include'] );
		$valid_fields['include'] = strip_tags( stripslashes( $include ) );

		// Validate Include Field
		$itunes_app_id = trim( $fields['itunes_app_id'] );
		$valid_fields['itunes_app_id'] = strip_tags( stripslashes( $itunes_app_id ) );
		 
		// Validate Environment Field
		$environement = trim( $fields['environement'] );
		$valid_fields['environement'] = strip_tags( stripslashes( $environement ) );
		 
		// Validate Background Color
		$background = trim( $fields['background'] );
		$background = strip_tags( stripslashes( $background ) );
		 
		// Check if is a valid hex color
		if( FALSE === $this->check_color( $background ) ) {
		 
			// Set the error message
			add_settings_error( 'chronogolf_settings_options', 'chronogolf_bg_error', 'Insert a valid color for Background', 'error' ); // $setting, $code, $message, $type
			 
			// Get the previous valid value
			$valid_fields['background'] = $this->options['background'];
		 
		} else {
		 
			$valid_fields['background'] = $background;  
		 
		}
		 
		return apply_filters( 'validate_options', $valid_fields, $fields);
	}
	 
	/**
	 * Function that will display the options page.
	 */
	public function display_page() { 
		?>
		<div class="wrap wrap-chronogolf">
			<h2>ChronoGolf <small>v5.0</small></h2>
			<form method="post" action="options.php" id="form-options">
			<h3>Options</h3>
			<?php 
				settings_fields(__FILE__);      
				do_settings_sections(__FILE__);
				submit_button();
			?>
			</form>

			<?php
				if(isset( $this->options['club']) && !empty($this->options['club']) && ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) )) {
					if(isset($_POST['synchro-event'])){
						if(isset( $this->options['environement']) && $this->options['environement'] == 'demo') :
							$environement = 'demo';
						elseif(isset( $this->options['environement']) && $this->options['environement'] == 'prod') :
							$environement = 'prod';
						else:
							$environement = 'prod';
						endif;

						$types = array(
							// 'social'=>'Events',
							'tournaments'=>'Tournaments'
						);
						if(isset($_POST['datepicker']) && !empty($_POST['datepicker'])){
							$date = explode(' to ',$_POST['datepicker']);
							if(isset($date[0]) && isset($date[1])){
								$date = array(
									'from'=>$date[0],
									'to'=>$date[1]
								);
							}
						} else {
							$oneYearOn = date('Y-m-d',strtotime(date("Y-m-d", mktime()) . " + 365 day"));
							$date = array(
								'from'=> date('Y-m-d'),
								'to'=>$oneYearOn
							);
						}
						echo $this->importElement($types,$date,$environement,$this->options['club']);
					}
				?>

				<form method="post" action="" id="form-sync"> 
					<h3>Import Events</h3>
					<p><?php 
					echo __('Please, save changes before sync.','chrono');
					?></p>

					<input type="text"
						name="datepicker"
						placeholder="Select a range of dates for the import" 
				    	class="datepicker-here"
				    	data-range="true"
				    	data-date-format="yyyy-mm-dd"
						data-multiple-dates-separator=" to "
					/>
					<br />
					<input type="submit" class="button button-primary" name="synchro-event" id="synchro-event" value="Import Events" />
				</form>
				<div class="clear clearfix"></div>

			<?php
				}
			?>

		</div> <!-- /wrap -->
		<?php    
	}

	public function importElement($types,$date,$environement,$clubId){
		if($environement =='demo')
			$getUrl = 'https://demo.chronogolf.com/private_api/events/';
		else
			$getUrl = 'https://www.chronogolf.com/private_api/events/';

		$ret = '';

		foreach ($types as $type=>$value) {
			$response = file_get_contents($getUrl.$type.'?club_id='.$clubId.'&from='.$date['from'].'&to='.$date['to']);
			$response = json_decode($response);
			if($response){
				$headers = $this->parseHeaders($http_response_header);
				$total = $headers['Total'];
				$perPage = $headers['Per-Page'];
				$nbPage = ceil($total / $perPage);
				$ret .= '<div class="updated notice">';
					$ret .= '<h3>'.__($value,'chrono').'</h3>';
					for($i = $nbPage; $i > 0; $i--){
						$response = file_get_contents($getUrl.$type.'?club_id='.$clubId.'&from='.$date['from'].'&to='.$date['to'].'&page='.$i);
						$response = json_decode($response);
						foreach ($response as $events => $event) {
							$content = $event->description ? $event->description : '';
							$addevent = array(
							   'post_title' => $event->name,
							   'post_content' => $content,
							   'post_status' => 'publish',
							   'post_author' => 1,
							   'EventStartDate' => $event->date,
							   'EventEndDate' => $event->date,
							   'EventStartHour' => date('g', strtotime($event->start_time)),
							   'EventStartMinute' => date('i', strtotime($event->start_time)),
							   'EventStartMeridian' => date('a', strtotime($event->start_time)),
							   'EventEndHour' => date('g', strtotime($event->end_time)),
							   'EventEndMinute' => date('i', strtotime($event->end_time)),
							   'EventEndMeridian' => date('a', strtotime($event->end_time)),
							   'meta_input' => array(
							    	'chronoEventId' => $event->id
								)
							);

							// Check if event already exists
							global $wpdb;
							$result = $wpdb->get_results ( "
							    SELECT * 
							    FROM  $wpdb->postmeta
							        WHERE meta_key = 'chronoEventId'
							        AND meta_value = $event->id
							" );
							 if ( empty( $result ) || !$result) {
								 	if(tribe_create_event( $addevent ))
										$ret .= '<p><b>'.$event->name.'</b> added (#'.$event->id.') !</p>';
							 } else {
							 	$ret .= '<p><i>'.$event->name.'</i> already exist (#'.$event->id.') !</p>';
							 }
						}
					}
				$ret .= '</div>';
			} else {
				$ret .= '<div id="" class="error notice">';
				$ret .= 	'<p>'.__('Can\'t import (or not any) '.$value.'.','chrono').'</p>';
				$ret .= '</div>';
			}
		}
		return $ret;
	}


	public function parseHeaders( $headers )
	{
	    $head = array();
	    foreach( $headers as $k=>$v )
	    {
	        $t = explode( ':', $v, 2 );
	        if( isset( $t[1] ) )
	            $head[ trim($t[0]) ] = trim( $t[1] );
	        else
	        {
	            $head[] = $v;
	            if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
	                $head['reponse_code'] = intval($out[1]);
	        }
	    }
	    return $head;
	}

       
    
     
    /**
	 * Function that will add javascript snippet in the footer..
	 */
	public function enqueue_footer_js() { 
		 if(isset( $this->options['club']) && !empty($this->options['club'])) {


				$local = get_locale();				
				$local = str_replace("_", "-", $local);
				$display = true;

				if(isset( $this->options['exclude']) && !empty($this->options['exclude'])){
					global $wp_query;
					$explode = explode(',', $this->options['exclude']);
					if(in_array($wp_query->post->ID,$explode))
						$display = false;
				
				}

				if(isset( $this->options['include']) && !empty($this->options['include'])){
					$display = false;
					global $wp_query;
					$explode = explode(',', $this->options['include']);
					if(in_array($wp_query->post->ID,$explode))
						$display = true;
				}


				if(isset($display) && $display == true) {
			 
					if(isset( $this->options['background']))
						 $background = $this->options['background'];
					else
						 $background = "#4a7234";
			?>
				<!-- Start / Chronogolf Widgets -->
				<div class="chrono-bookingbutton"></div>
				<script>
				  window.chronogolfSettings = {
					"clubId" : <?php echo $this->options['club']; ?>,
					"locale" : "<?php echo $local; ?>"
				  };
				  // Optional
				  window.chronogolfTheme = {
					"color"  : "<?php echo $background ?>"
				  };
				</script>
				<?php 
					if(isset( $this->options['environement']) && $this->options['environement'] == 'prod') :
				?>
				<script>
				  !function(d,i){if(!d.getElementById(i)){var s=d.createElement("script");
				  s.id=i,s.src="https://cdn2.chronogolf.com/widgets/v2";
				  var r=d.getElementsByTagName("script")[0];
				  r.parentNode.insertBefore(s,r)}}(document,"chronogolf-js");
				</script>
				<?php 
					elseif(isset( $this->options['environement']) && $this->options['environement'] == 'demo') :
				?>
				<script>
				  !function(d,i){if(!d.getElementById(i)){var s=d.createElement("script");
				  s.id=i,s.src="http://chronogolf-demo.s3-website-us-east-1.amazonaws.com/widgets/v2";
				  var r=d.getElementsByTagName("script")[0];
				  r.parentNode.insertBefore(s,r)}}(document,"chronogolf-js");
				</script>
				<?php 
					else :
				?>
				<script>
				  !function(d,i){if(!d.getElementById(i)){var s=d.createElement("script");
				  s.id=i,s.src="https://cdn2.chronogolf.com/widgets/v2";
				  var r=d.getElementsByTagName("script")[0];
				  r.parentNode.insertBefore(s,r)}}(document,"chronogolf-js");
				</script>
			<?php
				endif;
				?>
				<!-- End / Chronogolf Widgets -->
				<script>
					(function($) {
					    $(document).ready(function() {
					        $('.open-widget').click(function() {
					       //console.log(‘JS’);
					       $('.chrono-bookingbutton-open').click();
					   });
					    });
					})( jQuery );
				</script>
				<?php
			 }
		 }
	}

	public function head_meta() { 
		if($this->options['itunes_app_id'])
			echo '<meta name="apple-itunes-app" content="app-id='.$this->options['itunes_app_id'].'">';
	}
	 
	/**
	 * Function that will check if value is a valid HEX color.
	 */
	public function check_color( $value ) { 
		 
		if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with #     
			return true;
		}
		 
		return false;
	}
     
    /**
     * Callback function for settings section
     */
    public function display_section() { /* Leave blank */ } 
         
} // end class
  
Chronogolf::get_instance();