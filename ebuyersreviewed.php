<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.ebuyersreviewed.com/
 * @since             1.0.0
 * @package           Ebuyers_Reviewed
 *
 * @wordpress-plugin
 * Plugin Name:       Report Bad Buyers and Screen New Ones
 * Plugin URI:        http://www.ebuyersreviewed.com/
 * Description:       Bad buyers beware! eBuyersReviewed is the world's best place to screen and review online buyers. Here you can report a bad buyer who: made unreasonable demands, gave unfair feedback, misused returns, refused a delivery, disputed charges, or just made your head spin.  When you report a bad buyer, make sure you explain your case in detail, so other sellers can learn from your experience. After you report a bad buyer, or give a good buyer a positive review, you may select that our system notify them of your review. This makes your review even more powerful. Sellers report bad buyers because they believe they have been treated unfairly, often neglected by third parties such as eBay, Amazon, etc., and want to voice themselves.  They also want bad buyers to be held accountable for their actions by bringing publicity to their experiences. Sellers who report a bad buyer with us, and ask us to notify the bad buyer of their report, are able to resolve an issue with that bad buyer more successfully than sellers who report a bad buyer without notification. Join our large pool of sellers to lower your risk selling online and improve your financial performance. Our plugin allows you to screen and review buyers directly from your Wordpress store without going on our website. 
 * Version:           1.0.3
 * Author:            eBuyersReviewed
 * Author URI:        http://ebuyersreviewed.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ebuyers-reviewed
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ebuyers-reviewed-activator.php
 */
function activate_ebuyers_reviewed () {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ebuyers-reviewed-activator.php';
	Ebuyers_Reviewed_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ebuyers-reviewed-deactivator.php
 */
function deactivate_ebuyers_reviewed () {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ebuyers-reviewed-deactivator.php';
	Ebuyers_Reviewed_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ebuyers_reviewed' );
register_deactivation_hook( __FILE__, 'deactivate_ebuyers_reviewed' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ebuyers-reviewed.php';

function add_action_links ( $links ) {
 $mylinks = array(
 '<a href="' . admin_url( 'admin.php?page=ebuyers-reviewed-settings' ) . '">Settings</a>',
 );
return array_merge( $links, $mylinks );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ebuyers_reviewed () {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
  	$plugin = new Ebuyers_Reviewed();
		$plugin->run();
	} else { ?>
	    <div class="notice error no-screenings-notice is-dismissible" >
        <p>Woocommerce is required for EbuyersReviewed Plugin</p>
	    </div>
	<?php }

}
run_ebuyers_reviewed();
