<?php
/**
 * Plugin Name: Sell Your Phone
 * Plugin URI: 
 * Description: Easily create a phone resale system on your WordPress site. Allow users to submit device details, get instant quotes, and manage buyback requests seamlessly.
 * Version: 1.0.0
 * Author: PIXELCODE
 * Author URI: https://portfolio-client-y9gw.onrender.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pixelcode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// Define constants for plugin paths
define( 'PIXELCODE_URL', plugin_dir_url( __FILE__ ) );
define( 'PIXELCODE_PATH', plugin_dir_path( __FILE__ ) );


// Include the main plugin class
require_once PIXELCODE_PATH . 'includes/class-pixelcode-main.php';


// Initialize the plugin
function pixelcode_run_plugin() {
    $plugin = new Pixelcode_Main();
    $plugin->run();
}
pixelcode_run_plugin();