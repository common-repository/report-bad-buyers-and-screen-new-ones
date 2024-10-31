<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/includes
 * @author     Your Name <email@example.com>
 */
class Ebuyers_Reviewed {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ebuyers_Reviewed_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The user entered API key
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $version    The current version of the plugin.
	 */
	public $api_key;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'ebuyers-reviewed';
		$this->version = '1.0.0';
		$this->api_key = '';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_setting_hooks();
		$this->define_api_key();

		// Make sure the API key has been entered in the settings before doing anything
		if($this->api_key != '') {
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ebuyers_Reviewed_Loader. Orchestrates the hooks of the plugin.
	 * - Ebuyers_Reviewed_i18n. Defines internationalization functionality.
	 * - Ebuyers_Reviewed_Admin. Defines all hooks for the admin area.
	 * - Ebuyers_Reviewed_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ebuyers-reviewed-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ebuyers-reviewed-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ebuyers-reviewed-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin settings area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ebuyers-reviewed-admin-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ebuyers-reviewed-public.php';

		$this->loader = new Ebuyers_Reviewed_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ebuyers_Reviewed_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ebuyers_Reviewed_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the settings area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_setting_hooks() {
		$plugin_admin_settings = new Ebuyers_Reviewed_Admin_Settings();
		$this->loader->add_action( 'admin_menu', $plugin_admin_settings, 'add_plugin_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin_settings, 'page_init' );
	}

	/**
	 * Before anything, make sure the API key is entered
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_api_key() {
		$settings = get_option('ebuyer_opts');
		if(!isset($settings['api_key']) || $settings['api_key'] == '') {
			//$this->loader->add_action( 'admin_notices', $this, 'give_api_key_notice' );
		} else {
			$this->api_key = $settings['api_key'];
		}
	}

	/**
	 * Give notice if the API key isn't set-up yet
	 *
	 * @since    1.0.0
	 * @access   private
	 */
  public function give_api_key_notice() { ?>
    <div class="notice notice-error is-dismissible">
    	<p><?php _e("You need to add your API key For eBuyersReviewed. You can do that ", "ebuyers-reviewed"); ?> <a href="<?php echo get_admin_url(); ?>/admin.php?page=ebuyers-reviewed-settings"><?php _e("here", "ebuyers-reviewed"); ?></a></p>
    </div>
    <?php
  }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ebuyers_Reviewed_Admin( $this->get_plugin_name(), $this->get_version(), $this->api_key );

		// load scripts and styles
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// add a link to the WP Toolbar
		if(is_admin())
			$this->loader->add_action('admin_bar_menu', $plugin_admin, 'add_admin_menu_items', 999);
		// Add metabox to orders for reviewing customer
		$this->loader->add_action("add_meta_boxes", $plugin_admin, "ebr_add_meta_boxes");
		// add unreviewed users/orders page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_unreviewed_users_page' );
		// Add notice if user has no screenings left
		// $this->loader->add_action( 'admin_notices', $plugin_admin, 'no_screening_notice');
		// Update Customer review status in WP
		$this->loader->add_action( 'wp_ajax_update_customer_review', $plugin_admin, "update_customer_review" );
		// Ignore all button
		$this->loader->add_action( 'wp_ajax_update_all_customer_review', $plugin_admin, "update_all_customer_review" );
		// Set user/order to ignore
		$this->loader->add_action( 'wp_ajax_set_ignore', $plugin_admin, "set_ignore" );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ebuyers_Reviewed_Public( $this->get_plugin_name(), $this->get_version(), $this->api_key );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Update meta data of the order depending on if there is a customer
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'ebuyer_add_meta_data', 10, 2 );
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_public, 'give_user_neutral_review',  15, 1  );
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_public, 'collect_user_reviews',  15, 1  );
		// $this->loader->add_action( 'init', $plugin_public, 'collect_user_reviews',  15, 1  );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ebuyers_Reviewed_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
