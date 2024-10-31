<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/public
 * @author     Your Name <email@example.com>
 */
class Ebuyers_Reviewed_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $api_key ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_key = $api_key;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ebuyers-reviewed-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ebuyers-reviewed-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add meta data to a new order when it comes in
	 *
	 * @since    1.0.0
	 */
	function ebuyer_add_meta_data( $order_id, $posted ) {
		$order = new WC_Order($order_id);
		// If the post doesn't have a user the post needs to be reviewed,
		// If the post has a customer the customer will be reviewed, not the post.
		if($order->user_id == '')
    	update_post_meta( $order_id, 'ebuyers_reviewed_status', 0 );
    else
    	update_post_meta( $order_id, 'ebuyers_reviewed_status', 1 );
	}

	/**
	 * Give a neutral review when a new order is processed
	 *
	 * @since    1.0.0
	 */
	function give_user_neutral_review($order_id){
		global $woocommerce;
		$order = new WC_Order($order_id);

		$headers = array(
			"Accept: json",
		);

		$postFields = array(
			"data" => json_encode(array(
				"api_key" => $this->api_key,
				"name" =>  $order->get_formatted_billing_full_name(),
				"address" => str_replace('<br/>', " ", $order->billing_address_1 . ' ' . $order->billing_city . ' ' . $order->billing_postcode),
				"country" => $order->billing_country,
				"email" => $order->billing_email,
				"phone" => substr($order->billing_phone, -4),
				"product_category" => "",
				"courier" => "courier_15",
				"other_courier" => "",
				"tracking" => "",
				"risk_level" => "1",
				"summaries" => array("H"),
				"title" => "Transaction Completed",
				"details" => "This buyer has compeleted a transaction but no review was submitted by the seller.",
				"anonymous" => 1,
				"neutral" => 1,
				"notify_buyer" => 0,
			))
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.ebuyersreviewed.com/api/set/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, '30');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		$response = trim(curl_exec($ch));
		curl_close($ch);

		$response = json_decode($response,true);
		
		if(isset($response['error'])) {
			$order->add_order_note('eBuyersReviewed Error: ' . $response['error'] . '.');
		}

	}

	/**
	 * Collect the user's reviews on order and mark the order if poor reviews
	 *
	 * @since    1.0.0
	 */
	function collect_user_reviews($order_id){

		global $woocommerce;
		$order = new WC_Order($order_id);

		$request = "?api_key=".urlencode($this->api_key)."&";
		$request .= "name=".urlencode($order->get_formatted_billing_full_name())."&";
		$request .= "address=".urlencode($order->billing_address_1 . ' ' . $order->billing_city . ' ' . $order->billing_postcode)."&";
		$request .= "email=".urlencode($order->billing_email)."&";
		$request .= "country=".urlencode($order->billing_country)."&";
		$request .= "phone=".urlencode(substr($order->billing_phone, -4));

		$headers = array(
			"Accept: json",
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.ebuyersreviewed.com/api/screen/'.$request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, '30');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = trim(curl_exec($ch));
		curl_close($ch);

		$response = json_decode($response,true);
		if(isset($response['error'])) {
			if( strpos($response['error'], 'You have used all your screening credits.') )
				$order->add_order_note('You have used all your free screenings for this month. To upgrade click <a href="http://www.ebuyersreviewed.com/en/membership/My-Membership.html" target="_blank">here</a>.');
			else
				$order->add_order_note('Ebuyers Screening Error: ' . $response['error'] . '.');
			return;
		}

		$review_summary = array();
		$reviews = array();
		if(isset($response['data']['results'])) {

			$results = $response['data']['results'][0];

			$review_summary = array(
				'name' 										=> $results['name'],
				'address' 								=> $results['address'],
				'country_code'						=> $results['country_code'],
				'country_name'						=> $results['country_name'],
				'average_risk_level_value'=> $results['average_risk_level_value'],
				'average_risk_level_text' => $results['average_risk_level_text'],
				'summary'									=> $results['summary'],
				'suggestion'							=> $results['suggestion'],
				'date'										=> $results['date'],
				'number_reviews'					=> $results['number_reviews']
			);

			foreach($results['items'] as $key => $review) {
				$reviews[] = array(
					'id' 								=>$review['id'],
					'name' 							=>$review['name'],
					'address' 					=>$review['address'],
					'country_code' 			=>$review['country_code'],
					'country_name' 			=>$review['country_name'],
					'email' 						=>$review['email'],
					'phone' 						=>$review['phone'],
					'title' 						=>$review['title'],
					'details' 					=>$review['details'],
					'author' 						=>$review['author'],
					'risk_level_value' 	=>$review['risk_level_value'],
					'risk_level_text' 	=>$review['risk_level_text'],
					'summary' 					=>$review['summary'],
					'verified' 					=>$review['verified'],
					'neutral' 					=>$review['neutral'],
					'date' 							=>$review['date'],
				);
			}

		}
		
		add_post_meta($order->id, 'ebr-review-summary', $review_summary, true);
		add_post_meta($order->id, 'ebr-reviews', $reviews);

	}


}
