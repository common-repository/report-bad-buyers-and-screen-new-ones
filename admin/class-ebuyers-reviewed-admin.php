<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/admin
 * @author     Your Name <email@example.com>
 */
class Ebuyers_Reviewed_Admin {

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
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $api_key    User entered API key
	 */
	public $api_key;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $api_key ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_key = $api_key;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ebuyers-reviewed-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ebuyers-reviewed-review-box.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
	}

	/**
	 * Add menu to admin toolbar
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu_items($wp_admin_bar) {
		$this->add_screening_menu($wp_admin_bar);
		$this->add_bell_menu($wp_admin_bar);
	}

	public function no_screening_notice() {
    
		global $current_screen;

		if($current_screen->base != 'post' && $current_screen->id != 'shop_order')
			return;

		$request = "?api_key=" . $this->api_key;
		$headers = array(
			"Accept: json",
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.ebuyersreviewed.com/api/auth/'.$request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, '30');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = trim(curl_exec($ch));
		curl_close($ch);

		$response = json_decode($response,true);

		if(!isset($response['data']['screenings']))
			return;

		$out_of_screenings = false;
		if($response['data']['screenings'] == 0) {
			$out_of_screenings = true;
		}

		if( $out_of_screenings == true ) {
	    ?>
	    <div class="notice error no-screenings-notice is-dismissible" >
        <p>You have used all your free screenings for this month. To upgrade click <a href="http://www.ebuyersreviewed.com/en/membership/My-Membership.html" target="_blank">here</a>.</p>
	    </div>
	    <?php
	  }

	}

	protected function add_screening_menu($wp_admin_bar) {

		if(!method_exists($wp_admin_bar, 'add_node'))
			return;

		$poor_user_orders = get_posts( array(
			'numberposts' => 20,
			'meta_key'    => 'poor_user_flag',
			'meta_value'  => 1,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
		) );

		global $current_screen;
		global $post;
		$out_of_screenings = false;


		if($current_screen->base == 'post' && $current_screen->id == 'shop_order') {
			$args = array(
				'status' => 'approve',
				'post_id' => $post->ID
			);
			$comments = get_comments($args);
			foreach($comments as $comment) :
				if(strpos($comment->comment_content, 'Ebuyers Screening Error: You have used all your screening credits.') !== false
					|| strpos($comment->comment_content, 'You have used all your free screenings for this month') !== false) {
					$out_of_screenings = true;
				}
			endforeach;
		}
		
		// If it isn't an order page just show a gray icon. I told client they shouldn't show it... but they want it grey...
		if( ($current_screen->base != 'post' && $current_screen->id != 'shop_order') || ($out_of_screenings == true) ) {

			$menu_parent_args = array(
				'id' => 'ebuyer_screened_bell',
				'title' => "<span class='ab-icon dashicons dashicons-lightbulb'></span>",
				'href' => '#', 
				'meta' => array(
					'class' => 'ebuyers_reviewed',
					'title' => 'Screen a Buyer'
			) );
			$wp_admin_bar->add_node($menu_parent_args);

			return;

		}

		$review_summary = get_post_meta($post->ID, 'ebr-review-summary', true);
		$reviews = get_post_meta($post->ID, 'ebr-reviews');
		$review_count = count($reviews);

		// Add class so lightbulb is green or red
		if( isset($review_summary['average_risk_level_value']) && $review_summary['average_risk_level_value'] > 1.5 )
			$class = 'bad';
		else
			$class = 'good';

		// Add partent menu item
		$menu_parent_args = array(
			'id' => 'ebuyer_screened_bell',
			'title' => "<span class='ab-icon dashicons dashicons-lightbulb " . $class . "'></span>",
			'href' => get_admin_url().'post.php?post='.$post->ID.'&action=edit#screening-meta-box', 
			'meta' => array(
				'class' => 'ebuyers_reviewed',
		) );
		$wp_admin_bar->add_node($menu_parent_args);

		if($class == 'good') {
			if(isset($review_summary['summary']) && $review_summary['summary'] != '' && $review_summary['average_risk_level_text'] != 'neutral') {
				$message = 'You are dealing with a low risk buyer!';
				$html = '';
			} else {
				$message = 'No news is Good News!';
				$html = '<p class="good-news">No seller has yet reviewed this buyer.</p>';
			}

			$wp_admin_bar->add_node(array(
				'id' => 'ebuyer_screened_empty', 
				'title' => '<p>' . $message . '</p>',
				'parent' => 'ebuyer_screened_bell',
				'meta' => array(
					'html' => $html,
				)
			));
			return;
		}

		// Create node for bad reviewee
		if($review_count >= 1) {
			$args = array(
				'id' => 'ebuyer_screened_user_'.$post->ID,
				'title' => 'You are dealing with a high risk buyer!',
				'parent' => 'ebuyer_screened_bell',
				'href' => get_admin_url().'post.php?post='.$post->ID.'&action=edit#screening-meta-box', 
				'meta' => array(
					'class' => 'ebuyers_unreviewed_user match-count bad', 
					'title' => 'View customer order',
			) );
			$wp_admin_bar->add_node($args);	
		}

	}

	/**
	 * Add menu to admin toolbar
	 *
	 * @since    1.0.0
	 */
	protected function add_bell_menu($wp_admin_bar) {

		if(!method_exists($wp_admin_bar, 'add_node'))
			return;
		
		$users = $this->unreviewed_ebr_users();
		$customerless_posts = $this->customerless_posts();

		$notification_count = count($users);
		$notification_count += count($customerless_posts);

		if($notification_count == 0)
			$notification_class = 'display-none';
		else
			$notification_class = 'notification-count';

		// Add partent menu item
		$menu_parent_args = array(
			'id' => 'ebuyer_reviewed_bell',
			'title' => "<span class='bell-icon'></span><span class='". $notification_class . "'>".$notification_count."</span>", // Get the unreviewed customer count
			'href' => '#', 
			'meta' => array(
				'class' => 'ebuyers_reviewed', 
				'title' => 'Review a Buyer'
		) );
		$wp_admin_bar->add_node($menu_parent_args);

		// No user fallback
		if($notification_count == 0) {
			$wp_admin_bar->add_node(array('id' => 'ebuyer_reviewed_empty', 'title' => 'There are no buyers to be reviewed.', 'parent' => 'ebuyer_reviewed_bell'));
			return;
		}

		// Create node for each customer
		$most_recent_orders = array();
		foreach($users as $key => $user) {
			$most_recent_orders[] = get_post($user->ebuyer_order_id);
		}
		$all_unreviewed_orders = array_merge($most_recent_orders, $customerless_posts);
		usort($all_unreviewed_orders, array($this, "cmp"));

		// Create a node for each customerless order
		foreach($all_unreviewed_orders as $key => $customerless_post) {
			$order = new WC_Order($customerless_post);

			$args = array(
				'id' => 'ebuyer_reviewed_user_'.$order->id,
				'title' => $order->billing_first_name . ' ' . $order->billing_last_name,
				'parent' => 'ebuyer_reviewed_bell',
				'href' => get_admin_url().'post.php?post='.$order->id.'&action=edit#review-meta-box', 
				'meta' => array(
					'class' => 'ebuyers_unreviewed_user', 
					'title' => 'View customer order',
					'html' => 	'<table style="width:100%">
												<tr>
													<td class="registered">'.$order->order_date.'</td>
													<td id="'.$order->id.'" class="remove ebr-order" data-ebr-order-id="' . $order->id . '"><span class=ab-icon><a href="#" title="ignore notification"></a></span></td>
												</tr>
											</table>',
			) );
			$wp_admin_bar->add_node($args);
		}

		$args = array(
			'id' => 'view-all',
			'title' => 'View All',
			'parent' => 'ebuyer_reviewed_bell',
			'href' => get_admin_url().'admin.php?page=unreviewed-customers-and-orders', 
			'meta' => array(
				'class' => 'halfy ebr-view-all', 
				'title' => 'View all customer orders',
		) );
		$wp_admin_bar->add_node($args);
		$args = array(
			'id' => 'remove-all',
			'title' => 'Remove All',
			'parent' => 'ebuyer_reviewed_bell',
			'href' => '#',
			'meta' => array(
				'class' => 'halfy ebr-remove-all',
				'title' => 'Remove all Notifications',
		) );
		$wp_admin_bar->add_node($args);	
		
	}

	protected function unreviewed_ebr_users() {
		// Get users without reviews
		$args = array(
			'meta_query' 	 => array(
				'relation' => 'OR',
				array(
					'key' => 'ebuyers_reviewed',
					'value' => '',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => 'ebuyers_reviewed',
					'value' => '1',
					'compare' => '!=',
					'type' => 'numeric'
				),
			),
		);
		$users = get_users( $args );

		foreach($users as $key => $user) {

			// Get users's latest order
			$customer_order = get_posts( array(
				'numberposts' => 1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user->data->ID,
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
			) );

			// Remove customers without orders
			if(isset($customer_order[0])) {
				$users[$key]->ebuyer_order_id = $customer_order[0]->ID;
			} else {
				unset($users[$key]);
			}
		}
		return $users;
	}

	protected function customerless_posts() {
		// Get orders with no customer
		$customerless_posts = get_posts( array(
			'numberposts' => 50,
			'meta_key'    => 'ebuyers_reviewed_status',
			'meta_value'  => 0,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
		) );
		return $customerless_posts;
	}

	/**
	 * Add review metabox
	 *
	 * @since    1.0.0
	 */
	public function ebr_add_meta_boxes() {
		global $post;
		add_meta_box("review-meta-box", "eBuyersReviewed", array($this, "review_meta_box_markup"), "shop_order", "normal", "default", null);
		add_filter('postbox_classes_shop_order_review-meta-box',array( $this, 'add_review_classes'));

		$review_summary = get_post_meta($post->ID, 'ebr-review-summary', true);
		$attempted_screen = get_post_meta($post->ID, 'ebr-attempted-screen', true);

		if($review_summary == '' && $attempted_screen != 1) {
			$args = array(
				'status' => 'approve',
				'post_id' => $post->id
			);
			$comments = get_comments($args);
			foreach($comments as $comment) :
				if(strpos($comment->comment_content, 'Ebuyers Screening Error: You have used all your screening credits. To upgrade click') !== false) {
					$plugin_public = new Ebuyers_Reviewed_Public( $this->plugin_name, $this->version, $this->api_key );
					$plugin_public->collect_user_reviews($post->ID);
					update_post_meta($post->ID, 'ebr-attempted-screen', 1);
				}
			endforeach;
			$review_summary = get_post_meta($post->ID, 'ebr-review-summary', true);
			$attempted_screen = get_post_meta($post->ID, 'ebr-attempted-screen', true);
		}

		if(isset($review_summary['average_risk_level_value']) && $review_summary['average_risk_level_value'] != NULL) {
			add_meta_box("screening-meta-box", "Ebuyers Screened", array($this, "screened_meta_box_markup"), "shop_order", "normal", "default", null);
			add_filter('postbox_classes_shop_order_screening-meta-box',array( $this, 'add_screen_classes'));
		}
	}

	public function add_review_classes($classes) {
	    array_push($classes,'ebr-meta-box');
	    return $classes;
	}
	public function add_screen_classes($classes) {
	    array_push($classes,'ebr-meta-box');
	    return $classes;
	}

	/**
	 * Include reivew metabox markup
	 *
	 * @since    1.0.0
	 */
	public function review_meta_box_markup($object) {

		global $woocommerce;
		$order = new WC_Order($object->ID);
		$customer_id = $order->user_id;

		$display_form = true;
		if($order->user_id == '' && get_post_meta($object->ID, 'ebuyers_reviewed_status', true) == 1) 
			$display_form = false;
		if($customer_id != '' && get_user_meta($customer_id, 'ebuyers_reviewed', true) == 1)
			$display_form = false;

		if($display_form) {
			wp_localize_script( $this->plugin_name, 'ajax_review_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'order_id' => $order->id, 'customer_id' => $order->user_id ) );
			include('partials/ebuyers-reviewed-admin-review-meta-box.php');
		} else {
			$review = get_user_meta( $customer_id, 'ebuyers_reviewed_data', true);
			if($review == '')
				$review = get_post_meta( $object->ID , 'ebuyers_reviewed_data', true);
			if($review == '')
				return;
			?>

			<div id="detailed-report-" class="detailed-report-container review-meta-box-detailed-report">
				<div class="detailed-report">
					<div class="detailed-report-header">Your Review</div>
					<div class="detailed-report-col-left">
						<div class="detailed-report-title"><?php echo $review['title']; ?></div>
						<div class="detailed-report-details"><?php echo $review['details']; ?></div>
					</div>
					<div class="detailed-report-col-right">
						<br /><p align="center">Risk Level</p>
						<div class="risk-level-big rlb-<?php echo str_replace('.', '', $review['risk_level']); ?>"></div>
					</div>
					<div class="clear"></div>
				</div>
			</div>

			<?php
		}

	}

	/**
	 * Include screened metabox markup
	 *
	 * @since    1.0.0
	 */
	public function screened_meta_box_markup($object) {
		include('partials/ebuyers-reviewed-admin-screen-meta-box.php');
	}

	public function add_unreviewed_users_page() {
		add_submenu_page( 
				null,
				'Unreviewed Customers and Orders',
				'Unreviewed Customers and Orders',
				'manage_options',
				'unreviewed-customers-and-orders',
				array( $this, 'create_unreviewed_page')
		);
	}

	public function create_unreviewed_page() {

		$output = '<div class="wrap"><h1>Buyers to be reviewed</h1></div>';

		if(isset($_GET['ignore']) && isset($_GET['iid'])) {
			if($_GET['ignore'] == 'c') {
				update_user_meta( $_GET['iid'], 'ebuyers_reviewed', 1 );
			} elseif($_GET['ignore'] == 'o') {
				update_post_meta( $_GET['iid'], 'ebuyers_reviewed_status', 1 );
			}
		}

		$output .= '
		<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr>
					<th id="columnname" class="manage-column column-columnname" scope="col">Actions</th>
					<th id="columnname" class="manage-column column-columnname" scope="col">Customer Name</th>
					<th id="columnname" class="manage-column column-columnname" scope="col">Most Recent Action</th> 
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th class="manage-column column-columnname" scope="col"></th>
					<th class="manage-column column-columnname" scope="col"></th>
					<th class="manage-column column-columnname" scope="col"></th>
				</tr>
			</tfoot>
			<tbody>';

		$unreviewed_ebr_users = $this->unreviewed_ebr_users();
		$customerless_posts = $this->customerless_posts();

		$i = 0;
		foreach($unreviewed_ebr_users as $user) {

			$i++;
			if($i%2 == 0)
				$class = 'alternate';
			else 
				$class = '';

			$output .= '
				<tr class="'.$class.'" valign="top">
						<td class="column-columnname">
								<div class="row-actions">
										<span><a href="'.get_admin_url().'post.php?post='.$user->ebuyer_order_id.'&action=edit#review-meta-box'.'">View</a> |</span>
								</div>
						</td>
						<td class="column-columnname">'.$user->user_nicename.'</td>
						<td class="column-columnname">'.$user->user_registered.'</td>
				</tr>';
		}
		// * Ignore Link
		// <span><a href="'.get_admin_url().'admin.php?page=unreviewed-customers-and-orders&ignore=c&iid='. $user->ID .'">Ignore</a></span> 

		foreach($customerless_posts as $post) {
			
			$i++;
			if($i%2 == 0)
				$class = 'alternate';
			else 
				$class = '';

			$order = new WC_Order($post);
			$output .= '
				<tr class="'.$class.'" valign="top">
						<td class="column-columnname">
								<div class="row-actions">
										<span><a href="'.get_admin_url().'post.php?post='.$order->id.'&action=edit#review-meta-box'.'">View</a> |</span>
								</div>
						</td>
						<td class="column-columnname">'.$order->billing_first_name . ' ' . $order->billing_last_name . '</td>
						<td class="column-columnname">'.$order->order_date.'</td>
				</tr>';
		}
		// * Ignore Link
		// <span><a href="'.get_admin_url().'admin.php?page=unreviewed-customers-and-orders&ignore=o&iid='. $order->id .'">Ignore</a></span>

		if($i == 0) {
			$output .= '
				<tr valign="top">
					<td class="column-columnname">
							<p>All Customers have been Reviewed</p>
					</td>
			</tr>';
		}

		$output .= '</tbody></table>';
		echo $output;
	}

	/**
	 * Ajax call when a customer review is submitted.
	 *
	 * @since    1.0.0
	 */
	public function update_customer_review() {
		
		$ebuyers_reviewed_data = array();
		foreach ($_POST as $key => $post_data) {
			$ebuyers_reviewed_data[$key] = htmlspecialchars($post_data);
		}


		// Mark the customer or order as reviewed
		if(isset($_POST['customer_id']) && $_POST['customer_id'] != '') {
			update_user_meta( $_POST['customer_id'], 'ebuyers_reviewed', 1 );
			update_user_meta( $_POST['customer_id'], 'ebuyers_reviewed_data', $ebuyers_reviewed_data );
		} else {
			update_post_meta( $_POST['order_id'], 'ebuyers_reviewed_status', 1 );
			update_post_meta( $_POST['order_id'], 'ebuyers_reviewed_data', $ebuyers_reviewed_data );
		}

		echo 'truedat';
		wp_die();
	}

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public function update_all_customer_review() {

		$unreviewed_ebr_users = $this->unreviewed_ebr_users();
		$customerless_posts = $this->customerless_posts();

		foreach ($unreviewed_ebr_users as $key => $user) {
			update_user_meta( $user->ID, 'ebuyers_reviewed', 1 );
		}
		foreach ($customerless_posts as $key => $post) {
			update_post_meta( $post->ID, 'ebuyers_reviewed_status', 1 );
		}

		echo 'truedat';
		wp_die();
	}


	private function cmp($a, $b) {
		return $b->post_date - $a->post_date;
	}

}
