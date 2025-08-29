<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://anasnagati.com
 * @since             1.0.0
 * @package           Gsc
 *
 * @wordpress-plugin
 * Plugin Name:       Google Sheets Connector – Simple Sync for WordPress
 * Plugin URI:        https://most-integration.com
 * Description:       Easily connect your WordPress site to Google Sheets without the hassle of complex setups or API configurations. This plugin lets you send form entries, orders, or custom data straight to your Google Sheets in just a few clicks. Perfect for site owners who want real-time data management without touching code.
 * Version:           1.0.0
 * Author:            Anas nagati
 * Author URI:        https://anasnagati.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gsc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GSC_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gsc-activator.php
 */
function activate_gsc() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-gsc-activator.php';
    Gsc_Activator::activate();

    // Run send_all_forms once at activation
    if ( method_exists( 'Gsc_Activator', 'send_all_forms' ) ) {
        Gsc_Activator::send_all_forms();
    }
}
register_activation_hook( __FILE__, 'activate_gsc' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gsc-deactivator.php
 */
function deactivate_gsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gsc-deactivator.php';
	Gsc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gsc' );
require_once plugin_dir_path( __FILE__ ) . 'includes/class-gsc-activator.php';
register_deactivation_hook( __FILE__, 'deactivate_gsc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gsc.php';

// Autoload (if not using Composer, do manual includes)
require_once plugin_dir_path(__FILE__) . 'includes/class-gsc-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gsc-cf7.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gsc-woo.php';



// Init integrations
new GSC_CF7();
new GSC_CF7();


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gsc() {

	$plugin = new Gsc();
	$plugin->run();

}
run_gsc();
