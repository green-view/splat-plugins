<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://green-view.nl
 * @since             1.0.0
 * @package           Gv_Splat
 *
 * @wordpress-plugin
 * Plugin Name:       Splat by GreenView
 * Plugin URI:        https://green-view.nl
 * Description:       Plugin to attach SPLAT
 * Version:           1.0.1
 * Author:            GreenView
 * Author URI:        https://green-view.nl/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gv-splat
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
define( 'GV_SPLAT_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gv-splat-activator.php
 */
function activate_gv_splat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gv-splat-activator.php';
	Gv_Splat_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gv-splat-deactivator.php
 */
function deactivate_gv_splat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gv-splat-deactivator.php';
	Gv_Splat_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gv_splat' );
register_deactivation_hook( __FILE__, 'deactivate_gv_splat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gv-splat.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gv_splat() {

	$plugin = new Gv_Splat();
	$plugin->run();

}
run_gv_splat();
