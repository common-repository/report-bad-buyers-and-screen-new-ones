<?php
/*
 *
 * This generates the setting page in the admin.
 *
 */
class Ebuyers_Reviewed_Admin_Settings extends Ebuyers_Reviewed_Admin
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        $this->options = get_option( 'ebuyer_opts' );
        
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
    }
    
    /**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( __('Welcome to eBuyersReviewed', 'ebuyers-reviewed'), '', 'manage_options', 'ebuyers-reviewed-setup', array( $this, 'setup_wizard' ) );
	}
	
	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'ebuyers-reviewed-setup' !== $_GET['page'] ) {
			return;
		}
	?>
		
		<div class="main-content">
			<h1><?php _e( 'Welcome to eBuyersReviewed', 'ebuyers-reviewed' ); ?></h1>
			<p><?php _e('Here is how we can help you benefit from our service:', 'ebuyers-reviewed'); ?></p>
			<p><strong><?php _e( 'SCREEN A BUYER', 'ebuyers-reviewed' ); ?></strong></p>
			<div class="iframevideo-box">
			    <div class='embed-container'>
			    	<iframe width="560" height="315" src="https://www.youtube.com/embed/31Ip4MW0pl4" frameborder="0" allowfullscreen></iframe>
				</div>
			</div>
			<p><strong><?php _e( 'REVIEWING A BUYER', 'ebuyers-reviewed' ); ?></strong></p>
			<div class="iframevideo-box">
			    <div class='embed-container'>
			    	<iframe width="560" height="315" src="https://www.youtube.com/embed/YjDpo8M6NOE" frameborder="0" allowfullscreen></iframe>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add options page
	 */
	function add_plugin_page()
	{
		add_menu_page( 'eBuyersReviewed', 'eBuyersReviewed', 'manage_options', 'ebuyers-reviewed-settings', array( $this, 'create_admin_page' ) );
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		add_action( 'admin_enqueue_scripts',  array( $this, 'admin_enqueue_settings_styles' ) );
	}
	
	public function admin_enqueue_settings_styles() {
		wp_enqueue_style( 'ebuyers-reviewed-admin-settings-css', plugin_dir_url( __FILE__ ) . 'css/ebuyers-reviewed-admin-settings.css', array(), 1.0, 'all' );
		
		wp_enqueue_script( 'ebuyers-reviewed-validate-js', plugin_dir_url( __FILE__ ) . 'js/jquery.validate.js', array(), 1.0, '' );
		
		wp_enqueue_script( 'ebuyers-reviewed-custom-js', plugin_dir_url( __FILE__ ) . 'js/custom.js', array(), 1.0, '' );
	}
	
	// Get data through Curl
	
	public function ebuyers_curl($url, $parms, $method_type) {
		$headers = array(
			"Accept: json",
		);
		$ch = curl_init( $url );
		if(($method_type == 'post')) {
			curl_setopt( $ch, CURLOPT_POST, 1);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $parms);
		} 
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
		$response = trim(curl_exec($ch));
		curl_close($ch);
		$response = json_decode($response,true);
		return $response;
	}
	
	// All Froms action

	function create_admin_page() 
	{
		$validation = array();
		$results    = array();
    	// Get all options
    	$ebuyer_opts = get_option( 'ebuyer_opts' );
		// if sign up form is submitted
		
		if((isset($_POST['sign_up'])) && ($_POST['sign_up'] == 'sign_up')) {
			if(isset($_POST['ebuyer_opts']['buyer_first_name']) ) {
				$results['buyer_first_name'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_first_name'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_last_name']) ) {
				$results['buyer_last_name'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_last_name'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_email']) ) {
				$results['buyer_email'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_email'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_company_name']) ) {
				$results['buyer_company_name'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_company_name'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_pass']) ) {
				$results['buyer_pass'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_pass'] );
			}
			if(isset($_POST['ebuyer_opts']['terms_conditions']) ) {
				$results['terms_conditions'] = sanitize_text_field( $_POST['ebuyer_opts']['terms_conditions'] );
			}
			
			// Use Curl for Sign Up
			$url     = 'http://www.ebuyersreviewed.com/api/registration/';
			$parms   = 'first_name='.$results['buyer_first_name'].
						'&last_name='.$results['buyer_last_name'].
						'&email='.$results['buyer_email'].
						'&userpass='.$results['buyer_pass'].
						'&terms='.$results['terms_conditions'].
						'&company='.$results['buyer_company_name'].
						'&source=WooCommerce';

			$response = $this->ebuyers_curl($url, $parms, 'post');
			if(isset($response['api_key'])) {
				
				// If API return API key then save all options and redirect
				$results['api_key'] = $response['api_key'];
				update_option( 'ebuyer_opts', $results);
				wp_redirect( 'admin.php?page=ebuyers-reviewed-setup' );
				exit;
			
			} else {
				// If API return an error
				$validation['status'] = 'error';
				$validation['msg']    = $response['error'];
				
			} // if Update Password form is submitted
		} else if((isset($_POST['update_user'])) && ($_POST['update_user'] == 'update_user')) {
			
			if(isset($_POST['ebuyer_opts']['buyer_first_name']) ) {
				$results['buyer_first_name'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_first_name'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_last_name']) ) {
				$results['buyer_last_name'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_last_name'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_email']) ) {
				$results['buyer_email'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_email'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_company_name']) ) {
				$results['buyer_company_name'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_company_name'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_pass']) ) {
				$results['buyer_pass'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_pass'] );
			}
			
			if(isset($_POST['ebuyer_opts']['new_buyer_pass']) ) {
				$new_buyer_pass = sanitize_text_field( $_POST['ebuyer_opts']['new_buyer_pass'] );
			}
			if(isset($_POST['ebuyer_opts']['confirm_new_buyer_pass']) ) {
				$confirm_new_buyer_pass = sanitize_text_field( $_POST['ebuyer_opts']['confirm_new_buyer_pass'] );
			}
			
			// Use Curl for Change Password
			$url     = 'http://www.ebuyersreviewed.com/api/setProfile/';
			$parms   =  'api_key='.$ebuyer_opts['api_key']. 
						'&first_name='.$results['buyer_first_name'].
						'&last_name=' . $results['buyer_last_name'] .
						'&email=' . $results['buyer_email'] .
						'&old_userpass=' . $results['buyer_pass'] .
						'&userpass=' . $new_buyer_pass .
						'&userpass_confirm=' . $confirm_new_buyer_pass .
						'&company=' . $results['buyer_company_name'];
			$response = $this->ebuyers_curl($url, $parms, 'post');
			
			if($response['msg']) {
				// If API returns success message the update options
				if($new_buyer_pass) {
					$results['buyer_pass'] = $new_buyer_pass;
				} else {
					$results['buyer_pass'] = $ebuyer_opts['buyer_pass'];
				}
				$results['api_key']    = $ebuyer_opts['api_key'];
				update_option( 'ebuyer_opts', $results);
			
				$validation['status'] = 'success';
				$validation['msg']    = $response['msg'];
				
			} else {
				// If API returns error message
				
				$validation['status'] = 'error';
				$validation['msg']    = $response['error'];
				
				$validation_msg['error'] = $response['error'];
			} // if Login form is submitted
				
		} else if((isset($_POST['sign_in'])) && ($_POST['sign_in'] == 'sign_in')) {
			
			if(isset($_POST['ebuyer_opts']['buyer_email']) ) { 
				$results['buyer_email'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_email'] );
			}
			if(isset($_POST['ebuyer_opts']['buyer_pass']) ) {
				$results['buyer_pass'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_pass'] );
			}
			// Use Curl for Sign In
			$url   = 'http://www.ebuyersreviewed.com/api/login/?email='.$results['buyer_email'].'&password='.$results['buyer_pass'];
			$response = $this->ebuyers_curl($url, '', '');
			if(isset($response['api_key'])) {
				
				// If API return API key then update options
				$results['api_key']            = $response['api_key'];
				$results['buyer_first_name']   = $response['data']['first_name'];
				$results['buyer_last_name']    = $response['data']['last_name'];
				$results['buyer_company_name'] = $response['data']['company'];
				
				update_option( 'ebuyer_opts', $results);
				
				$validation['status'] = 'success';
				$validation['msg']    = __('Login Successfully', 'ebuyers-reviewed');
				
			} else {
			
				$validation['status'] = 'error';
				$validation['msg']    = $response['error'];
			} // Forgot Password Form
			
		} else if((isset($_POST['forgot_password'])) && ($_POST['forgot_password'] == 'forgot_password')) {
			
			if(isset($_POST['ebuyer_opts']['buyer_email']) ) { 
				$results['buyer_email'] = sanitize_text_field( $_POST['ebuyer_opts']['buyer_email'] );
			}
			
			// Use Curl for Forgot password 
			$url   = 'http://www.ebuyersreviewed.com/api/forgotPassword/';
			$parms = 'email='.$results['buyer_email'];
			$response = $this->ebuyers_curl($url, $parms, 'post');
			
			if(isset($response['msg'])) {
				// if API return success message then password will be sent by email
				$validation['status'] = 'success';
				$validation['msg']    = $response['msg'];
			} else {
				// if API return error message
				$validation['status'] = 'error';
				$validation['msg']    = $response['error'];
			} // Log Out Form
			
		} else if((isset($_POST['logout'])) && ($_POST['logout'] == 'logout')) {
			
			// Reset all options
			
			update_option( 'ebuyer_opts', '');
			
			$validation['status'] = 'success';
			$validation['msg']    = __('Log Out Successfully', 'ebuyers-reviewed');
		}
		
		// All Forms HTML
		include 'form.php';
	}

}
