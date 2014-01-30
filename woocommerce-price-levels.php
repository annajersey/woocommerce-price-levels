<?php
/**
 * WooCommerce Price Levels
 *
 * Offer different pricing on the same product to customers based on their user level.
 *
 * @package   woocommerce-price-levels
 * @author    Ethan Piliavin
 * @link      http://angeleswebdesign.com
 * @copyright 2014 Ethan Piliavin
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Price Levels
 * Plugin URI:        http://angeleswebdesign.com
 * Description:       Offer different pricing on the same product to customers based on their user level.
 * Version:           1.0.0
 * Author:            Ethan Piliavin
 * Author URI:        http://angeleswebdesign.com
 * Text Domain:       woocommerce-price-levels
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-woocommerce-price-levels.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'woocommerce-price-levels', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'woocommerce-price-levels', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Woocommerce_Price_Levels', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-plugin-name-admin.php' );
	add_action( 'plugins_loaded', array( 'Woocommerce_Price_Levels_Admin', 'get_instance' ) );

}
